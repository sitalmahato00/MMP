import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useState } from 'react';
import teacherService from '@services/teacherService';
import { BsDate } from '@components/ui/BsDate';
import { Spinner } from '@components/ui/Spinner';
import toast from 'react-hot-toast';
import { clsx } from 'clsx';

const GRADIENTS = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
const TABS = ['overview', 'subjects', 'timeline'] as const;
type Tab = typeof TABS[number];

function InfoRow({ label, value }: { label: string; value?: string | null }) {
  return (
    <div className="flex gap-3 py-2.5 border-b border-slate-100 last:border-0">
      <dt className="w-32 flex-shrink-0 text-xs text-slate-500 pt-0.5">{label}</dt>
      <dd className="font-medium text-slate-800 min-w-0 break-words">{value || '—'}</dd>
    </div>
  );
}

export default function TeacherShowPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [tab, setTab] = useState<Tab>('overview');
  const [confirmDelete, setConfirmDelete] = useState(false);

  const { data: res, isLoading } = useQuery({ queryKey: ['teacher', id], queryFn: () => teacherService.show(Number(id)) });
  const deleteMutation = useMutation({
    mutationFn: () => teacherService.destroy(Number(id)),
    onSuccess: () => { toast.success('Teacher deleted.'); queryClient.invalidateQueries({ queryKey: ['teachers'] }); navigate('..'); },
    onError: () => toast.error('Failed to delete.'),
  });

  if (isLoading) return <div className="flex h-64 items-center justify-center"><Spinner size="lg" /></div>;
  const teacher = res?.data;
  if (!teacher) return <div className="text-center py-20 text-slate-500">Teacher not found.</div>;

  const grad = GRADIENTS[teacher.id % 6];

  return (
    <div className="space-y-6">
      {/* Hero */}
      <div className={`relative overflow-hidden rounded-2xl bg-gradient-to-br ${grad} shadow-lg`}>
        <div className="relative px-8 py-10">
          <div className="flex flex-wrap items-end gap-6">
            {teacher.user.avatar
              ? <img src={teacher.user.avatar} alt="" className="h-24 w-24 rounded-2xl object-cover ring-4 ring-white/60 shadow-xl" />
              : <div className={`flex h-24 w-24 items-center justify-center rounded-2xl bg-white/25 text-4xl font-black text-white ring-4 ring-white/60 shadow-xl`}>{teacher.user.name.charAt(0).toUpperCase()}</div>}
            <div className="flex-1 min-w-0">
              <h1 className="text-3xl font-black text-white leading-tight">{teacher.user.name}</h1>
              <p className="mt-1 text-white/75 text-sm">{teacher.employee_id ?? 'No Employee ID'} · {teacher.department?.name ?? 'No Department'}</p>
              <div className="mt-3 flex flex-wrap gap-2">
                <span className="rounded-full bg-white/25 px-3 py-1 text-xs font-bold text-white">{teacher.designation ?? 'Teacher'}</span>
                <span className={clsx('rounded-full px-3 py-1 text-xs font-bold text-white', teacher.status === 'active' ? 'bg-emerald-400/40' : 'bg-white/15')}>
                  {teacher.status === 'active' ? '● Active' : '● Inactive'}
                </span>
              </div>
            </div>
            <div className="flex gap-2 flex-shrink-0 self-start">
              <Link to={`../${id}/edit`} className="inline-flex items-center gap-2 rounded-xl bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-bold text-white transition">Edit</Link>
              <button onClick={() => setConfirmDelete(true)} className="inline-flex items-center gap-2 rounded-xl bg-red-500/30 hover:bg-red-500/50 px-4 py-2 text-sm font-bold text-white transition">Delete</button>
              <Link to=".." className="inline-flex items-center gap-2 rounded-xl bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-bold text-white transition">← Back</Link>
            </div>
          </div>
        </div>
      </div>

      {/* Tabs */}
      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div className="flex border-b border-slate-100 overflow-x-auto">
          {TABS.map(t => (
            <button key={t} onClick={() => setTab(t)}
              className={clsx('whitespace-nowrap px-5 py-3.5 text-sm font-semibold transition flex-shrink-0',
                tab === t ? 'border-b-2 border-blue-600 text-blue-700 bg-blue-50/50 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50')}>
              {t.charAt(0).toUpperCase() + t.slice(1)}
            </button>
          ))}
        </div>

        {tab === 'overview' && (
          <div className="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2 space-y-5">
              <div className="rounded-xl border border-slate-100 bg-slate-50 p-5">
                <h3 className="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Personal Information</h3>
                <dl className="text-sm">
                  <InfoRow label="Full Name" value={teacher.user.name} />
                  <InfoRow label="Email" value={teacher.user.email} />
                  <InfoRow label="Phone" value={teacher.user.phone} />
                  <InfoRow label="Gender" value={(teacher.user as any).gender ? String((teacher.user as any).gender).charAt(0).toUpperCase() + String((teacher.user as any).gender).slice(1) : undefined} />
                  <InfoRow label="Date of Birth" value={(teacher.user as any).dob ? undefined : undefined} />
                  <InfoRow label="Address" value={(teacher.user as any).address} />
                </dl>
              </div>
              <div className="rounded-xl border border-slate-100 bg-slate-50 p-5">
                <h3 className="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Employment Details</h3>
                <dl className="grid grid-cols-2 gap-x-6 text-sm">
                  <InfoRow label="Employee ID" value={teacher.employee_id} />
                  <InfoRow label="Department" value={teacher.department?.name} />
                  <InfoRow label="Designation" value={teacher.designation} />
                  <InfoRow label="Employment Type" value={(teacher as any).employment_type} />
                  <InfoRow label="Status" value={teacher.status === 'active' ? 'Active' : 'Inactive'} />
                  <InfoRow label="Added On" value={teacher.created_at} />
                </dl>
              </div>
            </div>
            <div className="space-y-5">
              {(teacher.qualification || (teacher as any).specialization) && (
                <div className="rounded-xl border border-slate-100 bg-slate-50 p-5">
                  <h3 className="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Academic Background</h3>
                  <dl className="text-sm">
                    <InfoRow label="Qualification" value={teacher.qualification} />
                    <InfoRow label="Specialization" value={(teacher as any).specialization} />
                  </dl>
                </div>
              )}
              {teacher.subjects && teacher.subjects.length > 0 && (
                <div className="rounded-xl border border-slate-100 bg-slate-50 p-5">
                  <h3 className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Assigned Subjects</h3>
                  <div className="flex flex-wrap gap-2">
                    {teacher.subjects.map(s => (
                      <span key={s.id} className="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700">{s.name}</span>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {tab === 'subjects' && (
          <div className="p-6">
            {!teacher.subjects?.length
              ? <p className="py-8 text-center text-sm text-slate-400">No subjects assigned.</p>
              : <div className="overflow-hidden rounded-xl border border-slate-200">
                  <table className="w-full text-sm">
                    <thead className="bg-slate-50 border-b border-slate-100">
                      <tr>{['Subject','Code'].map(h => <th key={h} className="px-4 py-2.5 text-left text-[11px] font-bold uppercase text-slate-400">{h}</th>)}</tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {teacher.subjects.map(s => (
                        <tr key={s.id} className="hover:bg-slate-50">
                          <td className="px-4 py-2.5 font-medium text-slate-700">{s.name}</td>
                          <td className="px-4 py-2.5 text-slate-500 font-mono text-xs">{s.code}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>}
          </div>
        )}

        {tab === 'timeline' && (
          <div className="flex flex-col items-center justify-center py-20 text-center">
            <p className="text-sm font-medium text-slate-500">No activity log entries yet.</p>
          </div>
        )}
      </div>

      {/* Delete confirm modal */}
      {confirmDelete && (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-4">
          <div className="absolute inset-0 bg-black/50" onClick={() => setConfirmDelete(false)} />
          <div className="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            <h3 className="text-base font-black text-slate-900">Delete Teacher?</h3>
            <p className="mt-1 text-sm text-slate-500">This will permanently remove <strong>{teacher.user.name}</strong>. This action cannot be undone.</p>
            <div className="mt-5 flex gap-3">
              <button onClick={() => setConfirmDelete(false)} className="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
              <button onClick={() => deleteMutation.mutate()} className="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition">Yes, Delete</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
