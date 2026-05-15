import { useQuery } from '@tanstack/react-query';
import { useSearchParams, Link } from 'react-router-dom';
import { getQuestionBank, getDepartments } from '@shared/services/public.service';

export default function QuestionBankPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const dept = searchParams.get('department') ?? '';

  const { data, isLoading } = useQuery({
    queryKey: ['public-question-bank', dept],
    queryFn: () => getQuestionBank({ department_id: dept ? Number(dept) : undefined }),
  });
  const { data: deptData } = useQuery({ queryKey: ['public-departments'], queryFn: getDepartments });

  const downloads: any[] = data ?? [];
  const departments: any[] = deptData ?? [];
  // dept param stores id as string


  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2">
          <div className="section-header flex items-center justify-between" style={{ backgroundColor: '#003D82' }}>
            <span>📝 Question Bank</span>
            <span className="text-blue-200 text-xs">{downloads.length} papers</span>
          </div>

          <div className="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
            {isLoading ? (
              <div className="flex justify-center py-16">
                <div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" />
              </div>
            ) : downloads.length > 0 ? (
              downloads.map((dl: any, i: number) => (
                <div key={i} className="flex items-center gap-4 px-5 py-4 border-b border-gray-100 last:border-0 hover:bg-blue-50 transition-colors group">
                  <div className="flex-shrink-0 w-12 h-12 bg-yellow-50 text-yellow-700 rounded flex items-center justify-center border border-yellow-200">
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="font-semibold text-sm text-gray-900 group-hover:text-[#003D82] transition-colors">{dl.title}</div>
                    <div className="flex items-center gap-2 mt-1 flex-wrap">
                      {dl.department && (
                        <span className="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{dl.department?.name ?? dl.department}</span>
                      )}
                      <span className="text-xs text-gray-400">
                        {dl.created_at ? new Date(dl.created_at).toLocaleDateString('en', { year: 'numeric', month: 'long', day: 'numeric' }) : ''}
                      </span>
                    </div>
                  </div>
                  <a href={dl.file_url} download
                    className="flex-shrink-0 flex items-center gap-2 px-4 py-2 text-sm font-bold text-white rounded transition-colors hover:opacity-90"
                    style={{ backgroundColor: '#003D82' }}>
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download
                  </a>
                </div>
              ))
            ) : (
              <div className="py-16 text-center text-gray-400">
                <p className="text-5xl mb-4">📝</p>
                <p className="font-semibold text-gray-500">No question papers available yet.</p>
                <p className="text-sm text-gray-400 mt-2">Past exam question papers will be uploaded soon.</p>
              </div>
            )}
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Department Filter */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div className="section-header rounded-t-xl" style={{ backgroundColor: '#003D82' }}>Filter by Department</div>
            <div className="p-3 space-y-1">
              <button onClick={() => setSearchParams({})}
                className={`block w-full text-left px-3 py-2 rounded text-sm transition-colors ${dept === '' ? 'bg-blue-50 text-[#003D82] font-semibold' : 'text-gray-700 hover:bg-gray-50'}`}>
                All Departments
              </button>
              {departments.map((d: any) => (
                <button key={d.id} onClick={() => setSearchParams({ department: String(d.id) })}
                  className={`block w-full text-left px-3 py-2 rounded text-sm transition-colors ${dept === String(d.id) ? 'bg-blue-50 text-[#003D82] font-semibold' : 'text-gray-700 hover:bg-gray-50'}`}>
                  {d.name}
                </button>
              ))}
            </div>
          </div>
          <div>
            <div className="section-header" style={{ backgroundColor: '#003D82' }}>🔗 Quick Links</div>
            <div className="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
              {[
                { label: 'Downloads & Forms', to: '/downloads' },
                { label: 'Notice Board', to: '/notices' },
                { label: 'Our Programs', to: '/departments' },
                { label: 'Student Portal', to: '/login' },
              ].map(l => (
                <Link key={l.to} to={l.to} className="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors">
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
