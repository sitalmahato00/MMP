import { useQuery } from '@tanstack/react-query';
import { Link, useParams } from 'react-router-dom';
import { getNewsEventBySlug } from '@shared/services/public.service';

export default function NewsEventShowPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data, isLoading } = useQuery({
    queryKey: ['public-news-event', slug],
    queryFn: () => getNewsEventBySlug(slug!),
    enabled: !!slug,
  });

  const item = data;

  if (isLoading) {
    return <div className="flex justify-center py-20"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>;
  }
  if (!item) {
    return (
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-16 text-center">
        <div className="text-4xl mb-3">📰</div>
        <p className="text-gray-500 font-semibold">Article not found.</p>
        <Link to="/news-events" className="mt-4 inline-block text-[#003D82] font-bold hover:underline">← Back to News &amp; Events</Link>
      </div>
    );
  }

  const d = new Date(item.published_at ?? item.created_at);
  const images: any[] = item.attachments?.filter((a: any) => a.is_image) ?? [];
  const videos: any[] = item.attachments?.filter((a: any) => ['mp4', 'webm', 'mov', 'avi'].includes(a.file_type)) ?? [];
  const otherAttachments: any[] = item.attachments?.filter((a: any) => !a.is_image && !['mp4', 'webm', 'mov', 'avi'].includes(a.file_type)) ?? [];
  const isEvent = item.type === 'event';

  return (
    <div className="mx-auto w-full px-4 py-8 md:px-8 xl:px-16 2xl:px-24">
      <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <div className="overflow-hidden rounded-lg bg-white dark:bg-slate-800 shadow-md">
            {/* Header */}
            <div className="border-b border-gray-200 dark:border-slate-700 px-6 py-4">
              <div className="mb-3 flex flex-wrap items-center gap-2">
                <span className={`rounded border px-2 py-1 text-xs font-bold uppercase text-white ${isEvent ? 'bg-teal-500 border-teal-500' : 'bg-purple-500 border-purple-500'}`}>{item.type}</span>
                {item.department && <span className="rounded border border-blue-100 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">{item.department.name}</span>}
              </div>
              <h1 className="mb-4 text-2xl font-bold text-gray-900 dark:text-slate-100 md:text-3xl">{item.title}</h1>
              <div className="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-slate-400">
                <div className="flex items-center gap-2">
                  <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  <span>{d.toLocaleDateString('en', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                </div>
                {item.author && (
                  <div className="flex items-center gap-2">
                    <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span>{item.author.name}</span>
                  </div>
                )}
              </div>
            </div>

            {/* Media Gallery */}
            {(images.length > 0 || videos.length > 0) && (
              <div className="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 px-6 py-6">
                <h3 className="mb-4 text-lg font-semibold text-gray-900 dark:text-slate-100 flex items-center gap-2">
                  <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  Media Gallery
                </h3>
                {images.length > 0 && (
                  <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    {images.map((img: any, i: number) => (
                      <a key={i} href={img.url} target="_blank" rel="noopener"
                        className="group relative aspect-video rounded-lg overflow-hidden bg-gray-200 shadow-md hover:shadow-xl transition-all">
                        <img src={img.url} alt={img.file_name} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                        <div className="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                          <svg className="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                        </div>
                      </a>
                    ))}
                  </div>
                )}
                {videos.length > 0 && (
                  <div className="space-y-4">
                    {videos.map((v: any, i: number) => (
                      <div key={i} className="rounded-lg overflow-hidden bg-black shadow-lg">
                        <video controls className="w-full" preload="metadata">
                          <source src={v.url} type={`video/${v.file_type}`} />
                          Your browser does not support the video tag.
                        </video>
                        <div className="bg-gray-800 px-4 py-2 text-sm text-gray-300">{v.file_name}</div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )}

            {/* Content */}
            <div className="px-6 py-6">
              <div className="prose prose-lg max-w-none dark:prose-invert">
                {(item.content ?? '').split('\n').map((line: string, i: number) => (
                  <span key={i}>{line}<br /></span>
                ))}
              </div>
            </div>

            {/* Other Attachments */}
            {(item.attachment || otherAttachments.length > 0) && (
              <div className="border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 px-6 py-4">
                <h3 className="mb-4 text-lg font-semibold text-gray-900 dark:text-slate-100">Attachments</h3>
                <div className="space-y-3">
                  {item.attachment && (
                    <div className="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 hover:border-blue-300 transition-colors">
                      <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                        <svg className="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                      </div>
                      <div className="min-w-0 flex-1"><p className="text-sm font-medium text-gray-900">Main Attachment</p></div>
                      <a href={`/storage/${item.attachment}`} target="_blank" rel="noopener" className="flex-shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">Download</a>
                    </div>
                  )}
                  {otherAttachments.map((att: any, i: number) => (
                    <div key={i} className="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 hover:border-blue-300 transition-colors">
                      <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                        <svg className="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className="text-sm font-medium text-gray-900">{att.file_name}</p>
                        {(att.file_size || att.file_type) && <p className="text-xs text-gray-500">{[att.file_size ? `${(att.file_size / 1024).toFixed(1)} KB` : null, att.file_type?.toUpperCase()].filter(Boolean).join(' • ')}</p>}
                      </div>
                      <a href={`/storage/${att.file_path}`} target="_blank" rel="noopener" className="flex-shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">Download</a>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          <div>
            <div className="section-header" style={{ backgroundColor: '#003D82' }}>🔗 Quick Links</div>
            <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 border-t-0 rounded-b-lg shadow-md">
              {[
                { label: 'All News & Events', to: '/news-events' },
                { label: 'Notice Board', to: '/notices' },
                { label: 'Gallery', to: '/gallery' },
                { label: 'Departments', to: '/departments' },
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
