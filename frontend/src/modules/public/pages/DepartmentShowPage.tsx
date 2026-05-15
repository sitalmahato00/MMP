import { useQuery } from '@tanstack/react-query';
import { Link, useParams } from 'react-router-dom';
import { getDepartmentBySlug } from '@shared/services/public.service';

export default function DepartmentShowPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data, isLoading } = useQuery({
    queryKey: ['public-department', slug],
    queryFn: () => getDepartmentBySlug(slug!),
    enabled: !!slug,
  });

  const dept = data;

  if (isLoading) {
    return <div className="flex justify-center py-20"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>;
  }
  if (!dept) {
    return (
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-16 text-center">
        <div className="text-4xl mb-3">🏛️</div>
        <p className="text-gray-500 font-semibold">Department not found.</p>
        <Link to="/departments" className="mt-4 inline-block text-[#003D82] font-bold hover:underline">← Back to Departments</Link>
      </div>
    );
  }

  const programs: any[] = dept.programs ?? [];
  const progIconPaths = [
    'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    'M13 10V3L4 14h7v7l9-11h-7z',
    'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
  ];

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          {/* Department Hero */}
          <div className="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div className="h-2" style={{ backgroundColor: '#003D82' }} />
            {dept.photo_url && <img src={dept.photo_url} alt={dept.name} className="w-full h-72 object-cover" />}
            <div className="p-8">
              <div className="flex items-center gap-3 mb-4">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-slate-100">{dept.name}</h2>
                <span className="rounded-md bg-blue-50 border border-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 uppercase">{dept.code}</span>
              </div>
              <p className="text-gray-600 dark:text-slate-400 leading-relaxed mb-6">{dept.description ?? 'This department offers a comprehensive CTEVT-approved 3-year diploma program designed to equip students with hands-on technical skills required in today\'s industry.'}</p>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {[
                  { value: programs.length, label: 'Programs' },
                  { value: '3', label: 'Years Duration' },
                  { value: '6', label: 'Semesters' },
                  { value: 'CTEVT', label: 'Affiliated' },
                ].map(s => (
                  <div key={s.label} className="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                    <div className="text-2xl font-bold text-blue-800">{s.value}</div>
                    <div className="text-xs text-gray-500 font-medium mt-1">{s.label}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Programs */}
          <div className="section-header" style={{ backgroundColor: '#003D82' }}>📚 Programs Offered</div>
          {programs.length > 0 ? programs.map((program: any, idx: number) => (
            <div key={program.id} className="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
              <div className="p-6">
                <div className="flex items-start gap-4">
                  <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-50 border border-blue-100 text-[#003D82]">
                    <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={progIconPaths[idx % progIconPaths.length]} />
                    </svg>
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex items-start justify-between gap-3">
                      <div>
                        <h3 className="text-lg font-bold text-gray-900 dark:text-slate-100">{program.name}</h3>
                        <p className="text-xs text-gray-500 mt-0.5">
                          {program.code}{program.ctevt_code && ` • CTEVT: ${program.ctevt_code}`}{program.affiliation_type && ` • ${program.affiliation_type}`}
                        </p>
                      </div>
                      {program.is_active && <span className="shrink-0 rounded-md bg-emerald-50 border border-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Active</span>}
                    </div>
                    {program.description && <p className="text-sm text-gray-600 dark:text-slate-400 mt-3 line-clamp-2">{program.description}</p>}
                    <div className="flex flex-wrap gap-3 mt-4 text-xs text-gray-500">
                      <div className="flex items-center gap-1.5">
                        <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {program.duration_years} Years
                      </div>
                      <div className="flex items-center gap-1.5">
                        <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        {program.total_semesters} Semesters
                      </div>
                      {program.subjects_count > 0 && (
                        <div className="flex items-center gap-1.5">
                          <svg className="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                          {program.subjects_count} Subjects
                        </div>
                      )}
                    </div>
                    <div className="mt-4">
                      <Link to={`/departments/${dept.slug}/programs/${program.slug}`}
                        className="inline-flex items-center gap-2 rounded-lg border-2 border-[#003D82] bg-white dark:bg-slate-800 px-5 py-2 text-sm font-bold text-[#003D82] dark:text-blue-400 transition-colors hover:bg-[#003D82] hover:text-white">
                        View Full Details
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )) : (
            <div className="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 p-8 text-center shadow-sm">
              <div className="text-4xl mb-3">📚</div>
              <p className="font-semibold text-gray-700">No programs listed yet.</p>
            </div>
          )}
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          <div>
            <div className="section-header" style={{ backgroundColor: '#003D82' }}>🔗 Quick Links</div>
            <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 border-t-0 rounded-b-lg shadow-md">
              {[
                { label: 'All Departments', to: '/departments' },
                { label: 'Notice Board', to: '/notices' },
                { label: 'Downloads & Forms', to: '/downloads' },
                { label: 'People Directory', to: '/people' },
                { label: 'Student Portal', to: '/login' },
              ].map(l => (
                <Link key={l.to} to={l.to} className="flex items-center gap-3 px-4 py-3 border-b border-gray-100 dark:border-slate-700 last:border-0 text-sm text-gray-700 dark:text-slate-300 hover:bg-blue-50 hover:text-blue-800 transition-colors">
                  <span className="text-blue-600">›</span>{l.label}
                </Link>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
