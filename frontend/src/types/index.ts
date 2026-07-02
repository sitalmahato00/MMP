export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  address?: string;
  department_id?: number;
  department?: Department;
  avatar_url?: string;
  signature?: string;
  designation?: string;
  roles: string[];
  permissions: string[];
  is_active: boolean;
  created_at: string;
}

export interface Department {
  id: number;
  name: string;
  code: string;
  description?: string;
  is_active: boolean;
  created_at: string;
}

export interface MaterialRequest {
  id: number;
  request_number: string;
  date_bs: string;
  date_ad?: string;
  user_id: number;
  user?: User;
  department_id: number;
  department?: Department;
  status: FormStatus;
  remarks?: string;
  items: MaterialRequestItem[];
  approvals: Approval[];
  created_at: string;
  updated_at: string;
}

export interface MaterialRequestItem {
  id: number;
  material_request_id: number;
  item_name: string;
  specification?: string;
  unit: string;
  quantity: number;
  remarks?: string;
}

export interface RepairOrder {
  id: number;
  repair_number: string;
  date_bs: string;
  date_ad?: string;
  user_id: number;
  user?: User;
  department_id: number;
  department?: Department;
  equipment_name: string;
  problem_description: string;
  estimated_cost?: number;
  approved_cost?: number;
  status: FormStatus;
  remarks?: string;
  approvals: Approval[];
  created_at: string;
  updated_at: string;
}

export interface Approval {
  id: number;
  approvable_type: string;
  approvable_id: number;
  user_id: number;
  user?: User;
  role: string;
  status: 'pending' | 'recommended' | 'approved' | 'rejected';
  remarks?: string;
  signature?: string;
  date_bs: string;
  time: string;
  ip_address: string;
  created_at: string;
}

export type FormStatus = 'draft' | 'submitted' | 'recommended' | 'approved' | 'rejected' | 'printed' | 'completed';

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;
}

export interface DashboardStats {
  total_requests: number;
  pending: number;
  recommended: number;
  approved: number;
  rejected: number;
  printed: number;
  draft: number;
  recent_forms: (MaterialRequest | RepairOrder)[];
  recent_activities: ActivityLog[];
}

export interface ActivityLog {
  id: number;
  user_id: number;
  user?: User;
  action: string;
  model_type: string;
  model_id?: number;
  old_values?: Record<string, unknown>;
  new_values?: Record<string, unknown>;
  ip_address: string;
  created_at: string;
}

export interface Notification {
  id: number;
  type: string;
  data: Record<string, unknown>;
  read_at?: string;
  created_at: string;
}

export interface FormSetting {
  id: number;
  key: string;
  value: string;
  group: string;
  label: string;
  type: string;
}
