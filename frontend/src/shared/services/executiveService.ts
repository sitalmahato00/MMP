import { get, post, put, del } from '@shared/api/axios';
import type { ApiResponse } from '@shared/types/common.types';

export interface Executive {
  id: number;
  name: string;
  type: 'principal' | 'president';
  designation?: string;
  start_date_bs: string;
  end_date_bs?: string;
  is_current: boolean;
  avatar?: string;
  message?: string;
  order: number;
  created_at: string;
  updated_at: string;
}

export interface ExecutiveFilters {
  search?: string;
  type?: string;
  status?: string;
}

const executiveService = {
  list(filters: ExecutiveFilters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => { if (v !== undefined && v !== '') params.append(k, String(v)); });
    const qs = params.toString();
    return get<ApiResponse<Executive[]>>(`/v1/executives${qs ? `?${qs}` : ''}`);
  },
  show(id: number) { return get<ApiResponse<Executive>>(`/v1/executives/${id}`); },
  create(payload: FormData | Record<string, unknown>) { return post<ApiResponse<Executive>>('/v1/executives', payload); },
  update(id: number, payload: FormData | Record<string, unknown>) { return put<ApiResponse<Executive>>(`/v1/executives/${id}`, payload); },
  destroy(id: number) { return del<ApiResponse<null>>(`/v1/executives/${id}`); },
};
export default executiveService;
