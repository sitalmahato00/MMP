import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Plus, Star } from 'lucide-react';
import { get, del } from '@shared/api/axios';
import { BsDate } from '@components/ui/BsDate';
import toast from 'react-hot-toast';

export default function ExecutivesPage() {
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['executives'], queryFn: () => get<any>('/v1/executives') });
  const deleteMutation = useMutation({
    mutationFn: (id: number) => del<any>(`/v1/executives/${id}`),
    onSuccess: () => { toast.success('Executive removed.'); queryClient.invalidateQueries({ queryKey: ['executives'] }); },
    onError: () => toast.error('Failed to remove.'),
  });

  const executives = data?.data ?? [];

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Executives</h1>
          <p className="mt-0.5 text-sm text-slate-500">Presidents, Principals, and other executive leadership.</p>
        </div>
        <Link to="create" className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
          <Plus className="h-4 w-4" /> Add Executive
        </Link>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {isLoading ? (
          <div className="flex items-center justify-center py-20"><div className="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" /></div>
        ) : executives.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-20 text-center">
            <Star className="h-12 w-12 text-slate-300 mb-3" />
            <p className="text-sm font-medium text-slate-500">No executives found.</p>
            <Link to="create" className="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 transition"><Plus className="h-4 w-4" /> Add Executive</Link>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-slate-50/70 border-b border-slate-100">
                <tr>{['Executive','Type','Designation','Term','Actions'].map(h => (
                  <th key={h} className="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">{h}</th>
                ))}</tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {executives.map((exec: any) => (
                  <tr key={exec.id} className="group hover:bg-slate-50/60 transition-colors">
                    <td className="px-5 py-3.5">
                      <div className="flex items-center gap-3">
                        {exec.avatar
                          ? <img src={`/storage/${exec.avatar}`} className="h-10 w-10 rounded-xl object-cover flex-shrink-0" alt={exec.name} />
                          : <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 text-sm font-black">{exec.name?.charAt(0)?.toUpperCase()}</div>}
                        <div>
                          <p className="font-semibold text-slate-900">{exec.name}</p>
                          <p className="text-xs text-slate-400">{exec.designation}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-5 py-3.5">
                      <span className="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">{exec.type ? exec.type.charAt(0).toUpperCase() + exec.type.slice(1) : '—'}</span>
                    </td>
                    <td className="px-5 py-3.5 text-sm text-slate-700">{exec.designation ?? '—'}</td>
                    <td className="px-5 py-3.5 text-xs text-slate-500">
                      {exec.start_date_bs && <span><BsDate date={exec.start_date_bs} format="Y-m-d" /> — {exec.end_date_bs ? <BsDate date={exec.end_date_bs} format="Y-m-d" /> : 'Present'}</span>}
                    </td>
                    <td className="px-5 py-3.5">
                      <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Link to={`${exec.id}/edit`} className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </Link>
                        <button onClick={() => { if (confirm(`Remove ${exec.name}?`)) deleteMutation.mutate(exec.id); }}
                          className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
