import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { useState, useEffect, useRef } from 'react';
import { getHomepage } from '@shared/services/public.service';

// ─── Types ────────────────────────────────────────────────────────────────────
interface Banner { image_url?: string; title: string; subtitle?: string; }
interface Notice { slug: string; title: string; type: string; published_at?: string; created_at: string; department?: { name: string }; program?: { name: string }; semester?: string; attachment?: string; }
interface NewsEvent { slug: string; title: string; type: string; published_at?: string; created_at: string; attachments?: { is_image: boolean; url: string }[]; }
interface Executive { name?: string; designation?: string; type?: string; avatar?: string; end_date_bs?: string; }
interface Download { title: string; category?: string; created_at: string; }
interface Department { name: string; slug: string; }

// ─── Main Page ────────────────────────────────────────────────────────────────
export default function HomePage() {
  const { data, isLoading } = useQuery({ queryKey: ['homepage'], queryFn: getHomepage, staleTime: 5 * 60_000 });
  const hp = data ?? {};

  const banners: Banner[] = hp.banners ?? [];
  const notices: Notice[] = hp.notices ?? [];
  const examNotices: Notice[] = hp.examNotices ?? [];
  const newsEvents: NewsEvent[] = hp.newsEvents ?? [];
  const departments: Department[] = hp.departments ?? [];
  const leadership: any = hp.leadership ?? {};
  const siteSettings: Record<string, string> = hp.site_settings ?? {};
  const stats: Record<string, any> = hp.stats ?? {};
  const recentDownloads: Download[] = hp.recentDownloads ?? [];

  const currentPresident: Executive | null = leadership?.presidents?.find((e: Executive) => !e.end_date_bs) ?? null;
  const currentPrincipal: Executive | null = leadership?.principals?.find((e: Executive) => !e.end_date_bs) ?? null;
  const executives = [currentPresident, currentPrincipal].filter(Boolean) as Executive[];

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

      {/* ── MAIN 3-COLUMN CONTENT ── */}
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-[#f9f9f9] dark:bg-slate-900">
        <div className="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-12">

          {/* LEFT: Quick Links + Managements */}
          <div className="order-2 flex flex-col gap-6 lg:order-none lg:col-span-3 lg:h-full">
            <QuickLinks />
            <Executives executives={executives} />
          </div>

          {/* CENTER: Welcome + Notice Tabs */}
          <div className="order-1 lg:order-none lg:col-span-6 space-y-6">
            <WelcomeBox siteSettings={siteSettings} />
            <NoticeTabs notices={notices} examNotices={examNotices} />
          </div>

          {/* RIGHT: News & Events */}
          <div className="order-3 flex flex-col lg:order-none lg:col-span-3 lg:h-full">
            <NewsEventsCard newsEvents={newsEvents} />
          </div>

        </div>
      </div>

      <PrincipalMessage leadership={leadership} siteSettings={siteSettings} />
      <DiplomaPrograms departments={departments} />
      <StatsSection stats={stats} />

      {/* ── BOTTOM 3-COLUMN GRID ── */}
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-[#f9f9f9] dark:bg-slate-900">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <DownloadsPublications downloads={recentDownloads} />
          <ImportantLinks />
          <WhyChooseMMP />
        </div>
      </div>

      <FindUsSection siteSettings={siteSettings} />
    </div>
  );
}

// ─── Hero Slider ──────────────────────────────────────────────────────────────
function HeroSlider({ banners, departments }: { banners: Banner[]; departments: Department[] }) {
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
  const deptNames = departments.map(d => d.name);

  return (
    <section className="relative w-full h-[300px] sm:h-[350px] md:h-[420px] overflow-hidden bg-gray-900">
      {banners.length > 0 ? banners.map((banner, i) => (
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
                {banner.subtitle && <span className="bg-[#e74c3c] text-[9px] sm:text-[10px] font-bold px-2 sm:px-3 py-1 mb-2 sm:mb-4 inline-block uppercase text-white tracking-wider">{banner.subtitle}</span>}
                <h2 className="text-xl sm:text-2xl md:text-4xl lg:text-5xl font-bold leading-tight uppercase text-white drop-shadow-2xl mb-2 sm:mb-4">{banner.title}</h2>
                <div className="text-[11px] sm:text-xs md:text-sm font-semibold text-white drop-shadow-lg flex flex-wrap items-center gap-1 sm:gap-2 mb-3 sm:mb-5">
                  {deptNames.length > 0
                    ? deptNames.map((n, idx) => <span key={n}>{n}{idx < deptNames.length - 1 && <span className="text-yellow-400 mx-1">|</span>}</span>)
                    : <span>Information Technology <span className="text-yellow-400">|</span> Civil <span className="text-yellow-400">|</span> Electrical <span className="text-yellow-400">|</span> Mechanical <span className="text-yellow-400">|</span> Electronics Engineering</span>}
                </div>
                <Link to="/pages/what-is-mmp" className="border-2 border-white/65 hover:border-white text-white px-3 sm:px-4 md:px-5 py-1.5 sm:py-2 text-[11px] sm:text-xs font-bold inline-flex items-center gap-1 sm:gap-2 rounded-sm backdrop-blur-sm transition-colors">
                  Learn More <svg className="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </Link>
              </div>
            </div>
          </div>
        </div>
      )) : (
        <div className="absolute inset-0">
          <img src="/assets/image.png" alt="MMP Campus" className="w-full h-full object-cover mix-blend-overlay opacity-90" />
          <div className="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent" />
          <div className="absolute inset-0 flex flex-col justify-center">
            <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto text-white">
              <div className="max-w-3xl pl-4 md:pl-10">
                <span className="bg-[#e74c3c] text-[10px] font-bold px-3 py-1 mb-3 inline-block uppercase text-white tracking-wide">New Admission</span>
                <h1 className="text-3xl md:text-5xl font-semibold leading-[1.15] mb-4 text-white drop-shadow-lg">ADMISSION OPEN FOR DIPLOMA<br />COURSES</h1>
                <div className="text-sm md:text-[15px] mb-8 text-gray-200 flex flex-wrap items-center gap-1 md:gap-2 font-light">
                  <span>Information Technology</span> <span className="text-blue-400">|</span> <span>Civil</span> <span className="text-blue-400">|</span> <span>Electrical</span> <span className="text-blue-400">|</span> <span>Mechanical</span> <span className="text-blue-400">|</span> <span>Electronics Engineering</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
      {banners.length > 1 && (
        <>
          <button onClick={prev} className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/60 text-white rounded-full flex items-center justify-center z-20 backdrop-blur-sm transition-colors">
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" /></svg>
          </button>
          <button onClick={next} className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/60 text-white rounded-full flex items-center justify-center z-20 backdrop-blur-sm transition-colors">
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
          </button>
          <div className="absolute bottom-6 left-0 w-full flex justify-center gap-2 z-20">
            {banners.map((_, i) => (
              <button key={i} onClick={() => { setCurrent(i); startTimer(); }} className={`h-2.5 rounded-full transition-all duration-300 ${current === i ? 'bg-white w-6' : 'bg-white/40 hover:bg-white/70 w-2.5'}`} />
            ))}
          </div>
        </>
      )}
    </section>
  );
}

// ─── Notice Ticker ────────────────────────────────────────────────────────────
function NoticeTicker({ notices, examNotices }: { notices: Notice[]; examNotices: Notice[] }) {
  const items = [...notices.slice(0, 6), ...examNotices.slice(0, 6)]
    .sort((a, b) => new Date(b.published_at ?? b.created_at).getTime() - new Date(a.published_at ?? a.created_at).getTime())
    .slice(0, 8);
  if (!items.length) return null;
  const doubled = [...items, ...items];
  return (
    <div className="bg-[#2c2c2c] text-white overflow-hidden border-b-2 border-yellow-500">
      <div className="w-full flex items-center">
        <div className="bg-[#003D82] flex-shrink-0 flex items-center gap-2 px-4 py-2.5 font-bold text-sm z-10 shadow-md relative">
          <svg className="w-4 h-4 text-yellow-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" /></svg>
          Unread Notices
          <div className="absolute right-0 top-0 h-full w-4 bg-gradient-to-r from-[#003D82] to-transparent translate-x-full" />
        </div>
        <div className="flex-1 overflow-hidden">
          <div className="flex animate-ticker hover:[animation-play-state:paused] whitespace-nowrap py-2.5">
            {doubled.map((item, i) => {
              const type = item.type ?? 'general';
              const badgeClass = type === 'exam' ? 'bg-blue-400/15 text-blue-300 border border-blue-300/20'
                : type === 'news' ? 'bg-violet-400/15 text-violet-300 border border-violet-300/20'
                : type === 'event' ? 'bg-cyan-400/15 text-cyan-300 border border-cyan-300/20'
                : 'bg-white/10 text-gray-200';
              return (
                <Link key={i} to={`/notices/${item.slug}`} className="flex items-center gap-2 text-sm text-gray-200 hover:text-yellow-400 transition-colors flex-shrink-0 mx-8">
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

// ─── Quick Links ──────────────────────────────────────────────────────────────
function QuickLinks() {
  const links = [
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
    <div className="bg-white dark:bg-slate-800 shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex-1">
      <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
        Quick Links
      </div>
      <ul className="divide-y divide-gray-100 dark:divide-slate-700 p-1">
        {links.map(link => (
          <li key={link.to}>
            <Link to={link.to} className="block px-4 py-2.5 text-gray-700 dark:text-slate-300 hover:text-[#003D82] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-700 text-[13px] transition-all duration-200 hover:translate-x-1">
              <span className="text-blue-500 dark:text-blue-400 font-bold mr-2">›</span>{link.label}
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}

// ─── Executives / Managements ─────────────────────────────────────────────────
function Executives({ executives }: { executives: Executive[] }) {
  return (
    <div className="bg-white dark:bg-slate-800 shadow-md flex flex-col text-sm hover:shadow-xl transition-shadow duration-300 flex-1">
      <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600 flex-shrink-0">
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        Managements
      </div>
      <div className="flex-1 overflow-y-auto py-6 px-4">
        <div className="flex flex-col gap-5">
          {executives.length > 0 ? executives.map((exec, i) => (
            <div key={i} className="flex min-h-[6.25rem] gap-4 items-center">
              <div className="h-24 w-20 bg-gray-200 dark:bg-slate-700 border shadow-sm flex-shrink-0 overflow-hidden -ml-1">
                {exec.avatar
                  ? <img src={`/storage/${exec.avatar}`} className="w-full h-full object-cover" alt={exec.name} />
                  : <div className="w-full h-full flex items-center justify-center text-2xl font-bold" style={{ background: '#f3f4f6', color: '#003D82' }}>{(exec.name ?? 'N')[0].toUpperCase()}</div>}
              </div>
              <div className="flex min-h-[6rem] flex-1 flex-col justify-center pr-1">
                <div className="font-bold text-[#003D82] dark:text-blue-400 text-[15px] leading-tight">{exec.name ?? 'N/A'}</div>
                <div className="mt-1 text-[13px] text-gray-500 dark:text-slate-400">{exec.designation ?? (exec.type ? exec.type.charAt(0).toUpperCase() + exec.type.slice(1) : 'N/A')}</div>
              </div>
            </div>
          )) : <p className="text-xs text-gray-400 dark:text-slate-500 text-center py-2">Management details coming soon.</p>}
        </div>
      </div>
      <Link to="/leadership" className="block p-2.5 bg-[#003D82] dark:bg-blue-600 text-white text-xs font-bold text-left hover:bg-blue-900 dark:hover:bg-blue-700 transition-colors px-4 flex-shrink-0">
        View All Presidents &amp; Principals »
      </Link>
    </div>
  );
}

// ─── Welcome Box ──────────────────────────────────────────────────────────────
function WelcomeBox({ siteSettings }: { siteSettings: Record<string, string> }) {
  const welcomeMessage = (siteSettings?.what_is_mmp ?? 'Manmohan Memorial Polytechnic (MMP) is a constituent college of Manmohan Technical University — the first technical university in Nepal.').trim();
  const paragraphs = welcomeMessage.split(/\n\s*\n/).map(p => p.trim()).filter(Boolean);
  return (
    <div className="bg-[#003D82] dark:bg-slate-800 text-white p-8 text-center relative overflow-hidden shadow-sm border border-gray-200 dark:border-slate-700">
      <div className="absolute inset-0 opacity-10 bg-gradient-to-tr from-black to-transparent" />
      <div className="relative z-10">
        <h2 className="text-2xl font-semibold mb-3">Welcome to MMP</h2>
        <div className="mx-auto max-w-3xl px-4">
          <div className="max-h-[180px] md:max-h-[220px] overflow-y-auto pr-2 text-left">
            <div className="space-y-3 text-[13px] leading-relaxed text-gray-100">
              {paragraphs.length ? paragraphs.map((p, i) => <p key={i}>{p}</p>) : <p>{welcomeMessage}</p>}
            </div>
          </div>
        </div>
        <Link to="/pages/what-is-mmp" className="inline-block border border-white text-white px-6 py-2 text-xs font-bold hover:bg-white hover:text-[#003D82] transition-colors uppercase tracking-wide mt-4">
          About MMP
        </Link>
      </div>
    </div>
  );
}

// ─── Notice Tabs (Notice Board + Exam Results) ────────────────────────────────
function NoticeTabs({ notices, examNotices }: { notices: Notice[]; examNotices: Notice[] }) {
  const [activeTab, setActiveTab] = useState<'general' | 'exam'>('general');
  const activeList = activeTab === 'general' ? notices.slice(0, 6) : examNotices.slice(0, 6);
  return (
    <div className="bg-white dark:bg-slate-800 flex h-[520px] min-h-[520px] flex-col overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300">
      {/* Tab headers */}
      <div className="grid grid-cols-2 divide-x divide-white/10 bg-[#003D82]">
        {(['general', 'exam'] as const).map(tab => (
          <button key={tab} type="button" onClick={() => setActiveTab(tab)}
            className={`flex-1 border-x-0 border-b border-b-black/5 py-3.5 font-semibold text-sm flex items-center justify-center gap-2 transition-colors border-t-[3px] outline-none focus:outline-none
              ${activeTab === tab ? 'border-yellow-400 bg-[#0b4a92] text-white' : 'border-transparent bg-[#0f4d98] text-white/95 hover:text-white'}`}>
            {tab === 'general'
              ? <><svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>Notice Board</>
              : <><svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>Exam Results</>}
          </button>
        ))}
      </div>
      {/* Notice list */}
      <div className="flex-1 overflow-y-auto bg-white dark:bg-slate-800">
        <ul className="divide-y divide-gray-100 dark:divide-slate-700">
          {activeList.length > 0 ? activeList.map(notice => (
            <NoticeItem key={notice.slug} notice={notice} />
          )) : (
            <li className="px-4 py-8 text-center text-gray-500 dark:text-slate-400 text-sm">
              {activeTab === 'general' ? 'No recent notices found.' : 'No exam schedules or result notices found.'}
            </li>
          )}
        </ul>
      </div>
      {/* Footer link */}
      <Link to={activeTab === 'exam' ? '/notices?type=exam' : '/notices'}
        className="flex items-center justify-between p-2.5 px-4 bg-[#003D82] dark:bg-blue-600 text-white text-xs font-bold hover:bg-blue-900 dark:hover:bg-blue-700 transition-colors flex-shrink-0">
        <span>{activeTab === 'exam' ? 'View Exam Results' : 'View All Notices'}</span>
        <svg className="ml-3 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
      </Link>
    </div>
  );
}

// ─── Notice Item ──────────────────────────────────────────────────────────────
function NoticeItem({ notice }: { notice: Notice }) {
  const d = new Date(notice.published_at ?? notice.created_at);
  const day = d.getDate().toString().padStart(2, '0');
  const month = d.toLocaleString('en', { month: 'short' }).toUpperCase();
  const year = d.getFullYear();
  return (
    <li>
      <Link to={`/notices/${notice.slug}`} className="flex items-start gap-4 px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 group transition-colors">
        <div className="flex-shrink-0 w-11 h-14 text-white flex flex-col items-center justify-center rounded text-center" style={{ backgroundColor: '#003D82' }}>
          <span className="text-[8px] font-bold leading-none">{year}</span>
          <span className="text-sm font-bold leading-tight">{day}</span>
          <span className="text-[7px] font-bold uppercase leading-none">{month}</span>
        </div>
        <div className="flex-1 min-w-0">
          <div className="text-[13px] text-gray-700 dark:text-slate-300 group-hover:text-[#003D82] dark:group-hover:text-blue-400 font-medium leading-snug pt-0.5">{notice.title}</div>
          <div className="flex items-center gap-1.5 mt-2 flex-wrap">
            <span className="text-[9px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800 uppercase">{notice.type}</span>
            {notice.department && <span className="text-[9px] font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded border border-emerald-100 dark:border-emerald-800">{notice.department.name}</span>}
            {notice.program && <span className="text-[9px] font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-1.5 py-0.5 rounded border border-green-100 dark:border-green-800">{notice.program.name}</span>}
            {notice.semester && <span className="text-[9px] font-medium text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 px-1.5 py-0.5 rounded border border-purple-100 dark:border-purple-800">Semester {notice.semester}</span>}
            <span className="text-[10px] text-gray-400 dark:text-slate-500">{d.toLocaleDateString('en', { year: 'numeric', month: 'short', day: '2-digit' })}</span>
            {notice.attachment && <span className="text-[10px] text-blue-700 dark:text-blue-400 flex items-center gap-1 font-semibold"><svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>Attachment</span>}
          </div>
        </div>
        <svg className="w-4 h-4 mt-1 text-gray-300 dark:text-slate-600 group-hover:text-[#003D82] dark:group-hover:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
      </Link>
    </li>
  );
}

// ─── News & Events Card ───────────────────────────────────────────────────────
function NewsEventsCard({ newsEvents }: { newsEvents: NewsEvent[] }) {
  return (
    <div className="bg-white dark:bg-slate-800 shadow-md overflow-hidden flex flex-col hover:shadow-xl transition-shadow duration-300 flex-1">
      <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600 flex-shrink-0">
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        News &amp; Events
      </div>
      <div className="flex-1 overflow-y-auto py-2">
        {newsEvents.slice(0, 5).length > 0 ? newsEvents.slice(0, 5).map(event => {
          const d = new Date(event.published_at ?? event.created_at);
          const firstImage = event.attachments?.find(a => a.is_image);
          const typeLabel = event.type === 'event' ? 'Event' : 'News';
          const typeBadge = event.type === 'event'
            ? 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 border-teal-100 dark:border-teal-800'
            : 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-100 dark:border-purple-800';
          return (
            <Link key={event.slug} to={`/news-events/${event.slug}`}
              className="flex gap-3 px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 group transition-colors border-b border-gray-100 dark:border-slate-700 last:border-b-0">
              {firstImage ? (
                <div className="w-20 h-20 flex-shrink-0 overflow-hidden bg-gray-100 dark:bg-slate-700">
                  <img src={firstImage.url} alt={event.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
                </div>
              ) : (
                <div className="w-12 h-12 flex-shrink-0 text-white flex flex-col items-center justify-center rounded text-center shadow-sm" style={{ backgroundColor: '#003D82' }}>
                  <span className="text-[8px] font-bold leading-none">{d.getFullYear()}</span>
                  <span className="text-sm font-bold leading-tight">{d.getDate().toString().padStart(2, '0')}</span>
                  <span className="text-[7px] font-bold uppercase leading-none">{d.toLocaleString('en', { month: 'short' })}</span>
                </div>
              )}
              <div className="flex-1 w-full overflow-hidden">
                <div className="flex items-center gap-2 mb-1 flex-wrap">
                  <div className="text-[10px] font-bold text-gray-400 dark:text-slate-500">{d.toLocaleDateString('en', { year: 'numeric', month: 'short', day: '2-digit' })}</div>
                  <span className={`text-[9px] font-bold px-1.5 py-0.5 rounded-full border ${typeBadge}`}>{typeLabel}</span>
                </div>
                <h4 className="font-medium text-gray-800 dark:text-slate-200 text-[13px] leading-tight group-hover:text-[#003D82] dark:group-hover:text-blue-400 transition-colors line-clamp-2">{event.title}</h4>
              </div>
            </Link>
          );
        }) : (
          <div className="text-center py-6 text-gray-400 dark:text-slate-500">
            <svg className="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <p className="text-xs">No news or events yet.</p>
          </div>
        )}
      </div>
      <Link to="/news-events" className="block p-2.5 px-4 bg-[#003D82] dark:bg-blue-600 text-white text-xs font-bold text-left hover:bg-blue-900 dark:hover:bg-blue-700 transition-colors flex-shrink-0">
        View All News &amp; Events »
      </Link>
    </div>
  );
}

// ─── Principal's Message ──────────────────────────────────────────────────────
function PrincipalMessage({ leadership, siteSettings }: { leadership: any; siteSettings: Record<string, string> }) {
  const principal = leadership?.principals?.find((e: any) => e.is_current) ?? null;
  const mediaPath = siteSettings?.principal_message_media?.trim() ?? '';
  const ext = mediaPath ? mediaPath.split('.').pop()?.toLowerCase() ?? '' : '';
  const isVideo = ['mp4', 'webm', 'mov'].includes(ext);
  const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
  const isPdf = ext === 'pdf';
  const paragraphs = principal?.message
    ? principal.message.split(/\n\n+/).map((p: string) => p.trim()).filter(Boolean)
    : ['It is with immense pleasure that I welcome you to Manmohan Memorial Polytechnic. Here at MMP, we are confident that you will experience an enriching academic journey coupled with robust technical skill enhancement.',
       'We provide a vibrant learning environment that ensures our students gain hands-on practical knowledge that satisfies the needs of modern industries, preparing them for national and international career opportunities.'];
  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-gray-50 dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 relative overflow-hidden">
      <div className="flex justify-between items-center mb-8 pb-3 border-b border-gray-200 dark:border-slate-700">
        <h2 className="text-2xl font-bold text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">Principal's Message</h2>
      </div>
      <div className="flex flex-col lg:flex-row gap-10 items-start">
        <div className="w-full lg:w-64 flex-shrink-0 flex flex-col items-center">
          <div className="w-52 h-[260px] overflow-hidden bg-gray-200 border-4 border-white" style={{ boxShadow: '0 8px 32px rgba(139,0,0,0.18)' }}>
            {principal?.avatar
              ? <img src={`/storage/${principal.avatar}`} alt="Principal" className="w-full h-full object-cover object-top" />
              : <div className="w-full h-full flex items-center justify-center text-7xl font-bold" style={{ background: '#f3f4f6', color: '#003D82' }}>{(principal?.name ?? 'P')[0].toUpperCase()}</div>}
          </div>
          <div className="mt-4 text-center">
            <div className="font-bold text-[#003D82] text-base">{principal?.name ?? 'Principal'}</div>
            <div className="text-xs text-gray-500 font-medium mt-0.5">{principal?.designation ?? 'Principal, MMP'}</div>
          </div>
        </div>
        <div className="flex-1 min-w-0">
          {mediaPath && (
            <div className="mb-6 overflow-hidden border border-gray-200 shadow-sm">
              {isVideo && <video controls className="w-full max-h-72 bg-black" preload="metadata"><source src={`/storage/${mediaPath}`} /></video>}
              {isImage && <img src={`/storage/${mediaPath}`} alt="Principal's message media" className="w-full max-h-72 object-contain bg-gray-50" />}
              {isPdf && (
                <div className="bg-gray-50 p-4 flex items-center gap-4">
                  <div className="w-12 h-14 bg-blue-100 border border-blue-200 rounded flex items-center justify-center flex-shrink-0">
                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                  </div>
                  <div className="flex-1 min-w-0"><p className="text-sm font-semibold text-gray-800">PDF Document</p><p className="text-xs text-gray-500 truncate">{mediaPath.split('/').pop()}</p></div>
                  <a href={`/storage/${mediaPath}`} target="_blank" rel="noopener noreferrer" className="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white rounded transition-colors" style={{ background: '#003D82' }}>
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    Open PDF
                  </a>
                </div>
              )}
            </div>
          )}
          <div className="text-gray-700 dark:text-slate-300 text-[15px] leading-[2] text-justify space-y-4">
            {paragraphs.map((para: string, i: number) => <p key={i}>{para}</p>)}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Diploma Programs ─────────────────────────────────────────────────────────
const PROGRAM_ICONS: Record<string, JSX.Element> = {
  'Information Technology': <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>,
  'Civil Engineering': <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>,
  'Electrical Engineering': <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>,
  'Mechanical Engineering': <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>,
  'Electronics Engineering': <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>,
  'Architecture Engineering': <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>,
};
const DEFAULT_ICON = <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>;

function DiplomaPrograms({ departments }: { departments: Department[] }) {
  const programs = departments.length > 0 ? departments.slice(0, 6) : Object.keys(PROGRAM_ICONS).map(name => ({ name, slug: name.toLowerCase().replace(/\s+/g, '-') }));
  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-white dark:bg-slate-950 border-t border-[#f9f9f9] dark:border-slate-800">
      <div className="flex justify-between items-center mb-8 pb-3 border-b border-gray-100 dark:border-slate-800">
        <h2 className="text-2xl font-bold text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">Our Diploma Programs</h2>
        <Link to="/departments" className="text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-[#003D82] dark:hover:text-blue-400 flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded-sm hover:border-[#003D82] dark:hover:border-blue-400 transition-colors">
          <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>VIEW ALL
        </Link>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5">
        {programs.map(prog => (
          <Link key={prog.slug} to={`/departments/${prog.slug}`}
            className="group rounded-2xl shadow-md p-6 text-center flex flex-col items-center transition-all duration-300 h-full hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] bg-white dark:bg-slate-800 hover:bg-[#003D82]">
            <div className="w-14 h-14 bg-blue-50 dark:bg-slate-700 border border-blue-100 dark:border-slate-600 rounded-full shadow-sm flex items-center justify-center text-[#003D82] dark:text-blue-400 group-hover:text-white group-hover:bg-white/20 group-hover:border-white/30 mb-4 transition-colors">
              {PROGRAM_ICONS[prog.name] ?? DEFAULT_ICON}
            </div>
            <h3 className="font-semibold text-[13px] leading-snug mb-1.5 text-gray-900 dark:text-slate-200 group-hover:text-white transition-colors">
              Diploma in<br />{prog.name.replace('Diploma in ', '')}
            </h3>
            <p className="text-[11px] text-gray-400 dark:text-slate-500 group-hover:text-blue-200 mb-3">3 Years / 6 Semesters</p>
          </Link>
        ))}
      </div>
      <div className="text-center mt-10">
        <Link to="/departments" className="bg-[#003D82] dark:bg-blue-600 text-white px-6 py-2.5 font-bold shadow hover:bg-blue-900 dark:hover:bg-blue-700 transition-colors inline-flex items-center gap-2 text-sm">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
          View All Programs
        </Link>
      </div>
    </div>
  );
}

// ─── Stats Section ────────────────────────────────────────────────────────────
function StatsSection({ stats }: { stats: Record<string, any> }) {
  const items = [
    { icon: <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" /></svg>, value: `${(stats.graduates ?? 0).toLocaleString()}+`, label: 'Graduates' },
    { icon: <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>, value: `${(stats.students ?? 0).toLocaleString()}+`, label: 'Current Students' },
    { icon: <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" /></svg>, value: `${(stats.faculty_staff ?? 0).toLocaleString()}+`, label: 'Faculty & Staff' },
    { icon: <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>, value: String(stats.programs ?? 0), label: 'Diploma Programs' },
    { icon: <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.003 6.003 0 01-3.77 1.522m0 0a6.003 6.003 0 01-3.77-1.522" /></svg>, value: `${stats.years ?? 0}+`, label: 'Years of Excellence' },
  ];
  return (
    <div className="bg-[#003D82] dark:bg-slate-900 text-white py-14 shadow-inner relative overflow-hidden">
      <div className="absolute inset-0 bg-black/10 dark:bg-black/20" />
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto relative z-10">
        <div className="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6 text-center">
          {items.map((stat, i) => (
            <div key={i} className="px-2">
              <div className="w-14 h-14 mx-auto border-2 border-blue-400/30 rounded-full flex items-center justify-center mb-3 bg-blue-900/40 text-yellow-400">
                {stat.icon}
              </div>
              <div className="text-2xl lg:text-3xl font-bold mb-1 drop-shadow">{stat.value}</div>
              <div className="text-[10px] sm:text-xs font-bold text-blue-100 uppercase tracking-widest">{stat.label}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ─── Downloads & Publications ─────────────────────────────────────────────────
function DownloadsPublications({ downloads }: { downloads: Download[] }) {
  return (
    <div className="bg-white dark:bg-slate-800 shadow-md overflow-hidden text-sm flex flex-col hover:shadow-xl transition-shadow duration-300">
      <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        Downloads &amp; Publications
      </div>
      <div className="p-5 space-y-4 flex-1">
        {downloads.slice(0, 4).length > 0 ? downloads.slice(0, 4).map((dl, i) => (
          <div key={i} className={`flex gap-3 items-start ${i < Math.min(downloads.length, 4) - 1 ? 'border-b border-gray-100 dark:border-slate-700 pb-4' : ''}`}>
            <div className="w-10 h-10 bg-blue-50 dark:bg-slate-700 border border-blue-100 dark:border-slate-600 flex items-center justify-center text-[#003D82] dark:text-blue-400 rounded flex-shrink-0">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            </div>
            <div className="flex-1 min-w-0">
              <div className="font-bold text-[13px] text-[#003D82] dark:text-blue-400 truncate">{dl.title}</div>
              <div className="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">
                {new Date(dl.created_at).toLocaleDateString('en', { year: 'numeric', month: 'short', day: '2-digit' })}
                {dl.category ? ` · ${dl.category.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase())}` : ''}
              </div>
            </div>
          </div>
        )) : (
          <div className="text-center py-6 text-gray-400 dark:text-slate-500">
            <svg className="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            <p className="text-xs">Downloads coming soon.</p>
          </div>
        )}
      </div>
      <Link to="/downloads" className="block p-2.5 px-4 bg-[#003D82] dark:bg-blue-600 text-white text-xs font-bold text-left hover:bg-blue-900 dark:hover:bg-blue-700 transition-colors">
        All Downloads &amp; Publications »
      </Link>
    </div>
  );
}

// ─── Important Links ──────────────────────────────────────────────────────────
function ImportantLinks() {
  const links = [
    { label: 'CTEVT', href: 'https://ctevt.org.np' },
    { label: 'Manmohan Technical University', href: 'https://mtu.edu.np' },
    { label: 'National Skills Testing Board', href: 'https://nstb.org.np' },
    { label: 'Ministry of Education, Science & Technology', href: 'https://moest.gov.np' },
    { label: 'Department of Education, Nepal', href: 'https://doe.gov.np' },
  ];
  return (
    <div className="bg-white dark:bg-slate-800 shadow-md overflow-hidden text-sm flex flex-col hover:shadow-xl transition-shadow duration-300">
      <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
        Important Links
      </div>
      <ul className="divide-y divide-gray-100 dark:divide-slate-700 p-1 flex-1">
        {links.map(link => (
          <li key={link.href}>
            <a href={link.href} target="_blank" rel="noopener noreferrer"
              className="flex items-center gap-2 px-4 py-2.5 text-[13px] text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-[#003D82] dark:hover:text-blue-400 transition-colors group">
              <svg className="w-3.5 h-3.5 text-blue-500 dark:text-blue-400 group-hover:translate-x-0.5 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              {link.label}
            </a>
          </li>
        ))}
      </ul>
    </div>
  );
}

// ─── Why Choose MMP ───────────────────────────────────────────────────────────
function WhyChooseMMP() {
  const features = [
    { title: 'CTEVT Affiliated', desc: 'Government recognized technical education.' },
    { title: 'Modern Labs & Workshops', desc: 'Hands-on practical learning environment.' },
    { title: 'Industry Placements', desc: 'Internship and job placement support.' },
    { title: 'Scholarship Programs', desc: 'Merit and need-based financial support.' },
    { title: 'Part of MTU', desc: "Constituent college of Nepal's first technical university." },
  ];
  return (
    <div className="bg-white dark:bg-slate-800 shadow-md overflow-hidden text-sm flex flex-col hover:shadow-xl transition-shadow duration-300">
      <div className="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
        Why Choose MMP?
      </div>
      <div className="p-5 space-y-4 flex-1">
        {features.map((f, i) => (
          <div key={i} className={`flex gap-3 items-start ${i < features.length - 1 ? 'border-b border-gray-50 dark:border-slate-700 pb-3' : ''}`}>
            <div className="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" /></svg>
            </div>
            <div>
              <div className="font-bold text-[13px] text-gray-800 dark:text-slate-200">{f.title}</div>
              <div className="text-[11px] text-gray-500 dark:text-slate-400 mt-0.5">{f.desc}</div>
            </div>
          </div>
        ))}
      </div>
      <Link to="/facilities" className="block p-2.5 px-4 bg-[#003D82] dark:bg-blue-600 text-white text-xs font-bold text-left hover:bg-blue-900 dark:hover:bg-blue-700 transition-colors">
        Explore Facilities »
      </Link>
    </div>
  );
}

// ─── Find Us / Map ────────────────────────────────────────────────────────────
function FindUsSection({ siteSettings }: { siteSettings: Record<string, string> }) {
  const address = siteSettings?.contact_address ?? 'Budhiganga-4, Morang, Koshi Province, Nepal';
  const phone = siteSettings?.contact_phone ?? '+977 21 590696 / 590697';
  const email = siteSettings?.contact_email ?? 'info@mmp.edu.np';
  const website = siteSettings?.website_url ?? '';
  const affiliation = siteSettings?.affiliation_text ?? '';
  const mapsIframe = siteSettings?.google_maps_iframe ?? '';
  const hasCustomMap = mapsIframe && mapsIframe.includes('iframe');

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto pt-10 pb-16 bg-white dark:bg-slate-950 border-t border-gray-100 dark:border-slate-800">
      <div className="flex justify-between items-center mb-6 pb-2 border-b border-gray-100 dark:border-slate-800">
        <h2 className="text-2xl font-bold text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">Find Us</h2>
        <Link to="/contact" className="text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-[#003D82] dark:hover:text-blue-400 flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded-sm hover:border-[#003D82] dark:hover:border-blue-400 transition-colors">
          Contact Us
        </Link>
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-0 bg-[#f9f9f9] dark:bg-slate-900 shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
        {/* Map */}
        <div className="lg:col-span-2 h-[350px] relative">
          {hasCustomMap ? (
            <div className="w-full h-full [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0"
              dangerouslySetInnerHTML={{ __html: mapsIframe }} />
          ) : (
            <iframe
              className="w-full h-full border-0"
              src="https://maps.google.com/maps?width=100%25&height=600&hl=en&q=Manmohan%20Memorial%20Polytechnic+(Manmohan%20Memorial%20Polytechnic)&t=&z=14&ie=UTF8&iwloc=B&output=embed"
              loading="lazy"
              title="MMP Location"
            />
          )}
        </div>
        {/* Contact Info */}
        <div className="lg:col-span-1 p-6 md:p-8 bg-[#f9f9f9] dark:bg-slate-900">
          <h3 className="font-semibold text-[#003D82] dark:text-blue-400 text-[15px] mb-5 border-b border-blue-200 dark:border-slate-700 pb-2">Contact Information</h3>
          <ul className="space-y-4 text-[13px] text-gray-700 dark:text-slate-300 font-medium">
            {address && (
              <li className="flex gap-3">
                <svg className="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <div>{address}</div>
              </li>
            )}
            {phone && (
              <li className="flex gap-3">
                <svg className="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                <div>{phone}</div>
              </li>
            )}
            {email && (
              <li className="flex gap-3">
                <svg className="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                <div>{email}</div>
              </li>
            )}
            {website && (
              <li className="flex gap-3">
                <svg className="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                <div>{website}</div>
              </li>
            )}
          </ul>
          {affiliation && (
            <>
              <h3 className="font-semibold text-[#003D82] dark:text-blue-400 text-[15px] mt-8 mb-5 border-b border-blue-200 dark:border-slate-700 pb-2">Affiliated Under</h3>
              <ul className="space-y-3 text-[13px] text-gray-700 dark:text-slate-300 font-medium">
                <li className="flex gap-3">
                  <svg className="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                  <div>{affiliation}</div>
                </li>
              </ul>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
