import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useEffect, useRef, useState } from 'react';
import {
  BookOpen, CalendarCheck, Plus, Bell, FileText, Users,
  TrendingUp, AlertCircle, CheckCircle2, Info,
} from 'lucide-react';
import academicService from '@services/academicService';
import { useAuth } from '@hooks/useAuth';
import { Spinner } from '@components/ui/Spinner';
import { adToBS, formatBS, BS_MONTHS_SHORT } from '@shared/utils/nepaliDate';

/* ─── helpers ─────────────────────────────────────────────────────────── */
const alertIcon: Record<string, React.ReactNode> = {
  danger:  <AlertCircle className="h-4 w-4 text-rose-500" />,
  warning: <AlertCircle className="h-4 w-4 text-amber-500" />,
  success: <CheckCircle2 className="h-4 w-4 text-emerald-500" />,
  info:    <Info className="h-4 w-4 text-blue-500" />,
};
const alertBg: Record<string, string> = {
  danger:  'border-rose-200 bg-rose-50',
  warning: 'border-amber-200 bg-amber-50',
  success: 'border-emerald-200 bg-emerald-50',
  info:    'border-blue-200 bg-blue-50',
};

/* ─── KPI card ─────────────────────────────────────────────────────────── */
function KpiCard({
  label, value, sub, icon, color, href,
}: {
  label: string; value: string; sub?: string;
  icon: React.ReactNode; color: string; href?: string;
}) {
  const inner = (
    <div className="flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
      <div className="min-w-0">
        <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">{label}</p>
        <p className="mt-1.5 text-3xl font-bold text-slate-900">{value}</p>
        {sub && <p className="mt-1 text-xs text-slate-400">{sub}</p>}
      </div>
      <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-lg ${color}`}>
        {icon}
      </div>
    </div>
  );
  return href ? <Link to={href} className="block">{inner}</Link> : <div>{inner}</div>;
}

/* ─── Section header ───────────────────────────────────────────────────── */
function SectionHeader({ title, sub, action }: { title: string; sub?: string; action?: React.ReactNode }) {
  return (
    <div className="mb-4 flex items-center justify-between">
      <div>
        <h2 className="text-sm font-bold text-slate-800">{title}</h2>
        {sub && <p className="mt-0.5 text-xs text-slate-500">{sub}</p>}
      </div>
      {action}
    </div>
  );
}

/* ─── Page ─────────────────────────────────────────────────────────────── */
export default function DashboardPage() {
  const { user } = useAuth();
  const { data: dash, isLoading, error } = useQuery({
    queryKey: ['admin-dashboard'],
    queryFn: academicService.adminDashboard,
    staleTime: 60_000,
  });

  const chartRef = useRef<HTMLCanvasElement>(null);
  const [chartInstance, setChartInstance] = useState<any>(null);
  const [period, setPeriod] = useState('7');

  const donutRef = useRef<HTMLCanvasElement>(null);
  const [donutInstance, setDonutInstance] = useState<any>(null);

  const kpis        = dash?.kpis ?? [];
  const alerts      = dash?.alerts ?? [];
  const notices     = dash?.recentNotices ?? [];
  const semesters   = dash?.runningSemesters ?? [];
  const sessionLabel = dash?.sessionLabel ?? 'Session';
  const updatedAt   = dash?.updatedAt ?? null;
  const highlight   = dash?.highlight ?? null;
  const attChart    = dash?.attendanceChartData ?? null;
  const gradeDist   = dash?.gradeDistribution ?? null;
  const greeting    = dash?.greeting ?? 'Hello';

  const students      = kpis.find((k: any) => k.key === 'students')?.value ?? '0';
  const attendanceKpi = kpis.find((k: any) => k.key === 'attendance');
  const passKpi       = kpis.find((k: any) => k.key === 'pass');
  const departments   = kpis.find((k: any) => k.key === 'departments')?.value ?? '0';

  /* Attendance line chart */
  useEffect(() => {
    let destroyed = false;
    (async () => {
      const Chart = (await import('chart.js/auto')).default;
      if (destroyed || !chartRef.current) return;
      if (chartInstance) chartInstance.destroy();
      const labels = (attChart?.[period]?.labels ?? []).map((l: string) => {
        try {
          const d = new Date(l);
          if (isNaN(d.getTime())) return l;
          const bs = adToBS(d);
          return `${BS_MONTHS_SHORT[bs.month - 1]} ${bs.day}`;
        } catch { return l; }
      });
      const data   = attChart?.[period]?.data ?? [];
      const ctx    = chartRef.current.getContext('2d');
      if (!ctx) return;
      const inst = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Attendance %',
            data,
            borderColor: '#1a56db',
            backgroundColor: 'rgba(26,86,219,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 5,
            borderWidth: 2,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: {
              beginAtZero: false, min: 70, max: 100,
              ticks: { callback: (v: any) => v + '%', font: { size: 11 } },
              grid: { color: 'rgba(0,0,0,0.05)' },
            },
            x: { ticks: { font: { size: 11 } }, grid: { display: false } },
          },
        },
      });
      setChartInstance(inst);
    })();
    return () => { destroyed = true; if (chartInstance) chartInstance.destroy(); };
  }, [attChart, period]);

  /* Grade donut chart */
  useEffect(() => {
    let destroyed = false;
    (async () => {
      const Chart = (await import('chart.js/auto')).default;
      if (destroyed || !donutRef.current) return;
      if (donutInstance) donutInstance.destroy();
      if (!gradeDist?.hasData) {
        if (donutRef.current?.parentElement) {
          donutRef.current.parentElement.innerHTML =
            '<div class="flex h-full items-center justify-center text-sm text-slate-400">No grade data available</div>';
        }
        return;
      }
      const ctx = donutRef.current.getContext('2d');
      if (!ctx) return;
      const inst = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: gradeDist.labels,
          datasets: [{
            data: gradeDist.data,
            backgroundColor: ['#1a56db','#0e9f6e','#7e3af2','#ff5a1f','#faca15','#e02424'],
            borderWidth: 2,
            borderColor: '#fff',
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'right' as const, labels: { padding: 14, font: { size: 11 } } },
          },
        },
      });
      setDonutInstance(inst);
    })();
    return () => { destroyed = true; if (donutInstance) donutInstance.destroy(); };
  }, [gradeDist]);

  if (isLoading) {
    return (
      <div className="flex h-64 items-center justify-center">
        <Spinner size="lg" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex h-64 items-center justify-center">
        <div className="text-center">
          <p className="text-sm text-slate-500">Could not load dashboard data.</p>
          <p className="mt-1 text-xs text-slate-400">Please try refreshing the page.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-w-0 space-y-6">

      {/* ── 1. Page Header ─────────────────────────────────────────────── */}
      <div className="rounded-xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p className="text-[10px] font-bold uppercase tracking-widest text-blue-600">
              Principal Dashboard
            </p>
            <h1 className="mt-1 text-2xl font-bold text-slate-900">
              {greeting}, {user?.name ?? 'Admin'}
            </h1>
            <div className="mt-2 flex flex-wrap items-center gap-2">
              <span className="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                <span className="h-1.5 w-1.5 rounded-full bg-blue-500" />
                {sessionLabel}
              </span>
              {semesters.map((sem: any) => (
                <span
                  key={sem.number}
                  className={`inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[11px] font-semibold ${
                    sem.status === 'running'
                      ? 'bg-emerald-50 text-emerald-700'
                      : sem.status === 'delayed'
                      ? 'bg-amber-50 text-amber-700'
                      : 'bg-slate-100 text-slate-600'
                  }`}
                >
                  <span className={`h-1.5 w-1.5 rounded-full ${
                    sem.status === 'running' ? 'bg-emerald-500' : sem.status === 'delayed' ? 'bg-amber-500' : 'bg-slate-400'
                  }`} />
                  Semester {sem.number}
                </span>
              ))}
              {updatedAt && (
                <span className="text-[11px] text-slate-400">
                  Updated: {(() => {
                    try {
                      const d = new Date(updatedAt);
                      if (isNaN(d.getTime())) return updatedAt;
                      const bs = adToBS(d);
                      return `${formatBS(bs, 'Y, F d')} ${d.toLocaleTimeString('en', { hour: '2-digit', minute: '2-digit' })}`;
                    } catch { return updatedAt; }
                  })()}
                </span>
              )}
            </div>
          </div>

          {/* Quick actions */}
          <div className="flex flex-wrap items-center gap-2">
            <Link
              to="/admin/students/create"
              className="inline-flex items-center gap-1.5 rounded-lg bg-[#0f2d5e] px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#1a3f7a]"
            >
              <Plus className="h-3.5 w-3.5" />
              Add Student
            </Link>
            <Link
              to="/admin/notices/create"
              className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
              <Bell className="h-3.5 w-3.5" />
              Create Notice
            </Link>
            <Link
              to="/admin/attendance"
              className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700"
            >
              <CalendarCheck className="h-3.5 w-3.5" />
              Attendance
            </Link>
          </div>
        </div>
      </div>

      {/* ── 2. KPI Cards ───────────────────────────────────────────────── */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <KpiCard
          label="Total Active Users"
          value={students}
          sub="Students · Teachers · Parents"
          icon={<Users className="h-5 w-5 text-blue-600" />}
          color="bg-blue-50"
        />
        <KpiCard
          label="Attendance Rate"
          value={attendanceKpi ? `${attendanceKpi.value}${attendanceKpi.suffix ?? ''}` : '--%'}
          sub={attendanceKpi?.note ?? 'No data available'}
          icon={<CalendarCheck className="h-5 w-5 text-emerald-600" />}
          color="bg-emerald-50"
          href="/admin/attendance"
        />
        <KpiCard
          label="Pass Rate"
          value={passKpi ? `${passKpi.value}${passKpi.suffix ?? ''}` : '--%'}
          sub={passKpi?.note ?? 'No data available'}
          icon={<TrendingUp className="h-5 w-5 text-violet-600" />}
          color="bg-violet-50"
          href="/admin/exams"
        />
        <KpiCard
          label="Departments"
          value={departments}
          sub="Active departments"
          icon={<BookOpen className="h-5 w-5 text-amber-600" />}
          color="bg-amber-50"
          href="/admin/departments"
        />
      </div>

      {/* ── 3. Analytics + Sidebar ─────────────────────────────────────── */}
      <div className="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_280px]">

        {/* Charts column */}
        <div className="min-w-0 space-y-5">

          {/* Attendance Trend */}
          <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <SectionHeader
              title="Attendance Trend"
              sub="Daily attendance percentage over time"
              action={
                <div className="flex items-center gap-1 rounded-lg border border-slate-200 p-0.5">
                  {['7', '30'].map((p) => (
                    <button
                      key={p}
                      onClick={() => setPeriod(p)}
                      className={`rounded-md px-3 py-1 text-xs font-semibold transition ${
                        period === p
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'text-slate-600 hover:bg-slate-100'
                      }`}
                    >
                      {p === '7' ? '7 Days' : '30 Days'}
                    </button>
                  ))}
                </div>
              }
            />
            <div className="h-52 w-full">
              <canvas ref={chartRef} />
            </div>
          </div>

          {/* Grade Distribution */}
          <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <SectionHeader
              title="Grade Distribution"
              sub="Student performance breakdown by grade"
            />
            <div className="h-52 w-full">
              <canvas ref={donutRef} />
            </div>
          </div>
        </div>

        {/* Right sidebar column — stacks below charts on small/medium, side-by-side on xl+ */}
        <div className="min-w-0 space-y-5">

          {/* Notices */}
          <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
              <h2 className="text-sm font-bold text-slate-800">Recent Notices</h2>
              <Link to="/admin/notices" className="text-xs font-semibold text-blue-600 hover:text-blue-700">
                View all →
              </Link>
            </div>
            {notices.length > 0 ? (
              <div className="max-h-72 divide-y divide-slate-100 overflow-y-auto">
                {notices.map((n: any, i: number) => {
                  const dp = n.date ? new Date(n.date) : null;
                  const bs = dp && !isNaN(dp.getTime()) ? (() => { try { return adToBS(dp); } catch { return null; } })() : null;
                  const day = bs ? String(bs.day) : (dp ? dp.getDate() : '');
                  const mon = bs ? BS_MONTHS_SHORT[bs.month - 1] : (dp ? dp.toLocaleString('default', { month: 'short' }) : '');
                  const dateLabel = bs ? formatBS(bs, 'Y, F d') : (n.date ?? '');
                  return (
                    <div key={i} className="flex items-start gap-3 px-4 py-3 transition hover:bg-slate-50">
                      <div className="flex h-9 w-9 shrink-0 flex-col items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                        <span className="text-[11px] font-bold leading-none">{day}</span>
                        <span className="text-[9px] font-semibold uppercase leading-none">{mon}</span>
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className="line-clamp-2 text-xs font-medium text-slate-800">{n.title}</p>
                        <p className="mt-0.5 text-[10px] text-slate-400">{dateLabel}</p>
                      </div>
                    </div>
                  );
                })}
              </div>
            ) : (
              <div className="py-8 text-center">
                <Bell className="mx-auto h-7 w-7 text-slate-300" />
                <p className="mt-2 text-xs text-slate-400">No recent notices.</p>
              </div>
            )}
          </div>

          {/* Community Stats */}
          <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p className="mb-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
              Community Overview
            </p>
            <div className="grid grid-cols-3 gap-3">
              {[
                { label: 'Teachers', value: highlight?.quickStats?.teachers ?? '--' },
                { label: 'Parents',  value: highlight?.quickStats?.parents  ?? '--' },
                { label: 'Alumni',   value: highlight?.quickStats?.alumni   ?? '--' },
              ].map((s) => (
                <div key={s.label} className="rounded-lg bg-slate-50 p-3 text-center">
                  <p className="text-lg font-bold text-slate-900">{s.value}</p>
                  <p className="text-[10px] text-slate-500">{s.label}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Quick Links */}
          <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p className="mb-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
              Quick Access
            </p>
            <div className="space-y-1">
              {[
                { label: 'Manage Students',  href: '/admin/students',   icon: <Users className="h-3.5 w-3.5" /> },
                { label: 'Exam Results',     href: '/admin/exams',      icon: <FileText className="h-3.5 w-3.5" /> },
                { label: 'Attendance Log',   href: '/admin/attendance', icon: <CalendarCheck className="h-3.5 w-3.5" /> },
                { label: 'Departments',      href: '/admin/departments',icon: <BookOpen className="h-3.5 w-3.5" /> },
              ].map((l) => (
                <Link
                  key={l.href}
                  to={l.href}
                  className="flex items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] font-medium text-slate-700 transition hover:bg-blue-50 hover:text-blue-700"
                >
                  <span className="text-slate-400">{l.icon}</span>
                  {l.label}
                </Link>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* ── 4. Alerts & Insights ───────────────────────────────────────── */}
      <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div className="border-b border-slate-100 px-5 py-4">
          <h2 className="text-sm font-bold text-slate-800">Insights & Alerts</h2>
          <p className="mt-0.5 text-xs text-slate-500">Important notifications and system observations</p>
        </div>
        <div className="divide-y divide-slate-100 px-5">
          {alerts.length > 0 ? (
            alerts.map((a: any, i: number) => (
              <div key={i} className={`my-3 flex items-start gap-3 rounded-lg border p-3.5 ${alertBg[a.tone] ?? 'border-blue-200 bg-blue-50'}`}>
                <span className="mt-0.5 shrink-0">{alertIcon[a.tone] ?? alertIcon.info}</span>
                <div className="min-w-0 flex-1">
                  <p className="text-sm font-semibold text-slate-900">{a.title}</p>
                  <p className="mt-0.5 text-xs text-slate-600">{a.message}</p>
                </div>
                {a.actionHref && (
                  <a
                    href={a.actionHref}
                    className="shrink-0 rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50"
                  >
                    {a.actionLabel ?? 'View'}
                  </a>
                )}
              </div>
            ))
          ) : (
            <div className="my-3 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3.5">
              <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
              <div className="min-w-0 flex-1">
                <p className="text-sm font-semibold text-slate-900">Operations are stable</p>
                <p className="mt-0.5 text-xs text-slate-600">No critical alerts detected for the selected period.</p>
              </div>
              <Link
                to="/admin/audit-logs"
                className="shrink-0 rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50"
              >
                View Reports
              </Link>
            </div>
          )}
        </div>
      </div>

    </div>
  );
}
