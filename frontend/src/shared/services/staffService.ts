import { get, post, put, del } from '@shared/api/axios';
import type { ApiResponse, PaginatedResponse } from '@shared/types/common.types';

export interface StaffMember {
  id: number;
  staff_code: string;
  name: string;
  designation: string;
  department?: string;
  email?: string;
  phone?: string;
  address?: string;
  photo?: string;
  photo_url?: string;
  gender?: string;
  employment_type?: string;
  employment_status: string;
  join_date?: string;
  end_date?: string;
  salary_amount?: number;
  bio?: string;
  public_visible: boolean;
  featured: boolean;
  is_active: boolean;
  order: number;
  created_at: string;
  updated_at: string;
}

export interface StaffFilters {
  page?: number;
  per_page?: number;
  search?: string;
  department?: string;
  designation?: string;
  employment_status?: string;
}

const staffService = {
  list(filters: StaffFilters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => { if (v !== undefined && v !== '') params.append(k, String(v)); });
    return get<ApiResponse<PaginatedResponse<StaffMember>>>(`/v1/staff?${params}`);
  },
  show(id: number) { return get<ApiResponse<StaffMember>>(`/v1/staff/${id}`); },
  create(payload: FormData | Record<string, unknown>) { return post<ApiResponse<StaffMember>>('/v1/staff', payload); },
  update(id: number, payload: FormData | Record<string, unknown>) { return put<ApiResponse<StaffMember>>(`/v1/staff/${id}`, payload); },
  destroy(id: number) { return del<ApiResponse<null>>(`/v1/staff/${id}`); },
  toggleFeatured(id: number) { return put<ApiResponse<StaffMember>>(`/v1/staff/${id}/toggle-featured`); },
  togglePublic(id: number) { return put<ApiResponse<StaffMember>>(`/v1/staff/${id}/toggle-public`); },
};
export default staffService;
