import { useQuery } from '@tanstack/react-query';
import { getStaff } from '@shared/services/public.service';

export default function StaffPage() {
  const { data, isLoading } = useQuery({ queryKey: ['public-staff'], queryFn: getStaff });
  const staff: any[] = data ?? [];

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      {isLoading ? (
        <div className="flex justify-center py-32"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
      ) : (
        <div className="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
          {staff.length > 0 ? staff.map((member: any) => (
            <article key={member.id}
              className="group overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] transition hover:-translate-y-1 hover:shadow-[0_24px_80px_rgba(15,23,42,0.12)] text-center">
              {/* Profile Picture */}
              <div className="flex justify-center mb-3">
                <div className="h-20 w-20 overflow-hidden rounded-full border-4 border-slate-100 bg-slate-100 shadow-md">
                  <img src={member.photo_url ?? '/storage/placeholder-avatar.jpg'} alt={member.name} className="h-full w-full object-cover" loading="lazy" />
                </div>
              </div>

              {/* Badges */}
              <div className="flex flex-wrap gap-2 justify-center mb-3">
                {member.featured && (
                  <span className="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-700">Featured</span>
                )}
                <span className="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600">
                  {member.employment_status ? String(member.employment_status).charAt(0).toUpperCase() + String(member.employment_status).slice(1) : 'Active'}
                </span>
              </div>

              <h3 className="text-lg font-semibold text-slate-900">{member.name}</h3>
              <p className="mt-1 text-sm text-[#003D82]">{member.designation}</p>
              <p className="mt-2 text-sm text-slate-500">{member.department || 'General Administration'}</p>

              <div className="mt-4 space-y-1 text-sm text-slate-600">
                <div>{member.show_email_public ? member.email : 'Email hidden'}</div>
                <div>{member.show_phone_public ? member.phone : 'Phone hidden'}</div>
              </div>

              <div className="mt-5 flex flex-col items-center gap-2">
                <a href={`/staff/${member.id}`}
                  className="inline-flex items-center justify-center rounded-full bg-[#003D82] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#002a5c]">
                  View Profile
                </a>
                <span className="text-xs text-slate-400">
                  Joined {member.join_date ? new Date(member.join_date).getFullYear() : '—'}
                </span>
              </div>
            </article>
          )) : (
            <div className="col-span-full rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
              <div className="text-5xl">👥</div>
              <h3 className="mt-4 text-lg font-semibold text-slate-900">No public staff profiles yet</h3>
              <p className="mt-2 text-sm text-slate-500">The administrative team is still preparing visible staff records.</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
