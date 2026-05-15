import { useQuery } from '@tanstack/react-query';
import { Link, useSearchParams } from 'react-router-dom';
import { getNewsEvents } from '@shared/services/public.service';

export default function NewsEventsPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const page = parseInt(searchParams.get('page') ?? '1', 10);

  const { data, isLoading } = useQuery({
    queryKey: ['public-news-events', page],
    queryFn: () => getNewsEvents({ page, per_page: 12 }),
  });

  const items: any[] = data?.data ?? [];
  const total: number = data?.total ?? 0;
  const lastPage: number = data?.last_page ?? 1;

  return (
    <div className="mx-auto w-full px-4 py-8 md:px-8 xl:px-16 2xl:px-24">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900 dark:text-slate-100">News &amp; Events</h1>
        <p className="text-sm text-gray-500 dark:text-slate-400">{total} articles</p>
      </div>

      {isLoading ? (
        <div className="flex justify-center py-20"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {items.length > 0 ? items.map((item: any) => {
              const d = new Date(item.published_at ?? item.created_at);
              const firstImage = item.attachments?.find((a: any) => a.is_image);
              const hasVideo = item.attachments?.some((a: any) => ['mp4', 'webm'].includes(a.file_type));
              const isEvent = item.type === 'event';
              return (
                <Link key={item.id} to={`/news-events/${item.slug}`}
                  className="group block rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                  {/* Thumbnail */}
                  <div className="relative h-48 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-slate-700 dark:to-slate-600 overflow-hidden">
                    {firstImage
                      ? <img src={firstImage.url} alt={item.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                      : <div className="w-full h-full flex items-center justify-center">
                        <svg className="w-20 h-20 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                      </div>
                    }
                    {hasVideo && (
                      <div className="absolute inset-0 bg-black/30 flex items-center justify-center">
                        <div className="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center">
                          <svg className="w-8 h-8 text-blue-600 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                        </div>
                      </div>
                    )}
                    <div className="absolute top-3 left-3 bg-blue-600 text-white px-3 py-1.5 rounded-lg shadow-lg">
                      <div className="text-xs font-bold">{d.toLocaleString('en', { month: 'short' })}</div>
                      <div className="text-2xl font-bold leading-none">{d.getDate().toString().padStart(2, '0')}</div>
                    </div>
                    <div className="absolute top-3 right-3">
                      <span className={`px-3 py-1 rounded-full text-xs font-bold shadow-lg text-white ${isEvent ? 'bg-teal-500' : 'bg-purple-500'}`}>
                        {isEvent ? 'Event' : 'News'}
                      </span>
                    </div>
                  </div>
                  {/* Content */}
                  <div className="p-5">
                    <h3 className="text-lg font-bold text-gray-900 dark:text-slate-100 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">{item.title}</h3>
                    {item.content && (
                      <p className="text-sm text-gray-600 dark:text-slate-400 mb-3 line-clamp-2">{item.content.replace(/<[^>]+>/g, '').substring(0, 120)}</p>
                    )}
                    <div className="flex items-center justify-between text-xs text-gray-500">
                      <span className="flex items-center gap-1">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {d.toLocaleDateString('en', { year: 'numeric', month: 'short', day: 'numeric' })}
                      </span>
                      {item.attachments?.length > 0 && (
                        <span className="flex items-center gap-1 text-blue-600 font-semibold">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                          {item.attachments.length} {item.attachments.length === 1 ? 'file' : 'files'}
                        </span>
                      )}
                    </div>
                  </div>
                </Link>
              );
            }) : (
              <div className="col-span-full py-16 text-center">
                <p className="text-4xl mb-4">📰</p>
                <p className="font-semibold text-gray-500">No news or events published yet.</p>
                <p className="mt-2 text-sm text-gray-400">Check back soon for updates.</p>
              </div>
            )}
          </div>

          {/* Pagination */}
          {lastPage > 1 && (
            <div className="mt-8 flex items-center justify-center gap-2">
              {page > 1 && (
                <button onClick={() => setSearchParams({ page: String(page - 1) })}
                  className="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">← Prev</button>
              )}
              {Array.from({ length: Math.min(lastPage, 5) }, (_, i) => {
                const p = Math.max(1, page - 2) + i;
                if (p > lastPage) return null;
                return (
                  <button key={p} onClick={() => setSearchParams({ page: String(p) })}
                    className={`w-9 h-9 text-sm border rounded ${page === p ? 'bg-[#003D82] text-white border-[#003D82]' : 'border-gray-300 hover:bg-gray-50'}`}>
                    {p}
                  </button>
                );
              })}
              {page < lastPage && (
                <button onClick={() => setSearchParams({ page: String(page + 1) })}
                  className="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">Next →</button>
              )}
            </div>
          )}
        </>
      )}
    </div>
  );
}
