import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Calendar, Mail, Phone, MapPin, Briefcase, DollarSign } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import { BsDate } from '@components/ui/BsDate';
import staffService from '@shared/services/staffService';

export default function ShowStaffPage() {
  const { id } = useParams();

  const { data, isLoading } = useQuery({
    queryKey: ['staff', id],
    queryFn: () => staffService.show(Number(id)),
    enabled: !!id,
  });
  const s = data?.data?.data;

  if (isLoading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;
  if (!s) return <div className="py-20 text-center text-sm text-slate-500">Staff member not found.</div>;

  const badge = (label: string, cls: string) => (
    <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${cls}`}>
      <span className={`h-1.5 w-1.5 rounded-full ${cls.includes('emerald') ? 'bg-emerald-500' : cls.includes('blue') ? 'bg-blue-500' : cls.includes('amber') ? 'bg-amber-500' : 'bg-slate-400'}`} />
      {label}
    </span>
  );

  const statusBadge = (status: string) => {
    const map: Record<string, [string, string]> = {
      active: ['Active', 'bg-emerald-50 text-emerald-700'],
      leave: ['On Leave', 'bg-amber-50 text-amber-700'],
      resigned: ['Resigned', 'bg-red-50 text-red-700'],
    };
    const [label, cls] = map[status] ?? [status, 'bg-slate-100 text-slate-600'];
    return badge(label, cls);
  };

  return (
    <div className="space-y-5">
      <div className="flex items-center gap-4">
        <Link to="/admin/staff" className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" />
        </Link>
        <div className="flex-1">
          <h1 className="text-2xl font-black tracking-tight text-slate-900">{s.name}</h1>
          <p className="mt-0.5 text-sm text-slate-500">{s.designation ?? 'Staff member'}</p>
        </div>
        <Link to={`/admin/staff/${id}/edit`}
          className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
          Edit Staff
        </Link>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div className="lg:col-span-1 space-y-5">
          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 text-center">
            {s.photo_url
              ? <img src={s.photo_url} className="mx-auto h-24 w-24 rounded-2xl object-cover" alt={s.name} />
              : <div className="mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-slate-200 text-slate-600 text-3xl font-black">{s.name?.charAt(0)?.toUpperCase()}</div>}
            <h2 className="mt-3 text-lg font-bold text-slate-900">{s.name}</h2>
            <p className="text-xs text-slate-400 font-mono">{s.staff_code}</p>
            <div className="mt-3 flex flex-wrap justify-center gap-2">
              {statusBadge(s.employment_status)}
              {s.featured && badge('Featured', 'bg-blue-50 text-blue-700')}
              {s.public_visible && badge('Public', 'bg-green-50 text-green-700')}
            </div>
          </div>

          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Contact</h3>
            <div className="space-y-2">
              {s.email && <div className="flex items-center gap-2 text-sm text-slate-700"><Mail className="h-4 w-4 text-slate-400" />{s.email}{s.show_email_public && <span className="text-[10px] text-slate-400">(public)</span>}</div>}
              {s.phone && <div className="flex items-center gap-2 text-sm text-slate-700"><Phone className="h-4 w-4 text-slate-400" />{s.phone}{s.show_phone_public && <span className="text-[10px] text-slate-400">(public)</span>}</div>}
              {s.address && <div className="flex items-center gap-2 text-sm text-slate-700"><MapPin className="h-4 w-4 text-slate-400" />{s.address}</div>}
              {!s.email && !s.phone && !s.address && <p className="text-sm text-slate-400">—</p>}
            </div>
          </div>
        </div>

        <div className="lg:col-span-2 space-y-5">
          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Employment Details</h3>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div><p className="text-xs text-slate-400">Designation</p><p className="text-sm font-medium text-slate-900">{s.designation ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Department</p><p className="text-sm font-medium text-slate-900">{s.department ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Employment Type</p><p className="text-sm font-medium text-slate-900">{s.employment_type ? s.employment_type.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) : '—'}</p></div>
              <div><p className="text-xs text-slate-400">Status</p><p className="text-sm font-medium text-slate-900">{s.employment_status ? s.employment_status.charAt(0).toUpperCase() + s.employment_status.slice(1) : '—'}</p></div>
              <div><p className="text-xs text-slate-400">Gender</p><p className="text-sm font-medium text-slate-900">{s.gender ? s.gender.charAt(0).toUpperCase() + s.gender.slice(1) : '—'}</p></div>
              <div><p className="text-xs text-slate-400">Salary</p><p className="text-sm font-medium text-slate-900">{s.salary_amount ? `$${Number(s.salary_amount).toLocaleString()}` : '—'}</p></div>
            </div>
          </div>

          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Dates</h3>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div><p className="text-xs text-slate-400">Date of Birth</p><p className="text-sm font-medium text-slate-900">{s.dob ? <BsDate date={s.dob} format="F d, Y" /> : '—'}</p></div>
              <div><p className="text-xs text-slate-400">Join Date</p><p className="text-sm font-medium text-slate-900">{s.join_date ? <BsDate date={s.join_date} format="F d, Y" /> : '—'}</p></div>
              <div><p className="text-xs text-slate-400">End Date</p><p className="text-sm font-medium text-slate-900">{s.end_date ? <BsDate date={s.end_date} format="F d, Y" /> : '—'}</p></div>
              <div><p className="text-xs text-slate-400">Created</p><p className="text-sm font-medium text-slate-900"><BsDate date={s.created_at} format="Y, F d" /></p></div>
              <div><p className="text-xs text-slate-400">Updated</p><p className="text-sm font-medium text-slate-900"><BsDate date={s.updated_at} format="Y, F d" /></p></div>
              <div><p className="text-xs text-slate-400">Order</p><p className="text-sm font-medium text-slate-900">{s.order ?? '0'}</p></div>
            </div>
          </div>

          {s.bio && (
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
              <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Bio</h3>
              <p className="text-sm text-slate-700 whitespace-pre-wrap">{s.bio}</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
