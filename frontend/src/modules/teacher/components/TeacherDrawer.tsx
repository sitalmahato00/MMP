import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import { Drawer } from '@components/ui/Drawer';
import { BsDate } from '@components/ui/BsDate';
import { adToBS, formatBS } from '@shared/utils/nepaliDate';
import teacherService from '@services/teacherService';
import { clsx } from 'clsx';

const GRADIENTS = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
const EMP_MAP: Record<string, { label: string; cls: string }> = {
  permanent:  { label: 'Permanent', cls: 'bg-emerald-100 text-emerald-700' },
  contract:   { label: 'Contract',  cls: 'bg-amber-100 text-amber-700' },
  'part-time':{ label: 'Part-time', cls: 'bg-sky-100 text-sky-700' },
};
const TABS = ['Overview','Subjects','Attendance','Performance'] as const;
type Tab = typeof TABS[number];

function InfoRow({ label, value }: { label: string; value?: string | null }) {
  return (
    <div className="flex gap-3 py-2 border-b border-slate-100 last:border-0">
      <dt className="w-28 flex-shrink-0 text-xs text-slate-500">{label}</dt>
      <dd className="font-medium text-slate-800 min-w-0 truncate text-sm">{value || '—'}</dd>
    </div>
  );
}

interface Props {
  teacherId: number | null;
  onClose: () => void;
}

export function TeacherDrawer({ teacherId, onClose }: Props) {
  const [tab, setTab] = useState<Tab>('Overview');
  const open = teacherId !== null;

  const { data: res, isLoading } = useQuery({
    queryKey: ['teacher-drawer', teacherId],
    queryFn: () => teacherService.show(teacherId!),
    enabled: open,
  });

  const teacher = res?.data;
  const grad = teacher ? GRADIENTS[teacher.id % 6] : GRADIENTS[0];
  const emp = teacher ? (EMP_MAP[(teacher as any).employment_type] ?? { label: (teacher as any).employment_type ?? '—', cls: 'bg-slate-100 text-slate-600' }) : null;

  function bsDateStr(dateStr?: string | null) {
    if (!dateStr) return '—';
    try { const bs = adToBS(new Date(dateStr)); return formatBS(bs, 'Y, F d'); } catch { return dateStr; }
  }

  return (
    <Drawer open={open} onClose={onClose} width="w-[520px] max-w-[95vw]">
      {isLoading || !teacher ? (
        <div className="flex h-64 items-center justify-center">
          <div className="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" />
        </div>
      ) : (
        <>
          {/* Hero */}
          <div className={`relative bg-gradient-to-br ${grad} px-6 pb-6 pt-8`}>
            <div className="flex items-end gap-4">
              {teacher.user.avatar
                ? <img src={teacher.user.avatar} alt="" className="h-20 w-20 rounded-2xl object-cover ring-4 ring-white shadow-lg" />
                : <div className="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/25 text-3xl font-black text-white ring-4 ring-white shadow-lg">{teacher.user.name.charAt(0).toUpperCase()}</div>}
              <div className="min-w-0 pb-1">
                <h2 className="text-xl font-black text-white leading-tight truncate">{teacher.user.name}</h2>
                <p className="text-white/80 text-sm">{teacher.employee_id ?? '—'}</p>
                <div className="mt-2 flex flex-wrap gap-1.5">
                  <span className="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">{teacher.designation ?? 'Teacher'}</span>
                  {emp && <span className={clsx('rounded-full px-2.5 py-0.5 text-xs font-semibold', emp.cls)}>{emp.label}</span>}
                  <span className={clsx('rounded-full px-2.5 py-0.5 text-xs font-semibold', teacher.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-white/30 text-white')}>
                    {teacher.status === 'active' ? '● Active' : '● Inactive'}
                  </span>
                </div>
              </div>
            </div>
            <div className="mt-5 flex gap-2">
              <Link to={`/admin/teachers/${teacher.id}`} onClick={onClose}
                className="flex-1 rounded-xl bg-white/20 hover:bg-white/30 px-3 py-2 text-center text-xs font-bold text-white transition">
                Full Profile →
              </Link>
              <Link to={`/admin/teachers/${teacher.id}/edit`} onClick={onClose}
                className="flex-1 rounded-xl bg-white/20 hover:bg-white/30 px-3 py-2 text-center text-xs font-bold text-white transition">
                Edit
              </Link>
            </div>
          </div>

          {/* Stats strip */}
          <div className="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100 bg-slate-50/60">
            {[
              { val: teacher.subjects?.length ?? 0, lbl: 'Subjects' },
              { val: teacher.department?.name ?? '—', lbl: 'Department' },
              { val: teacher.status === 'active' ? 'Active' : 'Inactive', lbl: 'Status' },
            ].map(s => (
              <div key={s.lbl} className="py-3 text-center">
                <p className="text-sm font-black text-slate-800 truncate px-2">{s.val}</p>
                <p className="text-[10px] text-slate-500">{s.lbl}</p>
              </div>
            ))}
          </div>

          {/* Tabs */}
          <div className="sticky top-0 z-10 flex border-b border-slate-100 overflow-x-auto bg-white">
            {TABS.map(t => (
              <button key={t} onClick={() => setTab(t)}
                className={clsx('whitespace-nowrap px-4 py-3 text-xs font-semibold transition flex-shrink-0 border-b-2',
                  tab === t ? 'border-blue-600 text-blue-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700')}>
                {t}
              </button>
            ))}
          </div>

          {/* Overview */}
          {tab === 'Overview' && (
            <div className="p-5 space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                  <p className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Personal</p>
                  <dl className="space-y-1.5">
                    <InfoRow label="Email" value={teacher.user.email} />
                    <InfoRow label="Phone" value={teacher.user.phone} />
                    <InfoRow label="Gender" value={(teacher.user as any).gender ? String((teacher.user as any).gender).charAt(0).toUpperCase() + String((teacher.user as any).gender).slice(1) : undefined} />
                  </dl>
                </div>
                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                  <p className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Employment</p>
                  <dl className="space-y-1.5">
                    <InfoRow label="Department" value={teacher.department?.name} />
                    <InfoRow label="Joined" value={bsDateStr(teacher.joining_date)} />
                    <InfoRow label="Type" value={(teacher as any).employment_type ? String((teacher as any).employment_type).charAt(0).toUpperCase() + String((teacher as any).employment_type).slice(1) : undefined} />
                  </dl>
                </div>
              </div>
              {(teacher.qualification || (teacher as any).specialization) && (
                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                  <p className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Academic</p>
                  <dl className="space-y-1.5">
                    <InfoRow label="Qualification" value={teacher.qualification} />
                    <InfoRow label="Specialization" value={(teacher as any).specialization} />
                  </dl>
                </div>
              )}
              {(teacher.user as any).address && (
                <div className="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm">
                  <span className="text-slate-400">Address: </span>
                  <span className="font-medium text-slate-700">{(teacher.user as any).address}</span>
                </div>
              )}
            </div>
          )}

          {/* Subjects */}
          {tab === 'Subjects' && (
            <div className="p-5">
              {!teacher.subjects?.length ? (
                <p className="py-12 text-center text-sm text-slate-400">No subjects assigned yet.</p>
              ) : (
                <div className="overflow-hidden rounded-xl border border-slate-200">
                  <table className="w-full text-sm">
                    <thead className="bg-slate-50 border-b border-slate-100">
                      <tr>
                        {['Subject','Code'].map(h => <th key={h} className="px-3 py-2.5 text-left text-[10px] font-bold uppercase text-slate-400">{h}</th>)}
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {teacher.subjects.map(s => (
                        <tr key={s.id} className="hover:bg-slate-50">
                          <td className="px-3 py-2.5 font-medium text-slate-700">{s.name}</td>
                          <td className="px-3 py-2.5 text-slate-500 font-mono text-xs">{s.code}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          )}

          {/* Attendance */}
          {tab === 'Attendance' && (
            <div className="p-5">
              <div className="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                <svg className="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p className="text-sm font-medium">Attendance details on the full profile page.</p>
                <Link to={`/admin/teachers/${teacher.id}?tab=attendance`} onClick={onClose}
                  className="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                  View Full Attendance →
                </Link>
              </div>
            </div>
          )}

          {/* Performance */}
          {tab === 'Performance' && (
            <div className="p-5">
              <div className="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                <svg className="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p className="text-sm font-medium">Performance data on the full profile page.</p>
                <Link to={`/admin/teachers/${teacher.id}?tab=performance`} onClick={onClose}
                  className="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                  View Performance →
                </Link>
              </div>
            </div>
          )}
        </>
      )}
    </Drawer>
  );
}
