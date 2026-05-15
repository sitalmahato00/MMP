import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { getSiteSettings } from '@shared/services/public.service';

export default function ContactPage() {
  const { data } = useQuery({ queryKey: ['site-settings'], queryFn: getSiteSettings });
  const settings: Record<string, string> = Array.isArray(data)
    ? Object.fromEntries((data as any[]).map((s: any) => [s.key, s.value ?? '']))
    : {};

  const address = settings.contact_address ?? 'Budhiganga-4, Morang, Koshi Province, Nepal';
  const phone = settings.contact_phone ?? '+977 21 590696, +977 21 590697';
  const email = settings.contact_email ?? 'info@mmp.edu.np';
  const contactContent = settings.contact_us_content ?? null;
  const mapEmbed = settings.google_maps_iframe ?? null;

  const contactCards = [
    {
      icon: <svg className="w-6 h-6" style={{ color: '#003D82' }} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>,
      title: 'Address',
      content: <p className="text-sm text-gray-600">{address}</p>,
    },
    {
      icon: <svg className="w-6 h-6" style={{ color: '#003D82' }} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>,
      title: 'Phone',
      content: <p className="text-sm text-gray-600">{phone}</p>,
    },
    {
      icon: <svg className="w-6 h-6" style={{ color: '#003D82' }} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>,
      title: 'Email',
      content: <a href={`mailto:${email}`} className="text-sm text-[#003D82] hover:underline">{email}</a>,
    },
  ];

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          {/* Contact Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {contactCards.map((c, i) => (
              <div key={i} className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center group hover:border-[#003D82]/30 hover:shadow-md transition-all">
                <div className="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style={{ background: '#FEF2F2' }}>
                  {c.icon}
                </div>
                <h3 className="font-bold text-gray-900 mb-1">{c.title}</h3>
                {c.content}
              </div>
            ))}
          </div>

          {/* About Content */}
          {contactContent && (
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm">
              <div className="section-header rounded-t-xl">About Finding Us</div>
              <div className="p-6 prose prose-sm max-w-none text-gray-700 leading-relaxed" dangerouslySetInnerHTML={{ __html: contactContent }} />
            </div>
          )}

          {/* Map */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="section-header">Location Map</div>
            {mapEmbed && mapEmbed.includes('iframe') ? (
              <div className="w-full h-80 [&>iframe]:w-full [&>iframe]:h-full" dangerouslySetInnerHTML={{ __html: mapEmbed }} />
            ) : (
              <div className="w-full h-80">
                <iframe
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3572.8!2d87.2833!3d26.5333!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zManmohan+Memorial+Polytechnic!5e0!3m2!1sen!2snp!4v1234567890"
                  width="100%" height="100%" style={{ border: 0 }} allowFullScreen loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade" title="Manmohan Memorial Polytechnic Location"
                  sandbox="allow-scripts allow-same-origin allow-popups"
                />
              </div>
            )}
            <p className="text-xs text-gray-400 px-4 py-2 text-center">Set your exact map embed in Admin → Web Control → Contact &amp; Info</p>
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Office Hours */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div className="section-header rounded-t-xl">Office Hours</div>
            <div className="p-4 space-y-3">
              {[
                { day: 'Sunday – Friday', hours: '9:00 AM – 5:00 PM' },
                { day: 'Saturday', hours: 'Closed', muted: true },
                { day: 'Public Holidays', hours: 'Closed', muted: true },
              ].map(row => (
                <div key={row.day} className="flex justify-between text-sm">
                  <span className="text-gray-600 font-medium">{row.day}</span>
                  <span className={row.muted ? 'text-gray-500' : 'text-gray-900 font-semibold'}>{row.hours}</span>
                </div>
              ))}
              <div className="mt-3 pt-3 border-t border-gray-100">
                <p className="text-xs text-gray-500">For urgent matters outside office hours, please email us.</p>
              </div>
            </div>
          </div>

          {/* Quick Links */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div className="section-header rounded-t-xl">Quick Links</div>
            <div className="p-4">
              <ul className="space-y-2.5 text-sm">
                {[
                  { label: 'About MMP', to: '/pages/what-is-mmp' },
                  { label: 'Our Programs', to: '/departments' },
                  { label: 'Scholarship Schemes', to: '/pages/scholarship-schemes' },
                  { label: 'Notice Board', to: '/notices' },
                  { label: 'Downloads & Forms', to: '/downloads' },
                ].map(l => (
                  <li key={l.to}>
                    <Link to={l.to} className="flex items-center gap-2 text-gray-700 hover:text-[#003D82] transition-colors">
                      <span className="text-[#003D82] font-bold">›</span>{l.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          </div>

          {/* Admission Inquiry */}
          <div className="rounded-xl p-5 text-white text-center" style={{ background: 'linear-gradient(135deg, #003D82, #001F4D)' }}>
            <svg className="w-10 h-10 mx-auto mb-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 14l9-5-9-5-9 5 9 5z" />
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
            <h3 className="font-bold text-lg mb-2">Admissions Open</h3>
            <p className="text-blue-200 text-sm mb-4">Diploma programs in Engineering &amp; Technology. Apply today!</p>
            <Link to="/departments" className="inline-block bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-bold px-5 py-2 rounded text-sm transition-colors">
              View Programs
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
