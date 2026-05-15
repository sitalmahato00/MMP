import { useQuery } from '@tanstack/react-query';
import { Link, useSearchParams } from 'react-router-dom';
import { useState } from 'react';
import { getDownloads, getDepartments } from '@shared/services/public.service';

const CATEGORIES = [
  { label: 'All', value: '' },
  { label: 'Forms', value: 'forms' },
  { label: 'Syllabus', value: 'syllabus' },
  { label: 'Notes', value: 'notes' },
  { label: 'Question Bank', value: 'question-bank' },
  { label: 'Reports & Publications', value: 'reports' },
];

export default function DownloadsPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const category = searchParams.get('category') ?? '';
  const department = searchParams.get('department') ?? '';
  const search = searchParams.get('search') ?? '';

  const [searchInput, setSearchInput] = useState(search);
  const [deptInput, setDeptInput] = useState(department);
  const [catInput, setCatInput] = useState(category);

  const { data, isLoading } = useQuery({
    queryKey: ['public-downloads', category, department, search],
    queryFn: () => getDownloads({ category: category || undefined, department_id: department ? Number(department) : undefined, search: search || undefined }),
  });

  const { data: deptData } = useQuery({ queryKey: ['public-departments'], queryFn: getDepartments });
  const departments: any[] = deptData ?? [];
  const downloads: any[] = data ?? [];

  const applyFilters = (e: React.FormEvent) => {
    e.preventDefault();
    const params: Record<string, string> = {};
    if (searchInput) params.search = searchInput;
    if (deptInput) params.department = deptInput;
    if (catInput) params.category = catInput;
    setSearchParams(params);
  };

  const clearFilters = () => {
    setSearchInput(''); setDeptInput(''); setCatInput('');
    setSearchParams({});
  };

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8 bg-[#f9f9f9] dark:bg-slate-900">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2">
          <div className="section-header dark:bg-slate-700" style={{ backgroundColor: '#003D82' }}>📥 Downloads &amp; Resources</div>

          {/* Advanced Filters */}
          <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 border-t-0 rounded-lg shadow-md p-4 mb-4">
            <form onSubmit={applyFilters} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-2">Search Resources</label>
                  <input type="text" value={searchInput} onChange={e => setSearchInput(e.target.value)}
                    placeholder="Search by title..."
                    className="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-sm bg-white dark:bg-slate-700 dark:text-slate-200" />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-2">Department</label>
                  <select value={deptInput} onChange={e => setDeptInput(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:border-blue-600 text-sm bg-white dark:bg-slate-700 dark:text-slate-200">
                    <option value="">All Departments</option>
                    {departments.map((d: any) => <option key={d.code} value={d.code}>{d.name}</option>)}
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-2">Category</label>
                  <select value={catInput} onChange={e => setCatInput(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:border-blue-600 text-sm bg-white dark:bg-slate-700 dark:text-slate-200">
                    <option value="">All Categories</option>
                    {CATEGORIES.slice(1).map(c => <option key={c.value} value={c.value}>{c.label}</option>)}
                  </select>
                </div>
              </div>
              <div className="flex gap-2">
                <button type="submit" className="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-all hover:scale-105">Filter</button>
                <button type="button" onClick={clearFilters} className="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg transition-all">Clear</button>
              </div>
            </form>
          </div>

          {/* Category Quick Filters */}
          <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-md p-4 mb-4">
            <div className="flex flex-wrap gap-2">
              {CATEGORIES.map(f => (
                <button key={f.value}
                  onClick={() => { setCatInput(f.value); setSearchParams({ ...(f.value ? { category: f.value } : {}), ...(department ? { department } : {}), ...(search ? { search } : {}) }); }}
                  className={`inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border transition-all ${category === f.value ? 'bg-blue-700 text-white border-blue-700 scale-105' : 'bg-gray-50 dark:bg-slate-700 text-gray-600 dark:text-slate-300 border-gray-200 dark:border-slate-600 hover:text-blue-700 hover:border-blue-700 hover:scale-105'}`}>
                  {f.label}
                </button>
              ))}
            </div>
          </div>

          <div className="mb-4 text-sm text-gray-600 dark:text-slate-400 font-medium">Showing {downloads.length} resource(s)</div>

          {/* Downloads List */}
          <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-md overflow-hidden">
            {isLoading ? (
              <div className="flex justify-center py-16"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
            ) : downloads.length > 0 ? downloads.map((dl: any, i: number) => {
              const d = new Date(dl.created_at);
              return (
                <div key={i} className="flex flex-col sm:flex-row items-start sm:items-center gap-4 px-5 py-4 border-b border-gray-100 dark:border-slate-700 last:border-0 hover:bg-blue-50 dark:hover:bg-slate-700 transition-all group">
                  <div className="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="font-semibold text-sm text-gray-900 dark:text-slate-100 group-hover:text-blue-800 dark:group-hover:text-blue-400 transition-colors">{dl.title}</div>
                    <div className="flex flex-wrap gap-2 mt-2">
                      {dl.category && <span className="text-xs text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded border border-blue-100 dark:border-blue-800 uppercase font-bold">{dl.category}</span>}
                      {dl.department && <span className="text-xs text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded border border-blue-100 dark:border-blue-800 font-semibold">{dl.department.name}</span>}
                      <span className="text-xs text-gray-500 dark:text-slate-400">{d.toLocaleDateString('en', { year: 'numeric', month: 'short', day: 'numeric' })}</span>
                    </div>
                  </div>
                  <div className="flex items-center gap-2 flex-shrink-0 w-full sm:w-auto">
                    <a href={`/api/v1/public/downloads/${dl.id}/file`} target="_blank" rel="noopener"
                      className="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold rounded transition-all border border-blue-700 dark:border-blue-600 text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:scale-105">
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                      Download
                    </a>
                  </div>
                </div>
              );
            }) : (
              <div className="py-16 text-center">
                <p className="text-5xl mb-4">📂</p>
                <p className="text-gray-500 font-medium">
                  {search || department || category ? 'No resources found matching your filters.' : 'No downloads available yet.'}
                </p>
                <p className="text-sm text-gray-400 mt-2">Please check back later or contact the college office.</p>
              </div>
            )}
          </div>
        </div>

        {/* Sidebar */}
        <div>
          <div className="section-header" style={{ backgroundColor: '#003D82' }}>⚡ Quick Links</div>
          <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 border-t-0 rounded-b-lg shadow-md">
            {[
              { label: 'Notice Board', to: '/notices' },
              { label: 'Syllabus', to: '/downloads?category=syllabus' },
              { label: 'Notes', to: '/downloads?category=notes' },
              { label: 'Question Bank', to: '/question-bank' },
              { label: 'All Resources', to: '/downloads' },
              { label: '🔐 Student Portal', to: '/login', bold: true },
            ].map(l => (
              <Link key={l.to} to={l.to}
                className={`flex items-center gap-3 px-4 py-3 border-b border-gray-100 dark:border-slate-700 last:border-0 text-sm hover:bg-blue-50 hover:text-blue-800 transition-colors ${l.bold ? 'font-bold text-blue-800' : 'text-gray-700 dark:text-slate-300'}`}>
                {!l.bold && <span className="text-blue-600">›</span>}{l.label}
              </Link>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
