import { useQuery } from '@tanstack/react-query';
import { Link, useSearchParams } from 'react-router-dom';
import { useState } from 'react';
import { getPeople, getDepartments } from '@shared/services/public.service';

export default function PeoplePage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const deptSlug = searchParams.get('department') ?? '';
  const [menuOpen, setMenuOpen] = useState(false);

  const { data, isLoading } = useQuery({
    queryKey: ['public-people', deptSlug],
    queryFn: () => getPeople({ department: deptSlug || undefined }),
  });

  const { data: deptData } = useQuery({ queryKey: ['public-departments'], queryFn: getDepartments });
  const departments: any[] = deptData ?? [];
  // getPeopleByDepartment returns { departments: [...w/ hod+teachers], other_staff: [...] }
  const deptSections: any[] = data?.departments ?? [];
  const otherStaff: any[] = data?.other_staff ?? [];
  const sections: any[] = deptSections.map((d: any) => ({
    name: d.name, code: d.code, slug: d.slug,
    hod: d.hod ?? null,
    teachers: d.teachers ?? [],
    staff: [], labtechs: [],
  }));
  const totalCount: number = deptSections.reduce((n: number, d: any) => n + 1 + (d.teachers?.length ?? 0), 0) + otherStaff.length;

  const activeDeptLabel = departments.find((d: any) => d.slug === deptSlug)?.name ?? 'All Departments';

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          <div className="section-header flex items-center justify-between pr-3" style={{ backgroundColor: '#003D82' }}>
            <span>👥 People Directory</span>
            <span className="text-yellow-300 text-xs font-normal">{totalCount} members</span>
          </div>

          {/* Department Dropdown */}
          <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-4 shadow-sm space-y-3 relative">
            <div>
              <p className="text-sm font-semibold text-gray-700 dark:text-slate-300">Department dropdown</p>
              <p className="text-xs text-gray-500 dark:text-slate-400">Choose a department to view its HOD, teachers, staff, and lab techs.</p>
            </div>
            <div className="relative max-w-xl">
              <button type="button" onClick={() => setMenuOpen(!menuOpen)}
                className="flex w-full items-center justify-between gap-3 rounded-md border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-slate-200 shadow-sm transition hover:border-[#003D82] hover:text-[#003D82]">
                <span className="flex min-w-0 flex-col">
                  <span className="text-[11px] font-medium uppercase tracking-[0.2em] text-gray-400">Selected Department</span>
                  <span className="truncate">{activeDeptLabel}</span>
                </span>
                <svg className={`h-4 w-4 shrink-0 transition-transform ${menuOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              {menuOpen && (
                <div className="absolute left-0 top-full z-20 mt-2 w-full overflow-hidden rounded-md border border-gray-200 bg-white dark:bg-slate-800 shadow-lg">
                  <button onClick={() => { setSearchParams({}); setMenuOpen(false); }}
                    className={`block w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-slate-300 hover:bg-blue-50 hover:text-[#003D82] transition-colors ${deptSlug === '' ? 'bg-blue-50 text-[#003D82]' : ''}`}>
                    All Departments
                  </button>
                  {departments.map((d: any) => (
                    <button key={d.slug} onClick={() => { setSearchParams({ department: d.slug }); setMenuOpen(false); }}
                      className={`block w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-slate-300 hover:bg-blue-50 hover:text-[#003D82] transition-colors ${deptSlug === d.slug ? 'bg-blue-50 text-[#003D82]' : ''}`}>
                      {d.name}
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>

          {isLoading ? (
            <div className="flex justify-center py-16"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
          ) : sections.length > 0 ? (
            <div className="space-y-6">
              {sections.map((section: any, si: number) => {
                const categoryGroups = [
                  { label: 'Head of Department', members: section.hod ? [section.hod] : [] },
                  { label: 'Teachers', members: section.teachers ?? [] },
                  { label: 'Staff', members: section.staff ?? [] },
                  { label: 'Lab Techs', members: section.labtechs ?? [] },
                ];
                return (
                  <section key={si} className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div className="bg-[#003D82] px-5 py-3 text-white flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                      <div>
                        <h2 className="text-lg font-bold">{section.department?.name}</h2>
                        <p className="text-xs text-blue-100">Department-wise people list</p>
                      </div>
                      <Link to={`/departments/${section.department?.slug}`} className="text-xs font-semibold uppercase tracking-wide text-yellow-300 hover:text-yellow-200 transition-colors">
                        View Department
                      </Link>
                    </div>
                    <div className="p-5 space-y-6">
                      {categoryGroups.filter(g => g.members.length > 0).map(group => (
                        <div key={group.label}>
                          <div className="mb-3 flex items-center justify-between gap-3">
                            <h3 className="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-slate-300">{group.label}</h3>
                            <span className="text-xs text-gray-400">{group.members.length} people</span>
                          </div>
                          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                            {group.members.map((member: any, mi: number) => (
                              <div key={mi} className="group text-center p-4 border border-gray-100 dark:border-slate-700 rounded hover:border-[#003D82]/30 hover:shadow-md transition-all">
                                <div className="w-24 h-24 mx-auto mb-3 rounded-full overflow-hidden border-4 border-gray-100 group-hover:border-[#003D82]/20 transition-colors bg-gray-200 shadow-sm">
                                  <img src={member.photo_url ?? '/storage/placeholder-avatar.jpg'} alt={member.name} className="w-full h-full object-cover" loading="lazy" />
                                </div>
                                <h4 className="font-bold text-sm text-gray-900 dark:text-slate-100 group-hover:text-[#003D82] transition-colors">{member.name}</h4>
                                <p className="text-xs text-gray-500 mt-1">{member.designation}</p>
                                {member.department && (
                                  <Link to={`/departments/${member.department_slug}`}
                                    className="text-[10px] text-blue-700 bg-blue-50 px-2 py-0.5 rounded inline-block mt-2 border border-blue-100 hover:bg-blue-100 transition-colors">
                                    {member.department}
                                  </Link>
                                )}
                              </div>
                            ))}
                          </div>
                        </div>
                      ))}
                      {categoryGroups.every(g => g.members.length === 0) && (
                        <p className="text-sm text-gray-400 text-center py-4">No members listed for this department yet.</p>
                      )}
                    </div>
                  </section>
                );
              })}
            </div>
          ) : (
            <div className="bg-white dark:bg-slate-800 border border-gray-200 rounded-xl p-10 text-center shadow-sm">
              <div className="text-4xl mb-3">👥</div>
              <p className="font-semibold text-gray-700 dark:text-slate-300">No people found.</p>
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
                { label: 'Leadership', to: '/leadership' },
                { label: 'Notice Board', to: '/notices' },
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
