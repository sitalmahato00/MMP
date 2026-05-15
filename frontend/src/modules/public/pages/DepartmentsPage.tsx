import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { getDepartments } from '@shared/services/public.service';

const DEPT_ICONS: Record<string, string> = {
  IT: '💻', CE: '🏗️', EL: '⚡', EE: '📡', ME: '⚙️', AR: '📐',
};

export default function DepartmentsPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['public-departments'],
    queryFn: getDepartments,
  });

  const departments: any[] = data ?? [];

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8 bg-[#f9f9f9] dark:bg-slate-900">
      <div className="section-header mb-6 dark:bg-slate-700" style={{ backgroundColor: '#003D82' }}>🏛️ Our Departments</div>
      <p className="text-gray-600 dark:text-slate-400 text-sm mb-6 -mt-3">
        Explore the CTEVT-affiliated departments at Manmohan Memorial Polytechnic. Each department offers diploma programs with hands-on training and industry-ready skills.
      </p>

      {isLoading ? (
        <div className="flex justify-center py-20"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {departments.length > 0 ? departments.map((dept: any) => (
            <div key={dept.id} className="group bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-md hover:shadow-2xl hover:border-blue-200 dark:hover:border-blue-600 hover:scale-[1.02] hover:-translate-y-1 transition-all duration-300">
              <div className="h-2 dark:bg-blue-600" style={{ backgroundColor: '#003D82' }} />
              <div className="aspect-[16/9] bg-gray-100 dark:bg-slate-700 overflow-hidden relative">
                {dept.photo_url
                  ? <img src={dept.photo_url} alt={dept.name} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                  : <div className="w-full h-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center text-6xl">{DEPT_ICONS[dept.code] ?? '🏛️'}</div>
                }
                {dept.is_active && (
                  <span className="absolute top-3 right-3 rounded-md bg-emerald-500 px-2 py-0.5 text-[10px] font-bold text-white shadow">Active</span>
                )}
              </div>
              <div className="p-5">
                <div className="flex items-center gap-2 mb-2">
                  <span className="text-2xl">{DEPT_ICONS[dept.code] ?? '🏛️'}</span>
                  <span className="rounded-md bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 px-2 py-0.5 text-[10px] font-bold text-blue-800 dark:text-blue-400 uppercase tracking-wide">{dept.code}</span>
                </div>
                <h3 className="font-bold text-lg text-gray-900 dark:text-slate-100 mb-2 leading-tight group-hover:text-[#003D82] dark:group-hover:text-blue-400 transition-colors duration-300">{dept.name}</h3>
                <p className="text-sm text-gray-500 dark:text-slate-400 mb-4 leading-relaxed line-clamp-2">{dept.description ?? 'CTEVT approved diploma engineering program.'}</p>

                <div className="grid grid-cols-3 gap-2 mb-4 text-center">
                  {[
                    { value: dept.programs_count ?? 0, label: 'Programs' },
                    { value: dept.students_count ?? 0, label: 'Students' },
                    { value: dept.teachers_count ?? 0, label: 'Teachers' },
                  ].map(s => (
                    <div key={s.label} className="rounded-lg bg-gray-50 dark:bg-slate-700 border border-gray-100 dark:border-slate-600 py-2">
                      <div className="text-sm font-bold text-gray-900 dark:text-slate-100">{s.value}</div>
                      <div className="text-[10px] text-gray-400 dark:text-slate-500">{s.label}</div>
                    </div>
                  ))}
                </div>

                <Link to={`/departments/${dept.slug}`}
                  className="block w-full text-center rounded-lg border-2 border-[#003D82] dark:border-blue-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-bold text-[#003D82] dark:text-blue-400 transition-all duration-300 hover:bg-[#003D82] hover:text-white dark:hover:bg-blue-600 hover:scale-105">
                  View Department →
                </Link>
              </div>
            </div>
          )) : (
            <div className="col-span-full bg-white dark:bg-slate-800 border border-gray-200 rounded-2xl p-10 text-center text-gray-500 shadow-md">
              <div className="text-4xl mb-3">🏛️</div>
              <p className="font-semibold text-gray-700 dark:text-slate-300">No departments published yet.</p>
              <p className="text-sm mt-1">Departments will appear here automatically once added in the admin panel.</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
