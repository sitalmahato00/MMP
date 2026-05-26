import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import { Drawer } from '@components/ui/Drawer';
import { BsDate } from '@components/ui/BsDate';
import { adToBS, formatBS } from '@shared/utils/nepaliDate';
import studentService from '@services/studentService';
import { clsx } from 'clsx';

const GRADIENTS = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
const STATUS_MAP: Record<string, { label: string; cls: string }> = {
  active:    { label: 'Active',    cls: 'bg-blue-50 text-blue-700' },
  inactive:  { label: 'Inactive',  cls: 'bg-slate-100 text-slate-600' },
  graduated: { label: 'Alumni',    cls: 'bg-emerald-50 text-emerald-700' },
  suspended: { label: 'Suspended', cls: 'bg-amber-50 text-amber-700' },
  dropped:   { label: 'Dropped',   cls: 'bg-red-50 text-red-700' },
};
const TABS = ['Overview','Attendance','Marks','Assignments','Parent','Timeline'] as const;
type Tab = typeof TABS[number];

function InfoRow({ label, value }: { label: string; value?: string | null }) {
  return (
    <div className="flex gap-4 px-4 py-2.5 border-b border-slate-100 last:border-0">
      <dt className="w-32 flex-shrink-0 text-xs text-slate-500">{label}</dt>
      <dd className="font-medium text-slate-800 min-w-0 truncate text-sm">{value || '—'}</dd>
    </div>
  );
}

interface Props {
  studentId: number | null;
  onClose: () => void;
}

export function StudentDrawer({ studentId, onClose }: Props) {
  const [tab, setTab] = useState<Tab>('Overview');
  const open = studentId !== null;

  const { data: res, isLoading } = useQuery({
    queryKey: ['student-drawer', studentId],
    queryFn: () => studentService.show(studentId!),
    enabled: open,
  });

  const student = res?.data;

  // Reset tab when student changes
  const prevId = studentId;
  if (prevId !== studentId) setTab('Overview');

  const grad = student ? GRADIENTS[student.id % 6] : GRADIENTS[0];
  const st = student ? (STATUS_MAP[student.status] ?? { label: student.status, cls: 'bg-slate-100 text-slate-600' }) : null;

  function bsDateStr(dateStr?: string | null) {
    if (!dateStr) return '—';
    try { const bs = adToBS(new Date(dateStr)); return formatBS(bs, 'Y, F d'); } catch { return dateStr; }
  }

  return (
    <Drawer open={open} onClose={onClose} width="w-[520px] max-w-[95vw]">
      {isLoading || !student ? (
        <div className="flex h-64 items-center justify-center">
          <div className="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" />
        </div>
      ) : (
        <>
          {/* Hero header */}
          <div className={`bg-gradient-to-br from-slate-900 to-slate-800 px-6 py-5`}>
            <div className="flex items-start gap-4">
              {student.user.avatar
                ? <img src={student.user.avatar} alt="" className="h-16 w-16 flex-shrink-0 rounded-2xl object-cover ring-2 ring-white/20 shadow-lg" />
                : <div className={`flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br ${grad} text-2xl font-black text-white shadow-lg`}>{student.user.name.charAt(0).toUpperCase()}</div>}
              <div className="min-w-0 flex-1">
                <h3 className="text-lg font-black text-white leading-tight">{student.user.name}</h3>
                <p className="mt-0.5 font-mono text-xs text-slate-400">{student.student_no}</p>
                <div className="mt-2 flex flex-wrap items-center gap-2">
                  {st && <span className={clsx('rounded-lg px-2.5 py-1 text-xs font-semibold', st.cls)}>{st.label}</span>}
                  <span className="rounded-lg bg-violet-500/20 px-2.5 py-1 text-xs font-bold text-violet-200">Sem {student.current_semester}</span>
                  {student.program && <span className="rounded-lg bg-white/10 px-2.5 py-1 text-xs text-slate-300">{student.program.name}</span>}
                </div>
              </div>
              <div className="flex flex-col gap-1.5 flex-shrink-0">
                <Link to={`/admin/students/${student.id}/edit`} onClick={onClose}
                  className="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition">
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  Edit
                </Link>
                <Link to={`/admin/students/${student.id}`} onClick={onClose}
                  className="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition">
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                  Full page
                </Link>
              </div>
            </div>
          </div>

          {/* Tab bar */}
          <div className="sticky top-0 z-10 border-b border-slate-200 bg-white">
            <nav className="flex overflow-x-auto px-2 gap-0 scrollbar-none">
              {TABS.map(t => (
                <button key={t} onClick={() => setTab(t)}
                  className={clsx('whitespace-nowrap px-4 py-3.5 text-sm transition flex-shrink-0 border-b-2',
                    tab === t ? 'border-blue-600 text-blue-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800')}>
                  {t}
                </button>
              ))}
            </nav>
          </div>

          {/* Tab content */}
          <div className="flex-1">
            {tab === 'Overview' && (
              <div className="p-6 space-y-6">
                {/* Quick stats */}
                <div className="grid grid-cols-3 gap-3">
                  {[
                    { label: 'Attendance', value: (student as any).attendance_pct != null ? `${(student as any).attendance_pct}%` : '—', color: 'text-emerald-600' },
                    { label: 'Exam records', value: String((student as any).marks_count ?? '—'), color: 'text-slate-800' },
                    { label: 'Assignments', value: String((student as any).submissions_count ?? '—'), color: 'text-slate-800' },
                  ].map(s => (
                    <div key={s.label} className="rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
                      <p className={clsx('text-xl font-black', s.color)}>{s.value}</p>
                      <p className="mt-0.5 text-[11px] text-slate-500">{s.label}</p>
                    </div>
                  ))}
                </div>

                {/* Personal */}
                <div>
                  <h4 className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Personal</h4>
                  <dl className="rounded-xl border border-slate-200 overflow-hidden">
                    <InfoRow label="Email" value={student.user.email} />
                    <InfoRow label="Phone" value={student.user.phone} />
                    <InfoRow label="Gender" value={(student.user as any).gender ? String((student.user as any).gender).charAt(0).toUpperCase() + String((student.user as any).gender).slice(1) : undefined} />
                    <InfoRow label="Date of Birth" value={bsDateStr((student.user as any).dob)} />
                    <InfoRow label="Address" value={(student.user as any).address} />
                    <InfoRow label="Blood Group" value={student.blood_group} />
                  </dl>
                </div>

                {/* Academic */}
                <div>
                  <h4 className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Academic</h4>
                  <dl className="rounded-xl border border-slate-200 overflow-hidden">
                    <InfoRow label="Student ID" value={student.student_no} />
                    <InfoRow label="Registration No" value={student.registration_number} />
                    <InfoRow label="Department" value={student.department?.name} />
                    <InfoRow label="Program" value={student.program?.name} />
                    <InfoRow label="Session" value={student.academic_session?.name} />
                    <InfoRow label="Semester" value={`Semester ${student.current_semester}`} />
                    <InfoRow label="Section" value={student.section} />
                    <InfoRow label="Batch" value={student.batch} />
                    <InfoRow label="Admitted" value={bsDateStr(student.admission_date)} />
                    <InfoRow label="Enrolled" value={bsDateStr(student.created_at)} />
                  </dl>
                </div>

                {/* Emergency */}
                {(student.guardian_name || student.guardian_phone) && (
                  <div>
                    <h4 className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Emergency Contact</h4>
                    <div className="rounded-xl border border-amber-200 bg-amber-50/30 px-4 py-3">
                      <p className="font-semibold text-slate-800 text-sm">{student.guardian_name ?? '—'}</p>
                      <p className="text-slate-500 text-xs mt-0.5">{student.guardian_phone ?? '—'}</p>
                    </div>
                  </div>
                )}
              </div>
            )}

            {tab === 'Attendance' && (
              <div className="p-6 space-y-5">
                <div className="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                  <svg className="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                  <p className="text-sm font-medium">Attendance details available on the full profile page.</p>
                  <Link to={`/admin/students/${student.id}?tab=attendance`} onClick={onClose}
                    className="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    View Full Attendance →
                  </Link>
                </div>
              </div>
            )}

            {tab === 'Marks' && (
              <div className="p-6">
                <div className="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                  <svg className="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                  <p className="text-sm font-medium">Exam results available on the full profile page.</p>
                  <Link to={`/admin/students/${student.id}?tab=marks`} onClick={onClose}
                    className="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    View Marks & Exams →
                  </Link>
                </div>
              </div>
            )}

            {tab === 'Assignments' && (
              <div className="p-6">
                <div className="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                  <svg className="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  <p className="text-sm font-medium">Assignment submissions on the full profile page.</p>
                  <Link to={`/admin/students/${student.id}?tab=assignments`} onClick={onClose}
                    className="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    View Assignments →
                  </Link>
                </div>
              </div>
            )}

            {tab === 'Parent' && (
              <div className="p-6 space-y-4">
                {!(student as any).parents?.length ? (
                  <div className="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                    <svg className="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p className="text-sm font-medium">No parent accounts linked.</p>
                  </div>
                ) : (
                  (student as any).parents.map((parent: any, i: number) => {
                    const pg = ['from-emerald-500 to-teal-600','from-blue-500 to-indigo-600','from-violet-500 to-purple-600'][i % 3];
                    return (
                      <div key={parent.id} className="rounded-xl border border-slate-200 bg-white overflow-hidden">
                        <div className="flex items-center gap-4 p-4 bg-slate-50/60 border-b border-slate-100">
                          <div className={`flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${pg} text-xl font-black text-white shadow`}>
                            {parent.user?.name?.charAt(0)?.toUpperCase()}
                          </div>
                          <div>
                            <p className="font-bold text-slate-800">{parent.user?.name ?? '—'}</p>
                            <p className="text-xs text-slate-500">{parent.relation_to_student ? parent.relation_to_student.charAt(0).toUpperCase() + parent.relation_to_student.slice(1) : 'Parent / Guardian'}</p>
                          </div>
                        </div>
                        <dl className="divide-y divide-slate-100 text-sm">
                          {[['Email', parent.user?.email], ['Phone', parent.user?.phone], ['Occupation', parent.occupation]].filter(([, v]) => v).map(([l, v]) => (
                            <div key={l} className="flex gap-4 px-4 py-2.5">
                              <dt className="w-24 flex-shrink-0 text-xs text-slate-500">{l}</dt>
                              <dd className="font-medium text-slate-800 min-w-0 break-all text-sm">{v}</dd>
                            </div>
                          ))}
                        </dl>
                      </div>
                    );
                  })
                )}
              </div>
            )}

            {tab === 'Timeline' && (
              <div className="p-6">
                <div className="flex flex-col items-center justify-center py-12 text-center text-slate-400">
                  <svg className="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <p className="text-sm font-medium">Activity timeline on the full profile page.</p>
                  <Link to={`/admin/students/${student.id}?tab=timeline`} onClick={onClose}
                    className="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    View Timeline →
                  </Link>
                </div>
              </div>
            )}
          </div>
        </>
      )}
    </Drawer>
  );
}
