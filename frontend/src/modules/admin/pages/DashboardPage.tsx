import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useEffect, useRef, useState } from 'react';
import { BookOpen, CalendarCheck, Plus, Bell, FileText, Users } from 'lucide-react';
import academicService from '@services/academicService';
import { useAuth } from '@hooks/useAuth';
import { Spinner } from '@components/ui/Spinner';

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

  const kpis = dash?.kpis ?? [];
  const alerts = dash?.alerts ?? [];
  const notices = dash?.recentNotices ?? [];
  const semesters = dash?.runningSemesters ?? [];
  const sessionLabel = dash?.sessionLabel ?? 'Session';
  const rangeLabel = dash?.rangeLabel ?? null;
  const updatedAt = dash?.updatedAt ?? null;
  const highlight = dash?.highlight ?? null;
  const attChart = dash?.attendanceChartData ?? null;
  const gradeDist = dash?.gradeDistribution ?? null;

  const greeting = dash?.greeting ?? 'Hello';
  const students = kpis.find((k: any) => k.key === 'students')?.value ?? '0';
  const attendanceKpi = kpis.find((k: any) => k.key === 'attendance');
  const passKpi = kpis.find((k: any) => k.key === 'pass');
  const departments = kpis.find((k: any) => k.key === 'departments')?.value ?? '0';

  // Chart.js
  useEffect(() => {
    let destroyed = false;
    (async () => {
      const Chart = (await import('chart.js/auto')).default;
      if (destroyed || !chartRef.current) return;
      if (chartInstance) chartInstance.destroy();

      const labels = attChart?.[period]?.labels ?? [];
      const data = attChart?.[period]?.data ?? [];

      const ctx = chartRef.current.getContext('2d');
      if (!ctx) return;

      const inst = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Attendance %',
            data,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: false, min: 70, max: 100, ticks: { callback: (v: any) => v + '%' } },
          },
        },
      });
      setChartInstance(inst);
    })();
    return () => { destroyed = true; if (chartInstance) { chartInstance.destroy(); } };
  }, [attChart, period]);

  // Grade donut chart
  const donutRef = useRef<HTMLCanvasElement>(null);
  const [donutInstance, setDonutInstance] = useState<any>(null);

  useEffect(() => {
    let destroyed = false;
    (async () => {
      const Chart = (await import('chart.js/auto')).default;
      if (destroyed || !donutRef.current) return;
      if (donutInstance) donutInstance.destroy();

      if (!gradeDist?.hasData) {
        if (donutRef.current?.parentElement) {
          donutRef.current.parentElement.innerHTML =
            '<div class="flex items-center justify-center h-full text-slate-400 text-sm">No grade data available for this period</div>';
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
            backgroundColor: ['rgb(34,197,94)', 'rgb(59,130,246)', 'rgb(168,85,247)', 'rgb(251,146,60)', 'rgb(251,191,36)', 'rgb(239,68,68)'],
            borderWidth: 2,
            borderColor: '#fff',
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'right' as const, labels: { padding: 15, font: { size: 11 } } },
          },
        },
      });
      setDonutInstance(inst);
    })();
    return () => { destroyed = true; if (donutInstance) donutInstance.destroy(); };
  }, [gradeDist]);

  if (isLoading) {
    return <div className="flex h-64 items-center justify-center"><Spinner size="lg" /></div>;
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

  const semColors: Record<string, string> = {
    running: 'bg-emerald-100 text-emerald-700',
    delayed: 'bg-amber-100 text-amber-700',
    completed: 'bg-slate-100 text-slate-600',
  };
  const dotColors: Record<string, string> = {
    running: 'bg-emerald-500',
    delayed: 'bg-amber-500',
    completed: 'bg-slate-400',
  };
  const alertDots: Record<string, string> = {
    danger: 'bg-rose-500', warning: 'bg-amber-500', success: 'bg-emerald-500', info: 'bg-sky-500',
  };

  return (
    <div id="principal-dashboard" className="space-y-6">
      {/* 1. TOP HEADER */}
      <section className="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div className="px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
          <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-4">
            <div>
              <p className="text-[10px] font-semibold uppercase tracking-widest text-slate-400 sm:text-xs">Principal Dashboard</p>
              <h1 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">
                {greeting}, {user?.name ?? 'Admin'}
              </h1>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Link to="/admin/students/create" className="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:px-3.5 sm:py-2 sm:text-xs">
                <Plus className="h-3 w-3 sm:h-3.5 sm:w-3.5" /> Add Student
              </Link>
              <Link to="/admin/notices/create" className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 sm:px-3.5 sm:py-2 sm:text-xs">
                Create Notice
              </Link>
              <Link to="/admin/attendance" className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm transition hover:bg-blue-700 sm:px-3.5 sm:py-2 sm:text-xs">
                <CalendarCheck className="h-3 w-3 sm:h-3.5 sm:w-3.5" /> Attendance Overview
              </Link>
            </div>
          </div>
          <div className="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 sm:mt-4 sm:gap-3">
            <div className="flex items-center gap-2 rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 sm:px-3 sm:py-1.5 sm:text-xs">
              <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
              <span>{sessionLabel}</span>
            </div>
            {semesters.map((sem: any) => (
              <span key={sem.number} className={`inline-flex items-center gap-1.5 rounded-lg ${semColors[sem.status] ?? 'bg-slate-100 text-slate-600'} px-2 py-0.5 text-[10px] font-semibold sm:px-2.5 sm:py-1 sm:text-[11px]`}>
                <span className={`h-1.5 w-1.5 rounded-full ${dotColors[sem.status] ?? 'bg-slate-400'}`} />
                Sem {sem.number}
              </span>
            ))}
            <div className="flex w-full flex-col gap-1 text-[11px] text-slate-500 sm:w-auto sm:flex-row sm:items-center sm:gap-2 sm:text-xs sm:ml-auto">
              {rangeLabel && <span>{rangeLabel}</span>}
              {rangeLabel && <span className="hidden text-slate-300 sm:inline">|</span>}
              <span>{updatedAt}</span>
            </div>
          </div>
        </div>
      </section>

      {/* 2. STATS CARDS */}
      <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-slate-500">Total Active Users</p>
              <p className="mt-1 text-3xl font-bold text-slate-900">{students}</p>
              <p className="mt-1 text-xs text-slate-400">Students + Teachers + Parents</p>
            </div>
            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
              <Users className="h-6 w-6 text-blue-600" />
            </div>
          </div>
        </div>
        <Link to="/admin/attendance" className="block rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-slate-500">Attendance Rate</p>
              <p className="mt-1 text-3xl font-bold text-slate-900">{attendanceKpi ? `${attendanceKpi.value}${attendanceKpi.suffix ?? ''}` : '--%'}</p>
              <p className="mt-1 text-xs text-slate-400">{attendanceKpi?.note ?? 'No data available'}</p>
            </div>
            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50">
              <CalendarCheck className="h-6 w-6 text-emerald-600" />
            </div>
          </div>
        </Link>
        <Link to="/admin/exams" className="block rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-slate-500">Pass Rate</p>
              <p className="mt-1 text-3xl font-bold text-slate-900">{passKpi ? `${passKpi.value}${passKpi.suffix ?? ''}` : '--%'}</p>
              <p className="mt-1 text-xs text-slate-400">{passKpi?.note ?? 'No data available'}</p>
            </div>
            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-violet-50">
              <FileText className="h-6 w-6 text-violet-600" />
            </div>
          </div>
        </Link>
        <Link to="/admin/departments" className="block rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-slate-500">Total Departments</p>
              <p className="mt-1 text-3xl font-bold text-slate-900">{departments}</p>
              <p className="mt-1 text-xs text-slate-400">Active departments</p>
            </div>
            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50">
              <BookOpen className="h-6 w-6 text-amber-600" />
            </div>
          </div>
        </Link>
      </section>

      {/* 3. MAIN ANALYTICS */}
      <section className="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_320px]">
        <div className="space-y-5">
          {/* Attendance Trend Chart */}
          <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 className="text-sm font-semibold text-slate-900">Attendance Trend</h2>
                <p className="text-xs text-slate-500">Daily attendance percentage over time</p>
              </div>
              <div className="flex items-center gap-2">
                {['7', '30'].map((p) => (
                  <button key={p} onClick={() => setPeriod(p)}
                    className={`px-3 py-1 text-xs font-semibold rounded-md transition ${period === p ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50'}`}>
                    {p === '7' ? '7 Days' : '30 Days'}
                  </button>
                ))}
              </div>
            </div>
            <div className="h-[200px] sm:h-[250px]">
              <canvas ref={chartRef} />
            </div>
          </div>

          {/* Grade Distribution Donut */}
          <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div className="mb-4 flex items-center justify-between">
              <div>
                <h2 className="text-sm font-semibold text-slate-900">Grade Distribution</h2>
                <p className="text-xs text-slate-500">Student performance breakdown by grade</p>
              </div>
            </div>
            <div className="h-[200px] sm:h-[250px]">
              <canvas ref={donutRef} />
            </div>
          </div>
        </div>

        <div className="space-y-5">
          {/* Notices Panel */}
          <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div className="border-b border-slate-100 px-4 py-3">
              <h2 className="text-sm font-semibold text-slate-900">Notices & Updates</h2>
            </div>
            {notices.length > 0 ? (
              <div className="max-h-[400px] divide-y divide-slate-100 overflow-y-auto sm:max-h-[500px]">
                {notices.map((n: any, i: number) => {
                  const dp = n.date ? new Date(n.date) : null;
                  const day = dp ? dp.getDate() : '';
                  const mon = dp ? dp.toLocaleString('default', { month: 'short' }) : '';
                  const yr = dp ? dp.getFullYear() : '';
                  return (
                    <div key={i} className="flex items-start gap-2.5 px-4 py-3 transition hover:bg-slate-50">
                      <div className="flex h-9 w-9 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <span className="text-[7px] font-semibold leading-none">{yr}</span>
                        <span className="text-xs font-bold leading-none">{day}</span>
                        <span className="text-[6px] font-semibold uppercase leading-none">{mon}</span>
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className="line-clamp-2 text-xs font-medium text-slate-900">{n.title}</p>
                        <p className="mt-0.5 text-[10px] text-slate-500">{n.date}</p>
                      </div>
                    </div>
                  );
                })}
                <div className="bg-slate-50 px-4 py-2.5">
                  <Link to="/admin/notices" className="text-xs font-semibold text-blue-600 hover:text-blue-700">View all →</Link>
                </div>
              </div>
            ) : (
              <div className="divide-y divide-slate-100">
                <div className="py-8 text-center">
                  <Bell className="mx-auto h-8 w-8 text-slate-300" />
                  <p className="mt-2 text-xs text-slate-400">No recent notices.</p>
                </div>
              </div>
            )}
          </div>

          {/* Community Stats */}
          <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p className="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Community</p>
            <div className="mt-3 grid grid-cols-3 gap-2">
              <div className="text-center">
                <p className="text-base font-bold text-slate-900">{highlight?.quickStats?.teachers ?? '--'}</p>
                <p className="text-[9px] text-slate-500">Teachers</p>
              </div>
              <div className="text-center">
                <p className="text-base font-bold text-slate-900">{highlight?.quickStats?.parents ?? '--'}</p>
                <p className="text-[9px] text-slate-500">Parents</p>
              </div>
              <div className="text-center">
                <p className="text-base font-bold text-slate-900">{highlight?.quickStats?.alumni ?? '--'}</p>
                <p className="text-[9px] text-slate-500">Alumni</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 4. ALERTS */}
      <section className="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
          <div>
            <h2 className="text-sm font-semibold text-slate-900">Insights & Alerts</h2>
            <p className="text-xs text-slate-500">Important notifications and observations</p>
          </div>
        </div>
        <div className="divide-y divide-slate-100 px-5">
          {alerts.length > 0 ? alerts.map((a: any, i: number) => (
            <div key={i} className="flex items-start gap-3 py-3.5">
              <div className={`mt-0.5 h-2 w-2 shrink-0 rounded-full ${alertDots[a.tone] ?? 'bg-sky-500'}`} />
              <div className="min-w-0 flex-1">
                <p className="text-sm font-semibold text-slate-900">{a.title}</p>
                <p className="mt-0.5 text-xs text-slate-500">{a.message}</p>
              </div>
              {a.actionHref && (
                <a href={a.actionHref} className="shrink-0 rounded-md border border-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 sm:px-2.5 sm:py-1 sm:text-[11px]">
                  {a.actionLabel ?? 'View'}
                </a>
              )}
            </div>
          )) : (
            <div className="flex items-start gap-3 py-3.5">
              <div className="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500" />
              <div className="min-w-0 flex-1">
                <p className="text-sm font-semibold text-slate-900">Operations are stable</p>
                <p className="mt-0.5 text-xs text-slate-500">No critical alerts detected for the selected period.</p>
              </div>
              <Link to="/admin/audit-logs" className="shrink-0 rounded-md border border-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 sm:px-2.5 sm:py-1 sm:text-[11px]">
                View Reports
              </Link>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
