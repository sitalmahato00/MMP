import { NavLink, useLocation } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '@hooks/useRedux';
import { setSidebarOpen } from '@app/store/ui.store';
import { useAuth } from '@hooks/useAuth';
import {
  LayoutDashboard, GraduationCap, Users, BookOpen, CalendarCheck, ClipboardList,
  Library, Building, Settings, BarChart2, Bell, FileText, UserCheck, Star,
  Briefcase, Heart, Trophy, UserPlus, Calendar, Newspaper, Image, Download,
  Palette, Shield, ChevronLeft, ExternalLink, LogOut,
  type LucideProps,
} from 'lucide-react';
import { adminNavGroups } from '@app/config/navItems';
import authService from '@shared/services/authService';
import { logout as logoutAction } from '@app/store/auth.store';
import toast from 'react-hot-toast';
import { useNavigate } from 'react-router-dom';

const ICON_MAP: Record<string, React.ComponentType<LucideProps>> = {
  LayoutDashboard, GraduationCap, Users, BookOpen, CalendarCheck, ClipboardList,
  Library, Building, Settings, BarChart2, Bell, FileText, UserCheck, Star,
  Briefcase, Heart, Trophy, UserPlus, Calendar, Newspaper, Image, Download,
  Palette, Shield,
};

function Icon({ name }: { name: string }) {
  const C = ICON_MAP[name];
  return C ? <C className="h-4 w-4 shrink-0" /> : null;
}

export function Sidebar() {
  const dispatch = useAppDispatch();
  const { user } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();

  const isActive = (path: string) =>
    location.pathname === path || location.pathname.startsWith(path + '/');

  async function handleLogout() {
    try { await authService.logout(); } catch {}
    dispatch(logoutAction());
    navigate('/login', { replace: true });
    toast.success('Logged out successfully.');
  }

  const itemStyle = (active: boolean): React.CSSProperties => ({
    display: 'flex',
    alignItems: 'center',
    gap: 10,
    padding: '7px 10px',
    borderRadius: 3,
    backgroundColor: active ? '#1a56db' : 'transparent',
    color: active ? '#ffffff' : '#b8cce8',
    fontSize: 13,
    fontWeight: active ? 600 : 400,
    textDecoration: 'none',
    transition: 'background-color 0.15s',
    cursor: 'pointer',
    whiteSpace: 'nowrap',
  });

  return (
    <aside
      className="flex h-full w-full flex-col"
      style={{ backgroundColor: '#011e54', borderRight: '1px solid #0d2d6b' }}
    >
      {/* ── Mobile close button (desktop never needs this) ── */}
      <div
        className="flex h-9 shrink-0 items-center justify-between px-3 lg:hidden"
        style={{ borderBottom: '1px solid #0d2d6b' }}
      >
        <span style={{ color: '#4a6a9a', fontSize: 9, fontWeight: 700, letterSpacing: '0.18em', textTransform: 'uppercase' }}>
          Navigation
        </span>
        <button
          onClick={() => dispatch(setSidebarOpen(false))}
          className="rounded p-1 transition hover:bg-white/10"
          style={{ color: 'rgba(255,255,255,0.4)' }}
        >
          <ChevronLeft className="h-3.5 w-3.5" />
        </button>
      </div>

      {/* ── Nav ── */}
      <nav className="flex-1 overflow-y-auto overflow-x-hidden scrollbar-none py-1">
        <div className="px-2">

          {/* MAIN NAVIGATION label */}
          <p style={{ color: '#4a6a9a', fontSize: 9, fontWeight: 700, letterSpacing: '0.18em', textTransform: 'uppercase', padding: '10px 8px 4px' }}>
            Main Navigation
          </p>

          {adminNavGroups.map((group) => (
            <div key={group.label}>
              {group.standalone ? (
                /* Dashboard — no group label */
                <div className="mb-1 space-y-0.5">
                  {group.items.map((item) => {
                    const active = isActive(item.path);
                    return (
                      <NavLink
                        key={item.path}
                        to={item.path}
                        onClick={() => { if (window.innerWidth < 1024) dispatch(setSidebarOpen(false)); }}
                        style={itemStyle(active)}
                        onMouseEnter={(e) => { if (!active) (e.currentTarget as HTMLElement).style.backgroundColor = 'rgba(255,255,255,0.08)'; }}
                        onMouseLeave={(e) => { if (!active) (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'; }}
                      >
                        <Icon name={item.icon} />
                        <span className="truncate">{item.label}</span>
                      </NavLink>
                    );
                  })}
                </div>
              ) : (
                /* Named group */
                <div className="mb-1">
                  <p style={{ color: '#4a6a9a', fontSize: 9, fontWeight: 700, letterSpacing: '0.18em', textTransform: 'uppercase', padding: '8px 8px 3px' }}>
                    {group.label}
                  </p>
                  <div className="space-y-0.5">
                    {group.items.map((item) => {
                      const active = isActive(item.path);
                      return (
                        <NavLink
                          key={item.path}
                          to={item.path}
                          onClick={() => { if (window.innerWidth < 1024) dispatch(setSidebarOpen(false)); }}
                          style={itemStyle(active)}
                          onMouseEnter={(e) => { if (!active) (e.currentTarget as HTMLElement).style.backgroundColor = 'rgba(255,255,255,0.08)'; }}
                          onMouseLeave={(e) => { if (!active) (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'; }}
                        >
                          <Icon name={item.icon} />
                          <span className="truncate">{item.label}</span>
                        </NavLink>
                      );
                    })}
                  </div>
                </div>
              )}
            </div>
          ))}

          {/* Public site */}
          <div style={{ borderTop: '1px solid #0d2d6b', marginTop: 6, paddingTop: 6 }}>
            <a
              href="/"
              target="_blank"
              rel="noreferrer"
              style={{ ...itemStyle(false), color: '#4a6a9a' }}
              onMouseEnter={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = 'rgba(255,255,255,0.08)'; (e.currentTarget as HTMLElement).style.color = '#b8cce8'; }}
              onMouseLeave={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'; (e.currentTarget as HTMLElement).style.color = '#4a6a9a'; }}
            >
              <ExternalLink className="h-4 w-4 shrink-0" />
              <span className="truncate">Public Site</span>
            </a>
          </div>
        </div>
      </nav>

      {/* ── User footer ── */}
      <div className="shrink-0 p-2" style={{ borderTop: '1px solid #0d2d6b' }}>
        <div
          className="flex items-center gap-2 px-2 py-1.5"
          style={{ backgroundColor: 'rgba(255,255,255,0.05)', borderRadius: 3 }}
        >
          <div
            className="flex h-7 w-7 shrink-0 items-center justify-center text-[11px] font-bold text-white"
            style={{ borderRadius: '50%', border: '1px solid rgba(255,255,255,0.25)', backgroundColor: '#1a56db' }}
          >
            {user?.name?.charAt(0) || 'A'}
          </div>
          <div className="min-w-0 flex-1">
            <p className="truncate text-[12px] font-semibold text-white">{user?.name}</p>
            <p className="truncate text-[10px] capitalize" style={{ color: '#7a9cc8' }}>{user?.role ?? 'Admin'}</p>
          </div>
          <button
            onClick={handleLogout}
            title="Sign out"
            className="shrink-0 rounded p-1 transition hover:bg-white/10"
            style={{ color: 'rgba(255,255,255,0.4)' }}
          >
            <LogOut className="h-3.5 w-3.5" />
          </button>
        </div>
      </div>
    </aside>
  );
}
