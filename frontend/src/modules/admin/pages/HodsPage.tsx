import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Plus, Search, UserCheck } from 'lucide-react';
import { get } from '@shared/api/axios';
import { useState } from 'react';
import { BsDate } from '@components/ui/BsDate';
import { Pagination } from '@components/ui/Pagination';
import academicService from '@services/academicService';

export default function HodsPage() {
  const [search, setSearch] = useState('');
  const [deptId, setDeptId] = useState('');
  const [page, setPage] = useState(1);

  const { data, isLoading } = useQuery({
    queryKey: ['hods', search, deptId, page],
    queryFn: () => {
      const p = new URLSearchParams({ page: String(page), per_page: '20' });
      if (search) p.set('search', search);
      if (deptId) p.set('department_id', deptId);
      return get<any>(`/v1/hods?${p}`);
    },
  });
  const { data: deptsRes } = useQuery({ queryKey: ['departments'], queryFn: academicService.departments, staleTime: Infinity });

  const hods = data?.data?.data ?? [];
  const meta = data?.data?.meta;

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">HODs</h1>
          <p className="mt-0.5 text-sm text-slate-500">Head of Department accounts and assignments.</p>
        </div>
        <Link to="create" className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
          <Plus className="h-4 w-4" /> Assign HOD
        </Link>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-wrap gap-3">
        <div className="relative flex-1 min-w-[200px]">
          <Search className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
          <input type="search" placeholder="Search name or email…" value={search} onChange={e => { setSearch(e.target.value); setPage(1); }}
            className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
        </div>
        <select value={deptId} onChange={e => { setDeptId(e.target.value); setPage(1); }}
          className="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
          <option value="">All Departments</option>
          {(deptsRes?.data ?? []).map((d: any) => <option key={d.id} value={d.id}>{d.name}</option>)}
        </select>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {isLoading ? (
          <div className="flex items-center justify-center py-20"><div className="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" /></div>
        ) : hods.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-20 text-center">
            <UserCheck className="h-12 w-12 text-slate-300 mb-3" />
            <p className="text-sm font-medium text-slate-500">No HODs found.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-slate-50/70 border-b border-slate-100">
                <tr>{['HOD','Department','Status','Assigned','Actions'].map(h => (
                  <th key={h} className="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">{h}</th>
                ))}</tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {hods.map((hod: any) => (
                  <tr key={hod.id} className="group hover:bg-slate-50/60 transition-colors">
                    <td className="px-5 py-3.5">
                      <div className="flex items-center gap-3">
                        {hod.user?.avatar
                          ? <img src={hod.user.avatar} className="h-9 w-9 rounded-xl object-cover flex-shrink-0" alt={hod.user?.name} />
                          : <div className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 text-sm font-black">{hod.user?.name?.charAt(0)?.toUpperCase()}</div>}
                        <div>
                          <p className="font-semibold text-slate-900">{hod.user?.name}</p>
                          <p className="text-xs text-slate-400">{hod.user?.email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-5 py-3.5 text-sm text-slate-700">{hod.department?.name ?? '—'}</td>
                    <td className="px-5 py-3.5">
                      <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${hod.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>
                        <span className={`h-1.5 w-1.5 rounded-full ${hod.is_active ? 'bg-emerald-500' : 'bg-slate-400'}`} />{hod.is_active ? 'Active' : 'Inactive'}
                      </span>
                    </td>
                    <td className="px-5 py-3.5 text-xs text-slate-400"><BsDate date={hod.created_at} format="Y, F d" /></td>
                    <td className="px-5 py-3.5">
                      <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Link to={`${hod.id}/edit`} className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </Link>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
        {meta && <div className="border-t border-slate-100 px-5 py-4"><Pagination meta={meta} onPageChange={setPage} /></div>}
      </div>
    </div>
  );
}
