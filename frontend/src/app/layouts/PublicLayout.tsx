import { Outlet, Link, useLocation, NavLink } from 'react-router-dom';
import { useEffect, useState, useRef } from 'react';
import { useQuery } from '@tanstack/react-query';
import { getDepartments } from '@shared/services/public.service';

// ─── Types ───────────────────────────────────────────────────────────────────
interface Department { id: number; name: string; slug: string; }

// ─── Theme Hook ──────────────────────────────────────────────────────────────
function usePublicTheme() {
  const getEffective = () => {
    const choice = localStorage.getItem('mmp.theme') || 'system';
    const sys = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    return choice === 'system' ? sys : choice;
  };
  const [effectiveTheme, setEffective] = useState<string>(getEffective);

  const toggle = () => {
    const current = localStorage.getItem('mmp.theme') || 'system';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('mmp.theme', next);
    const ef = next;
    document.documentElement.classList.toggle('dark', ef === 'dark');
    document.documentElement.dataset.theme = ef;
    setEffective(ef);
  };

  useEffect(() => {
    const ef = getEffective();
    document.documentElement.classList.toggle('dark', ef === 'dark');
    document.documentElement.dataset.theme = ef;
  }, []);

  return { effectiveTheme, toggle };
}

// ─── Dropdown ────────────────────────────────────────────────────────────────
function NavDropdown({ label, children }: { label: string; children: React.ReactNode }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  return (
    <div
      className="relative"
      ref={ref}
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
    >
      <button className="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 border-transparent hover:border-white flex items-center gap-1">
        {label}
        <svg className={`w-3 h-3 ml-0.5 transition-transform duration-200 ${open ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      {open && (
        <div className="absolute top-full left-0 mt-0 w-64 bg-[#404040] py-2 z-50 shadow-xl border-t-2 border-white">
          {children}
        </div>
      )}
    </div>
  );
}

function NavDropdownItem({ href, children }: { href: string; children: React.ReactNode }) {
  return (
    <Link to={href} className="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5 last:border-0">
      {children}
    </Link>
  );
}

// ─── Mobile AccordionItem ─────────────────────────────────────────────────────
function DrawerAccordion({ label, icon, children }: { label: string; icon: React.ReactNode; children: React.ReactNode }) {
  const [open, setOpen] = useState(false);
  return (
    <div>
      <button onClick={() => setOpen(v => !v)}
        className="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 border-2 border-transparent hover:border-slate-200 dark:hover:border-slate-700">
        <span className="flex items-center gap-3">{icon}{label}</span>
        <svg className={`h-4 w-4 transition-transform ${open ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      {open && (
        <div className="ml-8 mt-1 space-y-1 border-l-2 border-slate-300 dark:border-slate-600 pl-3">
          {children}
        </div>
      )}
    </div>
  );
}
function DrawerSubLink({ to, children }: { to: string; children: React.ReactNode }) {
  return (
    <Link to={to} className="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-all duration-200 hover:translate-x-1 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
      {children}
    </Link>
  );
}

// ─── Breadcrumb Banner ────────────────────────────────────────────────────────
function BreadcrumbBanner({ title }: { title?: string }) {
  const { pathname } = useLocation();
  const isHome = pathname === '/';
  if (isHome || !title) return null;
  return (
    <div className="relative hidden overflow-hidden py-6 text-white lg:block"
      style={{ background: 'linear-gradient(to right, #001F4D, #003D82, #001F4D)' }}>
      <div className="absolute inset-0 opacity-5"
        style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")` }}>
      </div>
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto relative">
        <h1 className="text-xl font-bold font-serif mb-1.5">{title}</h1>
        <nav className="flex items-center gap-2 text-blue-200 text-xs">
          <Link to="/" className="hover:text-white transition-colors">Home</Link>
          <span className="text-blue-400">›</span>
          <span className="text-yellow-300">{title}</span>
        </nav>
      </div>
    </div>
  );
}

// ─── PublicLayout ─────────────────────────────────────────────────────────────
export default function PublicLayout() {
  const { pathname } = useLocation();
  const isHome = pathname === '/';
  const { effectiveTheme, toggle } = usePublicTheme();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [mobileNavOpen, setMobileNavOpen] = useState(false);

  const { data: deptData } = useQuery({ queryKey: ['public-departments'], queryFn: getDepartments, staleTime: 10 * 60_000 });
  const departments: Department[] = deptData ?? [];

  // Close menu on route change
  useEffect(() => { setMobileMenuOpen(false); setMobileNavOpen(false); }, [pathname]);

  // Prevent body scroll when drawer open
  useEffect(() => {
    document.body.style.overflow = mobileMenuOpen ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [mobileMenuOpen]);

  const mobileNavItems = [
    { label: 'Home', to: '/', icon: <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5.5v-6h-5v6H4a1 1 0 01-1-1v-9.5z" /> },
    { label: 'Departments', to: '/departments', icon: <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /> },
    { label: 'Notices', to: '/notices', icon: <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /> },
    { label: 'Gallery', to: '/gallery', icon: <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /> },
    { label: 'Login', to: '/login', icon: <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M15 3h3a2 2 0 012 2v14a2 2 0 01-2 2h-3M10 17l5-5-5-5M15 12H3" /> },
  ];

  const ThemeIcon = () => effectiveTheme !== 'dark'
    ? <svg className="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z" /></svg>
    : <svg className="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.364 6.364l-1.591-1.591M7.227 7.227L5.636 5.636m12.728 0l-1.591 1.591M7.227 16.773l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>;

  return (
    <div className="overflow-x-hidden bg-gray-100 text-gray-900 dark:bg-slate-950 dark:text-slate-100 antialiased transition-colors">

      {/* ── MOBILE FIXED HEADER ───────────────────────────────── */}
      <header className="fixed inset-x-0 top-0 z-50 border-b-2 border-slate-200 bg-white/95 shadow-md backdrop-blur lg:hidden dark:border-slate-700 dark:bg-slate-900/95">
        <div className="flex items-center justify-between gap-2 px-3 sm:px-4 pb-2 sm:pb-3 pt-2 sm:pt-3">
          <div className="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
            <button type="button" onClick={() => setMobileMenuOpen(v => !v)}
              className="inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-xl sm:rounded-2xl border-2 border-slate-300 bg-white text-slate-600 shadow-md transition-all duration-200 hover:bg-slate-50 hover:scale-105 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
              <svg className="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            <Link to="/" className="flex min-w-0 items-center gap-2 sm:gap-3 flex-1">
              <div className="flex h-9 w-9 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-[#003D82] shadow-lg border-2 border-blue-600 dark:border-blue-500">
                <img src="/api/v1/public/brand-logo" alt="MMP Logo" className="h-7 w-7 sm:h-9 sm:w-9 rounded-xl sm:rounded-2xl object-cover" />
              </div>
              <div className="min-w-0 flex-1">
                <p className="truncate text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-[#003D82] dark:text-blue-300 leading-tight">MMP</p>
                <p className="truncate text-[11px] sm:text-xs font-bold text-slate-900 dark:text-slate-50 leading-tight">Manmohan Memorial Polytechnic</p>
              </div>
            </Link>
          </div>
          <div className="flex items-center gap-1.5 sm:gap-2">
            <button type="button" onClick={toggle}
              className="inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-xl sm:rounded-2xl border-2 border-slate-300 bg-white text-slate-600 shadow-md transition-all duration-200 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
              <ThemeIcon />
            </button>
          </div>
        </div>
      </header>

      {/* ── MOBILE MENU OVERLAY ───────────────────────────────── */}
      {mobileMenuOpen && (
        <div onClick={() => setMobileMenuOpen(false)}
          className="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden"
          style={{ marginTop: 'calc(3.5rem)' }} />
      )}

      {/* ── MOBILE MENU DRAWER ────────────────────────────────── */}
      <div className={`fixed left-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white dark:bg-slate-900 shadow-2xl overflow-hidden z-50 lg:hidden flex flex-col transition-transform duration-300 ${mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'}`}
        style={{ marginTop: 'calc(3.5rem)' }}>
        {/* Drawer Header */}
        <div className="bg-[#003D82] px-4 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <img src="/api/v1/public/brand-logo" alt="MMP Logo" className="h-10 w-10 rounded-lg object-cover border-2 border-white/20" />
            <div>
              <p className="text-white font-bold text-sm">MMP</p>
              <p className="text-blue-200 text-xs">Navigation Menu</p>
            </div>
          </div>
          <button onClick={() => setMobileMenuOpen(false)} className="text-white hover:bg-white/10 rounded-lg p-2 transition-colors">
            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        {/* Drawer Content */}
        <div className="flex-1 overflow-y-auto px-3 py-3">
          <nav className="space-y-1">
            <NavLink to="/"
              className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 border-2 ${isActive ? 'bg-[#003D82] text-white border-[#003D82] shadow-md' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border-transparent hover:border-slate-200 dark:hover:border-slate-700'}`}>
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
              Home
            </NavLink>

            <DrawerAccordion label="About Us" icon={<svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>}>
              <DrawerSubLink to="/pages/what-is-mmp">What is MMP</DrawerSubLink>
              <DrawerSubLink to="/pages/objectives">Objectives</DrawerSubLink>
              <DrawerSubLink to="/leadership">Presidents &amp; Principals</DrawerSubLink>
              <DrawerSubLink to="/contact">Contact Us</DrawerSubLink>
            </DrawerAccordion>

            <DrawerAccordion label="Departments" icon={<svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>}>
              {departments.map(d => <DrawerSubLink key={d.id} to={`/departments/${d.slug}`}>{d.name}</DrawerSubLink>)}
              {departments.length === 0 && <span className="block px-4 py-2 text-sm text-slate-400">No departments</span>}
              <Link to="/departments" className="block rounded-lg px-4 py-2 text-sm font-semibold text-[#003D82] dark:text-blue-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 hover:translate-x-1">All Departments →</Link>
            </DrawerAccordion>

            <NavLink to="/news-events"
              className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 border-2 ${isActive ? 'bg-[#003D82] text-white border-[#003D82]' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border-transparent hover:border-slate-200 dark:hover:border-slate-700'}`}>
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
              News &amp; Events
            </NavLink>

            <DrawerAccordion label="People" icon={<svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>}>
              <DrawerSubLink to="/people">All People</DrawerSubLink>
              <DrawerSubLink to="/staff">Administrative Staff</DrawerSubLink>
              <DrawerSubLink to="/leadership">Presidents &amp; Principals</DrawerSubLink>
              {departments.map(d => <DrawerSubLink key={d.id} to={`/people?department=${d.slug}`}>{d.name}</DrawerSubLink>)}
            </DrawerAccordion>

            <DrawerAccordion label="Features" icon={<svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>}>
              <DrawerSubLink to="/facilities">Campus Facilities &amp; Resources</DrawerSubLink>
              <DrawerSubLink to="/pages/scholarship-schemes">Scholarship Schemes</DrawerSubLink>
              <DrawerSubLink to="/pages/internships">Internships &amp; Placements</DrawerSubLink>
            </DrawerAccordion>

            <NavLink to="/notices"
              className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 border-2 ${isActive ? 'bg-[#003D82] text-white border-[#003D82]' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border-transparent hover:border-slate-200 dark:hover:border-slate-700'}`}>
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
              Notices
            </NavLink>

            <NavLink to="/gallery"
              className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 border-2 ${isActive ? 'bg-[#003D82] text-white border-[#003D82]' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border-transparent hover:border-slate-200 dark:hover:border-slate-700'}`}>
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              Gallery
            </NavLink>

            <NavLink to="/alumni"
              className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 border-2 ${isActive ? 'bg-[#003D82] text-white border-[#003D82]' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border-transparent hover:border-slate-200 dark:hover:border-slate-700'}`}>
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
              Alumni
            </NavLink>

            <NavLink to="/result"
              className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 border-2 ${isActive ? 'bg-[#003D82] text-white border-[#003D82]' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border-transparent hover:border-slate-200 dark:hover:border-slate-700'}`}>
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
              Result
            </NavLink>

            <DrawerAccordion label="Resources" icon={<svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>}>
              <DrawerSubLink to="/downloads">All Resources</DrawerSubLink>
              <DrawerSubLink to="/downloads?category=forms">Forms &amp; Downloads</DrawerSubLink>
              <DrawerSubLink to="/downloads?category=syllabus">Syllabus</DrawerSubLink>
              <DrawerSubLink to="/question-bank">Question Bank</DrawerSubLink>
            </DrawerAccordion>

            <NavLink to="/contact"
              className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 border-2 ${isActive ? 'bg-[#003D82] text-white border-[#003D82]' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border-transparent hover:border-slate-200 dark:hover:border-slate-700'}`}>
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              Contact Us
            </NavLink>

            <Link to="/login"
              className="flex items-center gap-3 rounded-xl border-2 border-[#003D82] px-4 py-3 text-sm font-semibold text-[#003D82] transition hover:bg-[#003D82] hover:text-white dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-400 dark:hover:text-white">
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
              Login Portal
            </Link>
          </nav>
        </div>
      </div>

      {/* ── DESKTOP TOP INFO BAR ───────────────────────────────── */}
      <div style={{ backgroundColor: '#003D82' }} className="hidden py-1.5 text-xs text-white lg:block">
        <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex justify-between items-center">
          <div className="flex items-center gap-5">
            <span className="flex items-center gap-1.5">
              <svg className="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              Budhiganga-4, Morang, Koshi Province, Nepal
            </span>
            <span className="text-blue-400">|</span>
            <span className="flex items-center gap-1.5">
              <svg className="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
              +977 21 590696, +977 21 590697
            </span>
          </div>
          <div className="flex items-center gap-4">
            <a href="mailto:info@mmp.edu.np" className="flex items-center gap-1.5 hover:text-yellow-400 transition-colors">
              <svg className="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              info@mmp.edu.np
            </a>
            <button type="button" onClick={toggle}
              className="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/20 text-white transition hover:bg-white/10">
              <ThemeIcon />
            </button>
            <Link to="/login" className="bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-semibold px-4 py-1 rounded text-[11px] transition-colors">
              Login Portal
            </Link>
          </div>
        </div>
      </div>

      {/* ── DESKTOP LOGO BAR (home only) ───────────────────────── */}
      {isHome && (
        <div className="hidden border-b border-gray-200 bg-white py-2.5 shadow-sm lg:block md:py-3">
          <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex items-center justify-between gap-3">
            <Link to="/" className="flex min-w-0 flex-1 items-center gap-3">
              <div className="w-11 h-11 md:w-14 md:h-14 flex-shrink-0 rounded-full flex items-center justify-center border-2" style={{ background: 'radial-gradient(circle, #003D82, #001F4D)', borderColor: '#DAA520' }}>
                <img src="/api/v1/public/brand-logo" alt="MMP Logo" className="w-full h-full object-cover rounded-full" />
              </div>
              <div className="min-w-0 leading-tight">
                <div className="text-base sm:text-xl font-semibold font-serif leading-tight text-[#003D82] line-clamp-1">Manmohan Memorial Polytechnic</div>
                <div className="text-[11px] sm:text-sm font-normal line-clamp-1" style={{ color: '#DAA520' }}>Best Technical College in Koshi Province</div>
                <div className="hidden sm:block text-xs text-gray-500 font-normal">A Constituent College of Manmohan Technical University</div>
                <div className="sm:hidden text-[10px] font-normal text-gray-500">mmp.edu.np</div>
              </div>
            </Link>
          </div>
        </div>
      )}

      {/* ── DESKTOP STICKY NAV ────────────────────────────────── */}
      <nav style={{ backgroundColor: '#003D82' }} className="hidden sticky top-0 z-50 shadow-md lg:block">
        <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
          <div className="flex items-center justify-between">
            {/* Desktop Nav Links */}
            <div className="hidden xl:flex items-center flex-1">
              <NavLink to="/" end
                className={({ isActive }) => `text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 ${isActive ? 'border-white bg-white/10' : 'border-transparent hover:border-white'}`}>
                HOME
              </NavLink>

              <NavDropdown label="ABOUT US">
                <NavDropdownItem href="/pages/what-is-mmp">What is MMP</NavDropdownItem>
                <NavDropdownItem href="/pages/objectives">Objectives</NavDropdownItem>
                <NavDropdownItem href="/leadership">Presidents &amp; Principals</NavDropdownItem>
                <NavDropdownItem href="/contact">Contact Us</NavDropdownItem>
              </NavDropdown>

              {/* Departments dropdown */}
              <NavDropdown label="DEPARTMENTS">
                {departments.map(d => <NavDropdownItem key={d.id} href={`/departments/${d.slug}`}>{d.name}</NavDropdownItem>)}
                {departments.length === 0 && <span className="block px-5 py-2.5 text-[13px] text-gray-400">No departments available</span>}
                <div className="my-1 border-t border-white/10" />
                <NavDropdownItem href="/departments"><span className="text-yellow-300 font-bold">All Departments →</span></NavDropdownItem>
              </NavDropdown>

              <NavDropdown label="FEATURES">
                <NavDropdownItem href="/facilities">Campus Facilities &amp; Resources</NavDropdownItem>
                <NavDropdownItem href="/pages/scholarship-schemes">Scholarship Schemes</NavDropdownItem>
                <NavDropdownItem href="/pages/internships">Internships &amp; Placements</NavDropdownItem>
              </NavDropdown>

              {/* People dropdown */}
              <NavDropdown label="PEOPLE">
                <div className="px-5 py-2 text-[11px] uppercase tracking-[0.18em] text-gray-400 border-b border-white/5">Departments</div>
                {departments.map(d => <NavDropdownItem key={d.id} href={`/people?department=${d.slug}`}>{d.name}</NavDropdownItem>)}
                {departments.length === 0 && <span className="block px-5 py-2.5 text-[13px] text-gray-400 border-b border-white/5">No departments available</span>}
                <div className="my-1 border-t border-white/10" />
                <NavDropdownItem href="/staff">Administrative Staff</NavDropdownItem>
                <NavDropdownItem href="/leadership">Presidents &amp; Principals</NavDropdownItem>
              </NavDropdown>

              <NavLink to="/news-events"
                className={({ isActive }) => `text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 ${isActive ? 'border-white bg-white/10' : 'border-transparent hover:border-white'}`}>
                NEWS &amp; EVENTS
              </NavLink>
              <NavLink to="/notices"
                className={({ isActive }) => `text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 ${isActive ? 'border-white bg-white/10' : 'border-transparent hover:border-white'}`}>
                NOTICES
              </NavLink>
              <NavLink to="/gallery"
                className={({ isActive }) => `text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 ${isActive ? 'border-white bg-white/10' : 'border-transparent hover:border-white'}`}>
                GALLERY
              </NavLink>
              <NavLink to="/alumni"
                className={({ isActive }) => `text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 ${isActive ? 'border-white bg-white/10' : 'border-transparent hover:border-white'}`}>
                ALUMNI
              </NavLink>
              <NavLink to="/result"
                className={({ isActive }) => `text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 ${isActive ? 'border-white bg-white/10' : 'border-transparent hover:border-white'}`}>
                RESULT
              </NavLink>
              <NavDropdown label="RESOURCES">
                <NavDropdownItem href="/downloads">All Resources</NavDropdownItem>
                <NavDropdownItem href="/downloads?category=forms">Forms &amp; Downloads</NavDropdownItem>
                <NavDropdownItem href="/downloads?category=syllabus">Syllabus</NavDropdownItem>
                <NavDropdownItem href="/downloads?category=notes">Notes</NavDropdownItem>
                <NavDropdownItem href="/question-bank">Question Bank</NavDropdownItem>
                <NavDropdownItem href="/downloads?category=reports">Reports &amp; Publications</NavDropdownItem>
              </NavDropdown>
            </div>

            {/* Phone numbers right side */}
            <div className="hidden xl:flex flex-col items-end opacity-60 text-right pr-2">
              <div className="text-[11px] font-bold text-white tracking-widest leading-tight cursor-pointer hover:opacity-100 transition-opacity">021-590696</div>
              <div className="text-[11px] font-bold text-white tracking-widest leading-tight cursor-pointer hover:opacity-100 transition-opacity">021-590697</div>
            </div>

            {/* Mobile hamburger in sticky nav */}
            <button onClick={() => setMobileNavOpen(v => !v)}
              className="xl:hidden text-white p-3 hover:bg-white/10 transition-colors h-14 flex items-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <Link to="/login" className="xl:hidden ml-auto inline-flex items-center gap-1.5 rounded-md bg-yellow-500 hover:bg-yellow-400 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-gray-900 shadow-sm transition-colors">
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
              Login
            </Link>
          </div>

          {/* Sticky Nav Mobile Sub-menu (md breakpoint) */}
          {mobileNavOpen && (
            <div className="xl:hidden bg-[#333333] border-t border-white/10 text-white max-h-[80vh] overflow-y-auto">
              <div className="px-0 py-0 divide-y divide-white/10 text-sm font-semibold uppercase tracking-wider">
                <Link to="/" className="block px-5 py-4 hover:bg-white/5 transition-colors">Home</Link>
                <Link to="/news-events" className="block px-5 py-4 hover:bg-white/5 transition-colors">News &amp; Events</Link>
                <Link to="/notices" className="block px-5 py-4 hover:bg-white/5 transition-colors">Notices</Link>
                <Link to="/gallery" className="block px-5 py-4 hover:bg-white/5 transition-colors">Gallery</Link>
                <Link to="/alumni" className="block px-5 py-4 hover:bg-white/5 transition-colors">Alumni</Link>
                <Link to="/result" className="block px-5 py-4 hover:bg-white/5 transition-colors">Result</Link>
                <Link to="/departments" className="block px-5 py-4 hover:bg-white/5 transition-colors">Departments</Link>
                <Link to="/contact" className="block px-5 py-4 hover:bg-white/5 transition-colors">Contact</Link>
              </div>
            </div>
          )}
        </div>
      </nav>

      {/* ── BREADCRUMB (inner pages) ──────────────────────────── */}
      <BreadcrumbBanner title={undefined} />

      {/* ── MAIN CONTENT ─────────────────────────────────────── */}
      <main className="pt-[calc(3.5rem)] lg:pt-0 pb-[calc(5.75rem)] lg:pb-0">
        <Outlet />
      </main>

      {/* ── BOTTOM MOBILE NAV ────────────────────────────────── */}
      <nav className="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/95 shadow-[0_-10px_30px_rgba(15,23,42,0.12)] lg:hidden dark:border-slate-800 dark:bg-slate-950/95">
        <div className="grid grid-cols-5 gap-1 px-2 pb-3 pt-2">
          {mobileNavItems.map(item => (
            <NavLink key={item.to} to={item.to} end={item.to === '/'}
              className={({ isActive }) => `flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2.5 text-center transition ${isActive ? 'bg-[#003D82]/10 text-[#003D82] dark:bg-blue-500/15 dark:text-blue-300' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-900'}`}>
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">{item.icon}</svg>
              <span className="text-[11px] font-semibold">{item.label}</span>
            </NavLink>
          ))}
        </div>
      </nav>

      {/* ── FOOTER ───────────────────────────────────────────── */}
      <footer style={{ backgroundColor: '#003D82' }} className="mt-8 hidden pb-0 pt-12 text-white lg:block">
        <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-10 border-b border-blue-700">
            {/* About */}
            <div>
              <h3 className="font-bold font-serif text-lg mb-4 text-yellow-400">Manmohan Memorial Polytechnic</h3>
              <p className="text-blue-200 text-sm leading-relaxed mb-5">Best Technical College in Koshi Province. CTEVT affiliated constituent college of Manmohan Technical University (MTU).</p>
              <div className="flex gap-3">
                {['f', 't', 'y'].map(s => (
                  <div key={s} className="w-8 h-8 rounded-full bg-blue-700 hover:bg-blue-600 flex items-center justify-center cursor-pointer transition-colors text-sm font-bold">{s}</div>
                ))}
              </div>
            </div>
            {/* Quick Links */}
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
                  { to: '/login', label: '🔐 Student Portal' },
                ].map(l => (
                  <li key={l.to}><Link to={l.to} className="hover:text-white transition-colors flex items-center gap-2"><span className="text-red-500">›</span> {l.label}</Link></li>
                ))}
              </ul>
            </div>
            {/* Departments */}
            <div>
              <h3 className="font-bold font-serif text-lg mb-4 text-yellow-400">Our Departments</h3>
              <ul className="space-y-2 text-sm text-blue-200">
                {departments.length > 0
                  ? departments.map(d => (
                    <li key={d.id}><Link to={`/departments/${d.slug}`} className="hover:text-white transition-colors flex items-center gap-2"><span className="text-blue-500">›</span> {d.name}</Link></li>
                  ))
                  : <li><Link to="/departments" className="hover:text-white transition-colors flex items-center gap-2"><span className="text-red-500">›</span> Departments &amp; Programs</Link></li>
                }
                {departments.length > 0 && <li><Link to="/departments" className="hover:text-white transition-colors flex items-center gap-2"><span className="text-red-500">›</span> View All Departments</Link></li>}
              </ul>
            </div>
            {/* Contact */}
            <div>
              <h3 className="font-bold font-serif text-lg mb-4 text-yellow-400">Contact Us</h3>
              <ul className="space-y-3 text-sm text-blue-200">
                <li className="flex items-start gap-2"><span className="mt-0.5 text-blue-400">📍</span><span>Budhiganga-4, Morang, Koshi Province, Nepal</span></li>
                <li className="flex items-start gap-2"><span className="text-blue-400">📞</span><span>+977 21 590696 / 590697</span></li>
                <li className="flex items-start gap-2"><span className="text-blue-400">✉️</span><span>info@mmp.edu.np</span></li>
                <li className="mt-4">
                  <p className="text-xs text-blue-300 font-semibold uppercase tracking-wider mb-2">Useful Links</p>
                  <div className="space-y-1">
                    {[
                      { href: 'http://ctevt.org.np', label: 'CTEVT' },
                      { href: 'https://mtu.edu.np/', label: 'Manmohan Technical University' },
                      { href: 'http://nstb.org.np', label: 'NSTB' },
                    ].map(l => (
                      <a key={l.href} href={l.href} target="_blank" rel="noreferrer" className="flex items-center gap-2 hover:text-white transition-colors"><span className="text-red-500">›</span> {l.label}</a>
                    ))}
                  </div>
                </li>
              </ul>
            </div>
          </div>
          {/* Copyright */}
          <div className="-mx-4 px-4 py-4 mt-0 text-center text-sm text-blue-300" style={{ backgroundColor: '#001F4D' }}>
            <p>© {new Date().getFullYear()} Manmohan Memorial Polytechnic (www.mmp.edu.np). All Rights Reserved.</p>
            <p className="text-xs mt-1 text-blue-400">Budhiganga-4, Morang, Koshi Province, Nepal | Phone: +977 21 590696 | info@mmp.edu.np</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
