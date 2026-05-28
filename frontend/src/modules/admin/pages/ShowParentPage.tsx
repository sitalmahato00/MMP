import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { Spinner } from '@shared/components/ui/Spinner';
import parentService from '@shared/services/parentService';

const gradients = [
  'from-blue-500 to-indigo-600', 'from-violet-500 to-purple-600', 'from-emerald-500 to-teal-600',
  'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-sky-600',
];

export default function ShowParentPage() {
  const { id } = useParams();
  const queryClient = useQueryClient();
  const [tab, setTab] = useState('overview');

  const { data, isLoading } = useQuery({
    queryKey: ['parent', id],
    queryFn: () => parentService.show(Number(id)),
    enabled: !!id,
  });

  const parent = data?.data;
  const grad = gradients[(parent?.id ?? 0) % 6];
  const isActive = parent?.user?.is_active ?? false;

  const deleteMutation = useMutation({
    mutationFn: () => parentService.destroy(Number(id)),
    onSuccess: () => { toast.success('Parent account deleted.'); queryClient.invalidateQueries({ queryKey: ['parents'] }); },
    onError: () => toast.error('Failed to delete parent.'),
  });

  const handleDelete = () => {
    if (window.confirm('Are you sure you want to delete this parent account? This action cannot be undone.')) {
      deleteMutation.mutate(undefined, { onSuccess: () => window.location.href = '/admin/parents' });
    }
  };

  if (isLoading) return <div className="flex justify-center py-20"><Spinner /></div>;
  if (!parent) return <div className="py-20 text-center text-sm text-slate-500">Parent not found.</div>;

  const childrenCount = parent.students?.length ?? 0;
  const lastLogin = (parent as any).last_login ?? null;

  const tabs = [
    { key: 'overview', label: 'Overview' },
    { key: 'children', label: 'Children' },
    { key: 'account', label: 'Account' },
  ];

  return (
    <div>
      <div className="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 shadow-lg">
        <div className="absolute inset-0 opacity-5" style={{ backgroundImage: `url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="1"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')` }} />
        <div className="relative px-6 py-7">
          <div className="flex flex-wrap items-start gap-5">
            {parent.user?.avatar ? (
              <img src={parent.user.avatar} alt="" className="h-20 w-20 flex-shrink-0 rounded-2xl object-cover ring-4 ring-white/20 shadow-lg" />
            ) : (
              <div className={`flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br ${grad} text-3xl font-black text-white shadow-lg ring-4 ring-white/10`}>
                {(parent.user?.name ?? 'P').charAt(0).toUpperCase()}
              </div>
            )}
            <div className="min-w-0 flex-1">
              <div className="flex flex-wrap items-center gap-2">
                <h1 className="text-2xl font-black text-white leading-tight">{parent.user?.name}</h1>
                {isActive ? (
                  <span className="rounded-lg px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-200">Active</span>
                ) : (
                  <span className="rounded-lg px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-600 ring-1 ring-slate-200">Inactive</span>
                )}
              </div>
              <p className="mt-1 text-sm text-slate-400">
                {parent.relation_to_student ? parent.relation_to_student.charAt(0).toUpperCase() + parent.relation_to_student.slice(1) : 'Parent'} · {parent.occupation ?? 'Occupation not set'}
              </p>
              <div className="mt-3 flex flex-wrap gap-2">
                {parent.user?.phone && (
                  <span className="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-200">
                    <svg className="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    {parent.user.phone}
                  </span>
                )}
                {parent.user?.email && (
                  <span className="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-200">
                    <svg className="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    {parent.user.email}
                  </span>
                )}
                <span className="inline-flex items-center gap-1.5 rounded-lg bg-violet-500/30 px-3 py-1.5 text-xs font-bold text-violet-200">
                  {childrenCount} {childrenCount === 1 ? 'Child' : 'Children'}
                </span>
              </div>
            </div>
            <div className="flex flex-shrink-0 flex-wrap gap-2">
              <Link to={`/admin/parents/${parent.id}/edit`}
                className="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Edit
              </Link>
              <Link to="/admin/parents"
                className="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
              </Link>
            </div>
          </div>
          <div className="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div className="rounded-xl bg-white/10 p-3 text-center">
              <p className="text-2xl font-black text-white">{childrenCount}</p>
              <p className="mt-0.5 text-[11px] text-slate-400">Children</p>
            </div>
            <div className="rounded-xl bg-white/10 p-3 text-center">
              <p className="text-2xl font-black text-slate-300">—</p>
              <p className="mt-0.5 text-[11px] text-slate-400">Avg Attendance</p>
            </div>
            <div className="rounded-xl bg-white/10 p-3 text-center">
              <p className="text-2xl font-black text-white">—</p>
              <p className="mt-0.5 text-[11px] text-slate-400">Avg Marks</p>
            </div>
            <div className="rounded-xl bg-white/10 p-3 text-center">
              <p className="text-2xl font-black text-white">{lastLogin ?? 'Never'}</p>
              <p className="mt-0.5 text-[11px] text-slate-400">Last Login</p>
            </div>
          </div>
        </div>
      </div>

      <div className="mb-6 flex gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm overflow-x-auto">
        {tabs.map(t => (
          <button key={t.key} onClick={() => setTab(t.key)}
            className={`rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap ${tab === t.key ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'}`}>
            {t.label}
          </button>
        ))}
      </div>

      {tab === 'overview' && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
              <div className="border-b border-slate-100 px-5 py-4">
                <h3 className="font-bold text-slate-900">Personal Information</h3>
              </div>
              <div className="p-5 space-y-3">
                {[
                  { label: 'Full Name', value: parent.user?.name },
                  { label: 'Email', value: parent.user?.email },
                  { label: 'Phone', value: parent.user?.phone },
                  { label: 'Address', value: (parent as any).user?.address },
                  { label: 'Relation', value: parent.relation_to_student ? parent.relation_to_student.charAt(0).toUpperCase() + parent.relation_to_student.slice(1) : 'Parent' },
                  { label: 'Occupation', value: parent.occupation },
                ].map(f => (
                  <div key={f.label} className="flex justify-between py-2 border-b border-slate-50 last:border-0">
                    <span className="text-xs font-semibold text-slate-500">{f.label}</span>
                    <span className="text-sm text-slate-900 text-right">{f.value ?? '—'}</span>
                  </div>
                ))}
              </div>
            </div>
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
              <div className="border-b border-slate-100 px-5 py-4">
                <h3 className="font-bold text-slate-900">Account Details</h3>
              </div>
              <div className="p-5 space-y-3">
                {[
                  { label: 'Account Status', value: isActive ? 'Active' : 'Inactive' },
                  { label: 'Last Login', value: lastLogin ?? 'Never' },
                  { label: 'Created', value: parent.created_at ? new Date(parent.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—' },
                  { label: 'Children Linked', value: childrenCount },
                ].map(f => (
                  <div key={f.label} className="flex justify-between py-2 border-b border-slate-50 last:border-0">
                    <span className="text-xs font-semibold text-slate-500">{f.label}</span>
                    <span className="text-sm text-slate-900 text-right">{f.value ?? '—'}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}

      {tab === 'children' && (
        <div className="space-y-4">
          {parent.students && parent.students.length > 0 ? (
            parent.students.map((s: any) => (
              <div key={s.id} className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div className="p-5">
                  <div className="flex flex-wrap items-start gap-4">
                    {s.user?.avatar ? (
                      <img src={s.user.avatar} className="h-14 w-14 rounded-xl object-cover ring-2 ring-slate-100" />
                    ) : (
                      <div className="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-xl font-bold text-white">
                        {(s.user?.name ?? 'S').charAt(0).toUpperCase()}
                      </div>
                    )}
                    <div className="flex-1 min-w-0">
                      <div className="flex flex-wrap items-center gap-2">
                        <Link to={`/admin/students/${s.id}`} className="text-lg font-bold text-slate-900 hover:text-[#8B0000]">{s.user?.name}</Link>
                        <span className="rounded-lg bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">{s.student_no}</span>
                      </div>
                      <div className="mt-1 flex flex-wrap gap-2 text-xs text-slate-500">
                        <span>{s.department?.name ?? '—'}</span>
                        <span>→</span>
                        <span>{s.program?.name ?? '—'}</span>
                        <span>→</span>
                        <span className="font-bold text-violet-600">Sem {s.current_semester}</span>
                      </div>
                    </div>
                    <Link to={`/admin/students/${s.id}`}
                      className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                      View Child
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </Link>
                  </div>
                  <div className="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div className="rounded-xl bg-slate-50 p-3 text-center">
                      <p className="text-xl font-black text-slate-700">—</p>
                      <p className="mt-0.5 text-[11px] text-slate-500">Attendance</p>
                    </div>
                    <div className="rounded-xl bg-blue-50 p-3 text-center">
                      <p className="text-xl font-black text-blue-700">—</p>
                      <p className="mt-0.5 text-[11px] text-slate-500">Avg Marks</p>
                    </div>
                    <div className="rounded-xl bg-violet-50 p-3 text-center">
                      <p className="text-xl font-black text-violet-700">—</p>
                      <p className="mt-0.5 text-[11px] text-slate-500">Exam Records</p>
                    </div>
                    <div className="rounded-xl bg-slate-50 p-3 text-center">
                      <p className="text-xl font-black text-slate-700">Sem {s.current_semester}</p>
                      <p className="mt-0.5 text-[11px] text-slate-500">Current</p>
                    </div>
                  </div>
                </div>
              </div>
            ))
          ) : (
            <div className="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                <svg className="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
              </div>
              <h3 className="mt-4 text-lg font-bold text-slate-900">No children linked</h3>
              <p className="mt-1 text-sm text-slate-500">Link students to this parent account via the Edit page.</p>
              <Link to={`/admin/parents/${parent.id}/edit`} className="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">
                Link Children
              </Link>
            </div>
          )}
        </div>
      )}

      {tab === 'account' && (
        <div className="space-y-6">
          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div className="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
              <h3 className="font-bold text-slate-900">Account Management</h3>
            </div>
            <div className="p-5 space-y-4">
              <div className="flex items-center justify-between rounded-xl bg-slate-50 p-4">
                <div>
                  <p className="text-sm font-bold text-slate-900">Account Status</p>
                  <p className="text-xs text-slate-500 mt-0.5">
                    {isActive ? 'This account is active and can log in.' : 'This account is disabled.'}
                  </p>
                </div>
                {isActive ? (
                  <span className="rounded-lg bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Active</span>
                ) : (
                  <span className="rounded-lg bg-red-50 px-3 py-1 text-xs font-bold text-red-700">Disabled</span>
                )}
              </div>
              <div className="flex flex-wrap gap-3">
                <Link to={`/admin/parents/${parent.id}/edit`}
                  className="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  Edit Account
                </Link>
                <button onClick={handleDelete}
                  className="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 transition">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  Delete Account
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
