import { get, post, put, del } from '@shared/api/axios';
import type { ApiResponse, PaginatedResponse } from '@shared/types/common.types';

export interface Alumni {
  id: number;
  user_id: number;
  department_id: number;
  program_id: number;
  graduation_year?: string;
  admission_year?: string;
  roll_number?: string;
  current_job?: string;
  company_name?: string;
  work_location?: string;
  employment_status?: string;
  bio?: string;
  linkedin_url?: string;
  github_url?: string;
  portfolio_url?: string;
  achievements?: string;
  is_featured: boolean;
  is_active: boolean;
  is_verified: boolean;
  visibility: string;
  user: { id: number; name: string; email: string; avatar?: string; phone?: string; address?: string; is_active?: boolean };
  department?: { id: number; name: string };
  program?: { id: number; name: string };
  projects?: { id: number; title: string }[];
  created_at: string;
  updated_at: string;
}

export interface AlumniFilters {
  page?: number;
  per_page?: number;
  search?: string;
  department_id?: number;
  program_id?: number;
  graduation_year?: string;
  employment_status?: string;
}

const alumniService = {
  list(filters: AlumniFilters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => { if (v !== undefined && v !== '') params.append(k, String(v)); });
    return get<ApiResponse<PaginatedResponse<Alumni>>>(`/v1/alumni?${params}`);
  },
  show(id: number) { return get<ApiResponse<Alumni>>(`/v1/alumni/${id}`); },
  create(payload: FormData | Record<string, unknown>) { return post<ApiResponse<Alumni>>('/v1/alumni', payload); },
  update(id: number, payload: FormData | Record<string, unknown>) { return put<ApiResponse<Alumni>>(`/v1/alumni/${id}`, payload); },
  destroy(id: number) { return del<ApiResponse<null>>(`/v1/alumni/${id}`); },
  toggleFeatured(id: number) { return put<ApiResponse<Alumni>>(`/v1/alumni/${id}/toggle-featured`); },
};
export default alumniService;
