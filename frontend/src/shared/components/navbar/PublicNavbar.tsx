import { useState } from 'react';
import { NavLink, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { getDepartments } from '@shared/services/public.service';

const dropdownMenus: Record<string, { label: string; to?: string }[]> = {
  'ABOUT US': [
    { label: 'What is MMP', to: '/pages/what-is-mmp' },
    { label: 'Objectives', to: '/pages/objectives' },
    { label: 'Presidents & Principals', to: '/leadership' },
    { label: 'Contact Us', to: '/contact' },
  ],
  FEATURES: [
    { label: 'Campus Facilities & Resources', to: '/facilities' },
    { label: 'Scholarship Schemes', to: '/pages/scholarship-schemes' },
    { label: 'Internships & Placements', to: '/pages/internships' },
  ],
  RESOURCES: [
    { label: 'All Resources', to: '/downloads' },
    { label: 'Forms & Downloads', to: '/downloads?category=forms' },
    { label: 'Syllabus', to: '/downloads?category=syllabus' },
    { label: 'Notes', to: '/downloads?category=notes' },
    { label: 'Question Bank', to: '/question-bank' },
    { label: 'Reports & Publications', to: '/downloads?category=reports' },
  ],
  PEOPLE: [
    { label: 'Administrative Staff', to: '/staff' },
    { label: 'Presidents & Principals', to: '/leadership' },
    { label: 'All People', to: '/people' },
  ],
};

const menuOrder: ({ type: 'link'; label: string; to: string } | { type: 'dropdown'; label: string })[] = [
  { type: 'link',     label: 'HOME',           to: '/' },
  { type: 'dropdown', label: 'ABOUT US' },
  { type: 'dropdown', label: 'DEPARTMENTS' },
  { type: 'dropdown', label: 'FEATURES' },
  { type: 'dropdown', label: 'PEOPLE' },
  { type: 'link',     label: 'NEWS & EVENTS',   to: '/news-events' },
  { type: 'link',     label: 'NOTICES',         to: '/notices' },
  { type: 'link',     label: 'GALLERY',         to: '/gallery' },
  { type: 'link',     label: 'ALUMNI',          to: '/alumni' },
  { type: 'link',     label: 'RESULT',          to: '/result' },
  { type: 'dropdown', label: 'RESOURCES' },
];

export default function PublicNavbar() {
  const [mobileOpen, setMobileOpen] = useState(false);
  const [openDd, setOpenDd] = useState<Record<string, boolean>>({});
  const [hoverDd, setHoverDd] = useState<string | null>(null);

  const { data: deptData } = useQuery({
    queryKey: ['public-departments'],
    queryFn: getDepartments,
    staleTime: 10 * 60_000,
  });

  const departments: { label: string; to: string }[] = [];
  if (Array.isArray(deptData)) {
    for (const d of deptData) {
      departments.push({ label: d.name, to: `/departments/${d.slug}` });
    }
  }
  departments.push({ label: 'All Departments →', to: '/departments' });

  const peopleItems = [
    ...departments.filter((d) => d.to !== '/departments').map((d) => ({ label: d.label, to: `/people?department=${d.label}` })),
    ...dropdownMenus['PEOPLE'],
  ];

  function toggleDd(name: string) {
    setOpenDd((p) => ({ ...p, [name]: !p[name] }));
  }

  function getDdItems(label: string) {
    if (label === 'DEPARTMENTS') return departments;
    if (label === 'PEOPLE') return peopleItems;
    return dropdownMenus[label] ?? [];
  }

  return (
    <>
      {/* ── DESKTOP NAV ──────────────────────────────────────────── */}
      <nav style={{ backgroundColor: '#003D82' }} className="hidden shadow-md lg:block">
        <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
          <div className="flex items-center justify-between">
            <div className="hidden xl:flex items-center flex-1">
              {menuOrder.map((item) => {
                if (item.type === 'link') {
                  return (
                    <NavLink
                      key={item.label}
                      to={item.to}
                      end={item.to === '/'}
                      className={({ isActive }) =>
                        `text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 ${
                          isActive ? 'border-white bg-white/10' : 'border-transparent hover:border-white'
                        }`
                      }
                    >
                      {item.label}
                    </NavLink>
                  );
                }
                const ddItems = getDdItems(item.label);
                if (!ddItems.length) return null;
                const isOpen = hoverDd === item.label;
                return (
                  <div key={item.label} className="relative" onMouseEnter={() => setHoverDd(item.label)} onMouseLeave={() => setHoverDd(null)}>
                    <button className="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 border-transparent hover:border-white flex items-center gap-1">
                      {item.label}
                      <svg className="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    {isOpen && (
                      <div className="absolute top-full left-0 mt-0 w-64 bg-[#404040] py-2 z-50 shadow-xl border-t-2 border-white">
                        {ddItems.map((si) => (
                          <Link
                            key={si.to ?? si.label}
                            to={si.to!}
                            className="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5 last:border-0"
                          >
                            {si.label}
                          </Link>
                        ))}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>

            <div className="hidden xl:flex flex-col items-end opacity-60 text-right pr-2">
              <div className="text-[11px] font-bold text-white tracking-widest leading-tight">021-590696</div>
              <div className="text-[11px] font-bold text-white tracking-widest leading-tight">021-590697</div>
            </div>

            <button onClick={() => setMobileOpen(true)} className="xl:hidden text-white p-3 hover:bg-white/10 transition-colors h-14 flex items-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <Link to="/login" className="xl:hidden ml-auto inline-flex items-center gap-1.5 rounded-md bg-yellow-500 hover:bg-yellow-400 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-gray-900 shadow-sm transition-colors">
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
              Login
            </Link>
          </div>
        </div>
      </nav>

      {/* ── MOBILE HEADER ─────────────────────────────────────────── */}
      <header className="fixed inset-x-0 top-0 z-50 border-b-2 border-slate-200 bg-white/95 shadow-md backdrop-blur lg:hidden dark:border-slate-700 dark:bg-slate-900/95">
        <div className="flex items-center justify-between gap-2 px-3 sm:px-4 pb-2 sm:pb-3 pt-[max(0.5rem,env(safe-area-inset-top))] sm:pt-[max(0.75rem,env(safe-area-inset-top))]">
          <div className="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
            <button onClick={() => setMobileOpen(true)} className="inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-xl sm:rounded-2xl border-2 border-slate-300 bg-white text-slate-600 shadow-md transition-all duration-200 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
              <svg className="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <Link to="/" className="flex min-w-0 items-center gap-2 sm:gap-3 flex-1">
              <div className="flex h-9 w-9 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-xl sm:rounded-2xl" style={{ backgroundColor: '#003D82', border: '2px solid #DAA520' }}>
                <span className="text-white font-bold text-xs sm:text-sm">MMP</span>
              </div>
              <div className="min-w-0 flex-1">
                <p className="truncate text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-[#003D82] dark:text-blue-300 leading-tight">MMP</p>
                <p className="truncate text-[11px] sm:text-xs font-bold text-slate-900 dark:text-slate-50 leading-tight">Manmohan Memorial Polytechnic</p>
              </div>
            </Link>
          </div>
          <div className="flex items-center gap-1.5 sm:gap-2">
            <Link to="/login" className="inline-flex items-center gap-1 rounded-md bg-yellow-500 hover:bg-yellow-400 px-2.5 py-1.5 sm:px-3 sm:py-2 text-[10px] sm:text-xs font-bold text-gray-900 shadow-sm transition-colors">Login</Link>
          </div>
        </div>
      </header>

      {/* ── MOBILE OVERLAY ────────────────────────────────────────── */}
      {mobileOpen && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden" onClick={() => setMobileOpen(false)} />
      )}

      {/* ── MOBILE DRAWER ─────────────────────────────────────────── */}
      <div className={`fixed left-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white dark:bg-slate-900 shadow-2xl overflow-hidden z-50 lg:hidden flex flex-col transition-transform duration-300 ${mobileOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div style={{ backgroundColor: '#003D82' }} className="px-4 py-4 flex items-center justify-between pt-[max(1rem,env(safe-area-inset-top))]">
          <div className="flex items-center gap-3">
            <div className="h-10 w-10 rounded-lg flex items-center justify-center" style={{ background: 'radial-gradient(circle, #003D82, #001F4D)', border: '2px solid #DAA520' }}>
              <span className="text-white font-bold text-sm">MMP</span>
            </div>
            <div><p className="text-white font-bold text-sm">MMP</p><p className="text-blue-200 text-xs">Navigation Menu</p></div>
          </div>
          <button onClick={() => setMobileOpen(false)} className="text-white hover:bg-white/10 rounded-lg p-2 transition-colors">
            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div className="flex-1 overflow-y-auto px-3 sm:px-4 py-3">
          <nav className="space-y-1">
            {menuOrder.map((item) => {
              if (item.type === 'link') {
                return (
                  <NavLink key={item.label} to={item.to} end={item.to === '/'} onClick={() => setMobileOpen(false)}
                    className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 text-sm font-semibold transition-all duration-200 ${isActive ? 'bg-[#003D82] text-white shadow-md' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800'}`}>
                    {item.label}
                  </NavLink>
                );
              }
              const ddItems = getDdItems(item.label);
              if (!ddItems.length) return null;
              const isOpen = openDd[item.label];
              return (
                <div key={item.label}>
                  <button onClick={() => toggleDd(item.label)}
                    className="flex w-full items-center justify-between rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                    <span>{item.label}</span>
                    <svg className={`h-4 w-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"/></svg>
                  </button>
                  {isOpen && (
                    <div className="ml-6 sm:ml-8 mt-1 space-y-1 border-l-2 border-slate-300 dark:border-slate-600 pl-3">
                      {ddItems.map((si) => (
                        <Link key={si.to ?? si.label} to={si.to!} onClick={() => setMobileOpen(false)}
                          className="block rounded-lg px-3 sm:px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-all duration-200 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                          {si.label}
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              );
            })}
            <Link to="/login" onClick={() => setMobileOpen(false)}
              className="flex items-center gap-3 rounded-xl border-2 border-[#003D82] px-3 sm:px-4 py-2.5 sm:py-3 text-sm font-semibold text-[#003D82] transition hover:bg-[#003D82] hover:text-white dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-400 dark:hover:text-white">
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
              Login Portal
            </Link>
          </nav>
        </div>
      </div>

      {/* ── MOBILE OFFSET ─────────────────────────────────────────── */}
      <div className="lg:hidden h-[calc(env(safe-area-inset-top)+3.5rem)]" />
    </>
  );
}
