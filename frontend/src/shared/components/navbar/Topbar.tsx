import { useState, useRef, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '@hooks/useRedux';
import { setSidebarOpen } from '@app/store/ui.store';
import { useAuth } from '@hooks/useAuth';
import { logout as logoutAction } from '@app/store/auth.store';
import authService from '@shared/services/authService';
import toast from 'react-hot-toast';
import { Bell, LogOut, User, Settings, Menu, ChevronDown, Calendar } from 'lucide-react';
import { useLogo } from '@hooks/useLogo';
import { adToBS, formatBS } from '@shared/utils/nepaliDate';

export function Topbar() {
  const { user } = useAuth();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const sidebarOpen = useAppSelector((s) => s.ui.sidebarOpen);
  const logoUrl = useLogo();

  const [notifOpen, setNotifOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const notifRef = useRef<HTMLDivElement>(null);
  const userMenuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function handleClick(e: MouseEvent) {
      if (notifRef.current && !notifRef.current.contains(e.target as Node)) setNotifOpen(false);
      if (userMenuRef.current && !userMenuRef.current.contains(e.target as Node)) setUserMenuOpen(false);
    }
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  async function handleLogout() {
    try { await authService.logout(); } catch {}
    dispatch(logoutAction());
    navigate('/login', { replace: true });
    toast.success('Logged out successfully.');
  }

  const userAvatar = user?.avatar_url || user?.avatar;

  // BS date
  const bsDate = (() => {
    try { return adToBS(new Date()); } catch { return null; }
  })();
  const bsDateStr = bsDate ? formatBS(bsDate, 'F j, Y') : '';

  return (
    <header
      className="flex h-16 w-full items-center justify-between px-4"
      style={{
        backgroundColor: '#002366',
        borderBottom: '2px solid #1a3a7a',
        boxShadow: '0 2px 8px rgba(0,0,0,0.35)',
      }}
    >
      {/* ── LEFT: Logo + Institution Name ── */}
      <div className="flex min-w-0 items-center gap-3">
        {/* Mobile hamburger — only on small screens */}
        <button
          onClick={() => dispatch(setSidebarOpen(!sidebarOpen))}
          className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded text-white/70 transition hover:bg-white/10 hover:text-white lg:hidden"
        >
          <Menu className="h-5 w-5" />
        </button>

        {/* Logo circle */}
        <div
          className="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden bg-white"
          style={{ borderRadius: '50%', border: '2px solid #DAA520' }}
        >
          <img
            src={logoUrl}
            alt="MMP"
            className="h-full w-full object-contain p-0.5"
            onError={(e) => {
              const t = e.currentTarget;
              t.style.display = 'none';
              const fb = t.nextElementSibling as HTMLElement | null;
              if (fb) fb.style.display = 'flex';
            }}
          />
          <span className="hidden h-full w-full items-center justify-center text-[11px] font-extrabold text-white" style={{ backgroundColor: '#002366' }}>
            MMP
          </span>
        </div>

        {/* Institution text */}
        <div className="hidden min-w-0 flex-col leading-tight sm:flex">
          <span className="truncate text-[15px] font-bold tracking-wide text-white">
            Manmohan Memorial Polytechnic
          </span>
          <span className="truncate text-[11px] font-normal tracking-wide" style={{ color: '#93b8e8' }}>
            College Administration Management System
          </span>
        </div>
      </div>

      {/* ── CENTER: Session badge ── */}
      <div className="absolute left-1/2 -translate-x-1/2 hidden md:flex">
        <span
          className="inline-flex items-center gap-2 px-4 py-1 text-[12px] font-semibold text-white"
          style={{
            backgroundColor: 'rgba(255,255,255,0.12)',
            border: '1px solid rgba(255,255,255,0.22)',
            borderRadius: '4px',
          }}
        >
          Session 2081/82
        </span>
      </div>

      {/* ── RIGHT: BS Date · Bell · User ── */}
      <div className="flex shrink-0 items-center gap-1">

        {/* BS Date */}
        {bsDateStr && (
          <div className="hidden items-center gap-1.5 px-3 py-1 lg:flex" style={{ color: '#c5d8f0' }}>
            <Calendar className="h-3.5 w-3.5 shrink-0" />
            <span className="whitespace-nowrap text-[11px] font-medium">{bsDateStr}</span>
          </div>
        )}

        <div className="hidden h-4 w-px bg-white/20 lg:block" />

        {/* Notifications */}
        <div className="relative" ref={notifRef}>
          <button
            onClick={() => setNotifOpen(!notifOpen)}
            className="relative inline-flex h-8 w-8 items-center justify-center rounded transition hover:bg-white/10"
            style={{ color: '#c5d8f0' }}
          >
            <Bell className="h-4 w-4" />
            <span
              className="absolute right-1 top-1 inline-flex h-2 w-2 rounded-full"
              style={{ backgroundColor: '#ef4444' }}
            />
          </button>

          {notifOpen && (
            <div
              className="absolute right-0 z-50 mt-1 w-72 overflow-hidden"
              style={{
                backgroundColor: '#fff',
                border: '1px solid #DCE3EB',
                borderRadius: '4px',
                boxShadow: '0 4px 16px rgba(0,35,102,0.18)',
              }}
            >
              <div
                className="flex items-center justify-between px-4 py-2.5"
                style={{ borderBottom: '1px solid #DCE3EB', backgroundColor: '#EEF3FB' }}
              >
                <p className="text-xs font-semibold" style={{ color: '#002366' }}>Notifications</p>
                <Link to="/admin/notices" className="text-[11px] font-semibold" style={{ color: '#1D4ED8' }}>
                  View all
                </Link>
              </div>
              <div className="px-4 py-6 text-center">
                <Bell className="mx-auto h-8 w-8" style={{ color: '#c5d8f0' }} />
                <p className="mt-2 text-xs font-medium" style={{ color: '#1A2B45' }}>No notifications</p>
                <p className="mt-0.5 text-[11px]" style={{ color: '#6B7A8D' }}>New notices will appear here.</p>
              </div>
            </div>
          )}
        </div>

        <div className="h-4 w-px bg-white/20" />

        {/* User dropdown */}
        <div className="relative" ref={userMenuRef}>
          <button
            onClick={() => setUserMenuOpen(!userMenuOpen)}
            className="flex items-center gap-2 rounded px-2 py-1 transition hover:bg-white/10"
          >
            <div
              className="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden"
              style={{ borderRadius: '50%', border: '2px solid rgba(255,255,255,0.35)', backgroundColor: '#1D4ED8' }}
            >
              {userAvatar ? (
                <img src={userAvatar} alt={user?.name} className="h-full w-full object-cover" />
              ) : (
                <span className="text-[11px] font-bold text-white">{user?.name?.charAt(0) || 'A'}</span>
              )}
            </div>
            <div className="hidden flex-col items-start leading-tight md:flex">
              <span className="text-[12px] font-semibold text-white">{user?.name ?? 'Admin'}</span>
              <span className="text-[10px] capitalize" style={{ color: '#93b8e8' }}>{user?.role ?? 'Principal'}</span>
            </div>
            <ChevronDown className="h-3 w-3" style={{ color: 'rgba(255,255,255,0.5)' }} />
          </button>

          {userMenuOpen && (
            <div
              className="absolute right-0 z-50 mt-1 w-52 overflow-hidden"
              style={{
                backgroundColor: '#fff',
                border: '1px solid #DCE3EB',
                borderRadius: '4px',
                boxShadow: '0 4px 16px rgba(0,35,102,0.18)',
              }}
            >
              <div className="px-4 py-3" style={{ borderBottom: '1px solid #DCE3EB', backgroundColor: '#EEF3FB' }}>
                <p className="truncate text-xs font-semibold" style={{ color: '#002366' }}>{user?.name}</p>
                <p className="truncate text-[11px]" style={{ color: '#6B7A8D' }}>{user?.email}</p>
                <span
                  className="mt-1.5 inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold"
                  style={{ backgroundColor: '#DBEAFE', color: '#1E40AF', borderRadius: '2px' }}
                >
                  {user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'Admin'}
                </span>
              </div>
              <div className="py-1">
                <Link to="/admin/settings" onClick={() => setUserMenuOpen(false)}
                  className="flex items-center gap-2 px-4 py-2 text-xs transition hover:bg-[#F4F7FB]"
                  style={{ color: '#1A2B45' }}>
                  <User className="h-3.5 w-3.5" style={{ color: '#6B7A8D' }} />
                  My Profile
                </Link>
                <Link to="/admin/settings" onClick={() => setUserMenuOpen(false)}
                  className="flex items-center gap-2 px-4 py-2 text-xs transition hover:bg-[#F4F7FB]"
                  style={{ color: '#1A2B45' }}>
                  <Settings className="h-3.5 w-3.5" style={{ color: '#6B7A8D' }} />
                  Settings
                </Link>
              </div>
              <div className="py-1" style={{ borderTop: '1px solid #DCE3EB' }}>
                <button onClick={handleLogout}
                  className="flex w-full items-center gap-2 px-4 py-2 text-left text-xs transition hover:bg-red-50"
                  style={{ color: '#DC2626' }}>
                  <LogOut className="h-3.5 w-3.5" />
                  Sign Out
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
