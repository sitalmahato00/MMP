import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Globe, Github, Linkedin, Trophy } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import { BsDate } from '@components/ui/BsDate';
import alumniService from '@shared/services/alumniService';

export default function ShowAlumniPage() {
  const { id } = useParams();

  const { data, isLoading } = useQuery({
    queryKey: ['alumni', id],
    queryFn: () => alumniService.show(Number(id)),
    enabled: !!id,
  });
  const a = data?.data;

  if (isLoading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;
  if (!a) return <div className="py-20 text-center text-sm text-slate-500">Alumni not found.</div>;

  const badge = (label: string, cls: string) => (
    <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${cls}`}>
      <span className={`h-1.5 w-1.5 rounded-full ${cls.includes('emerald') ? 'bg-emerald-500' : cls.includes('blue') ? 'bg-blue-500' : 'bg-slate-400'}`} />
      {label}
    </span>
  );

  return (
    <div className="space-y-5">
      <div className="flex items-center gap-4">
        <Link to="/admin/alumni" className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" />
        </Link>
        <div className="flex-1">
          <h1 className="text-2xl font-black tracking-tight text-slate-900">{a.user?.name}</h1>
          <p className="mt-0.5 text-sm text-slate-500">Alumni profile details</p>
        </div>
        <Link to={`/admin/alumni/${id}/edit`}
          className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
          Edit Alumni
        </Link>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div className="lg:col-span-1 space-y-5">
          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 text-center">
            {a.user?.avatar
              ? <img src={a.user.avatar} className="mx-auto h-24 w-24 rounded-2xl object-cover" alt={a.user.name} />
              : <div className="mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 text-3xl font-black">{a.user?.name?.charAt(0)?.toUpperCase()}</div>}
            <h2 className="mt-3 text-lg font-bold text-slate-900">{a.user?.name}</h2>
            <p className="text-sm text-slate-400">{a.user?.email}</p>
            <div className="mt-3 flex flex-wrap justify-center gap-2">
              {a.is_verified && badge('Verified', 'bg-emerald-50 text-emerald-700')}
              {a.is_featured && badge('Featured', 'bg-blue-50 text-blue-700')}
            </div>
          </div>

          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Contact</h3>
            {a.user?.phone && <p className="text-sm text-slate-700 mb-1">{a.user.phone}</p>}
            {a.user?.address && <p className="text-sm text-slate-500">{a.user.address}</p>}
            {!a.user?.phone && !a.user?.address && <p className="text-sm text-slate-400">—</p>}
          </div>

          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Social Links</h3>
            <div className="space-y-2">
              {a.linkedin_url && <a href={a.linkedin_url} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 text-sm text-blue-600 hover:underline"><Linkedin className="h-4 w-4" />LinkedIn</a>}
              {a.github_url && <a href={a.github_url} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 text-sm text-blue-600 hover:underline"><Github className="h-4 w-4" />GitHub</a>}
              {a.portfolio_url && <a href={a.portfolio_url} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 text-sm text-blue-600 hover:underline"><Globe className="h-4 w-4" />Portfolio</a>}
              {!a.linkedin_url && !a.github_url && !a.portfolio_url && <p className="text-sm text-slate-400">—</p>}
            </div>
          </div>
        </div>

        <div className="lg:col-span-2 space-y-5">
          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Academic Details</h3>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div><p className="text-xs text-slate-400">Department</p><p className="text-sm font-medium text-slate-900">{a.department?.name ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Program</p><p className="text-sm font-medium text-slate-900">{a.program?.name ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Roll Number</p><p className="text-sm font-medium text-slate-900">{a.roll_number ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Admission Year</p><p className="text-sm font-medium text-slate-900">{a.admission_year ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Graduation Year</p><p className="text-sm font-medium text-slate-900">{a.graduation_year ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Joined</p><p className="text-sm font-medium text-slate-900"><BsDate date={a.created_at} format="Y, F d" /></p></div>
            </div>
          </div>

          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Employment</h3>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div><p className="text-xs text-slate-400">Status</p><p className="text-sm font-medium text-slate-900">{a.employment_status ? a.employment_status.charAt(0).toUpperCase() + a.employment_status.slice(1).replace('-', ' ') : '—'}</p></div>
              <div><p className="text-xs text-slate-400">Current Job</p><p className="text-sm font-medium text-slate-900">{a.current_job ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Company</p><p className="text-sm font-medium text-slate-900">{a.company_name ?? '—'}</p></div>
              <div><p className="text-xs text-slate-400">Work Location</p><p className="text-sm font-medium text-slate-900">{a.work_location ?? '—'}</p></div>
            </div>
          </div>

          {a.bio && (
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
              <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Bio</h3>
              <p className="text-sm text-slate-700 whitespace-pre-wrap">{a.bio}</p>
            </div>
          )}

          {a.achievements && (
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
              <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Achievements</h3>
              <div className="flex items-start gap-2">
                <Trophy className="h-4 w-4 text-amber-500 mt-0.5 flex-shrink-0" />
                <p className="text-sm text-slate-700 whitespace-pre-wrap">{a.achievements}</p>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
