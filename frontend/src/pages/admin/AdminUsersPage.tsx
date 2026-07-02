import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { t } from '@/i18n'
import { DataTable } from '@/components/ui/DataTable'
import { StatusBadge } from '@/components/ui/StatusBadge'
import type { User, ApiResponse, PaginatedResponse } from '@/types'
import api from '@/lib/api'

export function AdminUsersPage() {
  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['admin-users', page, search],
    queryFn: async () => {
      const res = await api.get<ApiResponse<PaginatedResponse<User>>>('/admin/users', {
        params: { page, search },
      })
      return res.data.data
    },
  })

  const columns = [
    { key: 'name', header: 'Name', render: (item: User) => (
      <div className="flex items-center gap-2">
        <div className="h-8 w-8 rounded-full bg-primary/20 flex items-center justify-center text-xs font-medium text-primary">
          {item.name?.charAt(0)}
        </div>
        <div>
          <p className="font-medium">{item.name}</p>
          <p className="text-xs text-muted">{item.email}</p>
        </div>
      </div>
    )},
    { key: 'phone', header: 'Phone' },
    { key: 'roles', header: 'Roles', render: (item: User) => (
      <div className="flex gap-1">
        {item.roles?.map((r) => (
          <span key={r} className="badge bg-primary/10 text-primary text-xs">{r}</span>
        ))}
      </div>
    )},
    { key: 'is_active', header: 'Status', render: (item: User) => (
      <StatusBadge status={item.is_active ? 'active' : 'inactive'} />
    )},
  ]

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-title">{t('nav.users')}</h1>
        <p className="page-subtitle">Manage system users</p>
      </div>
      <DataTable
        columns={columns}
        data={(data?.data ?? []) as unknown as Record<string, unknown>[]}
        isLoading={isLoading}
        currentPage={data?.current_page}
        lastPage={data?.last_page}
        onPageChange={setPage}
        onSearch={setSearch}
      />
    </div>
  )
}
