import { useAppSelector } from './useRedux';
import type { AuthUser, UserRole } from '@shared/types/common.types';

function getUserRoles(user: AuthUser | null): UserRole[] {
  if (!user) return [];
  if (user.roles && user.roles.length > 0) return user.roles;
  if (user.role) return [user.role];
  return [];
}

export function useAuth() {
  const { user, token, isAuthenticated, isLoading } =
    useAppSelector((s) => s.auth);

  function hasRole(role: UserRole | UserRole[]): boolean {
    const userRoles = getUserRoles(user);
    const roles = Array.isArray(role) ? role : [role];
    return roles.some((r) => userRoles.includes(r));
  }

  function hasAnyRole(roles: UserRole[]): boolean {
    return hasRole(roles);
  }

  const roles = getUserRoles(user);

  return {
    user: user as AuthUser | null,
    token,
    isAuthenticated,
    isLoading,
    hasRole,
    hasAnyRole,
    roles,
    isAdmin:     roles.includes('admin') || roles.includes('staff') || roles.includes('librarian') || roles.includes('accountant'),
    isTeacher:   roles.includes('teacher'),
    isStudent:   roles.includes('student'),
    isHod:       roles.includes('hod'),
    isStaff:     roles.includes('staff'),
    isParent:    roles.includes('parent'),
  };
}
