import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { get, del } from '@shared/api/axios';
import { StatCard } from '@components/ui/StatCard';
import { Badge } from '@components/ui/Badge';
import { BsDate } from '@components/ui/BsDate';
import { Pagination } from '@components/ui/Pagination';
import { Spinner } from '@components/ui/Spinner';
import toast from 'react-hot-toast';

export default function HodsPage() {
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [view, setView] = useState<'table' | 'cards'>(() => {
    return (localStorage.getItem('mmp_hods_view') as 'table' | 'cards') ?? 'table';
  });
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selectedHodId, setSelectedHodId] = useState<number | null>(null);

  const { data: statsRes } = useQuery({
    queryKey: ['hods-stats'],
    queryFn: () => get<{ data: { total_hods: number; active_hods: number; assigned_departments: number } }>('/v1/hods/stats'),
    staleTime: 30000,
  });

  const stats = statsRes?.data;

  const { data, isLoading } = useQuery({
    queryKey: ['hods', search, status, page],
    queryFn: () => {
      const p = new URLSearchParams({ page: String(page), per_page: '12' });
      if (search) p.set('search', search);
      if (status) p.set('status', status);
      return get<any>(`/v1/hods?${p}`);
    },
  });

  const hods = data?.data?.data ?? [];
  const meta = data?.data?.meta;

  const deleteMutation = useMutation({
    mutationFn: (id: number) => del(`/v1/hods/${id}`),
    onSuccess: () => { toast.success('HOD deleted successfully'); queryClient.invalidateQueries({ queryKey: ['hods'] }); },
    onError: () => toast.error('Failed to delete HOD'),
  });

  const switchView = (v: 'table' | 'cards') => {
    setView(v);
    localStorage.setItem('mmp_hods_view', v);
  };

  useEffect(() => {
    const handleEsc = (e: KeyboardEvent) => {
      if (e.key === 'Escape') { setDrawerOpen(false); setSelectedHodId(null); }
    };
    document.addEventListener('keydown', handleEsc);
    return () => document.removeEventListener('keydown', handleEsc);
  }, []);

  const selectedHod = hods.find((h: any) => h.id === selectedHodId);

  const hasFilters = search || status;

  const KPI_ICONS = {
    total: <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>,
    active: <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>,
    dept: <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>,
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">HOD Management</h1>
          <p className="mt-0.5 text-sm text-slate-500">Manage Heads of Department and their assignments.</p>
        </div>
        <Link to="create" className="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#6B0000] transition">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add HOD
        </Link>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
        <StatCard title="Total HODs" value={stats?.total_hods ?? 0} icon={KPI_ICONS.total} color="blue" />
        <StatCard title="Active HODs" value={stats?.active_hods ?? 0} icon={KPI_ICONS.active} color="green" />
        <StatCard title="Assigned Departments" value={stats?.assigned_departments ?? 0} icon={KPI_ICONS.dept} color="purple" />
      </div>

      <div className="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3">
        <div className="flex flex-wrap gap-3 items-center">
          <input name="search" value={search} onChange={e => { setSearch(e.target.value); setPage(1); }}
            placeholder="Search name or email\u2026"
            className="flex-1 min-w-[200px] form-input" />
          <select value={status} onChange={e => { setStatus(e.target.value); setPage(1); }}
            className="form-input w-auto">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <button onClick={() => { setSearch(''); setStatus(''); setPage(1); }}
            className="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
            Filter
          </button>
          {hasFilters && (
            <Link to="/admin/hods" onClick={() => { setSearch(''); setStatus(''); setPage(1); }}
              className="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
              Clear
            </Link>
          )}
        </div>
      </div>

      <div className="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5">
          <p className="text-sm text-slate-500">
            {meta && meta.total > 0 ? (
              <>Showing <span className="font-semibold text-slate-700">{meta.from}\u2013{meta.to}</span> of <span className="font-semibold text-slate-700">{meta.total.toLocaleString()}</span> HODs</>
            ) : (
              'No HODs match your filters'
            )}
          </p>
          <div className="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
            <button type="button" onClick={() => switchView('table')}
              className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition ${view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'}`}>
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M3 6h18M3 14h18M3 18h18" /></svg>
              Table
            </button>
            <button type="button" onClick={() => switchView('cards')}
              className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition ${view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'}`}>
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
              Cards
            </button>
          </div>
        </div>

        {isLoading ? (
          <div className="flex items-center justify-center py-20">
            <Spinner size="lg" />
          </div>
        ) : view === 'cards' ? (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
              {hods.length === 0 ? (
                <div className="col-span-full">
                  <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-14 text-center">
                    <svg className="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p className="text-sm font-semibold text-gray-400">No HODs found</p>
                    <p className="text-xs text-gray-300 mt-1">Try adjusting your search or filters.</p>
                  </div>
                </div>
              ) : (
                hods.map((hod: any) => (
                  <div key={hod.id} className="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <div className="p-5">
                      <div className="flex items-start gap-4">
                        <img src={hod.avatar_url} alt={hod.name}
                          className="w-16 h-16 rounded-full object-cover ring-2 ring-gray-100 flex-shrink-0" />
                        <div className="flex-1 min-w-0">
                          <h3 className="font-semibold text-gray-900 truncate">{hod.name}</h3>
                          <p className="text-xs text-gray-500 truncate mt-0.5">{hod.email}</p>
                          <div className="flex items-center gap-2 mt-2">
                            {hod.is_active ? (
                              <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-green-50 text-green-700">
                                <span className="h-1.5 w-1.5 rounded-full bg-green-500" />
                                Active
                              </span>
                            ) : (
                              <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-red-50 text-red-700">
                                <span className="h-1.5 w-1.5 rounded-full bg-red-500" />
                                Inactive
                              </span>
                            )}
                          </div>
                        </div>
                      </div>
                      <div className="mt-4 pt-4 border-t border-gray-100 space-y-2">
                        <div className="flex items-center gap-2 text-sm">
                          <svg className="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                          </svg>
                          {hod.hod_department ? (
                            <span className="text-gray-700 font-medium truncate">{hod.hod_department.name}</span>
                          ) : (
                            <span className="text-gray-400 italic">Not assigned</span>
                          )}
                        </div>
                        {hod.phone && (
                          <div className="flex items-center gap-2 text-sm">
                            <svg className="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span className="text-gray-600">{hod.phone}</span>
                          </div>
                        )}
                      </div>
                      <div className="mt-4 flex items-center gap-2">
                        <button onClick={() => { setSelectedHodId(hod.id); setDrawerOpen(true); }}
                          className="flex-1 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                          Quick View
                        </button>
                        <Link to={`${hod.id}/edit`}
                          className="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button onClick={() => { if (confirm(`Are you sure you want to delete ${hod.name}?`)) deleteMutation.mutate(hod.id); }}
                          className="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>

            {meta && meta.last_page > 1 && (
              <div className="border-t border-gray-100 px-5 py-4">
                <Pagination meta={meta} onPageChange={setPage} />
              </div>
            )}
          </>
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr>
                    <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-10 text-left">#</th>
                    <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">HOD</th>
                    <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Department</th>
                    <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Phone</th>
                    <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Status</th>
                    <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Joined</th>
                    <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-32 text-left">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {hods.length === 0 ? (
                    <tr>
                      <td colSpan={7} className="px-5 py-14 text-center">
                        <svg className="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p className="text-sm font-semibold text-gray-400">No HODs found</p>
                        <p className="text-xs text-gray-300 mt-1">Try adjusting your search or filters.</p>
                      </td>
                    </tr>
                  ) : (
                    hods.map((hod: any) => (
                      <tr key={hod.id} className="hover:bg-gray-50/70 transition-colors">
                        <td className="px-5 py-3.5 text-gray-400 font-mono text-xs">{hod.id}</td>
                        <td className="px-5 py-3.5">
                          <div className="flex items-center gap-3">
                            <img src={hod.avatar_url} alt={hod.name}
                              className="w-8 h-8 rounded-full object-cover ring-2 ring-gray-100 flex-shrink-0" />
                            <div className="min-w-0">
                              <p className="font-semibold text-gray-900 truncate">{hod.name}</p>
                              <p className="text-xs text-gray-400 truncate">{hod.email}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-5 py-3.5">
                          {hod.hod_department ? (
                            <div className="flex items-center gap-2">
                              <svg className="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                              </svg>
                              <span className="text-sm font-medium text-gray-900">{hod.hod_department.name}</span>
                            </div>
                          ) : (
                            <span className="text-xs text-gray-400 italic">Not assigned</span>
                          )}
                        </td>
                        <td className="px-5 py-3.5 text-gray-500 text-xs">{hod.phone ?? '\u2014'}</td>
                        <td className="px-5 py-3.5">
                          {hod.is_active ? (
                            <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-green-50 text-green-700">
                              <span className="h-1.5 w-1.5 rounded-full bg-green-500" />
                              Active
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-red-50 text-red-700">
                              <span className="h-1.5 w-1.5 rounded-full bg-red-500" />
                              Inactive
                            </span>
                          )}
                        </td>
                        <td className="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">
                          <BsDate date={hod.created_at} format="Y, F d" />
                        </td>
                        <td className="px-5 py-3.5">
                          <div className="flex items-center gap-1">
                            <Link to={`${hod.id}`}
                              className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                              </svg>
                            </Link>
                            <Link to={`${hod.id}/edit`}
                              className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                              </svg>
                            </Link>
                            <button onClick={() => { if (confirm(`Are you sure you want to delete ${hod.name}?`)) deleteMutation.mutate(hod.id); }}
                              className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                              </svg>
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
            {meta && meta.last_page > 1 && (
              <div className="border-t border-gray-100 px-5 py-4">
                <Pagination meta={meta} onPageChange={setPage} />
              </div>
            )}
          </>
        )}
      </div>

      {drawerOpen && (
        <div className="fixed inset-0 z-50 overflow-hidden">
          <div onClick={() => { setDrawerOpen(false); setSelectedHodId(null); }}
            className="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" />
          <div className="fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div className="w-screen max-w-md">
              <div className="flex h-full flex-col bg-white shadow-xl">
                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-6">
                  <div className="flex items-start justify-between">
                    <h2 className="text-lg font-semibold text-gray-900">HOD Details</h2>
                    <button onClick={() => { setDrawerOpen(false); setSelectedHodId(null); }} className="text-gray-400 hover:text-gray-500">
                      <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
                <div className="flex-1 overflow-y-auto px-6 py-6">
                  {selectedHod && (
                    <div>
                      <div className="flex items-center gap-4 mb-6">
                        <img src={selectedHod.avatar_url} alt={selectedHod.name}
                          className="w-20 h-20 rounded-full object-cover ring-4 ring-white shadow-lg" />
                        <div>
                          <h3 className="text-xl font-bold text-gray-900">{selectedHod.name}</h3>
                          <p className="text-sm text-gray-600 mt-1">{selectedHod.email}</p>
                          <div className="flex items-center gap-2 mt-2">
                            <Badge variant="blue">HOD</Badge>
                            {selectedHod.is_active ? (
                              <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-green-50 text-green-700">
                                <span className="h-1.5 w-1.5 rounded-full bg-green-500" />
                                Active
                              </span>
                            ) : (
                              <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-red-50 text-red-700">
                                <span className="h-1.5 w-1.5 rounded-full bg-red-500" />
                                Inactive
                              </span>
                            )}
                          </div>
                        </div>
                      </div>
                      <div className="space-y-6">
                        <div>
                          <h4 className="text-sm font-semibold text-gray-900 mb-3">Personal Information</h4>
                          <div className="space-y-3">
                            <div>
                              <p className="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</p>
                              <p className="mt-1 text-sm text-gray-900">{selectedHod.phone ?? '\u2014'}</p>
                            </div>
                            <div>
                              <p className="text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</p>
                              <p className="mt-1 text-sm text-gray-900">{selectedHod.gender ? selectedHod.gender.charAt(0).toUpperCase() + selectedHod.gender.slice(1) : '\u2014'}</p>
                            </div>
                            <div>
                              <p className="text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</p>
                              <p className="mt-1 text-sm text-gray-900">{selectedHod.dob ? <BsDate date={selectedHod.dob} format="Y F d" /> : '\u2014'}</p>
                            </div>
                            <div>
                              <p className="text-xs font-medium text-gray-500 uppercase tracking-wider">Address</p>
                              <p className="mt-1 text-sm text-gray-900">{selectedHod.address ?? '\u2014'}</p>
                            </div>
                          </div>
                        </div>
                        <div className="border-t border-gray-100 pt-6">
                          <h4 className="text-sm font-semibold text-gray-900 mb-3">Department Assignment</h4>
                          {selectedHod.hod_department ? (
                            <div className="bg-blue-50 rounded-lg p-4 border border-blue-100">
                              <div className="flex items-start gap-3">
                                <div className="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                  <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                  </svg>
                                </div>
                                <div>
                                  <h5 className="font-semibold text-gray-900">{selectedHod.hod_department.name}</h5>
                                  <p className="text-xs text-gray-600 mt-1">Code: {selectedHod.hod_department.code}</p>
                                </div>
                              </div>
                            </div>
                          ) : (
                            <div className="bg-amber-50 rounded-lg p-4 border border-amber-100">
                              <p className="text-sm text-amber-800">No department assigned</p>
                            </div>
                          )}
                        </div>
                        <div className="border-t border-gray-100 pt-6">
                          <h4 className="text-sm font-semibold text-gray-900 mb-3">Account Information</h4>
                          <div className="space-y-3">
                            <div>
                              <p className="text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</p>
                              <p className="mt-1 text-sm text-gray-900"><BsDate date={selectedHod.created_at} format="Y F d" /></p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
                <div className="border-t border-gray-100 px-6 py-4">
                  {selectedHod && (
                    <div className="flex gap-3">
                      <Link to={`${selectedHod.id}/edit`}
                        className="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors text-center">
                        Edit HOD
                      </Link>
                      <button onClick={() => { setDrawerOpen(false); setSelectedHodId(null); }}
                        className="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Close
                      </button>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
