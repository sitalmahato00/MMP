import api from '@/lib/api'
import type { ApiResponse, DashboardStats } from '@/types'

export const dashboardService = {
  async getStats() {
    const res = await api.get<ApiResponse<DashboardStats>>('/forms/dashboard')
    return res.data.data
  },
}
