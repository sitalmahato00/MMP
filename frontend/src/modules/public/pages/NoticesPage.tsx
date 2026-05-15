import { useQuery } from '@tanstack/react-query';
import { Link, useSearchParams } from 'react-router-dom';
import { useState } from 'react';
import { getNotices } from '@shared/services/public.service';

export default function NoticesPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const page = parseInt(searchParams.get('page') ?? '1', 10);
  const type = searchParams.get('type') ?? '';

  const { data, isLoading } = useQuery({
    queryKey: ['public-notices', page, type],
    queryFn: () => getNotices({ page, per_page: 20, type: type || undefined }),
  });

  const notices: any[] = data?.data ?? [];
  const total: number = data?.total ?? 0;
  const lastPage: number = data?.last_page ?? 1;

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="mb-4">
        <h1 className="text-2xl font-bold text-gray-900 dark:text-slate-100">Notices &amp; Announcements</h1>
        <p className="text-sm text-gray-500 dark:text-slate-400">{total} notices</p>
      </div>

      {/* Type filter tabs */}
      <div className="flex gap-2 mb-4 flex-wrap">
        {[{ label: 'All', value: '' }, { label: 'General', value: 'general' }, { label: 'Exam', value: 'exam' }, { label: 'Event', value: 'event' }].map(t => (
          <button key={t.value}
            onClick={() => setSearchParams(t.value ? { type: t.value, page: '1' } : { page: '1' })}
            className={`px-3 py-1.5 rounded-full text-xs font-semibold border transition-all ${type === t.value ? 'bg-[#003D82] text-white border-[#003D82]' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 border-gray-200 dark:border-slate-600 hover:border-[#003D82] hover:text-[#003D82]'}`}>
            {t.label}
          </button>
        ))}
      </div>

      {isLoading ? (
        <div className="flex justify-center py-20"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
      ) : (
        <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-md divide-y divide-gray-100 dark:divide-slate-700">
          {notices.length > 0 ? notices.map((notice: any) => {
            const d = new Date(notice.published_at ?? notice.created_at);
            return (
              <Link key={notice.id} to={`/notices/${notice.slug}`}
                className="flex items-center gap-4 px-5 py-4 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors group">
                <div className="flex-1 min-w-0">
                  <div className="font-semibold text-gray-900 dark:text-slate-100 group-hover:text-[#003D82] dark:group-hover:text-blue-400 text-sm leading-snug">{notice.title}</div>
                  <div className="flex items-center gap-1.5 mt-1.5 flex-wrap">
                    <span className="text-[10px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800 uppercase">{notice.type}</span>
                    {notice.department && <span className="text-[10px] font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded border border-emerald-100">{notice.department.name}</span>}
                    {notice.program && <span className="text-[10px] font-medium text-green-700 bg-green-50 px-1.5 py-0.5 rounded border border-green-100">{notice.program.name}</span>}
                    {notice.semester && <span className="text-[10px] font-medium text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-100">Semester {notice.semester}</span>}
                    <span className="text-[10px] text-gray-400">{d.toLocaleDateString('en', { year: 'numeric', month: 'short', day: 'numeric' })}</span>
                    {notice.attachment && (
                      <span className="text-[10px] text-blue-700 flex items-center gap-1 font-semibold">
                        <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        Attachment
                      </span>
                    )}
                  </div>
                </div>
                <svg className="w-4 h-4 text-gray-300 dark:text-slate-600 group-hover:text-[#003D82] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
              </Link>
            );
          }) : (
            <div className="py-16 text-center text-gray-400">
              <div className="text-4xl mb-3">📋</div>
              <p className="font-semibold text-gray-500">No notices found.</p>
            </div>
          )}
        </div>
      )}

      {/* Pagination */}
      {lastPage > 1 && (
        <div className="mt-6 flex items-center justify-center gap-2">
          {page > 1 && (
            <button onClick={() => setSearchParams({ page: String(page - 1), ...(type ? { type } : {}) })}
              className="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">← Prev</button>
          )}
          {Array.from({ length: Math.min(lastPage, 5) }, (_, i) => {
            const p = Math.max(1, page - 2) + i;
            if (p > lastPage) return null;
            return (
              <button key={p} onClick={() => setSearchParams({ page: String(p), ...(type ? { type } : {}) })}
                className={`w-9 h-9 text-sm border rounded ${page === p ? 'bg-[#003D82] text-white border-[#003D82]' : 'border-gray-300 hover:bg-gray-50'}`}>
                {p}
              </button>
            );
          })}
          {page < lastPage && (
            <button onClick={() => setSearchParams({ page: String(page + 1), ...(type ? { type } : {}) })}
              className="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">Next →</button>
          )}
        </div>
      )}
    </div>
  );
}
