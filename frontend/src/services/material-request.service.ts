import api from '@/lib/api'
import type { MaterialRequest, ApiResponse, PaginatedResponse } from '@/types'

export interface MaterialRequestPayload {
  date_bs: string
  department_id: number
  remarks?: string
  items: { item_name: string; specification?: string; unit: string; quantity: number; remarks?: string }[]
  status?: string
}

export const materialRequestService = {
  async getAll(params?: Record<string, string>) {
    const res = await api.get<ApiResponse<PaginatedResponse<MaterialRequest>>>('/forms/material-requests', { params })
    return res.data.data
  },

  async getById(id: number) {
    const res = await api.get<ApiResponse<MaterialRequest>>(`/forms/material-requests/${id}`)
    return res.data.data
  },

  async create(data: MaterialRequestPayload) {
    const res = await api.post<ApiResponse<MaterialRequest>>('/forms/material-requests', data)
    return res.data.data
  },

  async update(id: number, data: Partial<MaterialRequestPayload>) {
    const res = await api.put<ApiResponse<MaterialRequest>>(`/forms/material-requests/${id}`, data)
    return res.data.data
  },

  async delete(id: number) {
    await api.delete(`/forms/material-requests/${id}`)
  },

  async submit(id: number) {
    const res = await api.post<ApiResponse<MaterialRequest>>(`/forms/material-requests/${id}/submit`)
    return res.data.data
  },

  async duplicate(id: number) {
    const res = await api.post<ApiResponse<MaterialRequest>>(`/forms/material-requests/${id}/duplicate`)
    return res.data.data
  },

  async downloadPdf(id: number) {
    const res = await api.get(`/forms/material-requests/${id}/pdf`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `material-request-${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  },
}
