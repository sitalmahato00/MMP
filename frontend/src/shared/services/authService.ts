import { post } from '@shared/api/axios';
import type { ApiResponse, AuthUser } from '@shared/types/common.types';

export interface LoginPayload {
  email: string;
  password: string;
  otp?: string;
  remember?: boolean;
}

export interface LoginResponse {
  token: string;
  user: AuthUser;
}

export interface LoginApiResponse {
  success: boolean;
  message: string;
  data?: LoginResponse;
  requires_2fa?: boolean;
  two_factor_method?: string;
}

export interface OtpSendPayload  { phone: string; }
export interface OtpVerifyPayload { phone: string; otp: string; }

const authService = {
  /**
   * Standard email+password login (admin/teacher/staff portal)
   */
  login(payload: LoginPayload) {
    return post<LoginApiResponse>('/auth/login', payload);
  },

  /**
   * OTP-based login (student / parent mobile flow)
   */
  sendOtp(payload: OtpSendPayload) {
    return post<ApiResponse<null>>('/auth/otp/send', payload);
  },

  verifyOtp(payload: OtpVerifyPayload) {
    return post<ApiResponse<LoginResponse>>('/auth/otp/verify', payload);
  },

  logout() {
    return post<ApiResponse<null>>('/auth/logout');
  },

  me() {
    return post<ApiResponse<AuthUser>>('/v1/user');
  },

  /**
   * Request password reset link
   */
  forgotPassword(email: string) {
    return post<ApiResponse<null>>('/auth/forgot-password', { email });
  },

  /**
   * Reset password with token
   */
  resetPassword(payload: { email: string; token: string; password: string; password_confirmation: string }) {
    return post<ApiResponse<null>>('/auth/reset-password', payload);
  },
};

export default authService;
