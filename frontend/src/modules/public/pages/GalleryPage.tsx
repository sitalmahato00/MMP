import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import { getGallery } from '@shared/services/public.service';

export default function GalleryPage() {
  const { data, isLoading } = useQuery({ queryKey: ['public-gallery'], queryFn: getGallery });
  const media: any[] = data ?? [];
  const [lightbox, setLightbox] = useState<{ image: string; title: string } | null>(null);

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2">
          <div className="section-header flex items-center justify-between" style={{ backgroundColor: '#003D82' }}>
            <span>📷 Photo Gallery</span>
            <span className="text-blue-200 text-xs">{media.length} photos</span>
          </div>

          <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 border-t-0 p-5">
            {isLoading ? (
              <div className="flex justify-center py-16"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>
            ) : media.length > 0 ? (
              <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                {media.map((item: any, i: number) => (
                  <div key={i}
                    className="group relative aspect-square overflow-hidden rounded bg-gray-100 dark:bg-slate-700 cursor-pointer border border-gray-200 dark:border-slate-600 hover:border-[#003D82] transition-colors"
                    onClick={() => setLightbox({ image: item.url, title: item.title })}>
                    <img src={item.url} alt={item.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
                    <div className="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-end">
                      <div className="w-full p-2 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                        <p className="text-white text-xs font-medium truncate">{item.title}</p>
                        {item.department && <p className="text-white/70 text-[10px]">{item.department.name}</p>}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="py-16 text-center text-gray-400">
                <p className="text-5xl mb-4">📷</p>
                <p className="font-semibold text-gray-500">No photos available yet.</p>
                <p className="text-sm text-gray-400 mt-2">Gallery photos will be uploaded soon. Check back later!</p>
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
                { label: 'News & Events', to: '/news-events' },
                { label: 'Notice Board', to: '/notices' },
                { label: 'Downloads & Forms', to: '/downloads' },
                { label: 'Our Programs', to: '/departments' },
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

      {/* Lightbox */}
      {lightbox && (
        <div className="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4" onClick={() => setLightbox(null)}>
          <button className="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 z-50" onClick={() => setLightbox(null)}>&times;</button>
          <div className="max-w-4xl max-h-[85vh] w-full" onClick={e => e.stopPropagation()}>
            <img src={lightbox.image} alt={lightbox.title} className="max-w-full max-h-[80vh] mx-auto rounded shadow-2xl object-contain" />
            <p className="text-white text-center mt-3 text-sm font-medium">{lightbox.title}</p>
          </div>
        </div>
      )}
    </div>
  );
}
