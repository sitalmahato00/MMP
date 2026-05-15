import { post } from '@shared/api/axios';
import type { ApiResponse, AuthUser } from '@shared/types/common.types';

export interface LoginPayload {
  email: string;
  password: string;
}

export interface LoginResponse {
  token: string;
  user: AuthUser;
}

export interface OtpSendPayload  { phone: string; }
export interface OtpVerifyPayload { phone: string; otp: string; }

const authService = {
  /**
   * Standard email+password login (admin/teacher/staff portal)
   */
  login(payload: LoginPayload) {
    return post<ApiResponse<LoginResponse>>('/auth/login', payload);
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
};

export default authService;
