import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import toast from 'react-hot-toast';
import { Spinner } from '@components/ui/Spinner';
import parentService from '@shared/services/parentService';

export default function EditParentPage() {
  const { id } = useParams();
  const navigate = useNavigate();

  const { data, isLoading } = useQuery({
    queryKey: ['parent', id],
    queryFn: () => parentService.show(Number(id)),
    enabled: !!id,
  });

  const parent = data?.data;

  const mutation = useMutation({
    mutationFn: (payload: Record<string, unknown>) => parentService.update(Number(id), payload),
    onSuccess: () => { toast.success('Parent updated.'); navigate('/admin/parents'); },
    onError: () => toast.error('Failed to update parent.'),
  });

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const payload: Record<string, unknown> = {};
    fd.forEach((v, k) => {
      if (k === 'is_active') payload[k] = fd.get(k) === 'on';
      else payload[k] = v;
    });
    if (!payload.password) delete payload.password;
    mutation.mutate(payload);
  };

  if (isLoading) return <div className="flex justify-center py-20"><Spinner /></div>;

  return (
    <div className="space-y-5">
      <div>
        <Link to="/admin/parents" className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 transition mb-3">
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Parents
        </Link>
        <h1 className="text-2xl font-black tracking-tight text-slate-900">Edit Parent</h1>
        <p className="mt-0.5 text-sm text-slate-500">Update parent details.</p>
      </div>

      <form onSubmit={handleSubmit} className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 space-y-5">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
            <input name="name" defaultValue={parent?.user?.name} required className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
            <input name="email" type="email" defaultValue={parent?.user?.email} required className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</label>
            <input name="phone" defaultValue={parent?.user?.phone} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</label>
            <input name="address" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Password</label>
            <input name="password" type="password" placeholder="Leave blank to keep current" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Occupation</label>
            <input name="occupation" defaultValue={parent?.occupation} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Relation to Student</label>
            <select name="relation_to_student" defaultValue={parent?.relation_to_student} required className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select relation</option>
              <option value="father">Father</option>
              <option value="mother">Mother</option>
              <option value="guardian">Guardian</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <input name="is_active" type="checkbox" defaultChecked={parent?.user?.is_active} className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
          <label className="text-sm text-slate-700">Active</label>
        </div>

        <div className="flex items-center gap-3 pt-2">
          <button type="submit" disabled={mutation.isPending} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition disabled:opacity-50">
            <Save className="h-4 w-4" /> {mutation.isPending ? 'Saving...' : 'Update Parent'}
          </button>
          <Link to="/admin/parents" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
