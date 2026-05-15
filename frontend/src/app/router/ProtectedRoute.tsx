import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '@shared/hooks/useAuth';
import type { UserRole } from '@shared/types/common.types';
import type { ReactNode } from 'react';
import { Spinner } from '@shared/components/ui/Spinner';

interface Props {
  children: ReactNode;
  roles?: UserRole[];
}

/**
 * Wraps protected routes.
 * - Redirects to /login if not authenticated.
 * - Returns 403 page if authenticated but missing required role.
 */
export function RequireAuth({ children, roles }: Props) {
  const { isAuthenticated, isLoading, hasRole } = useAuth();
  const location = useLocation();

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  if (roles && !hasRole(roles)) {
    return <Navigate to="/403" replace />;
  }

  return <>{children}</>;
}
