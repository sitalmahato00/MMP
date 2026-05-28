import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Link, useSearchParams } from 'react-router-dom';
import { useState, useEffect, useCallback } from 'react';
import toast from 'react-hot-toast';
import staffService from '@shared/services/staffService';
import { BsDate } from '@shared/components/ui/BsDate';

const gradients = [
  'from-blue-500 to-indigo-600', 'from-violet-500 to-purple-600', 'from-emerald-500 to-teal-600',
  'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-sky-600',
];

const employmentMap: Record<string, { label: string; cls: string }> = {
  full_time: { label: 'Full Time', cls: 'bg-blue-50 text-blue-700' },
  part_time: { label: 'Part Time', cls: 'bg-violet-50 text-violet-700' },
  contract: { label: 'Contract', cls: 'bg-amber-50 text-amber-700' },
  temporary: { label: 'Temporary', cls: 'bg-slate-100 text-slate-600' },
};

const statusMap: Record<string, { label: string; cls: string }> = {
  active: { label: 'Active', cls: 'bg-emerald-50 text-emerald-700' },
  leave: { label: 'Leave', cls: 'bg-amber-50 text-amber-700' },
  resigned: { label: 'Resigned', cls: 'bg-rose-50 text-rose-700' },
};

export default function StaffAdminPage() {
  const queryClient = useQueryClient();
  const [searchParams, setSearchParams] = useSearchParams();
  const [view, setViewState] = useState(() => localStorage.getItem('mmp_staff_view') || 'table');

  const setView = useCallback((v: string) => {
    setViewState(v);
    localStorage.setItem('mmp_staff_view', v);
  }, []);

  const [search, setSearch] = useState(searchParams.get('search') || '');
  const [department, setDepartment] = useState(searchParams.get('department') || '');
  const [designation, setDesignation] = useState(searchParams.get('designation') || '');
  const [employmentStatus, setEmploymentStatus] = useState(searchParams.get('employment_status') || '');
  const [joinedYear, setJoinedYear] = useState(searchParams.get('joined_year') || '');
  const [featured, setFeatured] = useState(searchParams.get('featured') || '');

  const applyFilters = () => {
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (department) params.set('department', department);
    if (designation) params.set('designation', designation);
    if (employmentStatus) params.set('employment_status', employmentStatus);
    if (joinedYear) params.set('joined_year', joinedYear);
    if (featured) params.set('featured', featured);
    params.set('page', '1');
    setSearchParams(params);
  };

  const resetFilters = () => {
    setSearch('');
    setDepartment('');
    setDesignation('');
    setEmploymentStatus('');
    setJoinedYear('');
    setFeatured('');
    setSearchParams({});
  };

  useEffect(() => {
    setSearch(searchParams.get('search') || '');
    setDepartment(searchParams.get('department') || '');
    setDesignation(searchParams.get('designation') || '');
    setEmploymentStatus(searchParams.get('employment_status') || '');
    setJoinedYear(searchParams.get('joined_year') || '');
    setFeatured(searchParams.get('featured') || '');
  }, []);

  const { data, isLoading } = useQuery({
    queryKey: ['admin-staff', searchParams.toString()],
    queryFn: () => {
      const p = new URLSearchParams(searchParams);
      p.set('per_page', '20');
      if (!p.has('page')) p.set('page', '1');
      return staffService.list(Object.fromEntries(p));
    },
  });

  const togglePublic = useMutation({
    mutationFn: (staffId: number) => staffService.togglePublic(staffId),
    onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['admin-staff'] }); toast.success('Visibility toggled.'); },
    onError: () => toast.error('Failed to toggle visibility.'),
  });

  const toggleFeatured = useMutation({
    mutationFn: (staffId: number) => staffService.toggleFeatured(staffId),
    onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['admin-staff'] }); toast.success('Featured toggled.'); },
    onError: () => toast.error('Failed to toggle featured.'),
  });

  const staff = data?.data?.data ?? [];
  const meta = data?.data?.meta;
  const kpis = (data?.data as any)?.kpis;

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Administrative Staff</h1>
          <p className="mt-0.5 text-sm text-slate-500">Manage staff profiles, working schedules, documents, and public visibility.</p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <Link to="/admin/staff/create" className="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition">
            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Add Staff
          </Link>
          <a href={`/admin/staff/export/csv?${searchParams.toString()}`} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
          </a>
          <a href={`/admin/staff/export/pdf?${searchParams.toString()}`} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Export PDF
          </a>
        </div>
      </div>

      <form method="POST" action="/admin/staff/import" encType="multipart/form-data" className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="flex flex-wrap items-end gap-3">
          <div className="min-w-[220px] flex-1">
            <label className="mb-1.5 block text-xs font-semibold text-slate-600">Import CSV</label>
            <input type="file" name="csv" accept=".csv,text/csv" className="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-[#8B0000] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white focus:border-[#8B0000] focus:ring-[#8B0000]/20" />
          </div>
          <button type="submit" className="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">Import Staff CSV</button>
          <p className="text-sm text-slate-500">Upload the staff CSV schema to create or update records.</p>
        </div>
      </form>

      <div className="grid grid-cols-2 gap-4 lg:grid-cols-5">
        {[
          { label: 'Total Staff', value: kpis?.total_staff ?? staff?.length ?? 0, icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', color: 'blue' },
          { label: 'Active', value: kpis?.active_staff ?? 0, icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'green' },
          { label: 'Resigned', value: kpis?.resigned_staff ?? 0, icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', color: 'amber' },
          { label: 'This Year', value: kpis?.added_this_year ?? 0, icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', color: 'violet' },
          { label: 'Top Department', value: kpis?.top_department?.department ?? 'None', meta: kpis?.top_department?.total ? `${kpis.top_department.total} staff` : 'No records yet', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', color: 'slate' },
        ].map((kpi) => (
          <div key={kpi.label} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between gap-3">
              <div className={`flex h-10 w-10 items-center justify-center rounded-xl bg-${kpi.color}-50`}>
                <svg className={`h-5 w-5 text-${kpi.color}-600`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={kpi.icon} />
                </svg>
              </div>
              <span className={`rounded-full bg-${kpi.color}-50 px-2 py-0.5 text-[11px] font-bold text-${kpi.color}-700`}>{kpi.label === 'Top Department' ? (kpi.meta ?? 'Staff') : 'Staff'}</span>
            </div>
            {typeof kpi.value === 'number' ? (
              <p className="mt-3 text-3xl font-black text-slate-900">{kpi.value.toLocaleString()}</p>
            ) : (
              <p className="mt-3 text-2xl font-black tracking-tight text-slate-900">{kpi.value}</p>
            )}
            <p className="mt-0.5 text-sm text-slate-500">{kpi.label}</p>
            {typeof kpi.value !== 'number' && kpi.meta && <p className="mt-1 text-xs text-slate-400">{kpi.meta}</p>}
          </div>
        ))}
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
          <div className="relative xl:col-span-2">
            <svg className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
            </svg>
            <input type="search" value={search} onChange={e => setSearch(e.target.value)} placeholder="Search staff code, name, email..." className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" />
          </div>

          <select value={department} onChange={e => setDepartment(e.target.value)} className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All Departments</option>
            {kpis?.departments?.map((d: string) => <option key={d} value={d}>{d}</option>)}
          </select>

          <select value={designation} onChange={e => setDesignation(e.target.value)} className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All Designations</option>
            {kpis?.designations?.map((d: string) => <option key={d} value={d}>{d}</option>)}
          </select>

          <select value={employmentStatus} onChange={e => setEmploymentStatus(e.target.value)} className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="leave">Leave</option>
            <option value="resigned">Resigned</option>
          </select>

          <select value={joinedYear} onChange={e => setJoinedYear(e.target.value)} className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All BS Years</option>
            {kpis?.joined_years?.map((y: number) => <option key={y} value={y}>{y}</option>)}
          </select>

          <select value={featured} onChange={e => setFeatured(e.target.value)} className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All Featured</option>
            <option value="1">Featured only</option>
            <option value="0">Not featured</option>
          </select>
        </div>

        <div className="mt-5 flex flex-wrap items-center justify-between gap-3">
          <p className="text-sm text-slate-500">
            {meta && meta.total > 0 ? (
              <>Showing <span className="font-semibold text-slate-700">{meta.from}–{meta.to}</span> of <span className="font-semibold text-slate-700">{meta.total.toLocaleString()}</span> staff records</>
            ) : 'No staff records match your filters'}
          </p>
          <div className="flex flex-wrap gap-2">
            <button onClick={applyFilters} className="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#7a0000] shadow-sm">Apply Filters</button>
            <button onClick={resetFilters} className="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500 transition hover:bg-slate-50">Reset</button>
          </div>
        </div>
      </div>

      <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
          <div>
            <h2 className="text-lg font-semibold text-slate-900">Staff directory</h2>
            <p className="text-sm text-slate-500">Review records, toggle visibility, and jump to documents.</p>
          </div>
          <div className="flex items-center gap-3">
            <div className="text-sm text-slate-500">{meta?.total || 0} records</div>
            <div className="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
              <button type="button" onClick={() => setView('table')}
                className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition ${view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'}`}>
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M3 6h18M3 14h18M3 18h18" /></svg>
                Table
              </button>
              <button type="button" onClick={() => setView('cards')}
                className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition ${view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'}`}>
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                Cards
              </button>
            </div>
          </div>
        </div>

        {view === 'table' && (
          <>
            <div className="mmp-table-wrap">
              <table className="mmp-table divide-y divide-slate-200 text-left">
                <thead className="bg-slate-50/80">
                  <tr className="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                    <th className="px-6 py-4">Staff</th>
                    <th className="px-6 py-4">Employment</th>
                    <th className="px-6 py-4">Contact</th>
                    <th className="px-6 py-4">Visibility</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {isLoading ? (
                    <tr><td colSpan={5} className="px-6 py-16 text-center text-sm text-slate-500">Loading...</td></tr>
                  ) : staff.length === 0 ? (
                    <tr><td colSpan={5} className="px-6 py-16 text-center text-sm text-slate-500">No staff records match the current filters.</td></tr>
                  ) : staff.map((member: any) => {
                    const emp = employmentMap[member.employment_type] || { label: member.employment_type?.replace(/_/g, ' ')?.replace(/\b\w/g, (c: string) => c.toUpperCase()) || 'Unspecified', cls: 'bg-slate-100 text-slate-600' };
                    const st = statusMap[member.employment_status] || { label: member.employment_status?.charAt(0).toUpperCase() + member.employment_status?.slice(1) || 'Active', cls: 'bg-slate-100 text-slate-600' };
                    const gradient = gradients[member.id % gradients.length];
                    return (
                      <tr key={member.id} className="transition hover:bg-slate-50/80">
                        <td className="px-6 py-5 align-top">
                          <div className="flex items-start gap-4">
                            <div className="h-14 w-14 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                              {member.photo_url ? (
                                <img src={member.photo_url} alt={member.name} className="h-full w-full object-cover" />
                              ) : (
                                <div className={`flex h-full w-full items-center justify-center bg-gradient-to-br ${gradient} text-sm font-black text-white`}>
                                  {(member.name || 'S').charAt(0).toUpperCase()}
                                </div>
                              )}
                            </div>
                            <div>
                              <div className="font-semibold text-slate-900">{member.name}</div>
                              <div className="mt-1 text-sm text-slate-500">{member.staff_code}</div>
                              <div className="mt-2 text-sm font-medium text-[#8B0000]">{member.designation || 'Staff'}</div>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-5 align-top text-sm text-slate-600">
                          <div>{member.department || 'General Administration'}</div>
                          <div className="mt-2 flex flex-wrap gap-2">
                            <span className={`rounded-full px-3 py-1 text-xs font-semibold ${emp.cls}`}>{emp.label}</span>
                            <span className={`rounded-full px-3 py-1 text-xs font-semibold ${st.cls}`}>{st.label}</span>
                          </div>
                          <div className="mt-2 text-xs text-slate-500">Joined {member.join_date ? <BsDate date={member.join_date} format="Y F d" /> : '\u2014'}</div>
                        </td>
                        <td className="px-6 py-5 align-top text-sm text-slate-600">
                          <div>{member.email || '\u2014'}</div>
                          <div className="mt-1">{member.phone || '\u2014'}</div>
                          <div className="mt-2 text-xs text-slate-500">{member.documents_count ?? 0} documents</div>
                        </td>
                        <td className="px-6 py-5 align-top">
                          <div className="flex flex-wrap gap-2">
                            <span className={`rounded-full px-3 py-1 text-xs font-semibold ${member.public_visible ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-500'}`}>{member.public_visible ? 'Public' : 'Hidden'}</span>
                            <span className={`rounded-full px-3 py-1 text-xs font-semibold ${member.featured ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500'}`}>{member.featured ? 'Featured' : 'Standard'}</span>
                          </div>
                          <div className="mt-2 text-xs text-slate-500">
                            {member.show_email_public ? 'Email visible' : 'Email private'} &middot; {member.show_phone_public ? 'Phone visible' : 'Phone private'}
                          </div>
                        </td>
                        <td className="px-6 py-5 align-top">
                          <div className="flex flex-wrap justify-end gap-2">
                            <Link to={`/admin/staff/${member.id}`} className="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">View</Link>
                            <Link to={`/admin/staff/${member.id}/edit`} className="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Edit</Link>
                            <Link to={`/admin/staff/${member.id}/documents`} className="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Docs</Link>
                            <button onClick={() => togglePublic.mutate(member.id)} className="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{member.public_visible ? 'Hide' : 'Publish'}</button>
                            <button onClick={() => toggleFeatured.mutate(member.id)} className="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{member.featured ? 'Unfeature' : 'Feature'}</button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
            {meta && (
              <div className="border-t border-slate-200 px-6 py-4">
                <div className="flex items-center justify-between text-sm text-slate-500">
                  <span>Page {meta.current_page} of {meta.last_page}</span>
                  <div className="flex items-center gap-2">
                    <button onClick={() => { const p = new URLSearchParams(searchParams); p.set('page', String(meta.current_page - 1)); setSearchParams(p); }}
                      disabled={meta.current_page <= 1}
                      className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                    {Array.from({ length: Math.min(meta.last_page, 7) }, (_, i) => i + 1).map(p => (
                      <button key={p} onClick={() => { const p2 = new URLSearchParams(searchParams); p2.set('page', String(p)); setSearchParams(p2); }}
                        className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition ${p === meta.current_page ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>{p}</button>
                    ))}
                    <button onClick={() => { const p = new URLSearchParams(searchParams); p.set('page', String(meta.current_page + 1)); setSearchParams(p); }}
                      disabled={meta.current_page >= meta.last_page}
                      className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                  </div>
                </div>
              </div>
            )}
          </>
        )}

        {view === 'cards' && (
          <div className="p-5">
            {staff.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-16 text-center">
                <p className="text-sm font-medium text-slate-500">No staff records found.</p>
              </div>
            ) : (
              <>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                  {staff.map((member: any) => {
                    const emp = employmentMap[member.employment_type] || { label: member.employment_type?.replace(/_/g, ' ')?.replace(/\b\w/g, (c: string) => c.toUpperCase()) || 'Unspecified', cls: 'bg-slate-100 text-slate-600' };
                    const st = statusMap[member.employment_status] || { label: member.employment_status?.charAt(0).toUpperCase() + member.employment_status?.slice(1) || 'Active', cls: 'bg-slate-100 text-slate-600' };
                    const gradient = gradients[member.id % gradients.length];
                    return (
                      <div key={member.id} className="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-all">
                        <div className="flex flex-col items-center text-center">
                          <div className="h-16 w-16 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                            {member.photo_url ? (
                              <img src={member.photo_url} alt={member.name} className="h-full w-full object-cover" />
                            ) : (
                              <div className={`flex h-full w-full items-center justify-center bg-gradient-to-br ${gradient} text-xl font-black text-white`}>
                                {(member.name || 'S').charAt(0).toUpperCase()}
                              </div>
                            )}
                          </div>
                          <h3 className="mt-3 text-sm font-bold text-slate-900">{member.name}</h3>
                          <p className="mt-0.5 text-xs text-slate-500">{member.staff_code}</p>
                          <p className="mt-1 text-xs font-medium text-[#8B0000]">{member.designation || 'Staff'}</p>
                        </div>
                        <div className="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                          <span className={`rounded-lg px-2 py-0.5 text-[11px] font-semibold ${emp.cls}`}>{emp.label}</span>
                          <span className={`rounded-lg px-2 py-0.5 text-[11px] font-semibold ${st.cls}`}>{st.label}</span>
                        </div>
                        <div className="mt-3 space-y-0.5 text-center">
                          <p className="text-xs text-slate-600">{member.department || 'General Administration'}</p>
                          <p className="text-[11px] text-slate-400">{member.email || '\u2014'}</p>
                          <p className="text-[11px] text-slate-400">{member.phone || '\u2014'}</p>
                        </div>
                        <div className="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                          <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${member.public_visible ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-500'}`}>{member.public_visible ? 'Public' : 'Hidden'}</span>
                          <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${member.featured ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500'}`}>{member.featured ? 'Featured' : 'Standard'}</span>
                        </div>
                        <div className="mt-4 grid grid-cols-2 gap-2">
                          <Link to={`/admin/staff/${member.id}`} className="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</Link>
                          <Link to={`/admin/staff/${member.id}/edit`} className="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</Link>
                        </div>
                      </div>
                    );
                  })}
                </div>
                {meta && (
                  <div className="border-t border-slate-100 mt-5 pt-4">
                    <div className="flex items-center justify-between text-sm text-slate-500">
                      <span>Page {meta.current_page} of {meta.last_page}</span>
                      <div className="flex items-center gap-2">
                        <button onClick={() => { const p = new URLSearchParams(searchParams); p.set('page', String(meta.current_page - 1)); setSearchParams(p); }}
                          disabled={meta.current_page <= 1}
                          className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                        {Array.from({ length: Math.min(meta.last_page, 7) }, (_, i) => i + 1).map(p => (
                          <button key={p} onClick={() => { const p2 = new URLSearchParams(searchParams); p2.set('page', String(p)); setSearchParams(p2); }}
                            className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition ${p === meta.current_page ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>{p}</button>
                        ))}
                        <button onClick={() => { const p = new URLSearchParams(searchParams); p.set('page', String(meta.current_page + 1)); setSearchParams(p); }}
                          disabled={meta.current_page >= meta.last_page}
                          className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                      </div>
                    </div>
                  </div>
                )}
              </>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
