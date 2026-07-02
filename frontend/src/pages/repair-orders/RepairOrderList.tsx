import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { Plus, Printer, FileDown } from 'lucide-react'
import { repairOrderService } from '@/services/repair-order.service'
import { DataTable } from '@/components/ui/DataTable'
import { StatusBadge } from '@/components/ui/StatusBadge'
import { t } from '@/i18n'
import { formatBsDate } from '@/lib/utils'
import type { RepairOrder } from '@/types'

export function RepairOrderList() {
  const navigate = useNavigate()
  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['repair-orders', page, search],
    queryFn: () => repairOrderService.getAll({ page: String(page), search }),
  })

  const columns = [
    { key: 'repair_number', header: t('repairOrder.repairNumber'), render: (item: RepairOrder) => (
      <span className="font-medium">{item.repair_number}</span>
    )},
    { key: 'equipment_name', header: t('repairOrder.equipmentName') },
    { key: 'department', header: t('repairOrder.department'), render: (item: RepairOrder) => item.department?.name },
    { key: 'date_bs', header: t('repairOrder.date'), render: (item: RepairOrder) => formatBsDate(item.date_bs) },
    { key: 'status', header: t('common.status'), render: (item: RepairOrder) => <StatusBadge status={item.status} /> },
    { key: 'estimated_cost', header: t('repairOrder.estimatedCost'), render: (item: RepairOrder) => item.estimated_cost ? `Rs. ${item.estimated_cost}` : '-' },
    { key: 'actions', header: t('common.actions'), render: (item: RepairOrder) => (
      <div className="flex gap-1">
        <button onClick={(e) => { e.stopPropagation(); navigate(`/repair-orders/${item.id}`) }}
          className="btn-secondary px-2 py-1 text-xs">{t('common.view')}</button>
        <button onClick={(e) => { e.stopPropagation(); repairOrderService.downloadPdf(item.id) }}
          className="btn-secondary px-2 py-1 text-xs"><FileDown className="h-3 w-3" /></button>
        <button onClick={(e) => { e.stopPropagation(); window.open(`/repair-orders/${item.id}/print`) }}
          className="btn-secondary px-2 py-1 text-xs"><Printer className="h-3 w-3" /></button>
      </div>
    )},
  ]

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="page-title">{t('repairOrder.title')}</h1>
          <p className="page-subtitle">{t('app.subtitle')}</p>
        </div>
        <button
          onClick={() => navigate('/repair-orders/create')}
          className="btn-primary gap-2"
        >
          <Plus className="h-4 w-4" />
          {t('repairOrder.create')}
        </button>
      </div>

      <DataTable
        columns={columns}
        data={data?.data ?? []}
        isLoading={isLoading}
        onRowClick={(item: RepairOrder) => navigate(`/repair-orders/${item.id}`)}
        currentPage={data?.current_page}
        lastPage={data?.last_page}
        onPageChange={setPage}
        onSearch={setSearch}
      />
    </div>
  )
}
