import { get, post, put, del } from '@shared/api/axios';
import type { ApiResponse, PaginatedResponse } from '@shared/types/common.types';

export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  avatar?: string;
  avatar_url?: string;
  gender?: string;
  dob?: string;
  address?: string;
  is_active: boolean;
  role: string;
  roles?: { id: number; name: string }[];
  hod_department?: { id: number; name: string };
  created_at: string;
  updated_at: string;
}

export interface UserFilters {
  page?: number;
  per_page?: number;
  search?: string;
  role?: string;
  status?: string;
}

const userService = {
  list(filters: UserFilters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => { if (v !== undefined && v !== '') params.append(k, String(v)); });
    return get<ApiResponse<PaginatedResponse<User>>>(`/v1/users?${params}`);
  },
  show(id: number) { return get<ApiResponse<User>>(`/v1/users/${id}`); },
  create(payload: FormData | Record<string, unknown>) { return post<ApiResponse<User>>('/v1/users', payload); },
  update(id: number, payload: FormData | Record<string, unknown>) { return put<ApiResponse<User>>(`/v1/users/${id}`, payload); },
  destroy(id: number) { return del<ApiResponse<null>>(`/v1/users/${id}`); },
};
export default userService;
