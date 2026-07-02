import api from '@/lib/api'
import type { RepairOrder, ApiResponse, PaginatedResponse } from '@/types'

export interface RepairOrderPayload {
  date_bs: string
  department_id: number
  equipment_name: string
  problem_description: string
  estimated_cost?: number
  remarks?: string
  status?: string
}

export const repairOrderService = {
  async getAll(params?: Record<string, string>) {
    const res = await api.get<ApiResponse<PaginatedResponse<RepairOrder>>>('/forms/repair-orders', { params })
    return res.data.data
  },

  async getById(id: number) {
    const res = await api.get<ApiResponse<RepairOrder>>(`/forms/repair-orders/${id}`)
    return res.data.data
  },

  async create(data: RepairOrderPayload) {
    const res = await api.post<ApiResponse<RepairOrder>>('/forms/repair-orders', data)
    return res.data.data
  },

  async update(id: number, data: Partial<RepairOrderPayload>) {
    const res = await api.put<ApiResponse<RepairOrder>>(`/forms/repair-orders/${id}`, data)
    return res.data.data
  },

  async delete(id: number) {
    await api.delete(`/forms/repair-orders/${id}`)
  },

  async submit(id: number) {
    const res = await api.post<ApiResponse<RepairOrder>>(`/forms/repair-orders/${id}/submit`)
    return res.data.data
  },

  async downloadPdf(id: number) {
    const res = await api.get(`/forms/repair-orders/${id}/pdf`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `repair-order-${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  },
}
