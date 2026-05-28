import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { useState } from 'react';
import toast from 'react-hot-toast';
import staffService from '@shared/services/staffService';
import { BsDate } from '@shared/components/ui/BsDate';
import { Spinner } from '@shared/components/ui/Spinner';

export default function ShowStaffPage() {
  const { id } = useParams();
  const queryClient = useQueryClient();
  const [status, setStatus] = useState('');

  const { data, isLoading } = useQuery({
    queryKey: ['staff', id],
    queryFn: () => staffService.show(Number(id)),
    enabled: !!id,
  });

  const s = data?.data;

  const togglePublic = useMutation({
    mutationFn: () => staffService.togglePublic(Number(id)),
    onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['staff', id] }); toast.success('Visibility toggled.'); },
    onError: () => toast.error('Failed to toggle visibility.'),
  });

  const toggleFeatured = useMutation({
    mutationFn: () => staffService.toggleFeatured(Number(id)),
    onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['staff', id] }); toast.success('Featured toggled.'); },
    onError: () => toast.error('Failed to toggle featured.'),
  });

  const updateStatus = useMutation({
    mutationFn: () => staffService.update(Number(id), { employment_status: status } as Record<string, unknown>),
    onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['staff', id] }); toast.success('Status updated.'); },
    onError: () => toast.error('Failed to update status.'),
  });

  if (isLoading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;
  if (!s) return <div className="py-20 text-center text-sm text-slate-500">Staff member not found.</div>;

  const workingSchedule = s.working_schedule || {};
  const statusPillClass = s.employment_status === 'active' ? 'bg-emerald-500/20' : s.employment_status === 'leave' ? 'bg-amber-500/20' : 'bg-rose-500/20';

  return (
    <div className="space-y-8">
      <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
        <div className="grid gap-0 lg:grid-cols-[0.9fr_1.1fr]">
          <div className="bg-gradient-to-br from-slate-950 via-slate-900 to-[#8B0000] p-8 text-white lg:p-10">
            <div className="flex items-start gap-5">
              <div className="h-24 w-24 overflow-hidden rounded-3xl border border-white/20 bg-white/10 shadow-lg">
                {s.photo_url ? (
                  <img src={s.photo_url} alt={s.name} className="h-full w-full object-cover" />
                ) : (
                  <div className="flex h-full w-full items-center justify-center text-2xl font-black text-white/80">{s.name?.charAt(0)?.toUpperCase()}</div>
                )}
              </div>
              <div className="min-w-0 flex-1">
                <p className="text-xs font-semibold uppercase tracking-[0.35em] text-white/65">Administrative Staff</p>
                <h1 className="mt-2 text-3xl font-semibold tracking-tight">{s.name}</h1>
                <p className="mt-1 text-lg text-white/80">{s.designation}</p>
                <div className="mt-4 flex flex-wrap gap-2">
                  <span className="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">{s.staff_code}</span>
                  <span className="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">{s.department || 'General Administration'}</span>
                  <span className={`rounded-full ${statusPillClass} px-3 py-1 text-xs font-semibold text-white`}>{s.employment_status ? s.employment_status.charAt(0).toUpperCase() + s.employment_status.slice(1) : 'Active'}</span>
                  {s.featured && <span className="rounded-full bg-amber-400/20 px-3 py-1 text-xs font-semibold text-white">Featured</span>}
                </div>
              </div>
            </div>

            <div className="mt-8 grid gap-3 sm:grid-cols-3">
              <div className="rounded-2xl bg-white/10 p-4 backdrop-blur">
                <div className="text-xs uppercase tracking-[0.25em] text-white/60">Public</div>
                <div className="mt-2 text-lg font-semibold">{s.public_visible ? 'Visible' : 'Hidden'}</div>
              </div>
              <div className="rounded-2xl bg-white/10 p-4 backdrop-blur">
                <div className="text-xs uppercase tracking-[0.25em] text-white/60">Documents</div>
                <div className="mt-2 text-lg font-semibold">{s.documents?.length || 0}</div>
              </div>
              <div className="rounded-2xl bg-white/10 p-4 backdrop-blur">
                <div className="text-xs uppercase tracking-[0.25em] text-white/60">Public Docs</div>
                <div className="mt-2 text-lg font-semibold">{s.public_docs_count || 0}</div>
              </div>
            </div>
          </div>

          <div className="p-8 lg:p-10">
            <div className="grid gap-4 sm:grid-cols-2">
              <Link to={`/admin/staff/${id}/edit`} className="inline-flex items-center justify-center rounded-full bg-[#8B0000] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#6f0000]">Edit Profile</Link>
              <Link to={`/admin/staff/${id}/documents`} className="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Manage Documents</Link>
              <button onClick={() => togglePublic.mutate()} className="w-full rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{s.public_visible ? 'Hide from Public' : 'Make Public'}</button>
              <button onClick={() => toggleFeatured.mutate()} className="w-full rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{s.featured ? 'Remove Featured' : 'Feature Profile'}</button>
            </div>

            <div className="mt-8 rounded-3xl border border-slate-200 bg-slate-50 p-5">
              <p className="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Status Controls</p>
              <div className="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <select value={status || s.employment_status} onChange={e => setStatus(e.target.value)} className="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                  {['active', 'leave', 'resigned'].map(v => (
                    <option key={v} value={v}>{v === 'leave' ? 'On Leave' : v.charAt(0).toUpperCase() + v.slice(1)}</option>
                  ))}
                </select>
                <button onClick={() => updateStatus.mutate()} className="inline-flex justify-center rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#8B0000]">Update Status</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {[
          { label: 'Present', value: s.attendance_summary?.present || 0, color: 'emerald' },
          { label: 'Late', value: s.attendance_summary?.late || 0, color: 'amber' },
          { label: 'Leave', value: s.attendance_summary?.leave || 0, color: 'sky' },
          { label: 'Absent', value: s.attendance_summary?.absent || 0, color: 'rose' },
        ].map(metric => (
          <div key={metric.label} className="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_20px_60px_rgba(15,23,42,0.06)]">
            <div className="text-xs uppercase tracking-[0.25em] text-slate-500">{metric.label}</div>
            <div className="mt-3 text-3xl font-semibold text-slate-900">{metric.value}</div>
            <div className="mt-2 text-sm text-slate-500">This month attendance</div>
          </div>
        ))}
      </div>

      <div className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div className="space-y-6">
          <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <h2 className="text-lg font-semibold text-slate-900">Profile Details</h2>
            <dl className="mt-5 grid gap-5 md:grid-cols-2">
              {[
                { label: 'Staff Code', value: s.staff_code },
                { label: 'Name', value: s.name },
                { label: 'Designation', value: s.designation },
                { label: 'Department', value: s.department },
                { label: 'Address', value: s.address },
                { label: 'Date of Birth', value: s.dob ? <BsDate date={s.dob} format="Y F d" /> : null },
                { label: 'Gender', value: s.gender },
                { label: 'Join Date', value: s.join_date ? <BsDate date={s.join_date} format="Y F d" /> : null },
                { label: 'End Date', value: s.end_date ? <BsDate date={s.end_date} format="Y F d" /> : null },
                { label: 'Salary Amount', value: s.salary_amount ? Number(s.salary_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : null },
              ].map((item) => item.value ? (
                <div key={item.label} className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <dt className="text-xs uppercase tracking-[0.25em] text-slate-500">{item.label}</dt>
                  <dd className="mt-2 text-sm font-medium text-slate-900">{item.value}</dd>
                </div>
              ) : null)}
            </dl>
          </div>

          <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <h2 className="text-lg font-semibold text-slate-900">Working Schedule</h2>
            <div className="mt-5 grid gap-4 md:grid-cols-2">
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div className="text-xs uppercase tracking-[0.25em] text-slate-500">Label</div>
                <div className="mt-2 text-sm font-medium text-slate-900">{workingSchedule?.label || 'Not set'}</div>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div className="text-xs uppercase tracking-[0.25em] text-slate-500">Hours</div>
                <div className="mt-2 text-sm font-medium text-slate-900">{workingSchedule?.start || '\u2014'} to {workingSchedule?.end || '\u2014'}</div>
              </div>
            </div>

            <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div className="text-xs uppercase tracking-[0.25em] text-slate-500">Working Days</div>
              <div className="mt-2 flex flex-wrap gap-2">
                {(workingSchedule?.days?.length ?? 0) > 0 ? workingSchedule!.days!.map((day: string, i: number) => (
                  <span key={i} className="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">{day}</span>
                )) : <span className="text-sm text-slate-500">No schedule has been defined.</span>}
              </div>
            </div>

            <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div className="text-xs uppercase tracking-[0.25em] text-slate-500">Assigned Roles</div>
              <div className="mt-2 flex flex-wrap gap-2">
                {(s.assigned_roles?.length ?? 0) > 0 ? s.assigned_roles!.map((role: string, i: number) => (
                  <span key={i} className="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">{role}</span>
                )) : <span className="text-sm text-slate-500">No assigned roles recorded.</span>}
              </div>
            </div>

            <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div className="text-xs uppercase tracking-[0.25em] text-slate-500">Responsibilities</div>
              <div className="mt-2 text-sm leading-6 text-slate-700">{(s.responsibilities?.length ?? 0) > 0 ? s.responsibilities!.join(', ') : 'No responsibilities recorded.'}</div>
            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <h2 className="text-lg font-semibold text-slate-900">Visibility</h2>
            <div className="mt-4 space-y-3 text-sm text-slate-600">
              <div className="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                <span>Public visibility</span>
                <span className={`font-semibold ${s.public_visible ? 'text-emerald-600' : 'text-slate-500'}`}>{s.public_visible ? 'Enabled' : 'Disabled'}</span>
              </div>
              <div className="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                <span>Featured profile</span>
                <span className={`font-semibold ${s.featured ? 'text-amber-600' : 'text-slate-500'}`}>{s.featured ? 'Enabled' : 'Disabled'}</span>
              </div>
              <div className="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                <span>Email public</span>
                <span className={`font-semibold ${s.show_email_public ? 'text-emerald-600' : 'text-slate-500'}`}>{s.show_email_public ? 'Yes' : 'No'}</span>
              </div>
              <div className="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                <span>Phone public</span>
                <span className={`font-semibold ${s.show_phone_public ? 'text-emerald-600' : 'text-slate-500'}`}>{s.show_phone_public ? 'Yes' : 'No'}</span>
              </div>
            </div>
          </div>

          <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <h2 className="text-lg font-semibold text-slate-900">Public Documents</h2>
            <div className="mt-4 space-y-3">
              {(s.public_documents?.length ?? 0) > 0 ? s.public_documents!.map((doc: any, i: number) => (
                <div key={i} className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <div className="flex items-start justify-between gap-3">
                    <div>
                      <div className="text-sm font-semibold text-slate-900">{doc.label}</div>
                      <div className="mt-1 text-xs text-slate-500">{doc.document_type ? doc.document_type.replace(/_/g, ' ').replace(/\b\w/g, (c: string) => c.toUpperCase()) : ''}</div>
                    </div>
                    <a href={doc.file_url || `/storage/${doc.file_path?.replace(/^\//, '')}`} target="_blank" className="text-sm font-semibold text-[#8B0000]">Open</a>
                  </div>
                </div>
              )) : <p className="text-sm text-slate-500">No public documents are attached to this profile.</p>}
            </div>
          </div>
        </div>
      </div>

      <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
        <div className="flex items-center justify-between gap-3">
          <div>
            <h2 className="text-lg font-semibold text-slate-900">This Month Attendance</h2>
            <p className="text-sm text-slate-500">Recent attendance activity for the current month.</p>
          </div>
          <Link to={`/admin/staff/${id}/documents`} className="text-sm font-semibold text-[#8B0000]">Manage documents</Link>
        </div>

        <div className="mt-5 overflow-x-auto">
          <table className="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead className="bg-slate-50">
              <tr className="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                <th className="px-4 py-3">Date</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Check In</th>
                <th className="px-4 py-3">Check Out</th>
                <th className="px-4 py-3">Notes</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {(s.month_attendance?.length ?? 0) > 0 ? s.month_attendance!.map((r: any, i: number) => (
                <tr key={i}>
                  <td className="px-4 py-3 text-slate-700">{r.attendance_date ? <BsDate date={r.attendance_date} format="Y F d" /> : '\u2014'}</td>
                  <td className="px-4 py-3 text-slate-700">{r.status ? r.status.charAt(0).toUpperCase() + r.status.slice(1) : '\u2014'}</td>
                  <td className="px-4 py-3 text-slate-700">{r.check_in || '\u2014'}</td>
                  <td className="px-4 py-3 text-slate-700">{r.check_out || '\u2014'}</td>
                  <td className="px-4 py-3 text-slate-500">{r.notes || '\u2014'}</td>
                </tr>
              )) : (
                <tr>
                  <td colSpan={5} className="px-4 py-10 text-center text-slate-500">No attendance records found for this month.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
