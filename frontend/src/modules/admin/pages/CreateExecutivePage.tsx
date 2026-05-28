import { useMutation } from '@tanstack/react-query';
import { useNavigate, Link } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import toast from 'react-hot-toast';
import executiveService from '@shared/services/executiveService';

export default function CreateExecutivePage() {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: (payload: Record<string, unknown>) => executiveService.create(payload),
    onSuccess: () => { toast.success('Executive created.'); navigate('/admin/executives'); },
    onError: () => toast.error('Failed to create executive.'),
  });

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const payload: Record<string, unknown> = {};
    fd.forEach((v, k) => {
      if (k === 'is_current') payload[k] = fd.get(k) === 'on';
      else if (k === 'order') payload[k] = Number(v);
      else payload[k] = v;
    });
    mutation.mutate(payload);
  };

  return (
    <div className="space-y-5">
      <div>
        <Link to="/admin/executives" className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 transition mb-3">
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Executives
        </Link>
        <h1 className="text-2xl font-black tracking-tight text-slate-900">Create Executive</h1>
        <p className="mt-0.5 text-sm text-slate-500">Add a new executive team member.</p>
      </div>

      <form onSubmit={handleSubmit} className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 space-y-5">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
            <input name="name" required className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Type</label>
            <select name="type" required className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select type</option>
              <option value="principal">Principal</option>
              <option value="president">President</option>
            </select>
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Designation</label>
            <input name="designation" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Start Date (BS)</label>
            <input name="start_date_bs" required placeholder="YYYY-MM-DD" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">End Date (BS)</label>
            <input name="end_date_bs" placeholder="YYYY-MM-DD (optional)" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Order</label>
            <input name="order" type="number" defaultValue={0} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>
        </div>

        <div className="flex items-center gap-2">
          <input name="is_current" type="checkbox" defaultChecked className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
          <label className="text-sm text-slate-700">Current Executive</label>
        </div>

        <div className="space-y-1.5">
          <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Message</label>
          <textarea name="message" rows={4} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
        </div>

        <div className="flex items-center gap-3 pt-2">
          <button type="submit" disabled={mutation.isPending} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition disabled:opacity-50">
            <Save className="h-4 w-4" /> {mutation.isPending ? 'Saving...' : 'Create Executive'}
          </button>
          <Link to="/admin/executives" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
