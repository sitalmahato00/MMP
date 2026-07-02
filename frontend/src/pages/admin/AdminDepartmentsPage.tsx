import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { t } from '@/i18n'
import { DataTable } from '@/components/ui/DataTable'
import { StatusBadge } from '@/components/ui/StatusBadge'
import type { Department, ApiResponse } from '@/types'
import api from '@/lib/api'

export function AdminDepartmentsPage() {
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['admin-departments', search],
    queryFn: async () => {
      const res = await api.get<ApiResponse<Department[]>>('/departments', {
        params: { search },
      })
      return res.data.data
    },
  })

  const columns = [
    { key: 'code', header: 'Code', render: (item: Department) => (
      <span className="font-medium">{item.code}</span>
    )},
    { key: 'name', header: 'Name' },
    { key: 'description', header: 'Description', render: (item: Department) => item.description || '-' },
    { key: 'is_active', header: 'Status', render: (item: Department) => (
      <StatusBadge status={item.is_active ? 'active' : 'inactive'} />
    )},
  ]

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-title">{t('nav.departments')}</h1>
        <p className="page-subtitle">Manage departments</p>
      </div>
      <DataTable
        columns={columns}
        data={(data ?? []) as unknown as Record<string, unknown>[]}
        isLoading={isLoading}
        onSearch={setSearch}
      />
    </div>
  )
}
