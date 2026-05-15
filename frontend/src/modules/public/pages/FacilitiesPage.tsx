import { useQuery } from '@tanstack/react-query';
import { useSearchParams } from 'react-router-dom';
import { getFacilities, getDepartments } from '@shared/services/public.service';

export default function FacilitiesPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const deptSlug = searchParams.get('department') ?? '';

  const { data, isLoading } = useQuery({
    queryKey: ['public-facilities', deptSlug],
    queryFn: () => getFacilities({ department: deptSlug || undefined }),
  });
  const { data: deptData } = useQuery({ queryKey: ['public-departments'], queryFn: getDepartments });

  const facilities: any[] = data ?? [];
  const departments: any[] = deptData ?? [];

  // Group facilities by category
  const grouped = facilities.reduce((acc: Record<string, any[]>, f: any) => {
    const cat = f.category ?? 'uncategorized';
    if (!acc[cat]) acc[cat] = [];
    acc[cat].push(f);
    return acc;
  }, {});

  return (
    <div className="bg-[#f9f9f9] dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 min-h-screen">
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-[#003D82] dark:text-blue-400">Campus Facilities &amp; Resources</h1>
          <p className="text-gray-600 dark:text-slate-400 mt-2">State-of-the-art infrastructure facilitating excellence in technical education.</p>
        </div>

        {/* Department Filter */}
        <div className="mb-8 flex justify-center">
          <div className="inline-flex flex-wrap items-center gap-2 bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-2 shadow-md hover:shadow-lg transition-shadow duration-300">
            <button onClick={() => setSearchParams({})}
              className={`px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 ${!deptSlug ? 'bg-[#003D82] text-white shadow-md' : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 hover:shadow-sm'}`}>
              All Departments
            </button>
            {departments.map((d: any) => (
              <button key={d.slug} onClick={() => setSearchParams({ department: d.slug })}
                className={`px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 ${deptSlug === d.slug ? 'bg-[#003D82] text-white shadow-md' : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 hover:shadow-sm'}`}>
                {d.name}
              </button>
            ))}
          </div>
        </div>

        {isLoading ? (
          <div className="flex justify-center py-24"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
        ) : Object.keys(grouped).length > 0 ? (
          Object.entries(grouped).map(([category, items]) => (
            <div key={category} className="mb-12">
              <h2 className="text-2xl font-bold text-[#003366] dark:text-blue-400 border-b-2 border-yellow-500 dark:border-yellow-600 pb-2 mb-6 capitalize">
                {category.replace(/_/g, ' ')}
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                {items.map((facility: any, i: number) => (
                  <div key={i} className="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-gray-200 dark:border-slate-700 overflow-hidden group hover:shadow-2xl hover:scale-[1.02] hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                    <div className="w-full h-48 bg-gray-200 dark:bg-slate-700 relative overflow-hidden">
                      {facility.image_urls?.length > 0 ? (
                        <>
                          <img src={facility.image_urls[0]} alt={facility.name} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy" />
                          {facility.image_urls.length > 1 && (
                            <div className="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-3 py-1.5 rounded-full shadow-lg backdrop-blur-sm">
                              +{facility.image_urls.length - 1} Photos
                            </div>
                          )}
                        </>
                      ) : (
                        <div className="w-full h-full flex flex-col items-center justify-center text-gray-400 dark:text-slate-500">
                          <svg className="w-12 h-12 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                          </svg>
                        </div>
                      )}
                    </div>
                    <div className="p-5">
                      <h3 className="font-bold text-gray-900 dark:text-slate-100 text-lg mb-1 group-hover:text-[#003D82] dark:group-hover:text-blue-400 transition-colors duration-300">{facility.name}</h3>
                      {(facility.department || facility.program) && (
                        <div className="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-3">
                          {facility.department?.name ?? facility.program?.name ?? ''}
                        </div>
                      )}
                      {facility.location && (
                        <div className="text-xs text-gray-500 dark:text-slate-400 mb-3 flex items-center gap-1">
                          <svg className="w-3 h-3 text-[#003D82] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                          </svg>
                          {facility.location}
                          {facility.capacity && <><span className="mx-2">|</span><span>{facility.capacity} Seats</span></>}
                        </div>
                      )}
                      <p className="text-[13px] text-gray-600 dark:text-slate-300 line-clamp-3 mb-4">
                        {facility.description ?? (facility.content ? String(facility.content).replace(/<[^>]+>/g, '') : '')}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ))
        ) : (
          <div className="text-center py-24">
            <p className="text-5xl mb-4">🏫</p>
            <p className="font-semibold text-gray-600 dark:text-slate-400">No facilities found.</p>
          </div>
        )}
      </div>
    </div>
  );
}
