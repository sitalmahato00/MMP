import { useQuery } from '@tanstack/react-query';
import { Link, useParams } from 'react-router-dom';
import { getNoticeBySlug, getNotices } from '@shared/services/public.service';

export default function NoticeShowPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data, isLoading } = useQuery({
    queryKey: ['public-notice', slug],
    queryFn: () => getNoticeBySlug(slug!),
    enabled: !!slug,
  });
  const { data: relatedData } = useQuery({
    queryKey: ['public-notices-related'],
    queryFn: () => getNotices({ page: 1, per_page: 5 }),
  });

  const notice = data?.data;
  const related: any[] = relatedData?.data?.data ?? [];

  if (isLoading) {
    return <div className="flex justify-center py-20"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>;
  }
  if (!notice) {
    return (
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-16 text-center">
        <div className="text-4xl mb-3">📋</div>
        <p className="text-gray-500 font-semibold">Notice not found.</p>
        <Link to="/notices" className="mt-4 inline-block text-[#003D82] font-bold hover:underline">← Back to Notices</Link>
      </div>
    );
  }

  const d = new Date(notice.published_at ?? notice.created_at);
  const images: any[] = notice.attachments?.filter((a: any) => a.is_image) ?? [];
  const videos: any[] = notice.attachments?.filter((a: any) => ['mp4', 'webm', 'mov', 'avi'].includes(a.file_type)) ?? [];
  const otherAttachments: any[] = notice.attachments?.filter((a: any) => !a.is_image && !['mp4', 'webm', 'mov', 'avi'].includes(a.file_type)) ?? [];

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Main Content */}
        <div className="lg:col-span-2">
          <div className="bg-white dark:bg-slate-800 rounded-lg shadow-md overflow-hidden">
            {/* Notice Header */}
            <div className="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
              <div className="flex items-center gap-2 mb-3 flex-wrap">
                <span className="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-1 rounded border border-blue-100 uppercase">{notice.type}</span>
                {notice.department && <span className="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-1 rounded border border-blue-100">{notice.department.name}</span>}
                {notice.program && <span className="text-xs font-medium text-green-700 bg-green-50 px-2 py-1 rounded border border-green-100">{notice.program.name}</span>}
                {notice.semester && <span className="text-xs font-medium text-purple-700 bg-purple-50 px-2 py-1 rounded border border-purple-100">Semester {notice.semester}</span>}
              </div>
              <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-slate-100 mb-4">{notice.title}</h1>
              <div className="flex items-center gap-4 text-sm text-gray-600 dark:text-slate-400 flex-wrap">
                <div className="flex items-center gap-2">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  <span>{d.toLocaleDateString('en', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                </div>
                {notice.author && (
                  <div className="flex items-center gap-2">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span>{notice.author.name}</span>
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
                      <a key={i} href={img.url} target="_blank" rel="noopener" className="group relative aspect-video rounded-lg overflow-hidden bg-gray-200 shadow-md hover:shadow-xl transition-all">
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

            {/* Notice Content */}
            <div className="px-6 py-6">
              <div className="prose prose-lg max-w-none dark:prose-invert">
                {(notice.content ?? '').split('\n').map((line: string, i: number) => (
                  <span key={i}>{line}<br /></span>
                ))}
              </div>
            </div>

            {/* Attachments */}
            {(notice.attachment || otherAttachments.length > 0) && (
              <div className="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                  <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                  Attachments
                </h3>
                <div className="space-y-3">
                  {notice.attachment && (
                    <AttachmentRow label="Main Attachment" href={`/storage/${notice.attachment}`} />
                  )}
                  {otherAttachments.map((att: any, i: number) => (
                    <AttachmentRow key={i} label={att.file_name} subLabel={[att.file_size ? `${(att.file_size / 1024).toFixed(1)} KB` : null, att.file_type ? att.file_type.toUpperCase() : null].filter(Boolean).join(' • ')} href={`/storage/${att.file_path}`} />
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {related.filter((r: any) => r.id !== notice.id).length > 0 && (
            <div>
              <div className="section-header" style={{ backgroundColor: '#003D82' }}>📋 Related Notices</div>
              <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 border-t-0 rounded-b-lg shadow-md">
                {related.filter((r: any) => r.id !== notice.id).slice(0, 5).map((r: any) => {
                  const rd = new Date(r.published_at ?? r.created_at);
                  return (
                    <Link key={r.id} to={`/notices/${r.slug}`}
                      className="flex items-start gap-3 px-4 py-3 border-b border-gray-100 dark:border-slate-700 last:border-0 text-sm text-gray-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-800 transition-colors">
                      <div className="flex-shrink-0 w-8 h-10 text-white flex flex-col items-center justify-center rounded text-center text-xs" style={{ backgroundColor: '#003D82' }}>
                        <span className="font-bold leading-none">{rd.getDate().toString().padStart(2, '0')}</span>
                        <span className="text-[8px] uppercase leading-none">{rd.toLocaleString('en', { month: 'short' })}</span>
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-medium leading-snug">{r.title}</p>
                        <span className="text-xs font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded uppercase inline-block mt-1">{r.type}</span>
                      </div>
                    </Link>
                  );
                })}
              </div>
            </div>
          )}

          <div>
            <div className="section-header" style={{ backgroundColor: '#003D82' }}>🔗 Quick Links</div>
            <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 border-t-0 rounded-b-lg shadow-md">
              {[
                { label: 'All Notices', to: '/notices' },
                { label: 'Downloads & Forms', to: '/downloads' },
                { label: 'Departments', to: '/departments' },
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

function AttachmentRow({ label, subLabel, href }: { label: string; subLabel?: string; href: string }) {
  return (
    <div className="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3 hover:border-blue-300 transition-colors">
      <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
        <svg className="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
      </div>
      <div className="min-w-0 flex-1">
        <p className="text-sm font-medium text-gray-900 dark:text-slate-100">{label}</p>
        {subLabel && <p className="text-xs text-gray-500">{subLabel}</p>}
      </div>
      <a href={href} target="_blank" rel="noopener" className="flex-shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">Download</a>
    </div>
  );
}
