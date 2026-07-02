import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { Plus, Printer, FileDown, Copy } from 'lucide-react'
import { materialRequestService } from '@/services/material-request.service'
import { DataTable } from '@/components/ui/DataTable'
import { StatusBadge } from '@/components/ui/StatusBadge'
import { t } from '@/i18n'
import { formatBsDate } from '@/lib/utils'
import type { MaterialRequest } from '@/types'

export function MaterialRequestList() {
  const navigate = useNavigate()
  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['material-requests', page, search],
    queryFn: () => materialRequestService.getAll({ page: String(page), search }),
  })

  const columns = [
    { key: 'request_number', header: t('materialRequest.requestNumber'), render: (item: MaterialRequest) => (
      <span className="font-medium">{item.request_number}</span>
    )},
    { key: 'user', header: t('materialRequest.applicant'), render: (item: MaterialRequest) => item.user?.name },
    { key: 'department', header: t('materialRequest.department'), render: (item: MaterialRequest) => item.department?.name },
    { key: 'date_bs', header: t('materialRequest.date'), render: (item: MaterialRequest) => formatBsDate(item.date_bs) },
    { key: 'status', header: t('common.status'), render: (item: MaterialRequest) => <StatusBadge status={item.status} /> },
    { key: 'actions', header: t('common.actions'), render: (item: MaterialRequest) => (
      <div className="flex gap-1">
        <button onClick={(e) => { e.stopPropagation(); navigate(`/material-requests/${item.id}`) }}
          className="btn-secondary px-2 py-1 text-xs">{t('common.view')}</button>
        <button onClick={(e) => { e.stopPropagation(); materialRequestService.downloadPdf(item.id) }}
          className="btn-secondary px-2 py-1 text-xs"><FileDown className="h-3 w-3" /></button>
        <button onClick={(e) => { e.stopPropagation(); materialRequestService.duplicate(item.id) }}
          className="btn-secondary px-2 py-1 text-xs"><Copy className="h-3 w-3" /></button>
        <button onClick={(e) => { e.stopPropagation(); window.open(`/material-requests/${item.id}/print`) }}
          className="btn-secondary px-2 py-1 text-xs"><Printer className="h-3 w-3" /></button>
      </div>
    )},
  ]

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="page-title">{t('materialRequest.title')}</h1>
          <p className="page-subtitle">{t('app.subtitle')}</p>
        </div>
        <button
          onClick={() => navigate('/material-requests/create')}
          className="btn-primary gap-2"
        >
          <Plus className="h-4 w-4" />
          {t('materialRequest.create')}
        </button>
      </div>

      <DataTable
        columns={columns}
        data={data?.data ?? []}
        isLoading={isLoading}
        onRowClick={(item: MaterialRequest) => navigate(`/material-requests/${item.id}`)}
        currentPage={data?.current_page}
        lastPage={data?.last_page}
        onPageChange={setPage}
        onSearch={setSearch}
      />
    </div>
  )
}
