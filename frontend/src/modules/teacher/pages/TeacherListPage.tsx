import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Plus, Download, Search, LayoutList, LayoutGrid, Users, BookOpen } from 'lucide-react';
import teacherService, { type TeacherFilters } from '@services/teacherService';
import academicService from '@services/academicService';
import { BsDate } from '@components/ui/BsDate';
import { Pagination } from '@components/ui/Pagination';
import toast from 'react-hot-toast';
import { clsx } from 'clsx';
import type { Teacher } from '@/types';

const GRADIENTS = [
  'from-blue-500 to-indigo-600','from-violet-500 to-purple-600',
  'from-emerald-500 to-teal-600','from-amber-500 to-orange-600',
  'from-rose-500 to-pink-600','from-cyan-500 to-sky-600',
];
const EMP_MAP: Record<string, { label: string; cls: string }> = {
  permanent: { label: 'Permanent', cls: 'bg-emerald-50 text-emerald-700' },
  contract:  { label: 'Contract',  cls: 'bg-amber-50 text-amber-700' },
  'part-time': { label: 'Part-time', cls: 'bg-sky-50 text-sky-700' },
};

function Avatar({ teacher, size = 9 }: { teacher: Teacher; size?: number }) {
  const grad = GRADIENTS[teacher.id % 6];
  if (teacher.user.avatar)
    return <img src={teacher.user.avatar} alt={teacher.user.name} className={`h-${size} w-${size} flex-shrink-0 rounded-xl object-cover ring-2 ring-white shadow-sm`} />;
  return (
    <div className={`flex h-${size} w-${size} flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${grad} text-sm font-black text-white shadow-sm`}>
      {teacher.user.name.charAt(0).toUpperCase()}
    </div>
  );
}

export default function TeacherListPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [filters, setFilters] = useState<TeacherFilters>({ page: 1, per_page: 20 });
  const [view, setView] = useState<'table' | 'cards'>(() =>
    (localStorage.getItem('mmp_teachers_view') as 'table' | 'cards') ?? 'table'
  );

  const { data, isLoading } = useQuery({ queryKey: ['teachers', filters], queryFn: () => teacherService.list(filters) });
  const { data: deptsRes } = useQuery({ queryKey: ['departments'], queryFn: academicService.departments, staleTime: Infinity });

  const deleteMutation = useMutation({
    mutationFn: teacherService.destroy,
    onSuccess: () => { toast.success('Teacher deleted.'); queryClient.invalidateQueries({ queryKey: ['teachers'] }); },
    onError: () => toast.error('Failed to delete.'),
  });

  const teachers = data?.data?.data ?? [];
  const meta = data?.data?.meta;

  function setFilter(key: keyof TeacherFilters, value: string | number | undefined) {
    setFilters(p => ({ ...p, [key]: value || undefined, page: 1 }));
  }
  function toggleView(v: 'table' | 'cards') { setView(v); localStorage.setItem('mmp_teachers_view', v); }
  function confirmDelete(t: Teacher) {
    if (confirm(`Delete ${t.user.name}? This cannot be undone.`)) deleteMutation.mutate(t.id);
  }

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Teachers</h1>
          <p className="mt-0.5 text-sm text-slate-500">Manage teacher profiles, workloads, subjects, and performance analytics.</p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <Link to="create" className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
            <Plus className="h-4 w-4" /> Add Teacher
          </Link>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {[
          { label: 'Total Teachers', value: meta?.total ?? 0, icon: <Users className="h-5 w-5 text-blue-600" />, color: 'bg-blue-50', tag: 'Faculty' },
          { label: 'Active', value: teachers.filter(t => t.status === 'active').length, icon: <Users className="h-5 w-5 text-green-600" />, color: 'bg-green-50', tag: 'Active' },
          { label: 'Subjects Assigned', value: teachers.reduce((a, t) => a + (t.subjects?.length ?? 0), 0), icon: <BookOpen className="h-5 w-5 text-indigo-600" />, color: 'bg-indigo-50', tag: 'Total' },
          { label: 'Departments', value: new Set(teachers.map(t => t.department?.id).filter(Boolean)).size, icon: <BookOpen className="h-5 w-5 text-amber-600" />, color: 'bg-amber-50', tag: 'Depts' },
        ].map(k => (
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
        <div className="flex flex-wrap items-end gap-3">
          <div className="relative min-w-[220px] flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <input type="search" placeholder="Search name, email, employee ID…" value={filters.search ?? ''}
              onChange={e => setFilter('search', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" />
          </div>
          <select value={filters.department_id ?? ''} onChange={e => setFilter('department_id', Number(e.target.value) || undefined)}
            className="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
            <option value="">All Departments</option>
            {(deptsRes?.data ?? []).map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
          </select>
          <select value={filters.status ?? ''} onChange={e => setFilter('status', e.target.value)}
            className="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          {(filters.search || filters.department_id || filters.status) && (
            <button onClick={() => setFilters({ page: 1, per_page: 20 })}
              className="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear">✕</button>
          )}
        </div>
      </div>

      {/* Main Panel */}
      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
          <p className="text-sm text-slate-500">
            {meta ? <>Showing <span className="font-semibold text-slate-700">{meta.from}–{meta.to}</span> of <span className="font-semibold text-slate-700">{meta.total}</span> teachers</> : 'Loading…'}
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

        {view === 'table' && (
          isLoading ? (
            <div className="flex items-center justify-center py-20"><div className="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" /></div>
          ) : teachers.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 text-center">
              <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4"><Users className="h-8 w-8 text-slate-400" /></div>
              <h3 className="text-base font-bold text-slate-800">No teachers found</h3>
              <p className="mt-1 text-sm text-slate-500">Try adjusting your filters or add a new teacher.</p>
              <Link to="create" className="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 transition"><Plus className="h-4 w-4" /> Add Teacher</Link>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="border-b border-slate-100 bg-slate-50/60">
                  <tr>
                    {['Teacher','Department','Role','Employment','Status','Joined','Actions'].map(h => (
                      <th key={h} className="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">{h}</th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {teachers.map(teacher => {
                    const emp = EMP_MAP[teacher.status] ?? { label: teacher.status, cls: 'bg-slate-100 text-slate-600' };
                    return (
                      <tr key={teacher.id} className="group hover:bg-slate-50/60 transition-colors">
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-3">
                            <Avatar teacher={teacher} />
                            <div className="min-w-0">
                              <button onClick={() => navigate(`${teacher.id}`)} className="font-semibold text-slate-800 hover:text-blue-700 transition truncate block text-left">{teacher.user.name}</button>
                              <p className="text-xs text-slate-400 truncate">{teacher.employee_id ?? teacher.user.email}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3 text-sm font-medium text-slate-700">{teacher.department?.name ?? '—'}</td>
                        <td className="px-4 py-3">
                          <span className="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{teacher.designation ?? 'Teacher'}</span>
                        </td>
                        <td className="px-4 py-3">
                          <span className={clsx('rounded-lg px-2 py-1 text-xs font-semibold', emp.cls)}>{emp.label}</span>
                        </td>
                        <td className="px-4 py-3 text-center">
                          {teacher.status === 'active'
                            ? <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"><span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />Active</span>
                            : <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500"><span className="h-1.5 w-1.5 rounded-full bg-slate-400" />Inactive</span>}
                        </td>
                        <td className="px-4 py-3 text-xs text-slate-400">
                          <BsDate date={teacher.joining_date ?? teacher.created_at} format="Y, F d" />
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <Link to={`${teacher.id}`} className="rounded-lg bg-blue-50 p-1.5 text-blue-600 hover:bg-blue-100 transition" title="View">
                              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </Link>
                            <Link to={`${teacher.id}/edit`} className="rounded-lg bg-violet-50 p-1.5 text-violet-600 hover:bg-violet-100 transition" title="Edit">
                              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </Link>
                            <button onClick={() => confirmDelete(teacher)} className="rounded-lg bg-red-50 p-1.5 text-red-500 hover:bg-red-100 transition" title="Delete">
                              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

        {view === 'cards' && (
          <div className="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {teachers.map(teacher => (
              <div key={teacher.id} className="group relative rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm hover:shadow-md transition-all cursor-pointer" onClick={() => navigate(`${teacher.id}`)}>
                <div className={clsx('h-1 w-full', teacher.status === 'active' ? 'bg-emerald-400' : 'bg-slate-300')} />
                <div className="p-4">
                  <div className="flex items-center gap-3 mb-3">
                    <Avatar teacher={teacher} size={12} />
                    <div className="min-w-0">
                      <p className="font-bold text-slate-800 truncate">{teacher.user.name}</p>
                      <p className="text-xs text-slate-400 truncate">{teacher.employee_id ?? '—'}</p>
                    </div>
                  </div>
                  <p className="text-xs text-slate-500 mb-3 truncate">{teacher.department?.name ?? '—'}</p>
                  <div className="grid grid-cols-2 gap-2">
                    <Link to={`${teacher.id}`} onClick={e => e.stopPropagation()} className="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</Link>
                    <Link to={`${teacher.id}/edit`} onClick={e => e.stopPropagation()} className="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</Link>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {meta && <div className="border-t border-slate-100 px-5 py-4"><Pagination meta={meta} onPageChange={p => setFilters(f => ({ ...f, page: p }))} /></div>}
      </div>
    </div>
  );
}
