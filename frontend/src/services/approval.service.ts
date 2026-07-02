import api from '@/lib/api'
import type { Approval, ApiResponse, PaginatedResponse } from '@/types'

export const approvalService = {
  async getPending() {
    const res = await api.get<ApiResponse<{ submissions: PaginatedResponse<{ type: string; id: number; number: string; applicant: string; department: string; status: string; date_bs: string }> }>>('/forms/approvals/pending')
    return res.data.data
  },

  async getHistory() {
    const res = await api.get<ApiResponse<PaginatedResponse<Approval>>>('/forms/approvals/history')
    return res.data.data
  },

  async approve(type: string, id: number, data: { remarks?: string; signature?: string }) {
    const res = await api.post<ApiResponse<Approval>>(`/forms/approvals/${type}/${id}/approve`, data)
    return res.data.data
  },

  async recommend(type: string, id: number, data: { remarks?: string; signature?: string }) {
    const res = await api.post<ApiResponse<Approval>>(`/forms/approvals/${type}/${id}/recommend`, data)
    return res.data.data
  },

  async reject(type: string, id: number, data: { remarks: string }) {
    const res = await api.post<ApiResponse<Approval>>(`/forms/approvals/${type}/${id}/reject`, data)
    return res.data.data
  },
}
