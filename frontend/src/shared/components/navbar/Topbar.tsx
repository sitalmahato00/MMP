import { useState, useRef, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAppDispatch } from '@hooks/useRedux';
import { useAuth } from '@hooks/useAuth';
import { logout as logoutAction } from '@app/store/auth.store';
import authService from '@shared/services/authService';
import toast from 'react-hot-toast';
import { Bell, Moon, Sun, LogOut, User, Settings } from 'lucide-react';

export function Topbar() {
  const { user } = useAuth();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
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
    try {
      await authService.logout();
    } catch {}
    dispatch(logoutAction());
    navigate('/login', { replace: true });
    toast.success('Logged out successfully.');
  }

  const userAvatar = user?.avatar_url || user?.avatar;

  return (
    <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:px-6 lg:px-8">
      <div className="flex items-center gap-2">
        <div className="hidden items-center gap-2 md:flex">
          <span className="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
            <span className="mr-1.5 h-1.5 w-1.5 animate-pulse rounded-full bg-blue-500" />
            Session 2081/82
          </span>
          <span className="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
            <svg className="mr-1.5 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            IT Department
          </span>
          <span className="inline-flex items-center rounded-full border border-violet-200 bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700">
            <svg className="mr-1.5 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Principal
          </span>
        </div>
      </div>

      <div className="flex items-center gap-3">
        {/* Theme Toggle */}
        <button
          onClick={() => {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            html.dataset.theme = isDark ? 'dark' : 'light';
            html.style.colorScheme = isDark ? 'dark' : 'light';
          }}
          className="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gray-200 bg-white text-slate-500 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
        >
          <Moon className="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
          <Sun className="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
        </button>

        {/* Notifications */}
        <div className="relative" ref={notifRef}>
          <button
            onClick={() => setNotifOpen(!notifOpen)}
            className="relative inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gray-200 bg-white text-slate-500 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
          >
            <Bell className="h-5 w-5" />
            <span className="absolute -right-1 -top-1 inline-flex min-h-[1.35rem] min-w-[1.35rem] items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-bold text-white shadow-lg">
              0
            </span>
          </button>

          {notifOpen && (
            <div
              className="absolute right-0 z-50 mt-3 w-[22rem] overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900"
            >
              <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                  <p className="text-sm font-semibold text-slate-900 dark:text-white">Notifications</p>
                  <p className="text-xs text-slate-500">0 unread</p>
                </div>
                <Link to="/notifications" className="text-xs font-semibold text-blue-600 hover:text-blue-700">
                  View all
                </Link>
              </div>
              <div className="px-5 py-10 text-center">
                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                  <Bell className="h-6 w-6" />
                </div>
                <p className="mt-4 text-sm font-semibold text-slate-900 dark:text-white">No notifications yet</p>
                <p className="mt-1 text-xs leading-6 text-slate-500">New notices, results, and updates will appear here.</p>
              </div>
            </div>
          )}
        </div>

        {/* User Dropdown */}
        <div className="relative" ref={userMenuRef}>
          <button
            onClick={() => setUserMenuOpen(!userMenuOpen)}
            className="flex items-center gap-2 rounded-full pr-2 focus:outline-none"
          >
            <div className="h-8 w-8 overflow-hidden rounded-full border border-gray-300 bg-gray-200">
              {userAvatar ? (
                <img src={userAvatar} alt={user?.name} className="h-full w-full object-cover" />
              ) : (
                <div className="flex h-full w-full items-center justify-center bg-primary-100 text-sm font-semibold text-primary-700">
                  {user?.name?.charAt(0) || 'U'}
                </div>
              )}
            </div>
            <div className="hidden flex-col items-start md:flex">
              <span className="text-sm font-semibold leading-none text-gray-700 dark:text-slate-200">{user?.name}</span>
              <span className="text-xs capitalize text-gray-500">{user?.role}</span>
            </div>
            <svg className="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          {userMenuOpen && (
            <div className="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-900">
              <div className="border-b border-gray-100 px-4 py-3">
                <p className="truncate text-sm font-medium text-gray-900 dark:text-white">{user?.name}</p>
                <p className="truncate text-xs text-gray-500">{user?.email}</p>
                <div className="mt-2 flex flex-wrap gap-1.5">
                  <span className="inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'User'}
                  </span>
                </div>
              </div>
              <Link
                to="/admin/settings"
                onClick={() => setUserMenuOpen(false)}
                className="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800"
              >
                <User className="h-4 w-4" /> My Profile
              </Link>
              <Link
                to="/admin/settings"
                onClick={() => setUserMenuOpen(false)}
                className="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800"
              >
                <Settings className="h-4 w-4" /> Settings
              </Link>
              <div className="my-1 border-t border-gray-100" />
              <button
                onClick={handleLogout}
                className="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
              >
                <LogOut className="h-4 w-4" /> Sign Out
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
