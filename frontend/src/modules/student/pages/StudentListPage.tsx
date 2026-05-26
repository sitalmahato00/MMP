import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Plus, Download, Search, LayoutList, LayoutGrid, Users, GraduationCap, Calendar, Trophy } from 'lucide-react';
import studentService, { type StudentFilters } from '@services/studentService';
import academicService from '@services/academicService';
import { BsDate } from '@components/ui/BsDate';
import { Pagination } from '@components/ui/Pagination';
import { StudentDrawer } from '../components/StudentDrawer';
import toast from 'react-hot-toast';
import { clsx } from 'clsx';
import type { Student } from '@/types';

const STATUS_MAP: Record<string, { label: string; cls: string }> = {
  active:    { label: 'Active',    cls: 'bg-blue-50 text-blue-700' },
  inactive:  { label: 'Inactive',  cls: 'bg-slate-100 text-slate-600' },
  graduated: { label: 'Alumni',    cls: 'bg-emerald-50 text-emerald-700' },
  suspended: { label: 'Suspended', cls: 'bg-amber-50 text-amber-700' },
  dropped:   { label: 'Dropped',   cls: 'bg-red-50 text-red-700' },
};

const GRADIENTS = [
  'from-blue-500 to-indigo-600','from-violet-500 to-purple-600',
  'from-emerald-500 to-teal-600','from-amber-500 to-orange-600',
  'from-rose-500 to-pink-600','from-cyan-500 to-sky-600',
];

function Avatar({ student, size = 9 }: { student: Student; size?: number }) {
  const cls = `h-${size} w-${size} flex-shrink-0 rounded-xl object-cover ring-2 ring-white shadow-sm`;
  if (student.user.avatar)
    return <img src={student.user.avatar} alt={student.user.name} className={cls} />;
  const grad = GRADIENTS[student.id % 6];
  return (
    <div className={`flex h-${size} w-${size} flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${grad} text-sm font-black text-white shadow-sm`}>
      {student.user.name.charAt(0).toUpperCase()}
    </div>
  );
}

export default function StudentListPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [filters, setFilters] = useState<StudentFilters>({ page: 1, per_page: 20 });
  const [view, setView] = useState<'table' | 'cards'>(() =>
    (localStorage.getItem('mmp_students_view') as 'table' | 'cards') ?? 'table'
  );
  const [selected, setSelected] = useState<number[]>([]);
  const [drawerStudentId, setDrawerStudentId] = useState<number | null>(null);

  const { data, isLoading } = useQuery({
    queryKey: ['students', filters],
    queryFn: () => studentService.list(filters),
  });
  const { data: deptsRes }    = useQuery({ queryKey: ['departments'], queryFn: academicService.departments, staleTime: Infinity });
  const { data: programsRes } = useQuery({ queryKey: ['programs'],    queryFn: () => academicService.programs(), staleTime: Infinity });

  const deleteMutation = useMutation({
    mutationFn: studentService.destroy,
    onSuccess: () => { toast.success('Student deleted.'); queryClient.invalidateQueries({ queryKey: ['students'] }); },
    onError: () => toast.error('Failed to delete student.'),
  });

  const students = data?.data?.data ?? [];
  const meta     = data?.data?.meta;

  const kpis = [
    { label: 'Total Students',  value: meta?.total ?? 0,  icon: <Users className="h-5 w-5 text-blue-600" />,    color: 'bg-blue-50',    tag: 'Total' },
    { label: 'Active Students', value: students.filter(s => s.status === 'active').length, icon: <GraduationCap className="h-5 w-5 text-green-600" />, color: 'bg-green-50', tag: 'Active' },
    { label: 'This Session',    value: students.filter(s => s.status === 'active').length, icon: <Calendar className="h-5 w-5 text-violet-600" />,     color: 'bg-violet-50', tag: 'New' },
    { label: 'Alumni',          value: students.filter(s => s.status === 'graduated').length, icon: <Trophy className="h-5 w-5 text-amber-600" />,    color: 'bg-amber-50',  tag: 'Graduated' },
  ];

  function setFilter(key: keyof StudentFilters, value: string | number | undefined) {
    setFilters(p => ({ ...p, [key]: value || undefined, page: 1 }));
  }

  function toggleView(v: 'table' | 'cards') {
    setView(v); localStorage.setItem('mmp_students_view', v);
  }

  function toggleAll() {
    setSelected(s => s.length === students.length ? [] : students.map(s => s.id));
  }

  async function handleExport() {
    try {
      const blob = await studentService.exportCsv(filters);
      const url = URL.createObjectURL(blob as Blob);
      const a = document.createElement('a'); a.href = url; a.download = 'students.csv'; a.click();
      URL.revokeObjectURL(url);
    } catch { toast.error('Export failed.'); }
  }

  function confirmDelete(student: Student) {
    if (confirm(`Delete ${student.user.name}? This cannot be undone.`))
      deleteMutation.mutate(student.id);
  }

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Students</h1>
          <p className="mt-0.5 text-sm text-slate-500">Manage and monitor all students across departments and semesters</p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <button onClick={handleExport} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <Download className="h-4 w-4" /> Export
          </button>
          <Link to="create" className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
            <Plus className="h-4 w-4" /> Add Student
          </Link>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {kpis.map(k => (
          <div key={k.label} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between">
              <div className={`flex h-10 w-10 items-center justify-center rounded-xl ${k.color}`}>{k.icon}</div>
              <span className={`rounded-full ${k.color} px-2 py-0.5 text-[11px] font-bold text-blue-700`}>{k.tag}</span>
            </div>
            <p className="mt-3 text-3xl font-black text-slate-900">{k.value}</p>
            <p className="mt-0.5 text-xs text-slate-500">{k.label}</p>
          </div>
        ))}
      </div>

      {/* Filters */}
      <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
          <div className="relative xl:col-span-2">
            <Search className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <input type="search" placeholder="Search name, ID, email…" value={filters.search ?? ''}
              onChange={e => setFilter('search', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>
          <select value={filters.department_id ?? ''} onChange={e => setFilter('department_id', Number(e.target.value) || undefined)}
            className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <option value="">All Departments</option>
            {(deptsRes?.data ?? []).map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
          </select>
          <select value={filters.program_id ?? ''} onChange={e => setFilter('program_id', Number(e.target.value) || undefined)}
            className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <option value="">All Programs</option>
            {(programsRes?.data ?? []).map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
          </select>
          <select value={filters.semester ?? ''} onChange={e => setFilter('semester', Number(e.target.value) || undefined)}
            className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <option value="">All Semesters</option>
            {[1,2,3,4,5,6].map(i => <option key={i} value={i}>Semester {i}</option>)}
          </select>
          <div className="flex gap-2">
            <select value={filters.status ?? ''} onChange={e => setFilter('status', e.target.value)}
              className="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">All Status</option>
              {Object.entries(STATUS_MAP).map(([v, s]) => <option key={v} value={v}>{s.label}</option>)}
            </select>
            {(filters.search || filters.department_id || filters.program_id || filters.semester || filters.status) && (
              <button onClick={() => setFilters({ page: 1, per_page: 20 })}
                className="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear">✕</button>
            )}
          </div>
        </div>
      </div>

      {/* Main Panel */}
      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {/* Panel header */}
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
          <p className="text-sm text-slate-500">
            {selected.length > 0 ? (
              <span className="font-bold text-slate-800">{selected.length} selected</span>
            ) : meta ? (
              <>Showing <span className="font-semibold text-slate-700">{meta.from}–{meta.to}</span> of <span className="font-semibold text-slate-700">{meta.total}</span> students</>
            ) : 'Loading…'}
          </p>
          <div className="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5">
            {(['table','cards'] as const).map(v => (
              <button key={v} onClick={() => toggleView(v)}
                className={clsx('flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                  view === v ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700')}>
                {v === 'table' ? <LayoutList className="h-3.5 w-3.5" /> : <LayoutGrid className="h-3.5 w-3.5" />}
                {v.charAt(0).toUpperCase() + v.slice(1)}
              </button>
            ))}
          </div>
        </div>

        {/* Table View */}
        {view === 'table' && (
          isLoading ? (
            <div className="flex items-center justify-center py-20"><div className="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" /></div>
          ) : students.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 text-center">
              <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                <GraduationCap className="h-8 w-8 text-slate-400" />
              </div>
              <h3 className="text-base font-bold text-slate-800">No students found</h3>
              <p className="mt-1 text-sm text-slate-500 max-w-xs">Try adjusting your filters or enroll a new student.</p>
              <Link to="create" className="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 transition">
                <Plus className="h-4 w-4" /> Add Student
              </Link>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="bg-slate-50/70 border-b border-slate-100">
                    <th className="w-10 px-5 py-3 text-left">
                      <input type="checkbox" checked={selected.length === students.length && students.length > 0} onChange={toggleAll}
                        className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                    </th>
                    {['Student','Department / Program','Semester','Status','Enrolled','Guardian','Actions'].map(h => (
                      <th key={h} className="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">{h}</th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {students.map(student => {
                    const st = STATUS_MAP[student.status] ?? { label: student.status, cls: 'bg-slate-100 text-slate-600' };
                    return (
                      <tr key={student.id} className={clsx('group hover:bg-slate-50/60 transition-colors', selected.includes(student.id) && 'bg-blue-50/30')}>
                        <td className="px-5 py-3.5">
                          <input type="checkbox" checked={selected.includes(student.id)}
                            onChange={() => setSelected(s => s.includes(student.id) ? s.filter(i => i !== student.id) : [...s, student.id])}
                            className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                        </td>
                        <td className="px-5 py-3.5">
                          <div className="flex items-center gap-3 min-w-0">
                            <Avatar student={student} />
                            <div className="min-w-0">
                              <button onClick={() => setDrawerStudentId(student.id)} className="block font-semibold text-slate-900 hover:text-blue-700 truncate transition text-sm text-left">{student.user.name}</button>
                              <p className="font-mono text-[11px] text-slate-400 truncate">{student.student_no ?? '—'}</p>
                              <p className="text-[11px] text-slate-400 truncate hidden sm:block">{student.user.email}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-5 py-3.5">
                          <p className="text-sm font-medium text-slate-700 truncate max-w-[160px]">{student.program?.name ?? '—'}</p>
                          <p className="text-xs text-slate-400 truncate">{student.department?.name ?? '—'}</p>
                        </td>
                        <td className="px-5 py-3.5">
                          <span className="inline-flex items-center rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700">Sem {student.current_semester}</span>
                        </td>
                        <td className="px-5 py-3.5">
                          <span className={clsx('inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold', st.cls)}>
                            <span className="h-1.5 w-1.5 rounded-full bg-current opacity-60" />{st.label}
                          </span>
                        </td>
                        <td className="px-5 py-3.5 text-xs text-slate-400">
                          <BsDate date={student.created_at} format="Y, F d" />
                        </td>
                        <td className="px-5 py-3.5 text-xs">
                          {student.guardian_name ? (
                            <><p className="font-medium text-slate-700">{student.guardian_name}</p><p className="text-slate-400">{student.guardian_phone ?? ''}</p></>
                          ) : <span className="text-slate-300">Not linked</span>}
                        </td>
                        <td className="px-5 py-3.5">
                          <div className="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <Link to={`${student.id}`} className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </Link>
                            <Link to={`${student.id}/edit`} className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </Link>
                            <button onClick={() => confirmDelete(student)} className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )
        )}

        {/* Card View */}
        {view === 'cards' && (
          <div className="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {students.map(student => {
              const st = STATUS_MAP[student.status] ?? { label: student.status, cls: 'bg-slate-100 text-slate-600' };
              return (
                <div key={student.id} className="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
                  <div className="absolute top-3.5 right-3.5">
                    <input type="checkbox" checked={selected.includes(student.id)}
                      onChange={() => setSelected(s => s.includes(student.id) ? s.filter(i => i !== student.id) : [...s, student.id])}
                      className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                  </div>
                  <div className="flex flex-col items-center text-center">
                    <Avatar student={student} size={16} />
                    <button onClick={() => navigate(`${student.id}`)} className="mt-3 text-sm font-bold text-slate-900 hover:text-blue-700 transition leading-tight pr-5 text-center">{student.user.name}</button>
                    <p className="mt-0.5 font-mono text-[11px] text-slate-400">{student.student_no ?? '—'}</p>
                  </div>
                  <div className="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                    <span className="rounded-lg bg-violet-50 px-2 py-0.5 text-[11px] font-bold text-violet-700">Sem {student.current_semester}</span>
                    <span className={clsx('rounded-lg px-2 py-0.5 text-[11px] font-semibold', st.cls)}>{st.label}</span>
                  </div>
                  <div className="mt-3 space-y-0.5 text-center">
                    <p className="text-xs text-slate-600 font-medium truncate">{student.program?.name ?? '—'}</p>
                    <p className="text-[11px] text-slate-400 truncate">{student.department?.name ?? '—'}</p>
                  </div>
                  <div className="mt-4 grid grid-cols-2 gap-2">
                    <Link to={`${student.id}`} className="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</Link>
                    <Link to={`${student.id}/edit`} className="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</Link>
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {meta && <div className="border-t border-slate-100 px-5 py-4"><Pagination meta={meta} onPageChange={p => setFilters(f => ({ ...f, page: p }))} /></div>}
      </div>
      <StudentDrawer studentId={drawerStudentId} onClose={() => setDrawerStudentId(null)} />
    </div>
  );
}
