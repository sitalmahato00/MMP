import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { getPage } from '@shared/services/public.service';

export default function ContentPage() {
  const { slug } = useParams<{ slug: string }>();
  const { data, isLoading } = useQuery({
    queryKey: ['public-page', slug],
    queryFn: () => getPage(slug!),
    enabled: !!slug,
  });
  const page: any = data ?? null;

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2">
          {isLoading ? (
            <div className="flex justify-center py-16">
              <div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" />
            </div>
          ) : page ? (
            <>
              <div className="section-header" style={{ backgroundColor: '#003D82' }}>
                📄 {page.title}
              </div>
              <div
                className="bg-white border border-gray-200 border-t-0 p-8 prose prose-sm max-w-none prose-headings:text-blue-900 prose-a:text-blue-700"
                dangerouslySetInnerHTML={{ __html: page.content ?? '' }}
              />
            </>
          ) : (
            <div className="bg-white border border-gray-200 p-12 text-center">
              <p className="text-5xl mb-4">📄</p>
              <p className="font-semibold text-gray-600">Page not found.</p>
            </div>
          )}
        </div>
        <div>
          <div className="section-header" style={{ backgroundColor: '#003D82' }}>🔗 Quick Links</div>
          <div className="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
            {[
              { label: 'Notices', to: '/notices' },
              { label: 'Our Programs', to: '/departments' },
              { label: 'Downloads', to: '/downloads' },
            ].map(l => (
              <Link key={l.to} to={l.to} className="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors">
                <span className="text-blue-600">›</span>{l.label}
              </Link>
            ))}
            <Link to="/login" className="flex items-center gap-3 px-4 py-3 text-sm font-bold text-blue-800">
              <span>🔐</span> Student Portal
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
