import { get, post, put, del } from '@shared/api/axios';
import type { ApiResponse, PaginatedResponse, Student, QueryParams } from '@shared/types/common.types';

export interface StudentFilters extends QueryParams {
  department_id?: number;
  program_id?:    number;
  academic_session_id?: number;
  semester?: number;
  status?: string;
}

export interface CreateStudentPayload {
  name: string;
  email: string;
  phone?: string;
  password: string;
  department_id: number;
  program_id: number;
  academic_session_id: number;
  student_no: string;
  current_semester: number;
  section?: string;
  batch?: string;
  admission_date?: string;
  guardian_name?: string;
  guardian_phone?: string;
  blood_group?: string;
}

export type UpdateStudentPayload = Partial<CreateStudentPayload>;

function buildQuery(filters: StudentFilters): string {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([k, v]) => {
    if (v !== undefined && v !== '' && v !== null) {
      params.append(k, String(v));
    }
  });
  return params.toString() ? `?${params.toString()}` : '';
}

const studentService = {
  list(filters: StudentFilters = {}) {
    return get<ApiResponse<PaginatedResponse<Student>>>(`/v1/students${buildQuery(filters)}`);
  },

  show(id: number) {
    return get<ApiResponse<Student>>(`/v1/students/${id}`);
  },

  create(payload: CreateStudentPayload) {
    return post<ApiResponse<Student>>('/v1/students', payload);
  },

  update(id: number, payload: UpdateStudentPayload) {
    return put<ApiResponse<Student>>(`/v1/students/${id}`, payload);
  },

  destroy(id: number) {
    return del<ApiResponse<null>>(`/v1/students/${id}`);
  },

  restore(id: number) {
    return post<ApiResponse<Student>>(`/v1/students/${id}/restore`);
  },

  exportCsv(filters: StudentFilters = {}) {
    return get<Blob>(`/v1/students/export${buildQuery(filters)}`, {
      responseType: 'blob',
    });
  },
};

export default studentService;
