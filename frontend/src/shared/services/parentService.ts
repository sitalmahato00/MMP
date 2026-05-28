import { get, post, put, del } from '@shared/api/axios';
import type { ApiResponse, PaginatedResponse } from '@shared/types/common.types';

export interface Parent {
  id: number;
  user_id: number;
  occupation?: string;
  relation_to_student?: string;
  user: { id: number; name: string; email: string; avatar?: string; phone?: string; is_active: boolean };
  students?: { id: number; student_no: string; user?: { name: string } }[];
  students_count?: number;
  created_at: string;
  updated_at: string;
}

export interface ParentFilters {
  page?: number;
  per_page?: number;
  search?: string;
}

const parentService = {
  list(filters: ParentFilters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => { if (v !== undefined && v !== '') params.append(k, String(v)); });
    return get<ApiResponse<PaginatedResponse<Parent>>>(`/v1/parents?${params}`);
  },
  show(id: number) { return get<ApiResponse<Parent>>(`/v1/parents/${id}`); },
  create(payload: FormData | Record<string, unknown>) { return post<ApiResponse<Parent>>('/v1/parents', payload); },
  update(id: number, payload: FormData | Record<string, unknown>) { return put<ApiResponse<Parent>>(`/v1/parents/${id}`, payload); },
  destroy(id: number) { return del<ApiResponse<null>>(`/v1/parents/${id}`); },
};
export default parentService;
