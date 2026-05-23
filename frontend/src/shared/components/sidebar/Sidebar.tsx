import { useState } from 'react';
import { NavLink, useLocation, Link } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '@hooks/useRedux';
import { setSidebarOpen } from '@app/store/ui.store';
import { useAuth } from '@hooks/useAuth';
import {
  LayoutDashboard, GraduationCap, Users, BookOpen, CalendarCheck, ClipboardList,
  Library, Building, Banknote, Package, DollarSign, Globe, Settings, BarChart2,
  Bell, FileText, UserCheck, Star, Briefcase, Heart, Trophy, UserPlus,
  Calendar, Newspaper, Image, Download, Palette, Shield,
  ChevronLeft, ChevronRight, ExternalLink, LogOut,
  type LucideProps,
} from 'lucide-react';
import { clsx } from 'clsx';
import { adminNavGroups } from '@app/config/navItems';
import authService from '@shared/services/authService';
import { logout as logoutAction } from '@app/store/auth.store';
import toast from 'react-hot-toast';
import { useNavigate } from 'react-router-dom';

const ICON_MAP: Record<string, React.ComponentType<LucideProps>> = {
  LayoutDashboard, GraduationCap, Users, BookOpen, CalendarCheck, ClipboardList,
  Library, Building, Banknote, Package, DollarSign, Globe, Settings, BarChart2,
  Bell, FileText, UserCheck, Star, Briefcase, Heart, Trophy, UserPlus,
  Calendar, Newspaper, Image, Download, Palette, Shield,
};

function DynamicIcon({ name, className }: { name: string; className?: string }) {
  const Icon = ICON_MAP[name];
  return Icon ? <Icon className={clsx('h-5 w-5', className)} /> : null;
}

export function Sidebar() {
  const dispatch    = useAppDispatch();
  const expanded    = useAppSelector((s) => s.ui.sidebarOpen);
  const { user }    = useAuth();
  const location    = useLocation();
  const navigate    = useNavigate();

  const closeOnMobile = () => {
    if (window.innerWidth < 1024) {
      dispatch(setSidebarOpen(false));
    }
  };

  const [expandedGroups, setExpandedGroups] = useState<Record<string, boolean>>(() => {
    const saved: Record<string, boolean> = {};
    adminNavGroups.forEach((g) => {
      if (!g.standalone && g.items.some((i) => location.pathname.startsWith(i.path))) {
        saved[g.label] = true;
      }
    });
    return saved;
  });

  const toggleGroup = (label: string) => {
    setExpandedGroups((prev) => ({ ...prev, [label]: !prev[label] }));
  };

  const isActivePath = (path: string) => location.pathname === path || location.pathname.startsWith(path + '/');

  async function handleLogout() {
    try {
      await authService.logout();
    } catch {}
    dispatch(logoutAction());
    navigate('/login', { replace: true });
    toast.success('Logged out successfully.');
  }

  const accent = '#8B0000';
  const portalLabel = 'Admin Portal';

  return (
    <aside
      className={clsx(
        'fixed inset-y-0 left-0 z-50 flex h-screen flex-col bg-[#F8FAFC] text-slate-800 shadow-2xl transition-all duration-300 dark:bg-slate-900 dark:text-white',
        expanded
          ? 'w-64 translate-x-0'
          : '-translate-x-full lg:translate-x-0 lg:w-[4.75rem]'
      )}
    >
      {/* ── Brand ── */}
      <div className="flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 dark:border-slate-800 dark:bg-slate-900 lg:px-5">
        <Link to="/admin/dashboard" className="flex min-w-0 items-center gap-3 overflow-hidden">
          <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white shadow-lg dark:border-slate-700">
            <span className="text-sm font-bold text-[#8B0000]">MMP</span>
          </div>
          {expanded && (
            <div className="min-w-0">
              <p className="truncate text-sm font-bold tracking-tight text-slate-800 dark:text-white">MMP College</p>
              <p className="mt-0.5 text-[10px] font-semibold uppercase tracking-[0.22em]" style={{ color: accent }}>{portalLabel}</p>
            </div>
          )}
        </Link>
        <button
          onClick={() => dispatch(setSidebarOpen(!expanded))}
          className="hidden rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 lg:inline-flex"
        >
          {expanded ? <ChevronLeft className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
        </button>
      </div>

      {/* ── Navigation ── */}
      <nav className="flex-1 overflow-y-auto overflow-x-visible px-3 py-3 text-slate-700 dark:text-slate-300">
        {adminNavGroups.map((group) => (
          <div key={group.label}>
            {group.standalone ? (
              <div className="space-y-1 px-1 pb-1 pt-4">
                {group.items.map((item) => (
                  <NavLink
                    key={item.path}
                    to={item.path}
                    onClick={closeOnMobile}
                    className={clsx(
                      'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
                      isActivePath(item.path)
                        ? 'bg-[#8B0000]/10 font-semibold text-[#8B0000] dark:bg-red-500/15 dark:text-red-300'
                        : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
                    )}
                    title={!expanded ? item.label : undefined}
                  >
                    {isActivePath(item.path) && (
                      <span className="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-[#8B0000]" />
                    )}
                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl">
                      <DynamicIcon name={item.icon} />
                    </span>
                    {expanded && <span className="truncate">{item.label}</span>}
                    {!expanded && (
                      <span className="pointer-events-none absolute left-full top-1/2 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white opacity-0 shadow-2xl transition-opacity group-hover:opacity-100 dark:bg-slate-700">
                        {item.label}
                      </span>
                    )}
                  </NavLink>
                ))}
              </div>
            ) : (
              <>
                <button
                  onClick={() => toggleGroup(group.label)}
                  className={clsx(
                    'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold uppercase tracking-wider transition',
                    expanded
                      ? 'justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'
                      : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'
                  )}
                  title={!expanded ? group.label : undefined}
                >
                  <span className="flex h-6 w-6 shrink-0 items-center justify-center">
                    <DynamicIcon name={group.items[0]?.icon} className="h-3.5 w-3.5" />
                  </span>
                  {expanded && <span className="truncate">{group.label}</span>}
                  {expanded && (
                    <svg
                      className={clsx('ml-auto h-3 w-3 transition-transform', expandedGroups[group.label] && 'rotate-90')}
                      fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    >
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                    </svg>
                  )}
                  {!expanded && (
                    <span className="pointer-events-none absolute left-full top-1/2 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white opacity-0 shadow-2xl transition-opacity group-hover:opacity-100 dark:bg-slate-700">
                      {group.label}
                    </span>
                  )}
                </button>
                {expanded && expandedGroups[group.label] && (
                  <div className="mb-2 space-y-1 px-1">
                    {group.items.map((item) => (
                      <NavLink
                        key={item.path}
                        to={item.path}
                        onClick={closeOnMobile}
                        className={clsx(
                          'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
                          isActivePath(item.path)
                            ? 'bg-[#8B0000]/10 font-semibold text-[#8B0000] dark:bg-red-500/15 dark:text-red-300'
                            : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
                        )}
                        title={!expanded ? item.label : undefined}
                      >
                        {isActivePath(item.path) && (
                          <span className="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-[#8B0000]" />
                        )}
                        <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl">
                          <DynamicIcon name={item.icon} />
                        </span>
                        {expanded && <span className="truncate">{item.label}</span>}
                        {!expanded && (
                          <span className="pointer-events-none absolute left-full top-1/2 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white opacity-0 shadow-2xl transition-opacity group-hover:opacity-100 dark:bg-slate-700">
                            {item.label}
                          </span>
                        )}
                      </NavLink>
                    ))}
                  </div>
                )}
              </>
            )}
          </div>
        ))}

        <div className="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
          <a
            href="/"
            target="_blank"
            rel="noreferrer"
            className="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white"
          >
            <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400 group-hover:bg-slate-200 group-hover:text-slate-900 dark:bg-slate-800 dark:text-slate-500">
              <ExternalLink className="h-4 w-4" />
            </span>
            {expanded && <span className="truncate">Public Site</span>}
            {!expanded && (
              <span className="pointer-events-none absolute left-full top-1/2 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white opacity-0 shadow-2xl transition-opacity group-hover:opacity-100 dark:bg-slate-700">
                Public Site
              </span>
            )}
          </a>
        </div>
      </nav>

      {/* ── Footer ── */}
      <div className="border-t border-slate-200 p-3 dark:border-slate-700 lg:p-4">
        <div className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg dark:border-slate-700 dark:bg-slate-800">
          <div className="h-10 w-10 shrink-0 overflow-hidden rounded-full ring-2 ring-slate-200 dark:ring-slate-600">
            {user?.avatar_url || user?.avatar ? (
              <img src={user.avatar_url || user.avatar || ''} alt={user.name} className="h-full w-full object-cover" />
            ) : (
              <div className="flex h-full w-full items-center justify-center bg-slate-200 text-xs font-bold text-slate-500">
                {user?.name?.charAt(0) || 'U'}
              </div>
            )}
          </div>
          {expanded && (
            <div className="min-w-0 flex-1">
              <p className="truncate text-sm font-semibold text-slate-800 dark:text-white">{user?.name}</p>
              <p className="truncate text-[10px] uppercase tracking-[0.22em] text-slate-400">{portalLabel}</p>
            </div>
          )}
          <button
            onClick={handleLogout}
            title="Sign out"
            className="shrink-0 rounded-xl p-2 text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-500"
          >
            <LogOut className="h-4 w-4" />
          </button>
        </div>
      </div>
    </aside>
  );
}
