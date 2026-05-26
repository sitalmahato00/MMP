import { useState, useRef, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '@hooks/useRedux';
import { setSidebarOpen } from '@app/store/ui.store';
import { useAuth } from '@hooks/useAuth';
import { logout as logoutAction } from '@app/store/auth.store';
import authService from '@shared/services/authService';
import toast from 'react-hot-toast';
import { Bell, LogOut, User, Settings, Menu, ChevronDown } from 'lucide-react';

export function Topbar() {
  const { user } = useAuth();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const sidebarOpen = useAppSelector((s) => s.ui.sidebarOpen);

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

  return (
    <header className="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6">
      {/* Left: hamburger + breadcrumb area */}
      <div className="flex items-center gap-3">
        {/* Mobile menu toggle */}
        <button
          onClick={() => dispatch(setSidebarOpen(!sidebarOpen))}
          className="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 lg:hidden"
        >
          <Menu className="h-5 w-5" />
        </button>

        {/* Institution name — visible on md+ */}
        <div className="hidden items-center gap-2 md:flex">
          <div className="h-4 w-px bg-slate-200" />
          <span className="text-[13px] font-semibold text-slate-700">
            Manmohan Memorial Polytechnic
          </span>
          <div className="h-4 w-px bg-slate-200" />
          <span className="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
            <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-500" />
            Session 2081/82
          </span>
        </div>
      </div>

      {/* Right: notifications + user */}
      <div className="flex items-center gap-2">
        {/* Notifications */}
        <div className="relative" ref={notifRef}>
          <button
            onClick={() => setNotifOpen(!notifOpen)}
            className="relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
          >
            <Bell className="h-4 w-4" />
            <span className="absolute -right-1 -top-1 inline-flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white">
              0
            </span>
          </button>

          {notifOpen && (
            <div className="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
              <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <div>
                  <p className="text-sm font-semibold text-slate-900">Notifications</p>
                  <p className="text-xs text-slate-500">0 unread messages</p>
                </div>
                <Link to="/admin/notices" className="text-xs font-semibold text-blue-600 hover:text-blue-700">
                  View all
                </Link>
              </div>
              <div className="px-4 py-8 text-center">
                <div className="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100">
                  <Bell className="h-5 w-5 text-slate-400" />
                </div>
                <p className="mt-3 text-sm font-medium text-slate-700">No notifications</p>
                <p className="mt-1 text-xs text-slate-400">New notices and updates will appear here.</p>
              </div>
            </div>
          )}
        </div>

        {/* Divider */}
        <div className="h-6 w-px bg-slate-200" />

        {/* User Dropdown */}
        <div className="relative" ref={userMenuRef}>
          <button
            onClick={() => setUserMenuOpen(!userMenuOpen)}
            className="flex items-center gap-2 rounded-lg border border-transparent px-2 py-1.5 transition hover:border-slate-200 hover:bg-slate-50"
          >
            <div className="h-7 w-7 overflow-hidden rounded-full border border-slate-200 bg-blue-600">
              {userAvatar ? (
                <img src={userAvatar} alt={user?.name} className="h-full w-full object-cover" />
              ) : (
                <div className="flex h-full w-full items-center justify-center text-xs font-bold text-white">
                  {user?.name?.charAt(0) || 'U'}
                </div>
              )}
            </div>
            <div className="hidden flex-col items-start md:flex">
              <span className="text-[13px] font-semibold leading-none text-slate-800">{user?.name}</span>
              <span className="mt-0.5 text-[10px] capitalize leading-none text-slate-500">{user?.role ?? 'Admin'}</span>
            </div>
            <ChevronDown className="h-3.5 w-3.5 text-slate-400" />
          </button>

          {userMenuOpen && (
            <div className="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
              {/* User info header */}
              <div className="border-b border-slate-100 bg-slate-50 px-4 py-3">
                <p className="truncate text-[13px] font-semibold text-slate-900">{user?.name}</p>
                <p className="truncate text-xs text-slate-500">{user?.email}</p>
                <span className="mt-1.5 inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-800">
                  {user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'Admin'}
                </span>
              </div>

              <div className="py-1">
                <Link
                  to="/admin/settings"
                  onClick={() => setUserMenuOpen(false)}
                  className="flex items-center gap-2.5 px-4 py-2 text-[13px] text-slate-700 transition hover:bg-slate-50 hover:text-blue-700"
                >
                  <User className="h-4 w-4 text-slate-400" />
                  My Profile
                </Link>
                <Link
                  to="/admin/settings"
                  onClick={() => setUserMenuOpen(false)}
                  className="flex items-center gap-2.5 px-4 py-2 text-[13px] text-slate-700 transition hover:bg-slate-50 hover:text-blue-700"
                >
                  <Settings className="h-4 w-4 text-slate-400" />
                  Settings
                </Link>
              </div>

              <div className="border-t border-slate-100 py-1">
                <button
                  onClick={handleLogout}
                  className="flex w-full items-center gap-2.5 px-4 py-2 text-left text-[13px] text-rose-600 transition hover:bg-rose-50"
                >
                  <LogOut className="h-4 w-4" />
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
