import { createSlice, type PayloadAction } from '@reduxjs/toolkit';
import type { AuthState, AuthUser, UserRole } from '@shared/types/common.types';

const TOKEN_KEY = 'erp_token';
const USER_KEY  = 'erp_user';

const ROLE_ALIAS: Record<string, UserRole[]> = {
  principal: ['principal', 'admin'],
};

function normalizeUser(raw: AuthUser): AuthUser {
  const baseRoles = raw.roles?.length ? raw.roles : (raw.role ? [raw.role as UserRole] : []);
  const expandedRoles = new Set<UserRole>();
  for (const r of baseRoles) {
    expandedRoles.add(r);
    const aliases = ROLE_ALIAS[r];
    if (aliases) for (const a of aliases) expandedRoles.add(a);
  }
  return {
    ...raw,
    avatar: raw.avatar ?? raw.avatar_url ?? undefined,
    roles: [...expandedRoles],
  };
}

function loadInitialState(): AuthState {
  try {
    const token = localStorage.getItem(TOKEN_KEY);
    const raw   = localStorage.getItem(USER_KEY);
    const user  = raw ? normalizeUser(JSON.parse(raw) as AuthUser) : null;
    return { token, user, isAuthenticated: !!(token && user), isLoading: false };
  } catch {
    return { token: null, user: null, isAuthenticated: false, isLoading: false };
  }
}

const authSlice = createSlice({
  name: 'auth',
  initialState: loadInitialState(),
  reducers: {
    setCredentials(state, action: PayloadAction<{ token: string; user: AuthUser }>) {
      const user = normalizeUser(action.payload.user);
      state.token           = action.payload.token;
      state.user            = user;
      state.isAuthenticated = true;
      state.isLoading       = false;
      localStorage.setItem(TOKEN_KEY, action.payload.token);
      localStorage.setItem(USER_KEY,  JSON.stringify(user));
    },
    setUser(state, action: PayloadAction<AuthUser>) {
      const user = normalizeUser(action.payload);
      state.user = user;
      localStorage.setItem(USER_KEY, JSON.stringify(user));
    },
    setLoading(state, action: PayloadAction<boolean>) {
      state.isLoading = action.payload;
    },
    logout(state) {
      state.token           = null;
      state.user            = null;
      state.isAuthenticated = false;
      state.isLoading       = false;
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
    },
  },
});

export const { setCredentials, setUser, setLoading, logout } = authSlice.actions;
export default authSlice.reducer;
