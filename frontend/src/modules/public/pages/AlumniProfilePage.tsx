import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { getAlumniProfile } from '@shared/services/public.service';

const GRADIENTS = [
  'from-blue-500 to-indigo-600', 'from-violet-500 to-purple-600', 'from-emerald-500 to-teal-600',
  'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-sky-600',
];

const STATUS_MAP: Record<string, { label: string; cls: string }> = {
  employed:    { label: 'Employed',    cls: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
  studying:    { label: 'Studying',    cls: 'bg-blue-50 text-blue-700 ring-blue-200' },
  freelancing: { label: 'Freelancing', cls: 'bg-violet-50 text-violet-700 ring-violet-200' },
  unemployed:  { label: 'Unemployed',  cls: 'bg-amber-50 text-amber-700 ring-amber-200' },
};

export default function AlumniProfilePage() {
  const { id } = useParams<{ id: string }>();
  const { data, isLoading } = useQuery({
    queryKey: ['public-alumni-profile', id],
    queryFn: () => getAlumniProfile(Number(id!)),
    enabled: !!id,
  });
  const a: any = data ?? null;

  if (isLoading) return <div className="flex justify-center py-32"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>;
  if (!a) return <div className="text-center py-24 text-gray-500"><p className="text-5xl mb-4">🎓</p><p>Alumni profile not found.</p></div>;

  const grad = GRADIENTS[a.id % 6];
  const st = STATUS_MAP[a.employment_status] ?? { label: 'Unknown', cls: 'bg-slate-100 text-slate-600 ring-slate-200' };
  const profileCompletion = Math.max(0, Math.min(100, parseInt(a.profile_completion ?? 0, 10)));
  const skills: string[] = Array.isArray(a.skills) ? a.skills.filter(Boolean) : [];
  const visibleProjects = (a.projects ?? []).filter((p: any) => p.is_visible);
  const achievementRecords: any[] = a.achievementRecords ?? a.achievement_records ?? [];
  const employmentHistory: any[] = a.employmentHistory ?? a.employment_history ?? [];

  const stats = [
    { label: 'Projects',       value: a.visible_projects_count ?? visibleProjects.length },
    { label: 'Achievements',   value: a.achievement_records_count ?? achievementRecords.length },
    { label: 'Career entries', value: a.employment_history_count ?? employmentHistory.length },
    { label: 'Completion',     value: `${profileCompletion}%` },
  ];

  const detailSections = [
    {
      title: 'Academic Record',
      items: [
        { label: 'Department',       value: a.department?.name },
        { label: 'Program',          value: a.program?.name },
        { label: 'Roll Number',      value: a.roll_number },
        { label: 'Admission Year',   value: a.admission_year },
        { label: 'Graduation Year',  value: a.graduation_year },
      ],
    },
    {
      title: 'Professional Record',
      items: [
        { label: 'Current Job',       value: a.current_job },
        { label: 'Company',           value: a.company_name },
        { label: 'Work Location',     value: a.work_location },
        { label: 'Employment Status', value: st.label },
      ],
    },
  ];

  return (
    <section className="relative py-12 lg:py-16">
      <div className="absolute inset-x-0 top-0 -z-10 h-64 bg-gradient-to-b from-slate-50 to-transparent" />
      <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <Link to="/alumni" className="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Directory
        </Link>

        {/* Hero */}
        <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm mb-8">
          <div className={`h-32 bg-gradient-to-br ${grad} relative`} />
          <div className="px-6 pb-6 -mt-10 relative">
            <div className="flex flex-wrap items-end gap-5">
              {a.user?.avatar ? (
                <img src={`/storage/${a.user.avatar}`} alt="" className="h-24 w-24 rounded-2xl object-cover ring-4 ring-white shadow-lg" />
              ) : (
                <div className={`flex h-24 w-24 items-center justify-center rounded-2xl bg-gradient-to-br ${grad} text-4xl font-bold text-white ring-4 ring-white shadow-lg`}>
                  {(a.user?.name ?? 'A')[0].toUpperCase()}
                </div>
              )}
              <div className="flex-1 min-w-0 pt-12">
                <div className="flex flex-wrap items-center gap-2">
                  <h1 className="text-3xl font-bold text-slate-900">{a.user?.name}</h1>
                  {a.is_featured && <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">Featured</span>}
                  {a.is_verified && <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">Verified</span>}
                  <span className={`rounded-full px-3 py-1 text-xs font-bold ring-1 ${st.cls}`}>{st.label}</span>
                </div>
                <p className="mt-1 text-sm text-slate-500">
                  {a.current_job ?? ''}{a.company_name ? ` at ${a.company_name}` : ''}
                  {!a.current_job && !a.company_name ? 'Alumni' : ''}
                  {a.department?.name ? ` · ${a.department.name}` : ''}
                  {a.graduation_year ? ` · Batch ${a.graduation_year}` : ''}
                </p>
                {a.work_location && <p className="mt-1 text-sm text-slate-400">{a.work_location}</p>}
                <div className="mt-4 flex flex-wrap gap-2">
                  {a.linkedin_url && <a href={a.linkedin_url} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">LinkedIn</a>}
                  {a.github_url && <a href={a.github_url} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">GitHub</a>}
                  {a.portfolio_url && <a href={a.portfolio_url} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Portfolio</a>}
                  {a.cv_path && <a href={`/storage/${a.cv_path}`} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-2 rounded-xl bg-[#003D82] px-3 py-2 text-xs font-semibold text-white hover:bg-[#002a5c] transition">Download CV</a>}
                </div>
              </div>
            </div>
            <div className="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
              {stats.map(s => (
                <div key={s.label} className="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                  <div className="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">{s.label}</div>
                  <div className="mt-1 text-lg font-bold text-slate-900">{s.value}</div>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div className="space-y-6 lg:col-span-2">
            {/* Bio */}
            {a.bio && (
              <div className="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                <h3 className="font-bold text-slate-900 mb-3">About</h3>
                <p className="text-sm leading-relaxed text-slate-600" dangerouslySetInnerHTML={{ __html: (a.bio as string).replace(/\n/g, '<br>') }} />
              </div>
            )}

            {/* Achievements */}
            {a.achievements && (
              <div className="rounded-2xl border border-amber-200 bg-amber-50/70 shadow-sm p-6">
                <div className="flex items-center gap-2 mb-3">
                  <span className="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-amber-700">Summary</span>
                  <h3 className="font-bold text-slate-900">Achievements</h3>
                </div>
                <p className="text-sm leading-relaxed text-slate-700" dangerouslySetInnerHTML={{ __html: (a.achievements as string).replace(/\n/g, '<br>') }} />
              </div>
            )}

            {/* Skills */}
            {skills.length > 0 && (
              <div className="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                <h3 className="font-bold text-slate-900 mb-3">Skills</h3>
                <div className="flex flex-wrap gap-2">
                  {skills.map((sk, i) => <span key={i} className="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-800">{sk}</span>)}
                </div>
              </div>
            )}

            {/* Projects */}
            {visibleProjects.length > 0 && (
              <div className="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                <h3 className="font-bold text-slate-900 mb-4">Projects</h3>
                <div className="space-y-4">
                  {visibleProjects.map((p: any, i: number) => (
                    <div key={i} className="border border-slate-200 rounded-xl p-4 hover:shadow-sm transition-shadow">
                      <div className="flex items-start gap-3">
                        <div>
                          <h4 className="font-semibold text-slate-800">{p.title}</h4>
                          {p.description && <p className="text-sm text-slate-600 mt-1 leading-relaxed">{p.description}</p>}
                          {p.project_url && <a href={p.project_url} target="_blank" rel="noopener noreferrer" className="inline-block mt-2 text-xs font-semibold text-blue-700 hover:underline">View Project →</a>}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Employment History */}
            {employmentHistory.length > 0 && (
              <div className="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                <h3 className="font-bold text-slate-900 mb-4">Career History</h3>
                <div className="space-y-4">
                  {employmentHistory.map((e: any, i: number) => (
                    <div key={i} className="flex gap-3">
                      <div className="w-2 h-2 rounded-full bg-[#003D82] mt-2 flex-shrink-0" />
                      <div>
                        <h4 className="font-semibold text-slate-800">{e.position ?? e.job_title}</h4>
                        <p className="text-xs text-gray-500">{e.company} {e.from_year ? `(${e.from_year} – ${e.to_year ?? 'Present'})` : ''}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {detailSections.map(sec => (
              <div key={sec.title} className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div className="bg-[#003D82] text-white px-4 py-3 text-sm font-bold">{sec.title}</div>
                <div className="divide-y divide-slate-100">
                  {sec.items.filter(item => item.value).map(item => (
                    <div key={item.label} className="flex justify-between px-4 py-3 text-sm">
                      <span className="text-slate-500 font-medium">{item.label}</span>
                      <span className="font-semibold text-slate-800 text-right">{item.value}</span>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
