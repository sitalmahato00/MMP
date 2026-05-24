import { Navigate } from 'react-router-dom';
import { useAuth } from '@shared/hooks/useAuth';
import type { ReactNode } from 'react';

interface Props { children: ReactNode; }

/** Redirects authenticated users away from /login back to their dashboard. */
export function GuestOnly({ children }: Props) {
  const { isAuthenticated, user } = useAuth();

  if (isAuthenticated && user) {
    return <Navigate to={getDashboardPath(user.role)} replace />;
  }

  return <>{children}</>;
}

function getDashboardPath(role: string): string {
  const map: Record<string, string> = {
    admin:          '/admin/dashboard',
    principal:      '/admin/dashboard',
    teacher:        '/teacher/dashboard',
    student:        '/student/dashboard',
    hod:            '/hod/dashboard',
    staff:          '/admin/dashboard',
    parent:         '/parent/dashboard',
    librarian:      '/admin/dashboard',
    accountant:     '/admin/dashboard',
    hostel_warden:  '/hostel/dashboard',
  };
  return map[role] ?? '/admin/dashboard';
}
