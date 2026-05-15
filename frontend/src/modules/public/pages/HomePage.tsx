import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState, useEffect, useRef } from 'react';
import { getHomepage } from '@shared/services/public.service';

export default function HomePage() {
  const { data, isLoading } = useQuery({ queryKey: ['homepage'], queryFn: getHomepage, staleTime: 5 * 60_000 });
  const hp = data ?? {};

  const banners: any[] = hp.banners ?? [];
  const notices: any[] = hp.notices ?? [];
  const examNotices: any[] = hp.examNotices ?? [];
  const newsEvents: any[] = hp.newsEvents ?? [];
  const departments: any[] = hp.departments ?? [];
  const leadership: any = hp.leadership ?? {};
  const siteSettings: Record<string, string> = hp.site_settings ?? {};

  const currentPresident = leadership?.presidents?.find((e: any) => !e.end_date_bs) ?? null;
  const currentPrincipal = leadership?.principals?.find((e: any) => !e.end_date_bs) ?? null;
  const executives = [currentPresident, currentPrincipal].filter(Boolean);

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-[#f9f9f9] dark:bg-slate-900">
        <div className="w-12 h-12 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div>
      <HeroSlider banners={banners} departments={departments} />
      <NoticeTicker notices={notices} examNotices={examNotices} />
      <MainContent
        notices={notices}
        examNotices={examNotices}
        newsEvents={newsEvents}
        executives={executives}
        siteSettings={siteSettings}
        departments={departments}
      />
      <PrincipalMessage principal={currentPrincipal} siteSettings={siteSettings} />
      <DiplomaPrograms departments={departments} />
    </div>
  );
}

// ─── Hero Slider ──────────────────────────────────────────────────────────────
function HeroSlider({ banners, departments }: { banners: any[]; departments: any[] }) {
  const [current, setCurrent] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);
  const total = Math.max(banners.length, 1);

  const startTimer = () => {
    if (timerRef.current) clearInterval(timerRef.current);
    timerRef.current = setInterval(() => setCurrent(c => (c + 1) % total), 5000);
  };
  useEffect(() => { startTimer(); return () => { if (timerRef.current) clearInterval(timerRef.current); }; }, [total]);

  const next = () => { setCurrent(c => (c + 1) % total); startTimer(); };
  const prev = () => { setCurrent(c => (c - 1 + total) % total); startTimer(); };

  const deptNames = departments.map((d: any) => d.name);

  return (
    <section className="hero-section relative w-full h-[300px] sm:h-[350px] md:h-[420px] overflow-hidden bg-gray-900">
      {banners.length > 0 ? (
        banners.map((banner: any, i: number) => (
          <div key={i} className={`absolute inset-0 transition-opacity duration-700 ${current === i ? 'opacity-100 z-10' : 'opacity-0 z-0'}`}>
            <img src={banner.image_url ?? '/assets/image.png'} alt={banner.title} className="w-full h-full object-cover" />
            <div className="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent" />
            <div className="absolute inset-0 flex flex-col justify-center">
              <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto text-white">
                <div className="max-w-3xl pl-2 sm:pl-4 md:pl-10">
                  <div className="flex flex-wrap items-center gap-1 sm:gap-2 mb-2 sm:mb-4">
                    <span className="text-yellow-400 text-[10px] sm:text-xs font-bold uppercase tracking-wider drop-shadow-lg">Best Technical College in Nepal</span>
                    <span className="text-white/40 text-[10px] sm:text-xs">·</span>
                    <span className="text-white/85 text-[10px] sm:text-xs font-medium">Est. 2054 B.S.</span>
                  </div>
                  {banner.subtitle && (
                    <span className="rounded-none bg-[#e74c3c] text-[9px] sm:text-[10px] font-bold px-2 sm:px-3 py-1 sm:py-1.5 mb-2 sm:mb-4 inline-block uppercase text-white tracking-wider">{banner.subtitle}</span>
                  )}
                  <h2 className="text-xl sm:text-2xl md:text-4xl lg:text-5xl font-bold leading-tight uppercase text-white drop-shadow-2xl mb-2 sm:mb-4">{banner.title}</h2>
                  <div className="text-[11px] sm:text-xs md:text-sm font-semibold text-white drop-shadow-lg flex flex-wrap items-center gap-1 sm:gap-2 mb-3 sm:mb-5">
                    {deptNames.length > 0
                      ? deptNames.map((n: string, idx: number) => (
                        <span key={n}>{n}{idx < deptNames.length - 1 && <span className="text-yellow-400 mx-1">|</span>}</span>
                      ))
                      : <span>Information Technology <span className="text-yellow-400">|</span> Civil <span className="text-yellow-400">|</span> Electrical <span className="text-yellow-400">|</span> Mechanical <span className="text-yellow-400">|</span> Electronics Engineering</span>}
                  </div>
                  <Link to="/pages/what-is-mmp" className="border-2 border-white/65 hover:border-white text-white px-3 sm:px-4 md:px-5 py-1.5 sm:py-2 text-[11px] sm:text-xs font-bold inline-flex items-center gap-1 sm:gap-2 rounded-sm backdrop-blur-sm transition-colors">
                    Learn More <svg className="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        ))
      ) : (
        <div className="absolute inset-0">
          <img src="/assets/image.png" alt="MMP Campus" className="w-full h-full object-cover mix-blend-overlay opacity-90" />
          <div className="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent" />
          <div className="absolute inset-0 flex flex-col justify-center">
            <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto text-white">
              <div className="max-w-3xl pl-4 md:pl-10">
                <span className="bg-[#e74c3c] text-[10px] font-bold px-3 py-1 mb-3 inline-block uppercase text-white shadow-sm tracking-wide">New Admission</span>
                <h1 className="text-3xl md:text-5xl font-semibold leading-[1.15] mb-4 text-white drop-shadow-lg">ADMISSION OPEN FOR DIPLOMA<br />COURSES</h1>
                <div className="text-sm md:text-[15px] mb-8 text-gray-200 drop-shadow flex flex-wrap items-center gap-1 md:gap-2 tracking-wide font-light">
                  <span>Information Technology</span> <span className="text-blue-400">|</span>
                  <span>Civil</span> <span className="text-blue-400">|</span>
                  <span>Electrical</span> <span className="text-blue-400">|</span>
                  <span>Mechanical</span> <span className="text-blue-400">|</span>
                  <span>Electronics Engineering</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {banners.length > 1 && (
        <>
          <button onClick={prev} className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/60 text-white rounded-full flex items-center justify-center z-20 transition-colors backdrop-blur-sm">
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" /></svg>
          </button>
          <button onClick={next} className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/60 text-white rounded-full flex items-center justify-center z-20 transition-colors backdrop-blur-sm">
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
          </button>
          <div className="absolute bottom-6 left-0 w-full flex justify-center gap-2 z-20">
            {banners.map((_: any, i: number) => (
              <button key={i} onClick={() => { setCurrent(i); startTimer(); }}
                className={`h-2.5 rounded-full transition-all duration-300 ${current === i ? 'bg-white w-6' : 'bg-white/40 hover:bg-white/70 w-2.5'}`} />
            ))}
          </div>
        </>
      )}
    </section>
  );
}

// ─── Notice Ticker ────────────────────────────────────────────────────────────
function NoticeTicker({ notices, examNotices }: { notices: any[]; examNotices: any[] }) {
  const items = [...notices.slice(0, 6), ...examNotices.slice(0, 6)]
    .sort((a, b) => new Date(b.published_at ?? b.created_at).getTime() - new Date(a.published_at ?? a.created_at).getTime())
    .slice(0, 8);
  if (!items.length) return null;
  return (
    <div className="bg-[#2c2c2c] text-white overflow-hidden border-b-2 border-yellow-500">
      <div className="w-full flex items-center">
        <div className="bg-[#003D82] flex-shrink-0 flex items-center gap-2 px-4 py-2.5 font-bold text-sm z-10 shadow-md relative">
          <svg className="w-4 h-4 text-yellow-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" /></svg>
          Unread Notices
        </div>
        <div className="flex-1 overflow-hidden">
          <div className="flex animate-[ticker_30s_linear_infinite] whitespace-nowrap py-2.5">
            {[...items, ...items].map((item: any, i: number) => {
              const type = item.type ?? 'general';
              const badgeClass = type === 'exam' ? 'bg-blue-400/15 text-blue-300 border border-blue-300/20'
                : type === 'news' ? 'bg-violet-400/15 text-violet-300 border border-violet-300/20'
                  : type === 'event' ? 'bg-cyan-400/15 text-cyan-300 border border-cyan-300/20'
                    : 'bg-white/10 text-gray-200';
              return (
                <Link key={i} to={`/notices/${item.slug}`}
                  className="flex items-center gap-2 text-sm text-gray-200 hover:text-yellow-400 transition-colors flex-shrink-0 mx-8">
                  <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${badgeClass}`}>{type}</span>
                  <span className="font-medium">{item.title}</span>
                </Link>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Notice Item ──────────────────────────────────────────────────────────────
function NoticeItem({ notice }: { notice: any }) {
  const d = new Date(notice.published_at ?? notice.created_at);
  const day = d.getDate().toString().padStart(2, '0');
  const month = d.toLocaleString('en', { month: 'short' }).toUpperCase();
  const year = d.getFullYear();
  return (
    <li>
      <Link to={`/notices/${notice.slug}`}
        className="flex items-start gap-4 px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 group transition-colors">
        <div className="flex-shrink-0 w-11 h-14 text-white flex flex-col items-center justify-center rounded text-center" style={{ backgroundColor: '#003D82' }}>
          <span className="text-[8px] font-bold leading-none">{year}</span>
          <span className="text-sm font-bold leading-tight">{day}</span>
          <span className="text-[7px] font-bold uppercase leading-none">{month}</span>
        </div>
        <div className="flex-1 min-w-0">
          <div className="text-[13px] text-gray-700 dark:text-slate-300 group-hover:text-[#003D82] dark:group-hover:text-blue-400 font-medium leading-snug pt-0.5">{notice.title}</div>
          <div className="flex items-center gap-1.5 mt-2 flex-wrap">
            <span className="text-[9px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800 uppercase">{notice.type}</span>
            {notice.department && <span className="text-[9px] font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">{notice.department.name}</span>}
            <span className="text-[10px] text-gray-400">{d.toLocaleDateString('en', { year: 'numeric', month: 'short', day: 'numeric' })}</span>
            {notice.attachment && (
              <span className="text-[10px] text-blue-700 flex items-center gap-1 font-semibold">
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Attachment
              </span>
            )}
          </div>
        </div>
        <svg className="w-4 h-4 mt-1 text-gray-300 dark:text-slate-600 group-hover:text-[#003D82] dark:group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
      </Link>
    </li>
  );
}

// ─── Main 3-column Content ────────────────────────────────────────────────────
function MainContent({ notices, examNotices, newsEvents, executives, siteSettings }: any) {
  const [activeTab, setActiveTab] = useState<'general' | 'exam'>('general');
  const welcomeText = siteSettings?.what_is_mmp ?? 'Manmohan Memorial Polytechnic (MMP) is a constituent college of Manmohan Technical University — the first technical university in Nepal.';

  const quickLinks = [
    { label: 'MMP At A Glance', to: '/pages/what-is-mmp' },
    { label: 'Courses & Programs', to: '/departments' },
    { label: 'Notice Board', to: '/notices' },
    { label: 'Downloads & Forms', to: '/downloads' },
    { label: 'Question Bank', to: '/question-bank' },
    { label: 'Campus Facilities', to: '/facilities' },
    { label: 'Scholarship Schemes', to: '/pages/scholarship-schemes' },
    { label: 'Internships & Placements', to: '/pages/internships' },
  ];

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-[#f9f9f9] dark:bg-slate-900">
      <div className="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-12">

        {/* LEFT: Quick Links + Officials */}
        <div className="order-2 flex flex-col gap-6 lg:order-none lg:col-span-3 lg:h-full">
          {/* Quick Links */}
          <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-shadow flex-1">
            <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
              Quick Links
            </div>
            <ul className="divide-y divide-gray-100 dark:divide-slate-700 p-1">
              {quickLinks.map(l => (
                <li key={l.to}>
                  <Link to={l.to} className="block px-4 py-2.5 text-gray-700 dark:text-slate-300 hover:text-[#003D82] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-700 text-[13px] transition-all duration-200 hover:translate-x-1">
                    <span className="text-blue-500 font-bold mr-2">›</span>{l.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Managements */}
          <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-shadow flex-1 flex flex-col">
            <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 flex-shrink-0">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              Managements
            </div>
            <div className="flex-1 overflow-y-auto py-6 px-4">
              <div className="flex flex-col gap-5">
                {executives.length > 0 ? executives.map((exec: any, i: number) => (
                  <div key={i} className="flex min-h-[6.25rem] gap-4 items-center">
                    <div className="h-24 w-20 bg-gray-200 dark:bg-slate-700 border shadow-sm flex-shrink-0 overflow-hidden -ml-1">
                      {exec.avatar
                        ? <img src={`/storage/${exec.avatar}`} className="w-full h-full object-cover" alt={exec.name} />
                        : <div className="w-full h-full flex items-center justify-center text-2xl font-bold bg-gray-100 text-[#003D82]">{(exec.name ?? 'N')[0].toUpperCase()}</div>
                      }
                    </div>
                    <div className="flex flex-col min-h-[6rem] flex-1 justify-center pr-1">
                      <div className="font-bold text-[#003D82] dark:text-blue-400 text-[15px] leading-tight">{exec.name ?? 'N/A'}</div>
                      <div className="mt-1 text-[13px] text-gray-500 dark:text-slate-400">{exec.designation ?? exec.type ?? 'N/A'}</div>
                    </div>
                  </div>
                )) : <p className="text-xs text-gray-400 text-center py-2">Management details coming soon.</p>}
              </div>
            </div>
            <Link to="/leadership" className="block p-2.5 bg-[#003D82] dark:bg-blue-600 text-white text-xs font-bold text-left hover:bg-blue-900 transition-colors px-4 flex-shrink-0">
              View All Presidents &amp; Principals »
            </Link>
          </div>
        </div>

        {/* CENTER: Welcome + Notice tabs */}
        <div className="order-1 lg:order-none lg:col-span-6 space-y-6">
          {/* Welcome Box */}
          <div className="bg-[#003D82] dark:bg-slate-800 text-white p-8 text-center relative overflow-hidden shadow-sm border border-gray-200 dark:border-slate-700">
            <div className="absolute inset-0 opacity-10 bg-gradient-to-tr from-black to-transparent" />
            <div className="relative z-10">
              <h2 className="text-2xl font-semibold mb-3">Welcome to MMP</h2>
              <div className="mx-auto max-w-3xl px-4">
                <div className="max-h-[180px] md:max-h-[220px] overflow-y-auto pr-2 text-left">
                  <div className="space-y-3 text-[13px] leading-relaxed text-gray-100">
                    {welcomeText.split(/\n\s*\n/).filter(Boolean).map((p: string, i: number) => (
                      <p key={i}>{p.trim()}</p>
                    ))}
                  </div>
                </div>
              </div>
              <Link to="/pages/what-is-mmp" className="inline-block border border-white text-white px-6 py-2 text-xs font-bold hover:bg-white hover:text-[#003D82] transition-colors uppercase tracking-wide mt-4">
                About MMP
              </Link>
            </div>
          </div>

          {/* Notice Board Tabs */}
          <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden h-[520px] flex flex-col hover:shadow-xl transition-shadow">
            <div className="grid grid-cols-2 divide-x divide-white/10 bg-[#003D82]">
              {(['general', 'exam'] as const).map(tab => (
                <button key={tab} onClick={() => setActiveTab(tab)}
                  className={`flex-1 border-x-0 border-b border-b-black/5 py-3.5 font-semibold text-sm flex items-center justify-center gap-2 transition-colors border-t-[3px] ${activeTab === tab ? 'border-yellow-400 bg-[#0b4a92] text-white' : 'border-transparent bg-[#0f4d98] text-white/95 hover:text-white'}`}>
                  {tab === 'general'
                    ? <><svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>Notice Board</>
                    : <><svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>Exam Results</>
                  }
                </button>
              ))}
            </div>
            <div className="flex-1 overflow-y-auto bg-white dark:bg-slate-800">
              <ul className="divide-y divide-gray-100 dark:divide-slate-700">
                {(activeTab === 'general' ? notices : examNotices).slice(0, 6).map((n: any, i: number) => (
                  <NoticeItem key={i} notice={n} />
                ))}
                {(activeTab === 'general' ? notices : examNotices).length === 0 && (
                  <li className="px-4 py-8 text-center text-gray-500 text-sm">
                    {activeTab === 'general' ? 'No recent notices found.' : 'No exam schedules or result notices found.'}
                  </li>
                )}
              </ul>
            </div>
            <Link to={activeTab === 'exam' ? '/notices?type=exam' : '/notices'}
              className="block p-2.5 bg-[#003D82] text-white text-xs font-bold text-left hover:bg-blue-900 transition-colors px-4 flex items-center justify-between flex-shrink-0">
              <span>{activeTab === 'exam' ? 'View Exam Results' : 'View All Notices'}</span>
              <svg className="ml-3 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
            </Link>
          </div>
        </div>

        {/* RIGHT: News & Events */}
        <div className="order-3 flex flex-col lg:order-none lg:col-span-3 lg:h-full">
          <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden flex flex-col hover:shadow-xl transition-shadow flex-1">
            <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 flex-shrink-0">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              News &amp; Events
            </div>
            <div className="flex-1 overflow-y-auto py-6">
              {newsEvents.length > 0 ? newsEvents.slice(0, 5).map((event: any, i: number) => {
                const d = new Date(event.published_at ?? event.created_at);
                const isEvent = event.type === 'event';
                const firstImage = event.attachments?.find((a: any) => a.is_image);
                return (
                  <Link key={i} to={`/news-events/${event.slug}`}
                    className="flex gap-3 px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 group transition-colors border-b border-gray-100 dark:border-slate-700 last:border-b-0">
                    {firstImage
                      ? <div className="w-20 h-20 flex-shrink-0 rounded overflow-hidden bg-gray-100"><img src={firstImage.url} alt={event.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" /></div>
                      : <div className="w-12 h-12 flex-shrink-0 text-white flex flex-col items-center justify-center rounded text-center shadow-sm" style={{ backgroundColor: '#003D82' }}>
                        <span className="text-[8px] font-bold leading-none">{d.getFullYear()}</span>
                        <span className="text-sm font-bold leading-tight">{d.getDate().toString().padStart(2, '0')}</span>
                        <span className="text-[7px] font-bold uppercase leading-none">{d.toLocaleString('en', { month: 'short' })}</span>
                      </div>
                    }
                    <div className="flex-1 w-full overflow-hidden">
                      <div className="flex items-center gap-2 mb-1 flex-wrap">
                        <div className="text-[10px] font-bold text-gray-400">{d.toLocaleDateString('en', { year: 'numeric', month: 'short', day: 'numeric' })}</div>
                        <span className={`text-[9px] font-bold px-1.5 py-0.5 rounded-full border ${isEvent ? 'bg-teal-50 text-teal-700 border-teal-100' : 'bg-purple-50 text-purple-700 border-purple-100'}`}>
                          {isEvent ? 'Event' : 'News'}
                        </span>
                      </div>
                      <h4 className="font-medium text-gray-800 dark:text-slate-200 text-[13px] leading-tight group-hover:text-[#003D82] dark:group-hover:text-blue-400 transition-colors line-clamp-2">{event.title}</h4>
                    </div>
                  </Link>
                );
              }) : (
                <div className="text-center py-6 text-gray-400">
                  <svg className="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  <p className="text-xs">No news or events yet.</p>
                </div>
              )}
            </div>
            <Link to="/news-events" className="block p-2.5 bg-[#003D82] dark:bg-blue-600 text-white text-xs font-bold hover:bg-blue-900 transition-colors px-4 flex-shrink-0">
              View All News &amp; Events »
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Principal's Message ──────────────────────────────────────────────────────
function PrincipalMessage({ principal, siteSettings }: any) {
  const message = principal?.message ?? "It is with immense pleasure that I welcome you to Manmohan Memorial Polytechnic. Here at MMP, we are confident that you will experience an enriching academic journey coupled with robust technical skill enhancement.\n\nWe provide a vibrant learning environment that ensures our students gain hands-on practical knowledge that satisfies the needs of modern industries, preparing them for national and international career opportunities.";
  const paragraphs = message.split(/\n\n+/).filter(Boolean);
  const media = siteSettings?.principal_message_media ?? null;

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-gray-50 dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 relative overflow-hidden">
      <div className="flex justify-between items-center mb-8 pb-3 border-b border-gray-200 dark:border-slate-700">
        <h2 className="text-2xl font-bold text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">Principal's Message</h2>
      </div>
      <div className="flex flex-col lg:flex-row gap-10 items-start">
        <div className="w-full lg:w-64 flex-shrink-0 flex flex-col items-center">
          <div className="w-52 h-[260px] overflow-hidden bg-gray-200 border-4 border-white rounded-sm" style={{ boxShadow: '0 8px 32px rgba(139,0,0,0.18)' }}>
            {principal?.avatar
              ? <img src={`/storage/${principal.avatar}`} alt="Principal" className="w-full h-full object-cover object-top" />
              : <div className="w-full h-full flex items-center justify-center text-7xl font-bold bg-gray-100 text-[#003D82]">{(principal?.name ?? 'P')[0].toUpperCase()}</div>
            }
          </div>
          <div className="mt-4 text-center">
            <div className="font-bold text-[#003D82] text-base">{principal?.name ?? 'Principal'}</div>
            <div className="text-xs text-gray-500 font-medium mt-0.5">{principal?.designation ?? 'Principal, MMP'}</div>
          </div>
        </div>
        <div className="flex-1 min-w-0">
          {media && (
            <div className="mb-6 rounded-lg overflow-hidden border border-gray-200 shadow-sm">
              <img src={`/storage/${media}`} alt="Principal's message" className="w-full max-h-72 object-contain bg-gray-50" />
            </div>
          )}
          <div className="text-gray-700 dark:text-slate-300 text-[15px] leading-[2] text-justify space-y-4">
            {paragraphs.map((p: string, i: number) => <p key={i}>{p.trim()}</p>)}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Diploma Programs ─────────────────────────────────────────────────────────
const PROG_ICONS: Record<string, string> = {
  'Information Technology': 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
  'Civil Engineering': 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  'Electrical Engineering': 'M13 10V3L4 14h7v7l9-11h-7z',
  'Mechanical Engineering': 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
  'Electronics Engineering': 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
};
const DEFAULT_ICON = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';

function DiplomaPrograms({ departments }: { departments: any[] }) {
  const progs = departments.slice(0, 6);
  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-white dark:bg-slate-950 border-t border-[#f9f9f9] dark:border-slate-800">
      <div className="flex justify-between items-center mb-8 pb-3 border-b border-gray-100 dark:border-slate-800">
        <h2 className="text-2xl font-bold text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">Our Diploma Programs</h2>
        <Link to="/departments" className="text-xs font-bold text-gray-500 hover:text-[#003D82] flex items-center gap-1 border border-gray-200 px-3 py-1.5 rounded-sm hover:border-[#003D82] transition-colors">
          <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
          VIEW ALL
        </Link>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5">
        {(progs.length > 0 ? progs : Object.keys(PROG_ICONS).map(name => ({ name, slug: name.toLowerCase().replace(/\s+/g, '-') }))).map((prog: any) => (
          <Link key={prog.slug} to={`/departments/${prog.slug}`}
            className="group rounded-2xl shadow-md p-6 text-center flex flex-col items-center transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] bg-white dark:bg-slate-800 dark:text-slate-200 hover:bg-[#003D82]">
            <div className="w-14 h-14 bg-blue-50 dark:bg-slate-700 border border-blue-100 rounded-full shadow-sm flex items-center justify-center text-[#003D82] dark:text-blue-400 group-hover:bg-white mb-4 transition-colors">
              <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d={PROG_ICONS[prog.name] ?? DEFAULT_ICON} />
              </svg>
            </div>
            <h3 className="font-semibold text-[13px] leading-snug mb-1.5 text-gray-900 dark:text-slate-200 group-hover:text-white transition-colors">
              Diploma in<br />{prog.name.replace('Diploma in ', '')}
            </h3>
            <p className="text-[11px] text-gray-400 dark:text-slate-500 font-normal group-hover:text-blue-200 mb-3">3 Years / 6 Semesters</p>
          </Link>
        ))}
      </div>
      <div className="text-center mt-10">
        <Link to="/departments" className="bg-[#003D82] dark:bg-blue-600 text-white px-6 py-2.5 rounded-sm font-bold shadow hover:bg-blue-900 transition-colors inline-flex items-center gap-2 text-sm">
          View All Programs
        </Link>
      </div>
    </div>
  );
}
