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
  ChevronLeft, ChevronRight, ExternalLink, LogOut, ChevronDown,
  type LucideProps,
} from 'lucide-react';
import { clsx } from 'clsx';
import { adminNavGroups } from '@app/config/navItems';
import authService from '@shared/services/authService';
import { logout as logoutAction } from '@app/store/auth.store';
import toast from 'react-hot-toast';
import { useNavigate } from 'react-router-dom';
import { useLogo } from '@hooks/useLogo';

const ICON_MAP: Record<string, React.ComponentType<LucideProps>> = {
  LayoutDashboard, GraduationCap, Users, BookOpen, CalendarCheck, ClipboardList,
  Library, Building, Banknote, Package, DollarSign, Globe, Settings, BarChart2,
  Bell, FileText, UserCheck, Star, Briefcase, Heart, Trophy, UserPlus,
  Calendar, Newspaper, Image, Download, Palette, Shield,
};

function DynamicIcon({ name, className }: { name: string; className?: string }) {
  const Icon = ICON_MAP[name];
  return Icon ? <Icon className={clsx('h-4 w-4', className)} /> : null;
}

export function Sidebar() {
  const dispatch = useAppDispatch();
  const expanded = useAppSelector((s) => s.ui.sidebarOpen);
  const { user } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const logoUrl  = useLogo();

  const closeOnMobile = () => {
    if (window.innerWidth < 1024) dispatch(setSidebarOpen(false));
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

  const toggleGroup = (label: string) =>
    setExpandedGroups((prev) => ({ ...prev, [label]: !prev[label] }));

  const isActivePath = (path: string) =>
    location.pathname === path || location.pathname.startsWith(path + '/');

  async function handleLogout() {
    try { await authService.logout(); } catch {}
    dispatch(logoutAction());
    navigate('/login', { replace: true });
    toast.success('Logged out successfully.');
  }

  return (
    <aside
      className={clsx(
        'fixed inset-y-0 left-0 z-50 flex h-screen flex-col transition-all duration-300',
        /* Government deep-navy background */
        'bg-[#0f2d5e] text-white',
        expanded
          ? 'w-64 translate-x-0'
          : '-translate-x-full lg:translate-x-0 lg:w-[4.5rem]'
      )}
    >
      {/* ── Brand Header ── */}
      <div className={clsx(
        'flex h-16 shrink-0 items-center border-b border-white/10',
        expanded ? 'justify-between px-4' : 'justify-center px-0'
      )}>
        <Link to="/admin/dashboard" className={clsx(
          'flex min-w-0 items-center gap-3 overflow-hidden',
          !expanded && 'justify-center'
        )}>
          {/* College logo */}
          <div className="h-9 w-9 shrink-0 overflow-hidden rounded-full border-2 border-[#DAA520] bg-white" style={{ minWidth: '2.25rem' }}>
            <img
              src={logoUrl}
              alt="MMP Logo"
              className="h-full w-full object-contain"
              onError={(e) => {
                const t = e.currentTarget;
                t.style.display = 'none';
                const fallback = t.nextElementSibling as HTMLElement | null;
                if (fallback) fallback.style.display = 'flex';
              }}
            />
            <span className="hidden h-full w-full items-center justify-center text-[11px] font-extrabold tracking-tight text-white">
              MMP
            </span>
          </div>
          {expanded && (
            <div className="min-w-0 leading-tight">
              <p className="truncate text-[13px] font-bold text-white">MMP College</p>
              <p className="text-[9px] font-semibold uppercase tracking-[0.2em] text-blue-300">
                Admin Portal
              </p>
            </div>
          )}
        </Link>
        {expanded && (
          <button
            onClick={() => dispatch(setSidebarOpen(!expanded))}
            className="hidden shrink-0 rounded-md p-1.5 text-white/50 transition hover:bg-white/10 hover:text-white lg:inline-flex"
          >
            <ChevronLeft className="h-4 w-4" />
          </button>
        )}
      </div>
      {/* Collapsed expand button — sits just below the header, centered */}
      {!expanded && (
        <button
          onClick={() => dispatch(setSidebarOpen(true))}
          className="hidden w-full items-center justify-center py-2 text-white/40 transition hover:bg-white/10 hover:text-white lg:flex"
        >
          <ChevronRight className="h-4 w-4" />
        </button>
      )}

      {/* ── Navigation ── */}
      <nav className="flex-1 overflow-y-auto overflow-x-hidden py-3 text-sm">
        {adminNavGroups.map((group) => (
          <div key={group.label}>
            {group.standalone ? (
              /* Standalone items (Dashboard) */
              <div className="px-2 pb-1 pt-2">
                {group.items.map((item) => (
                  <NavLink
                    key={item.path}
                    to={item.path}
                    onClick={closeOnMobile}
                    title={!expanded ? item.label : undefined}
                    className={clsx(
                      'group relative flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition-all',
                      isActivePath(item.path)
                        ? 'bg-white/15 text-white'
                        : 'text-blue-100/80 hover:bg-white/10 hover:text-white'
                    )}
                  >
                    {isActivePath(item.path) && (
                      <span className="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-blue-300" />
                    )}
                    <span className="flex h-7 w-7 shrink-0 items-center justify-center">
                      <DynamicIcon name={item.icon} />
                    </span>
                    {expanded && <span className="truncate text-[13px]">{item.label}</span>}
                    {!expanded && (
                      <span className="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-xl transition-opacity group-hover:opacity-100">
                        {item.label}
                      </span>
                    )}
                  </NavLink>
                ))}
              </div>
            ) : (
              /* Collapsible group */
              <div className="px-2 pb-0.5">
                {/* Group header */}
                {expanded ? (
                  <button
                    onClick={() => toggleGroup(group.label)}
                    className="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-[0.18em] text-blue-300/70 transition hover:text-blue-200"
                  >
                    <span className="flex-1 text-left">{group.label}</span>
                    <ChevronDown
                      className={clsx(
                        'h-3 w-3 transition-transform',
                        expandedGroups[group.label] && 'rotate-180'
                      )}
                    />
                  </button>
                ) : (
                  /* Collapsed: show a thin divider */
                  <div className="my-1 mx-3 border-t border-white/10" />
                )}

                {/* Group items */}
                {(expanded ? expandedGroups[group.label] : true) && (
                  <div className="space-y-0.5">
                    {group.items.map((item) => (
                      <NavLink
                        key={item.path}
                        to={item.path}
                        onClick={closeOnMobile}
                        title={!expanded ? item.label : undefined}
                        className={clsx(
                          'group relative flex items-center gap-3 rounded-lg px-3 py-2 font-medium transition-all',
                          isActivePath(item.path)
                            ? 'bg-white/15 text-white'
                            : 'text-blue-100/70 hover:bg-white/10 hover:text-white'
                        )}
                      >
                        {isActivePath(item.path) && (
                          <span className="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-blue-300" />
                        )}
                        <span className="flex h-7 w-7 shrink-0 items-center justify-center">
                          <DynamicIcon name={item.icon} />
                        </span>
                        {expanded && <span className="truncate text-[13px]">{item.label}</span>}
                        {!expanded && (
                          <span className="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-xl transition-opacity group-hover:opacity-100">
                            {item.label}
                          </span>
                        )}
                      </NavLink>
                    ))}
                  </div>
                )}
              </div>
            )}
          </div>
        ))}

        {/* Public site link */}
        <div className="mt-2 border-t border-white/10 px-2 pt-3">
          <a
            href="/"
            target="_blank"
            rel="noreferrer"
            title={!expanded ? 'Public Site' : undefined}
            className="group relative flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-blue-100/60 transition hover:bg-white/10 hover:text-white"
          >
            <span className="flex h-7 w-7 shrink-0 items-center justify-center">
              <ExternalLink className="h-4 w-4" />
            </span>
            {expanded && <span className="truncate text-[13px]">Public Site</span>}
            {!expanded && (
              <span className="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-xl transition-opacity group-hover:opacity-100">
                Public Site
              </span>
            )}
          </a>
        </div>
      </nav>

      {/* ── User Footer ── */}
      <div className="shrink-0 border-t border-white/10 p-3">
        {expanded ? (
          <div className="flex items-center gap-3 rounded-lg bg-white/10 p-2.5">
            <div className="h-8 w-8 shrink-0 overflow-hidden rounded-full ring-2 ring-white/20">
              {user?.avatar_url || user?.avatar ? (
                <img src={user.avatar_url || user.avatar || ''} alt={user.name} className="h-full w-full object-cover" />
              ) : (
                <div className="flex h-full w-full items-center justify-center bg-blue-600 text-xs font-bold text-white">
                  {user?.name?.charAt(0) || 'U'}
                </div>
              )}
            </div>
            <div className="min-w-0 flex-1">
              <p className="truncate text-[13px] font-semibold text-white">{user?.name}</p>
              <p className="truncate text-[10px] capitalize text-blue-300">{user?.role ?? 'Admin'}</p>
            </div>
            <button
              onClick={handleLogout}
              title="Sign out"
              className="shrink-0 rounded-md p-1.5 text-white/50 transition hover:bg-white/10 hover:text-white"
            >
              <LogOut className="h-4 w-4" />
            </button>
          </div>
        ) : (
          <div className="flex flex-col items-center gap-2">
            <div className="h-8 w-8 overflow-hidden rounded-full ring-2 ring-white/20">
              {user?.avatar_url || user?.avatar ? (
                <img src={user.avatar_url || user.avatar || ''} alt={user.name} className="h-full w-full object-cover" />
              ) : (
                <div className="flex h-full w-full items-center justify-center bg-blue-600 text-xs font-bold text-white">
                  {user?.name?.charAt(0) || 'U'}
                </div>
              )}
            </div>
            <button
              onClick={handleLogout}
              title="Sign out"
              className="group relative rounded-md p-1.5 text-white/50 transition hover:bg-white/10 hover:text-white"
            >
              <LogOut className="h-4 w-4" />
              <span className="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-xl transition-opacity group-hover:opacity-100">
                Sign out
              </span>
            </button>
          </div>
        )}
      </div>
    </aside>
  );
}
