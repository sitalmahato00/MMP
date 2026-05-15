import { useAppSelector } from './useRedux';
import type { AuthUser, UserRole } from '@shared/types/common.types';

export function useAuth() {
  const { user, token, isAuthenticated, isLoading } =
    useAppSelector((s) => s.auth);

  function hasRole(role: UserRole | UserRole[]): boolean {
    if (!user) return false;
    const roles = Array.isArray(role) ? role : [role];
    return roles.some((r) => user.roles.includes(r));
  }

  function hasAnyRole(roles: UserRole[]): boolean {
    return hasRole(roles);
  }

  return {
    user: user as AuthUser | null,
    token,
    isAuthenticated,
    isLoading,
    hasRole,
    hasAnyRole,
    isAdmin:     hasRole('admin'),
    isTeacher:   hasRole('teacher'),
    isStudent:   hasRole('student'),
    isHod:       hasRole('hod'),
    isStaff:     hasRole('staff'),
    isParent:    hasRole('parent'),
  };
}
