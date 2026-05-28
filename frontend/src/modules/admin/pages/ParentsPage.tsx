import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { Spinner } from '@shared/components/ui/Spinner';
import { Pagination } from '@shared/components/ui/Pagination';
import parentService from '@shared/services/parentService';
import { get } from '@shared/api/axios';

export default function ParentsPage() {
  const queryClient = useQueryClient();
  const [view, setView] = useState<'table' | 'cards'>(() => {
    return (localStorage.getItem('mmp_parents_view') as 'table' | 'cards') ?? 'table';
  });

  const [search, setSearch] = useState('');
  const [departmentId, setDepartmentId] = useState('');
  const [programId, setProgramId] = useState('');
  const [status, setStatus] = useState('');
  const [linked, setLinked] = useState('');
  const [page, setPage] = useState(1);

  const params: Record<string, any> = { page, per_page: 20 };
  if (search) params.search = search;
  if (departmentId) params.department_id = departmentId;
  if (programId) params.program_id = programId;
  if (status !== '') params.status = status;
  if (linked) params.linked = linked;

  const { data, isLoading } = useQuery({
    queryKey: ['parents', params],
    queryFn: () => parentService.list(params),
  });

  const parents = data?.data?.data ?? [];
  const meta = data?.data?.meta;

  const { data: statsData } = useQuery({
    queryKey: ['parents-stats'],
    queryFn: () => get<any>('/v1/parents/stats'),
  });
  const stats = statsData?.data ?? {};

  const { data: filtersData } = useQuery({
    queryKey: ['parents-filters'],
    queryFn: () => get<any>('/v1/filters/parents'),
  });
  const departments = filtersData?.data?.departments ?? [];
  const programs = filtersData?.data?.programs ?? [];

  const deleteMutation = useMutation({
    mutationFn: (id: number) => parentService.destroy(id),
    onSuccess: () => { toast.success('Parent deleted.'); queryClient.invalidateQueries({ queryKey: ['parents'] }); },
    onError: () => toast.error('Failed to delete parent.'),
  });

  const handleDelete = (id: number) => {
    if (window.confirm('Delete this parent account?')) deleteMutation.mutate(id);
  };

  const setViewPersist = (v: 'table' | 'cards') => {
    setView(v);
    localStorage.setItem('mmp_parents_view', v);
  };

  const clearFilters = () => {
    setSearch('');
    setDepartmentId('');
    setProgramId('');
    setStatus('');
    setLinked('');
    setPage(1);
  };

  const hasFilters = search || departmentId || programId || status !== '' || linked;

  const kpis = [
    { label: 'Total Parents', value: stats.total_parents ?? 0, icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', color: 'blue', tag: 'Total' },
    { label: 'Linked Children', value: stats.linked_children ?? 0, icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', color: 'green', tag: 'Children' },
    { label: 'Unlinked Parents', value: stats.unlinked_parents ?? 0, icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', color: 'amber', tag: 'Unlinked' },
    { label: 'Recently Added', value: stats.recently_added ?? 0, icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', color: 'violet', tag: '30 Days' },
  ];

  const selectCls = 'rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100';

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Parents & Guardians</h1>
          <p className="mt-0.5 text-sm text-slate-500">Manage parent accounts and linked children</p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <Link to="/admin/parents/create"
            className="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Add Parent
          </Link>
        </div>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {kpis.map(k => (
          <div key={k.label} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between">
              <div className={`flex h-10 w-10 items-center justify-center rounded-xl bg-${k.color}-50`}>
                <svg className={`w-5 h-5 text-${k.color}-600`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={k.icon} />
                </svg>
              </div>
              <span className={`rounded-full bg-${k.color}-50 px-2 py-0.5 text-[11px] font-bold text-${k.color}-700`}>{k.tag}</span>
            </div>
            <p className="mt-3 text-3xl font-black text-slate-900">{k.value}</p>
            <p className="mt-0.5 text-xs text-slate-500">{k.label}</p>
          </div>
        ))}
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
          <div className="relative xl:col-span-2">
            <svg className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" value={search} onChange={e => { setSearch(e.target.value); setPage(1); }}
              placeholder="Search name, phone, email…"
              className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" />
          </div>
          <select value={departmentId} onChange={e => { setDepartmentId(e.target.value); setPage(1); }} className={selectCls}>
            <option value="">Child Department</option>
            {departments.map((d: any) => <option key={d.id} value={d.id}>{d.name}</option>)}
          </select>
          <select value={programId} onChange={e => { setProgramId(e.target.value); setPage(1); }} className={selectCls}>
            <option value="">Child Program</option>
            {programs.map((p: any) => <option key={p.id} value={p.id}>{p.name}</option>)}
          </select>
          <select value={status} onChange={e => { setStatus(e.target.value); setPage(1); }} className={selectCls}>
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <div className="flex gap-2">
            <select value={linked} onChange={e => { setLinked(e.target.value); setPage(1); }} className={`flex-1 ${selectCls}`}>
              <option value="">All Parents</option>
              <option value="linked">With Children</option>
              <option value="unlinked">No Children</option>
            </select>
            <button onClick={() => setPage(1)} className="rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition whitespace-nowrap">Apply</button>
            {hasFilters && (
              <button onClick={clearFilters} className="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">✕</button>
            )}
          </div>
        </div>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
          <p className="text-sm text-slate-500">
            {meta && meta.total > 0 ? (
              <>Showing <span className="font-semibold text-slate-700">{meta.from}–{meta.to}</span> of <span className="font-semibold text-slate-700">{meta.total}</span> parents</>
            ) : (
              'No parents match your filters'
            )}
          </p>
          <div className="flex items-center gap-1 rounded-lg border border-slate-200 p-0.5">
            <button onClick={() => setViewPersist('table')}
              className={`rounded-md p-1.5 transition ${view === 'table' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-400 hover:text-slate-600'}`}>
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
            </button>
            <button onClick={() => setViewPersist('cards')}
              className={`rounded-md p-1.5 transition ${view === 'cards' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-400 hover:text-slate-600'}`}>
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            </button>
          </div>
        </div>

        {view === 'table' && (
          <>
            {isLoading ? (
              <div className="flex items-center justify-center py-20"><Spinner /></div>
            ) : parents.length === 0 ? (
              <div className="px-6 py-16 text-center">
                <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                  <svg className="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <h3 className="mt-4 text-lg font-bold text-slate-900">No parents found</h3>
                <p className="mt-1 text-sm text-slate-500">Parents will appear here after creation or import.</p>
                <Link to="/admin/parents/create" className="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                  Add First Parent
                </Link>
              </div>
            ) : (
              <div className="mmp-table-wrap">
                <table className="mmp-table w-full text-left text-sm">
                  <thead className="border-b border-slate-100 bg-slate-50/60">
                    <tr>
                      <th className="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Parent/Guardian</th>
                      <th className="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Contact</th>
                      <th className="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Relation</th>
                      <th className="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Linked Children</th>
                      <th className="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                      <th className="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-50">
                    {parents.map((parent: any) => {
                      const pActive = parent.user?.is_active ?? false;
                      return (
                        <tr key={parent.id} className="hover:bg-slate-50/70 transition-colors">
                          <td className="px-5 py-3.5">
                            <div className="flex items-center gap-3">
                              {parent.user?.avatar ? (
                                <img src={parent.user.avatar} className="h-9 w-9 rounded-xl object-cover ring-2 ring-slate-100" />
                              ) : (
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-bold text-white">
                                  {(parent.user?.name ?? 'P').charAt(0).toUpperCase()}
                                </div>
                              )}
                              <div className="min-w-0">
                                <Link to={`/admin/parents/${parent.id}`} className="font-semibold text-slate-900 hover:text-[#8B0000] truncate block">{parent.user?.name}</Link>
                                <p className="text-xs text-slate-400">{parent.occupation ?? '—'}</p>
                              </div>
                            </div>
                          </td>
                          <td className="px-5 py-3.5">
                            <p className="text-sm text-slate-700">{parent.user?.phone ?? '—'}</p>
                            <p className="text-xs text-slate-400">{parent.user?.email}</p>
                          </td>
                          <td className="px-5 py-3.5">
                            <span className="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                              {parent.relation_to_student ? parent.relation_to_student.charAt(0).toUpperCase() + parent.relation_to_student.slice(1) : 'Parent'}
                            </span>
                          </td>
                          <td className="px-5 py-3.5">
                            <div className="flex flex-wrap gap-1">
                              {parent.students && parent.students.length > 0 ? (
                                parent.students.map((s: any) => (
                                  <span key={s.id} className="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                                    {s.user?.name}
                                  </span>
                                ))
                              ) : (
                                <span className="text-xs text-slate-400 italic">No children linked</span>
                              )}
                            </div>
                          </td>
                          <td className="px-5 py-3.5">
                            {pActive ? (
                              <span className="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">Active</span>
                            ) : (
                              <span className="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Inactive</span>
                            )}
                          </td>
                          <td className="px-5 py-3.5">
                            <div className="flex items-center justify-end gap-1">
                              <Link to={`/admin/parents/${parent.id}`} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" title="View">
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                              </Link>
                              <Link to={`/admin/parents/${parent.id}/edit`} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" title="Edit">
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                              </Link>
                              <button onClick={() => handleDelete(parent.id)} className="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                              </button>
                            </div>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </>
        )}

        {view === 'cards' && (
          <div className="p-5">
            {isLoading ? (
              <div className="flex items-center justify-center py-20"><Spinner /></div>
            ) : parents.length === 0 ? (
              <div className="py-12 text-center">
                <p className="text-sm text-slate-500">No parents match your filters.</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {parents.map((parent: any) => {
                  const pActive = parent.user?.is_active ?? false;
                  return (
                    <div key={parent.id} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
                      <div className="flex items-center gap-3">
                        {parent.user?.avatar ? (
                          <img src={parent.user.avatar} className="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100" />
                        ) : (
                          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-lg font-bold text-white">
                            {(parent.user?.name ?? 'P').charAt(0).toUpperCase()}
                          </div>
                        )}
                        <div className="min-w-0 flex-1">
                          <Link to={`/admin/parents/${parent.id}`} className="font-bold text-slate-900 hover:text-[#8B0000] truncate block">{parent.user?.name}</Link>
                          <p className="text-xs text-slate-500">
                            {parent.relation_to_student ? parent.relation_to_student.charAt(0).toUpperCase() + parent.relation_to_student.slice(1) : 'Parent'} · {parent.occupation ?? '—'}
                          </p>
                        </div>
                        {pActive ? (
                          <span className="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">Active</span>
                        ) : (
                          <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">Inactive</span>
                        )}
                      </div>
                      <div className="mt-3 space-y-1.5">
                        <p className="text-xs text-slate-500"><span className="font-semibold text-slate-600">Phone:</span> {parent.user?.phone ?? '—'}</p>
                        <p className="text-xs text-slate-500"><span className="font-semibold text-slate-600">Email:</span> {parent.user?.email}</p>
                      </div>
                      <div className="mt-3 flex flex-wrap gap-1">
                        {parent.students && parent.students.length > 0 ? (
                          parent.students.map((s: any) => (
                            <span key={s.id} className="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 hover:bg-blue-100 transition">
                              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                              {s.user?.name}
                            </span>
                          ))
                        ) : (
                          <span className="text-[11px] text-slate-400 italic">No children linked</span>
                        )}
                      </div>
                      <div className="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">
                        <Link to={`/admin/parents/${parent.id}`} className="flex-1 rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</Link>
                        <Link to={`/admin/parents/${parent.id}/edit`} className="flex-1 rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Edit</Link>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        )}

        {meta && meta.last_page > 1 && (
          <div className="border-t border-slate-100 px-5 py-4">
            <Pagination meta={meta} onPageChange={setPage} />
          </div>
        )}
      </div>
    </div>
  );
}
