import { useQuery } from '@tanstack/react-query';
import { useSearchParams, Link } from 'react-router-dom';
import { useState } from 'react';
import { getAlumniDirectory, getDepartments } from '@shared/services/public.service';

const GRADIENTS = [
  'from-blue-500 to-indigo-600', 'from-violet-500 to-purple-600', 'from-emerald-500 to-teal-600',
  'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-sky-600',
];

export default function AlumniPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [search, setSearch] = useState(searchParams.get('search') ?? '');
  const [dept, setDept] = useState(searchParams.get('department') ?? '');
  const [year, setYear] = useState(searchParams.get('year') ?? '');

  const page = parseInt(searchParams.get('page') ?? '1', 10);

  const { data, isLoading } = useQuery({
    queryKey: ['public-alumni', searchParams.get('search'), searchParams.get('department'), searchParams.get('year'), page],
    queryFn: () => getAlumniDirectory({
      search: searchParams.get('search') ?? undefined,
      department_id: searchParams.get('department') ? Number(searchParams.get('department')) : undefined,
      year: searchParams.get('year') ?? undefined,
      page,
    }),
  });

  const { data: deptData } = useQuery({ queryKey: ['public-departments'], queryFn: getDepartments });
  const departments: any[] = deptData ?? [];

  const alumni: any[] = data?.alumni?.data ?? [];
  const meta = data?.alumni ?? {};
  const graduationYears: number[] = data?.graduation_years ?? [];

  const hasFilters = !!(searchParams.get('search') || searchParams.get('department') || searchParams.get('year'));

  function handleSearch(e: React.FormEvent) {
    e.preventDefault();
    const params: Record<string, string> = {};
    if (search) params.search = search;
    if (dept) params.department = dept;
    if (year) params.year = year;
    setSearchParams(params);
  }

  return (
    <section className="py-12 lg:py-16">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8 text-center">
          <h1 className="text-3xl font-bold text-slate-900 lg:text-4xl">Alumni Directory</h1>
          <p className="mx-auto mt-3 max-w-2xl text-base text-slate-600">Explore our growing network of graduates making an impact across Nepal and beyond.</p>
        </div>

        {/* Filters */}
        <form onSubmit={handleSearch} className="mb-8 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5 shadow-md hover:shadow-xl transition-shadow duration-300">
          <div className="flex flex-wrap items-end gap-3">
            <div className="flex-1 min-w-[200px]">
              <label className="mb-1.5 block text-xs font-semibold text-slate-600">Search</label>
              <input value={search} onChange={e => setSearch(e.target.value)} type="text" placeholder="Search alumni by name…"
                className="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#003D82] focus:ring-[#003D82]/20 border" />
            </div>
            <div className="min-w-[180px]">
              <label className="mb-1.5 block text-xs font-semibold text-slate-600">Department</label>
              <select value={dept} onChange={e => setDept(e.target.value)}
                className="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#003D82] focus:ring-[#003D82]/20 border">
                <option value="">All Departments</option>
                {departments.map((d: any) => <option key={d.id} value={d.id}>{d.name}</option>)}
              </select>
            </div>
            <div className="min-w-[140px]">
              <label className="mb-1.5 block text-xs font-semibold text-slate-600">Batch</label>
              <select value={year} onChange={e => setYear(e.target.value)}
                className="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#003D82] focus:ring-[#003D82]/20 border">
                <option value="">All Years</option>
                {graduationYears.map(y => <option key={y} value={y}>{y}</option>)}
              </select>
            </div>
            <button type="submit" className="rounded-xl bg-[#003D82] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#002a5c] transition">Search</button>
            {hasFilters && (
              <button type="button" onClick={() => { setSearch(''); setDept(''); setYear(''); setSearchParams({}); }}
                className="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                Clear
              </button>
            )}
          </div>
        </form>

        {/* Results */}
        {isLoading ? (
          <div className="flex justify-center py-24"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
        ) : alumni.length > 0 ? (
          <>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
              {alumni.map((a: any) => {
                const grad = GRADIENTS[a.id % 6];
                const profileCompletion = Math.max(0, Math.min(100, parseInt(a.profile_completion ?? 0, 10)));
                return (
                  <Link key={a.id} to={`/alumni/${a.id}`}
                    className="group rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-md overflow-hidden hover:shadow-2xl hover:border-slate-300 hover:scale-[1.02] hover:-translate-y-1 transition-all duration-300">
                    <div className="px-4 pt-6 pb-4 relative text-center">
                      {a.is_featured && (
                        <span className="absolute top-2 right-2 rounded-lg bg-amber-50 border border-amber-200 px-2 py-0.5 text-[10px] font-bold text-amber-700">⭐ Featured</span>
                      )}
                      <div className="flex justify-center mb-3">
                        {a.user?.avatar ? (
                          <img src={`/storage/${a.user.avatar}`} alt="" className="h-20 w-20 rounded-full object-cover ring-4 ring-slate-100 shadow-md" />
                        ) : (
                          <div className={`flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br ${grad} text-2xl font-bold text-white ring-4 ring-slate-100 shadow-md`}>
                            {(a.user?.name ?? 'A')[0].toUpperCase()}
                          </div>
                        )}
                      </div>
                      <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-[#003D82] transition truncate">{a.user?.name}</h3>
                      {a.current_job && (
                        <p className="text-xs text-slate-500 truncate">{a.current_job}{a.company_name ? ` · ${a.company_name}` : ''}</p>
                      )}
                      {a.work_location && <p className="text-[11px] text-slate-400 truncate">{a.work_location}</p>}
                      <div className="mt-2 flex flex-wrap gap-1 justify-center">
                        {a.department?.code && <span className="rounded bg-slate-100 dark:bg-slate-700 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:text-slate-300">{a.department.code}</span>}
                        {a.graduation_year && <span className="rounded bg-slate-100 dark:bg-slate-700 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:text-slate-300">Batch {a.graduation_year}</span>}
                        {a.is_verified && <span className="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Verified</span>}
                        {(a.visible_projects_count ?? 0) > 0 && <span className="rounded bg-violet-50 px-2 py-0.5 text-[10px] font-bold text-violet-600">{a.visible_projects_count} project{a.visible_projects_count > 1 ? 's' : ''}</span>}
                      </div>
                      {profileCompletion > 0 && (
                        <div className="mt-3">
                          <div className="h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div className="h-full rounded-full bg-gradient-to-r from-[#003D82] to-amber-500" style={{ width: `${profileCompletion}%` }} />
                          </div>
                          <p className="mt-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Profile {profileCompletion}%</p>
                        </div>
                      )}
                    </div>
                  </Link>
                );
              })}
            </div>
            {/* Pagination */}
            {meta.last_page > 1 && (
              <div className="mt-8 flex justify-center gap-2">
                {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(p => (
                  <button key={p} onClick={() => setSearchParams({ ...Object.fromEntries(searchParams), page: String(p) })}
                    className={`px-3 py-1.5 rounded text-sm font-semibold transition-colors ${p === page ? 'bg-[#003D82] text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'}`}>
                    {p}
                  </button>
                ))}
              </div>
            )}
          </>
        ) : (
          <div className="py-24 text-center">
            <p className="text-5xl mb-4">🎓</p>
            <p className="font-semibold text-gray-600">No alumni found.</p>
          </div>
        )}
      </div>
    </section>
  );
}
