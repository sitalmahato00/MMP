import { useEffect, type ReactNode } from 'react';
import { useAppDispatch } from '@shared/hooks/useRedux';
import { setUser, logout, setLoading } from '@app/store/auth.store';
import api from '@shared/api/axios';
import type { AuthUser } from '@shared/types/common.types';

interface Props { children: ReactNode; }

/**
 * Runs once on app mount.
 * If a stored token exists, validates it with /api/v1/user.
 * On failure, clears stale auth state.
 */
export function AuthInitializer({ children }: Props) {
  const dispatch = useAppDispatch();

  useEffect(() => {
    const token = localStorage.getItem('erp_token');
    if (!token) return;

    dispatch(setLoading(true));
      api
        .get<{ success: boolean; data: { user: AuthUser } }>('/v1/user')
      .then(({ data }) => {
        if (data.success && data.data?.user) {
          dispatch(setUser(data.data.user));
        } else {
          dispatch(logout());
        }
      })
      .catch(() => dispatch(logout()))
      .finally(() => dispatch(setLoading(false)));
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return <>{children}</>;
}
