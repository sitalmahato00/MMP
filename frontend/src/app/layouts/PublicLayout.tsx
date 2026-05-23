import { Outlet, Link, useLocation } from 'react-router-dom';
import PublicNavbar from '@shared/components/navbar/PublicNavbar';

export default function PublicLayout() {
  const { pathname } = useLocation();
  const isHome = pathname === '/';

  const topBar = (
    <div style={{ backgroundColor: '#003D82' }} className="sticky top-0 z-50 hidden py-1.5 text-xs text-white lg:block">
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex justify-between items-center">
        <div className="flex items-center gap-5">
          <span className="flex items-center gap-1.5">
            <svg className="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Budhiganga-4, Morang, Koshi Province, Nepal
          </span>
          <span className="text-blue-400">|</span>
          <span className="flex items-center gap-1.5">
            <svg className="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            +977 21 590696, +977 21 590697
          </span>
        </div>
        <div className="flex items-center gap-4">
          <a href="mailto:info@mmp.edu.np" className="flex items-center gap-1.5 hover:text-yellow-400 transition-colors">
            <svg className="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            info@mmp.edu.np
          </a>
          <Link to="/login" className="bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-semibold px-4 py-1 rounded text-[11px] transition-colors">
            Login Portal
          </Link>
        </div>
      </div>
    </div>
  );

  const logoBar = (
    <div className="hidden border-b border-gray-200 bg-white py-2.5 shadow-sm lg:block md:py-3">
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex items-center justify-between gap-3">
        <Link to="/" className="flex min-w-0 flex-1 items-center gap-3">
          <div className="w-11 h-11 md:w-14 md:h-14 flex-shrink-0 rounded-full flex items-center justify-center" style={{ background: 'radial-gradient(circle, #003D82, #001F4D)', border: '2px solid #DAA520' }}>
            <span className="text-lg md:text-xl font-bold text-white">MMP</span>
          </div>
          <div className="min-w-0 leading-tight">
            <div className="text-base sm:text-xl font-semibold font-serif leading-tight text-[#003D82] line-clamp-1">Manmohan Memorial Polytechnic</div>
            <div className="text-[11px] sm:text-sm font-normal text-[#DAA520] line-clamp-1">Best Technical College in Koshi Province</div>
            <div className="hidden sm:block text-xs text-gray-500 font-normal">A Constituent College of Manmohan Technical University</div>
          </div>
        </Link>
      </div>
    </div>
  );

  return (
    <div className="bg-gray-100 text-gray-900 dark:bg-slate-950 dark:text-slate-100 antialiased transition-colors">
      {topBar}
      {isHome && logoBar}
      <div className="sticky lg:top-[36px] top-0 z-50"><PublicNavbar /></div>

      {/* ── Main Content ─────────────────────────────────────────── */}
      <main className="min-h-[60vh] overflow-x-hidden">
        <Outlet />
      </main>

      {/* ── Footer ───────────────────────────────────────────────── */}
      <footer style={{ backgroundColor: '#003D82' }} className="mt-8 pb-0 pt-12 text-white lg:block">
        <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-10 border-b border-blue-700">
            <div>
              <h3 className="font-bold font-serif text-lg mb-4 text-yellow-400">Manmohan Memorial Polytechnic</h3>
              <p className="text-blue-200 text-sm leading-relaxed mb-5">
                Best Technical College in Koshi Province. CTEVT affiliated constituent college of Manmohan Technical University (MTU).
              </p>
            </div>
            <div>
              <h3 className="font-bold font-serif text-lg mb-4 text-yellow-400">Quick Links</h3>
              <ul className="space-y-2 text-sm text-blue-200">
                {[
                  { to: '/', label: 'Home' },
                  { to: '/pages/what-is-mmp', label: 'About MMP' },
                  { to: '/departments', label: 'Departments & Programs' },
                  { to: '/notices', label: 'Notice Board' },
                  { to: '/downloads', label: 'Downloads & Forms' },
                  { to: '/contact', label: 'Contact Us' },
                  { to: '/login', label: 'Student Portal' },
                ].map((l) => (
                  <li key={l.to}>
                    <Link to={l.to} className="hover:text-white transition-colors flex items-center gap-2">
                      <span className="text-red-500">›</span> {l.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
            <div>
              <h3 className="font-bold font-serif text-lg mb-4 text-yellow-400">Contact Us</h3>
              <ul className="space-y-3 text-sm text-blue-200">
                <li className="flex items-start gap-2"><span className="mt-0.5 text-blue-400">📍</span>Budhiganga-4, Morang, Koshi Province, Nepal</li>
                <li className="flex items-start gap-2"><span className="text-blue-400">📞</span>+977 21 590696 / 590697</li>
                <li className="flex items-start gap-2"><span className="text-blue-400">✉️</span>info@mmp.edu.np</li>
              </ul>
            </div>
            <div>
              <h3 className="font-bold font-serif text-lg mb-4 text-yellow-400">Useful Links</h3>
              <div className="space-y-1 text-sm">
                {[
                  { href: 'http://ctevt.org.np', label: 'CTEVT' },
                  { href: 'https://mtu.edu.np/', label: 'Manmohan Technical University' },
                  { href: 'http://nstb.org.np', label: 'NSTB' },
                ].map((l) => (
                  <a key={l.href} href={l.href} target="_blank" rel="noreferrer" className="flex items-center gap-2 hover:text-white transition-colors">
                    <span className="text-red-500">›</span>{l.label}
                  </a>
                ))}
              </div>
            </div>
          </div>
          <div className="-mx-4 px-4 py-4 mt-0 text-center text-sm text-blue-300" style={{ backgroundColor: '#001F4D' }}>
            <p>&copy; {new Date().getFullYear()} Manmohan Memorial Polytechnic (www.mmp.edu.np). All Rights Reserved.</p>
            <p className="text-xs mt-1 text-blue-400">Budhiganga-4, Morang, Koshi Province, Nepal | Phone: +977 21 590696 | info@mmp.edu.np</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
