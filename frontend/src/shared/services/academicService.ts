import { get } from '@shared/api/axios';
import type { ApiResponse, DashboardStats, AcademicSession, Program, Department } from '@shared/types/common.types';

const academicService = {
  dashboardStats() {
    return get<ApiResponse<DashboardStats>>('/v1/dashboard/stats');
  },
  adminDashboard() {
    return get<any>('/v1/dashboard/admin');
  },

  sessions() {
    return get<ApiResponse<AcademicSession[]>>('/v1/academic/sessions');
  },

  currentSession() {
    return get<ApiResponse<AcademicSession>>('/v1/academic/sessions/current');
  },

  programs(departmentId?: number) {
    const q = departmentId ? `?department_id=${departmentId}` : '';
    return get<ApiResponse<Program[]>>(`/v1/academic/programs${q}`);
  },

  departments() {
    return get<ApiResponse<Department[]>>('/v1/departments');
  },
};

export default academicService;
