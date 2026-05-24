// ─── Global API Types ─────────────────────────────────────────────────────────

export interface ApiResponse<T = unknown> {
  success: boolean;
  message: string;
  data: T;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
  links: PaginationLinks;
}

export interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

export interface PaginationLinks {
  first: string | null;
  last: string | null;
  next: string | null;
  prev: string | null;
}

// ─── Auth Types ───────────────────────────────────────────────────────────────

export type UserRole = 'admin' | 'principal' | 'teacher' | 'student' | 'staff' | 'hod' | 'parent' | 'librarian' | 'accountant' | 'hostel_warden';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  phone?: string;
  avatar?: string;
  avatar_url?: string;
  role: UserRole;
  roles?: UserRole[];
  panel_type?: string;
  is_active?: boolean;
  profile?: StudentProfile | TeacherProfile | StaffProfile;
}

export interface AuthState {
  user: AuthUser | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}

// ─── Common Filter / Query Types ─────────────────────────────────────────────

export interface QueryParams {
  page?: number;
  per_page?: number;
  search?: string;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
  [key: string]: string | number | boolean | undefined;
}

// ─── Student Types ────────────────────────────────────────────────────────────

export interface Student {
  id: number;
  user_id: number;
  student_no: string;
  registration_number?: string;
  current_semester: number;
  section?: string;
  batch?: string;
  admission_date?: string;
  guardian_name?: string;
  guardian_phone?: string;
  blood_group?: string;
  roll_number?: string;
  status: 'active' | 'inactive' | 'graduated' | 'suspended' | 'transferred';
  is_archived: boolean;
  user: UserSummary;
  program?: ProgramSummary;
  department?: DepartmentSummary;
  academic_session?: AcademicSessionSummary;
  parents_count?: number;
  created_at: string;
  updated_at: string;
}

export interface StudentProfile {
  student_no: string;
  program?: ProgramSummary;
  department?: DepartmentSummary;
  current_semester: number;
}

// ─── Teacher Types ────────────────────────────────────────────────────────────

export interface Teacher {
  id: number;
  user_id: number;
  employee_id?: string;
  designation?: string;
  qualification?: string;
  joining_date?: string;
  status: 'active' | 'inactive' | 'on_leave';
  user: UserSummary;
  department?: DepartmentSummary;
  subjects?: SubjectSummary[];
}

export interface TeacherProfile {
  employee_id?: string;
  designation?: string;
}

// ─── Staff Types ──────────────────────────────────────────────────────────────

export interface StaffProfile {
  employee_id?: string;
  position?: string;
}

// ─── Academic Types ───────────────────────────────────────────────────────────

export interface AcademicSession {
  id: number;
  name: string;
  name_bs?: string;
  start_date?: string;
  end_date?: string;
  is_current: boolean;
}

export interface Program {
  id: number;
  name: string;
  code?: string;
  duration_years?: number;
  total_semesters?: number;
  department_id?: number;
  department?: DepartmentSummary;
}

export interface Department {
  id: number;
  name: string;
  code?: string;
  hod_id?: number;
}

export interface Subject {
  id: number;
  name: string;
  code: string;
  credit_hours?: number;
  semester?: number;
  program_id?: number;
}

// ─── Summary/Embedded Types ───────────────────────────────────────────────────

export interface UserSummary {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  phone?: string;
}

export interface ProgramSummary    { id: number; name: string; }
export interface DepartmentSummary { id: number; name: string; }
export interface AcademicSessionSummary { id: number; name: string; name_bs?: string; }
export interface SubjectSummary    { id: number; name: string; code: string; }

// ─── Attendance Types ─────────────────────────────────────────────────────────

export interface AttendanceRecord {
  id: number;
  student_id: number;
  subject_id: number;
  date: string;
  status: 'present' | 'absent' | 'late' | 'excused';
  student?: UserSummary;
  subject?: SubjectSummary;
}

export interface AttendanceSummary {
  total: number;
  present: number;
  absent: number;
  late: number;
  percentage: number;
}

// ─── Exam / Marks Types ───────────────────────────────────────────────────────

export interface Exam {
  id: number;
  name: string;
  type: string;
  academic_session_id: number;
  start_date?: string;
  end_date?: string;
  status: 'draft' | 'published' | 'completed';
  is_published: boolean;
  academic_session?: AcademicSessionSummary;
}

export interface Mark {
  id: number;
  student_id: number;
  exam_id: number;
  subject_id: number;
  obtained_marks: number;
  full_marks: number;
  grade?: string;
  remarks?: string;
  exam?: Exam;
  subject?: SubjectSummary;
}

// ─── Notification Types ───────────────────────────────────────────────────────

export interface Notification {
  id: string;
  type: string;
  data: Record<string, unknown>;
  read_at: string | null;
  created_at: string;
}

// ─── Dashboard Stat Types ─────────────────────────────────────────────────────

export interface DashboardStats {
  total_students: number;
  active_students: number;
  total_teachers: number;
  active_teachers: number;
  total_departments: number;
  current_session?: AcademicSessionSummary;
}
