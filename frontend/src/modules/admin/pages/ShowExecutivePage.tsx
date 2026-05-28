import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Star } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import { BsDate } from '@components/ui/BsDate';
import { Badge } from '@components/ui/Badge';
import executiveService from '@shared/services/executiveService';

export default function ShowExecutivePage() {
  const { id } = useParams();

  const { data, isLoading } = useQuery({
    queryKey: ['executive', id],
    queryFn: () => executiveService.show(Number(id)),
    enabled: !!id,
  });

  const exec = data?.data;

  if (isLoading) return <div className="flex justify-center py-20"><Spinner /></div>;
  if (!exec) return <div className="py-20 text-center text-sm text-slate-500">Executive not found.</div>;

  return (
    <div className="space-y-5">
      <div>
        <Link to="/admin/executives" className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 transition mb-3">
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Executives
        </Link>
        <h1 className="text-2xl font-black tracking-tight text-slate-900">{exec.name}</h1>
        <p className="mt-0.5 text-sm text-slate-500">{exec.designation}</p>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 space-y-5">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
            <p className="mt-1 text-sm font-medium text-slate-900">{exec.name}</p>
          </div>

          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Type</label>
            <div className="mt-1">
              <Badge variant={exec.type === 'principal' ? 'blue' : 'amber'}>{exec.type?.charAt(0).toUpperCase() + exec.type?.slice(1)}</Badge>
            </div>
          </div>

          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Designation</label>
            <p className="mt-1 text-sm font-medium text-slate-900">{exec.designation ?? '—'}</p>
          </div>

          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Order</label>
            <p className="mt-1 text-sm font-medium text-slate-900">{exec.order}</p>
          </div>

          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Start Date</label>
            <p className="mt-1 text-sm font-medium text-slate-900"><BsDate date={exec.start_date_bs} format="Y-m-d" /></p>
          </div>

          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">End Date</label>
            <p className="mt-1 text-sm font-medium text-slate-900">
              {exec.end_date_bs ? <BsDate date={exec.end_date_bs} format="Y-m-d" /> : 'Present'}
            </p>
          </div>

          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</label>
            <div className="mt-1">
              <Badge variant={exec.is_current ? 'emerald' : 'slate'}>{exec.is_current ? 'Current' : 'Former'}</Badge>
            </div>
          </div>
        </div>

        {exec.message && (
          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Message</label>
            <p className="mt-1 text-sm text-slate-700 whitespace-pre-wrap">{exec.message}</p>
          </div>
        )}
      </div>

      <div className="flex items-center gap-3">
        <Link to={`/admin/executives/${exec.id}/edit`} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
          <Star className="h-4 w-4" /> Edit Executive
        </Link>
        <Link to="/admin/executives" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Back</Link>
      </div>
    </div>
  );
}
