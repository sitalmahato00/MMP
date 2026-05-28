import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Heart, Star } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import { BsDate } from '@components/ui/BsDate';
import { Badge } from '@components/ui/Badge';
import parentService from '@shared/services/parentService';

export default function ShowParentPage() {
  const { id } = useParams();

  const { data, isLoading } = useQuery({
    queryKey: ['parent', id],
    queryFn: () => parentService.show(Number(id)),
    enabled: !!id,
  });

  const parent = data?.data;

  if (isLoading) return <div className="flex justify-center py-20"><Spinner /></div>;
  if (!parent) return <div className="py-20 text-center text-sm text-slate-500">Parent not found.</div>;

  return (
    <div className="space-y-5">
      <div>
        <Link to="/admin/parents" className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 transition mb-3">
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Parents
        </Link>
        <h1 className="text-2xl font-black tracking-tight text-slate-900">{parent.user?.name}</h1>
        <p className="mt-0.5 text-sm text-slate-500">{parent.user?.email}</p>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 space-y-5">
        <div className="flex items-center gap-4 pb-4 border-b border-slate-100">
          <div className="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 text-xl font-black">
            {parent.user?.name?.charAt(0)?.toUpperCase()}
          </div>
          <div>
            <p className="text-lg font-bold text-slate-900">{parent.user?.name}</p>
            <p className="text-sm text-slate-500">{parent.user?.email}</p>
            {parent.user?.phone && <p className="text-xs text-slate-400">{parent.user.phone}</p>}
          </div>
          <div className="ml-auto">
            <Badge variant={parent.user?.is_active ? 'emerald' : 'slate'}>
              {parent.user?.is_active ? 'Active' : 'Inactive'}
            </Badge>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Occupation</label>
            <p className="mt-1 text-sm font-medium text-slate-900">{parent.occupation ?? '—'}</p>
          </div>

          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Relation to Student</label>
            <p className="mt-1 text-sm font-medium text-slate-900">
              {parent.relation_to_student ? parent.relation_to_student.charAt(0).toUpperCase() + parent.relation_to_student.slice(1) : '—'}
            </p>
          </div>

          <div>
            <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Joined</label>
            <p className="mt-1 text-sm font-medium text-slate-900"><BsDate date={parent.created_at} format="Y, F d" /></p>
          </div>
        </div>

        <div>
          <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Linked Students</label>
          {parent.students && parent.students.length > 0 ? (
            <div className="mt-2 space-y-2">
              {parent.students.map((s) => (
                <div key={s.id} className="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-2.5">
                  <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700 text-xs font-black">
                    {s.user?.name?.charAt(0)?.toUpperCase()}
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-slate-900">{s.user?.name}</p>
                    <p className="text-xs text-slate-400">{s.student_no}</p>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="mt-1 text-sm text-slate-500">No students linked yet.</p>
          )}
        </div>
      </div>

      <div className="flex items-center gap-3">
        <Link to={`/admin/parents/${parent.id}/edit`} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
          <Star className="h-4 w-4" /> Edit Parent
        </Link>
        <Link to="/admin/parents" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Back</Link>
      </div>
    </div>
  );
}
