import { Bell, LogOut, User } from 'lucide-react';
import { useAppDispatch } from '@hooks/useRedux';
import { useAuth } from '@hooks/useAuth';
import { logout } from '@app/store/auth.store';
import authService from '@shared/services/auth.service';
import toast from 'react-hot-toast';
import { useNavigate } from 'react-router-dom';

export function Topbar() {
  const { user }   = useAuth();
  const dispatch   = useAppDispatch();
  const navigate   = useNavigate();

  async function handleLogout() {
    try {
      await authService.logout();
    } finally {
      dispatch(logout());
      navigate('/login', { replace: true });
      toast.success('Logged out successfully.');
    }
  }

  return (
    <header className="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-white/80">
      <div className="text-sm text-gray-500">
        {/* Breadcrumb can be added here */}
      </div>

      <div className="flex items-center gap-4">
        {/* Notifications */}
        <button
          className="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 transition"
          aria-label="Notifications"
        >
          <Bell className="h-5 w-5" />
        </button>

        {/* User info */}
        <div className="flex items-center gap-3">
          <div className="text-right hidden sm:block">
            <p className="text-sm font-medium text-gray-900">{user?.name}</p>
            <p className="text-xs text-gray-500 capitalize">{user?.role}</p>
          </div>
          {user?.avatar ? (
            <img
              src={user.avatar}
              alt={user.name}
              className="h-8 w-8 rounded-full object-cover ring-2 ring-gray-200"
            />
          ) : (
            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-primary-700">
              <User className="h-4 w-4" />
            </div>
          )}
        </div>

        {/* Logout */}
        <button
          onClick={handleLogout}
          className="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 transition"
          aria-label="Logout"
          title="Logout"
        >
          <LogOut className="h-5 w-5" />
        </button>
      </div>
    </header>
  );
}
