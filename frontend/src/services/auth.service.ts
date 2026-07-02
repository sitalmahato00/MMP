import api from '@/lib/api'
import type { User, ApiResponse } from '@/types'

export const authService = {
  async login(email: string, password: string) {
    const res = await api.post<ApiResponse<{ user: User; token: string }>>('/auth/login', { email, password })
    return res.data.data
  },

  async logout() {
    await api.post('/auth/logout')
  },

  async getUser() {
    const res = await api.get<ApiResponse<{ user: User }>>('/user')
    return res.data.data.user
  },

  async updateProfile(data: Partial<User>) {
    const res = await api.put<ApiResponse<{ user: User }>>('/user/profile', data)
    return res.data.data.user
  },

  async changePassword(currentPassword: string, newPassword: string) {
    await api.post('/user/change-password', {
      current_password: currentPassword,
      password: newPassword,
      password_confirmation: newPassword,
    })
  },
}
