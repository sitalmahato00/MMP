import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import { BsDate } from '@components/ui/BsDate';
import { Pagination } from '@components/ui/Pagination';
import { StatCard } from '@components/ui/StatCard';
import userService from '@shared/services/userService';

const ROLE_COLORS: Record<string, string> = {
  principal: 'red',
  hod: 'blue',
  teacher: 'green',
  student: 'purple',
  parent: 'yellow',
  alumni: 'gray',
};

const GRADIENTS = [
  'from-blue-500 to-indigo-600',
  'from-violet-500 to-purple-600',
  'from-emerald-500 to-teal-600',
  'from-amber-500 to-orange-600',
  'from-rose-500 to-pink-600',
  'from-cyan-500 to-sky-600',
];

export default function UsersPage() {
  const [search, setSearch] = useState('');
  const [role, setRole] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [view, setView] = useState<'table' | 'cards'>(() => {
    try { return (localStorage.getItem('mmp_users_view') as 'table' | 'cards') ?? 'table'; } catch { return 'table'; }
  });

  const hasFilters = search || role || status;

  const { data: res, isLoading } = useQuery({
    queryKey: ['system-users', search, role, status, page],
    queryFn: () => userService.list({ page, per_page: 20, search, role: role || undefined, status: status || undefined }),
  });

  const users = res?.data?.data ?? [];
  const meta = res?.data?.meta;

  const totalUsers = meta?.total ?? 0;
  const currentPageUsers = users;
  const activeUsers = currentPageUsers.filter((u: any) => u.is_active).length;
  const studentCount = currentPageUsers.filter((u: any) => {
    const roles = u.roles?.map((r: any) => r.name) ?? (u.role ? [u.role] : []);
    return roles.includes('student');
  }).length;
  const teacherCount = currentPageUsers.filter((u: any) => {
    const roles = u.roles?.map((r: any) => r.name) ?? (u.role ? [u.role] : []);
    return roles.includes('teacher');
  }).length;
  const parentCount = currentPageUsers.filter((u: any) => {
    const roles = u.roles?.map((r: any) => r.name) ?? (u.role ? [u.role] : []);
    return roles.includes('parent');
  }).length;
  const alumniCount = currentPageUsers.filter((u: any) => {
    const roles = u.roles?.map((r: any) => r.name) ?? (u.role ? [u.role] : []);
    return roles.includes('alumni');
  }).length;
  const inactiveCount = currentPageUsers.filter((u: any) => !u.is_active).length;
  const withDeptCount = currentPageUsers.filter((u: any) => {
    const roles = u.roles?.map((r: any) => r.name) ?? (u.role ? [u.role] : []);
    if (roles.includes('hod') && u.hod_department) return true;
    return false;
  }).length;

  const switchView = (v: 'table' | 'cards') => {
    setView(v);
    try { localStorage.setItem('mmp_users_view', v); } catch {}
  };

  const clearFilters = () => {
    setSearch('');
    setRole('');
    setStatus('');
    setPage(1);
  };

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">User Management</h1>
          <p className="mt-0.5 text-sm text-slate-500">Manage all system accounts, role assignments, and department affiliations across the institution.</p>
        </div>
        <Link to="create" className="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Add User
        </Link>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <StatCard title="Total Users" value={totalUsers.toLocaleString()} color="blue" icon={
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        } />
        <StatCard title="Active Users" value={activeUsers.toLocaleString()} color="green" icon={
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
        } />
        <StatCard title="Students" value={studentCount.toLocaleString()} color="purple" icon={
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        } />
        <StatCard title="Teachers" value={teacherCount.toLocaleString()} color="purple" icon={
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        } />
      </div>

      <div className="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 px-4 py-3">
        <div className="flex flex-wrap gap-3 items-center">
          <input type="search" value={search} onChange={e => { setSearch(e.target.value); setPage(1); }} placeholder="Search name or email…" className="flex-1 min-w-[200px] rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          <select value={role} onChange={e => { setRole(e.target.value); setPage(1); }} className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="principal">Principal</option>
            <option value="hod">Hod</option>
            <option value="executive">Executive</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <option value="parent">Parent</option>
            <option value="alumni">Alumni</option>
          </select>
          <select value={status} onChange={e => { setStatus(e.target.value); setPage(1); }} className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <button type="button" onClick={() => setPage(1)} className="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Filter</button>
          {hasFilters && (
            <button type="button" onClick={clearFilters} className="text-sm font-semibold text-slate-500 hover:text-slate-700 transition">Clear</button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <StatCard title="Parents" value={parentCount.toLocaleString()} color="yellow" icon={
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        } />
        <StatCard title="Alumni" value={alumniCount.toLocaleString()} color="green" icon={
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        } />
        <StatCard title="With Departments" value={withDeptCount.toLocaleString()} color="purple" icon={
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        } />
        <StatCard title="Inactive Users" value={inactiveCount.toLocaleString()} color="red" icon={
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        } />
      </div>

      <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5">
          <p className="text-sm text-gray-500">
            {meta && meta.total > 0 ? (
              <>Showing <span className="font-semibold text-gray-700">{meta.from}–{meta.to}</span> of <span className="font-semibold text-gray-700">{(meta.total).toLocaleString()}</span> users</>
            ) : (
              <>No users match your filters</>
            )}
          </p>
          <div className="flex items-center rounded-xl border border-gray-200 p-1 gap-0.5 flex-shrink-0">
            <button type="button" onClick={() => switchView('table')}
              className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition ${view === 'table' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-700'}`}>
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
              Table
            </button>
            <button type="button" onClick={() => switchView('cards')}
              className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition ${view === 'cards' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-700'}`}>
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
              Cards
            </button>
          </div>
        </div>

        {view === 'table' && (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-10 text-left">#</th>
                  <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">User</th>
                  <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Role</th>
                  <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Department</th>
                  <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Phone</th>
                  <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Status</th>
                  <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">Joined</th>
                  <th className="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 bg-white">
                {isLoading ? (
                  <tr>
                    <td colSpan={8} className="py-14 text-center">
                      <div className="flex justify-center"><div className="h-8 w-8 animate-spin rounded-full border-4 border-gray-300 border-t-gray-900" /></div>
                    </td>
                  </tr>
                ) : users.length === 0 ? (
                  <tr>
                    <td colSpan={8} className="px-5 py-14 text-center">
                      <svg className="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                      <p className="text-sm font-semibold text-gray-400">No users found</p>
                      <p className="text-xs text-gray-300 mt-1">Try adjusting your search or filters.</p>
                    </td>
                  </tr>
                ) : (
                  users.map((u: any) => {
                    const roleNames = u.roles?.map((r: any) => r.name) ?? (u.role ? [u.role] : []);
                    const isHod = roleNames.includes('hod');
                    const isTeacher = roleNames.includes('teacher');
                    let department: string | null = null;
                    if (isTeacher && u.teacher?.department?.name) department = u.teacher.department.name;
                    else if (isHod && u.hod_department?.name) department = u.hod_department.name;
                    return (
                      <tr key={u.id} className="hover:bg-gray-50/70 transition-colors">
                        <td className="px-5 py-3.5 text-gray-400 font-mono text-xs">{u.id}</td>
                        <td className="px-5 py-3.5">
                          <div className="flex items-center gap-3">
                            {u.avatar_url ? (
                              <img src={u.avatar_url} alt={u.name} className="w-8 h-8 rounded-full object-cover ring-2 ring-gray-100 flex-shrink-0" />
                            ) : (
                              <div className="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-xs font-black text-white flex-shrink-0">
                                {u.name?.charAt(0)?.toUpperCase()}
                              </div>
                            )}
                            <div className="min-w-0">
                              <p className="font-semibold text-gray-900 truncate">{u.name}</p>
                              <p className="text-xs text-gray-400 truncate">{u.email}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-5 py-3.5">
                          <div className="flex flex-wrap gap-1">
                            {roleNames.map((role: string) => {
                              const color = ROLE_COLORS[role] ?? 'gray';
                              return <span key={role} className={`badge-${color}`}>{role}</span>;
                            })}
                          </div>
                        </td>
                        <td className="px-5 py-3.5">
                          {department ? (
                            <span className="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{department}</span>
                          ) : (
                            <span className="text-xs text-gray-400">—</span>
                          )}
                        </td>
                        <td className="px-5 py-3.5 text-gray-500 text-xs">{u.phone || '—'}</td>
                        <td className="px-5 py-3.5">
                          <span className={`inline-flex items-center gap-1.5 badge-${u.is_active ? 'green' : 'red'}`}>
                            <span className={`h-1.5 w-1.5 rounded-full ${u.is_active ? 'bg-green-500' : 'bg-red-500'}`}></span>
                            {u.is_active ? 'Active' : 'Inactive'}
                          </span>
                        </td>
                        <td className="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap"><BsDate date={u.created_at} format="Y, F d" /></td>
                        <td className="px-5 py-3.5">
                          <div className="flex items-center justify-end gap-1">
                            <Link to={`${u.id}`} className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </Link>
                            <Link to={`${u.id}/edit`} className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </Link>
                            <button type="button" className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        )}

        {view === 'cards' && (
          <div className="p-5">
            {isLoading ? (
              <div className="flex justify-center py-16"><div className="h-8 w-8 animate-spin rounded-full border-4 border-gray-300 border-t-gray-900" /></div>
            ) : users.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-16 text-center">
                <p className="text-sm font-medium text-gray-500">No users found.</p>
              </div>
            ) : (
              <>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                  {users.map((u: any, idx: number) => {
                    const roleNames = u.roles?.map((r: any) => r.name) ?? (u.role ? [u.role] : []);
                    const isHod = roleNames.includes('hod');
                    const isTeacher = roleNames.includes('teacher');
                    let department: string | null = null;
                    if (isTeacher && u.teacher?.department?.name) department = u.teacher.department.name;
                    else if (isHod && u.hod_department?.name) department = u.hod_department.name;
                    const grad = GRADIENTS[idx % GRADIENTS.length];
                    return (
                      <div key={u.id} className="group relative rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-all">
                        <div className="flex flex-col items-center text-center">
                          {u.avatar_url ? (
                            <img src={u.avatar_url} alt={u.name} className="h-16 w-16 rounded-2xl object-cover ring-2 ring-white shadow" />
                          ) : (
                            <div className={`flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br ${grad} text-2xl font-black text-white shadow`}>
                              {u.name?.charAt(0)?.toUpperCase()}
                            </div>
                          )}
                          <h3 className="mt-3 text-sm font-bold text-gray-900">{u.name}</h3>
                          <p className="mt-0.5 text-xs text-gray-500">{u.email}</p>
                        </div>
                        <div className="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                          {roleNames.map((role: string) => {
                            const roleColor = ROLE_COLORS[role] ?? 'gray';
                            const badgeCls = {
                              red: 'bg-red-50 text-red-700',
                              blue: 'bg-blue-50 text-blue-700',
                              green: 'bg-green-50 text-green-700',
                              purple: 'bg-purple-50 text-purple-700',
                              yellow: 'bg-yellow-50 text-yellow-700',
                              gray: 'bg-gray-50 text-gray-700',
                            }[roleColor] ?? 'bg-gray-50 text-gray-700';
                            return (
                              <span key={role} className={`rounded-lg ${badgeCls} px-2 py-0.5 text-[11px] font-bold`}>{role}</span>
                            );
                          })}
                        </div>
                        {department && (
                          <div className="mt-2 flex items-center justify-center">
                            <span className="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">{department}</span>
                          </div>
                        )}
                        <div className="mt-3 flex items-center justify-center">
                          {u.is_active ? (
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                              <span className="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                              <span className="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive
                            </span>
                          )}
                        </div>
                        <div className="mt-3 space-y-0.5 text-center">
                          <p className="text-xs text-gray-600">{u.phone || '—'}</p>
                          <p className="text-[11px] text-gray-400">Joined <BsDate date={u.created_at} format="Y, F d" /></p>
                        </div>
                        <div className="mt-4 grid grid-cols-2 gap-2">
                          <Link to={`${u.id}`} className="rounded-lg border border-gray-200 py-1.5 text-center text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">View</Link>
                          <Link to={`${u.id}/edit`} className="rounded-lg bg-gray-900 py-1.5 text-center text-xs font-bold text-white hover:bg-gray-700 transition">Edit</Link>
                        </div>
                      </div>
                    );
                  })}
                </div>
                {meta && (
                  <div className="border-t border-gray-100 mt-5 pt-4">
                    <Pagination meta={meta} onPageChange={setPage} />
                  </div>
                )}
              </>
            )}
          </div>
        )}

        {view === 'table' && meta && (
          <div className="border-t border-gray-100">
            <Pagination meta={meta} onPageChange={setPage} />
          </div>
        )}
      </div>
    </div>
  );
}
