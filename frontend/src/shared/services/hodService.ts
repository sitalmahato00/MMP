import { get, post, put, del } from '@shared/api/axios';
import type { ApiResponse, PaginatedResponse } from '@shared/types/common.types';

export interface Hod {
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
  hod_department?: { id: number; name: string };
  created_at: string;
  updated_at: string;
}

export interface HodFilters {
  page?: number;
  per_page?: number;
  search?: string;
  department_id?: number;
  status?: string;
}

const hodService = {
  list(filters: HodFilters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => { if (v !== undefined && v !== '') params.append(k, String(v)); });
    return get<ApiResponse<PaginatedResponse<Hod>>>(`/v1/hods?${params}`);
  },
  show(id: number) { return get<ApiResponse<Hod>>(`/v1/hods/${id}`); },
  create(payload: FormData | Record<string, unknown>) { return post<ApiResponse<Hod>>('/v1/hods', payload); },
  update(id: number, payload: FormData | Record<string, unknown>) { return put<ApiResponse<Hod>>(`/v1/hods/${id}`, payload); },
  destroy(id: number) { return del<ApiResponse<null>>(`/v1/hods/${id}`); },
};
export default hodService;
