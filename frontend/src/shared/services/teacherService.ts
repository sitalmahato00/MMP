import { get, post, put, del } from '@shared/api/axios';
import type { ApiResponse, PaginatedResponse, Teacher, QueryParams } from '@shared/types/common.types';

export interface TeacherFilters extends QueryParams {
  department_id?: number;
  status?: string;
}

const teacherService = {
  list(filters: TeacherFilters = {}) {
    const params = new URLSearchParams(
      Object.entries(filters)
        .filter(([, v]) => v !== undefined)
        .map(([k, v]) => [k, String(v)])
    );
    return get<ApiResponse<PaginatedResponse<Teacher>>>(`/v1/teachers?${params}`);
  },

  show(id: number) {
    return get<ApiResponse<Teacher>>(`/v1/teachers/${id}`);
  },

  create(payload: Record<string, unknown>) {
    return post<ApiResponse<Teacher>>('/v1/teachers', payload);
  },

  update(id: number, payload: Record<string, unknown>) {
    return put<ApiResponse<Teacher>>(`/v1/teachers/${id}`, payload);
  },

  destroy(id: number) {
    return del<ApiResponse<null>>(`/v1/teachers/${id}`);
  },
};

export default teacherService;
