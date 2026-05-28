import { useMutation } from '@tanstack/react-query';
import { useNavigate, Link } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import toast from 'react-hot-toast';
import parentService from '@shared/services/parentService';

export default function CreateParentPage() {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: (payload: Record<string, unknown>) => parentService.create(payload),
    onSuccess: () => { toast.success('Parent created.'); navigate('/admin/parents'); },
    onError: () => toast.error('Failed to create parent.'),
  });

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const payload: Record<string, unknown> = {};
    fd.forEach((v, k) => { payload[k] = v; });
    mutation.mutate(payload);
  };

  return (
    <div className="space-y-5">
      <div>
        <Link to="/admin/parents" className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 transition mb-3">
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Parents
        </Link>
        <h1 className="text-2xl font-black tracking-tight text-slate-900">Create Parent</h1>
        <p className="mt-0.5 text-sm text-slate-500">Create a new parent/guardian account.</p>
      </div>

      <form onSubmit={handleSubmit} className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 space-y-5">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
            <input name="name" required className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
            <input name="email" type="email" required className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</label>
            <input name="phone" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</label>
            <input name="address" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Password</label>
            <input name="password" type="password" required className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Occupation</label>
            <input name="occupation" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Relation to Student</label>
            <select name="relation_to_student" required className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select relation</option>
              <option value="father">Father</option>
              <option value="mother">Mother</option>
              <option value="guardian">Guardian</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div className="rounded-xl bg-blue-50 border border-blue-200 px-4 py-3">
          <p className="text-xs text-blue-700">Students can be linked to this parent after creation from the student management section.</p>
        </div>

        <div className="flex items-center gap-3 pt-2">
          <button type="submit" disabled={mutation.isPending} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition disabled:opacity-50">
            <Save className="h-4 w-4" /> {mutation.isPending ? 'Saving...' : 'Create Parent'}
          </button>
          <Link to="/admin/parents" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
