import { useParams, useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { materialRequestService } from '@/services/material-request.service'
import { StatusBadge } from '@/components/ui/StatusBadge'
import { t } from '@/i18n'
import { formatBsDate } from '@/lib/utils'
import { ArrowLeft, Printer, FileDown, Copy, Edit } from 'lucide-react'
import type { Approval } from '@/types'

export function MaterialRequestDetail() {
  const { id } = useParams()
  const navigate = useNavigate()

  const { data: form, isLoading } = useQuery({
    queryKey: ['material-request', id],
    queryFn: () => materialRequestService.getById(Number(id)),
    enabled: !!id,
  })

  if (isLoading) {
    return <div className="flex h-64 items-center justify-center text-muted">{t('common.loading')}</div>
  }

  if (!form) {
    return <div className="flex h-64 items-center justify-center text-muted">{t('common.noData')}</div>
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate('/material-requests')} className="btn-secondary">
            <ArrowLeft className="h-4 w-4" />
          </button>
          <div>
            <h1 className="page-title">{form.request_number}</h1>
            <p className="page-subtitle">
              <StatusBadge status={form.status} />
              <span className="ml-2">{formatBsDate(form.date_bs)}</span>
            </p>
          </div>
        </div>
        <div className="flex gap-2">
          {form.status === 'draft' && (
            <button onClick={() => navigate(`/material-requests/${id}/edit`)} className="btn-primary gap-2">
              <Edit className="h-4 w-4" />
              {t('common.edit')}
            </button>
          )}
          <button onClick={() => navigate(`/material-requests/${id}/edit`)} className="btn-secondary gap-2">
            <Printer className="h-4 w-4" />
            {t('materialRequest.print')}
          </button>
          <button onClick={() => materialRequestService.downloadPdf(Number(id))} className="btn-secondary gap-2">
            <FileDown className="h-4 w-4" />
            {t('materialRequest.downloadPdf')}
          </button>
          <button onClick={() => materialRequestService.duplicate(Number(id))} className="btn-secondary gap-2">
            <Copy className="h-4 w-4" />
            {t('materialRequest.duplicate')}
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2 space-y-6">
          <div className="card">
            <div className="card-header">
              <h2 className="text-lg font-semibold">{t('materialRequest.items')}</h2>
            </div>
            <div className="card-body">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b bg-gray-50">
                    <th className="px-3 py-2 text-left font-medium text-muted">#</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.itemName')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.specification')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.unit')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.quantity')}</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">{t('materialRequest.remarks')}</th>
                  </tr>
                </thead>
                <tbody>
                  {form.items.map((item, idx) => (
                    <tr key={item.id} className="border-b">
                      <td className="px-3 py-2 text-muted">{idx + 1}</td>
                      <td className="px-3 py-2 font-medium">{item.item_name}</td>
                      <td className="px-3 py-2 text-muted">{item.specification || '-'}</td>
                      <td className="px-3 py-2">{item.unit}</td>
                      <td className="px-3 py-2">{item.quantity}</td>
                      <td className="px-3 py-2 text-muted">{item.remarks || '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          {form.remarks && (
            <div className="card">
              <div className="card-header">
                <h2 className="text-lg font-semibold">{t('materialRequest.remarks')}</h2>
              </div>
              <div className="card-body">
                <p className="text-sm text-gray-600">{form.remarks}</p>
              </div>
            </div>
          )}
        </div>

        <div className="space-y-6">
          <div className="card">
            <div className="card-header">
              <h2 className="text-lg font-semibold">Details</h2>
            </div>
            <div className="card-body space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-muted">{t('materialRequest.requestNumber')}</span>
                <span className="font-medium">{form.request_number}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted">{t('materialRequest.date')}</span>
                <span className="font-medium">{formatBsDate(form.date_bs)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted">{t('materialRequest.applicant')}</span>
                <span className="font-medium">{form.user?.name}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted">{t('materialRequest.department')}</span>
                <span className="font-medium">{form.department?.name}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted">{t('common.status')}</span>
                <StatusBadge status={form.status} />
              </div>
            </div>
          </div>

          {form.approvals && form.approvals.length > 0 && (
            <div className="card">
              <div className="card-header">
                <h2 className="text-lg font-semibold">Approvals</h2>
              </div>
              <div className="card-body space-y-3">
                {form.approvals.map((approval: Approval) => (
                  <div key={approval.id} className="rounded-lg border p-3 text-sm">
                    <div className="flex items-center justify-between mb-1">
                      <span className="font-medium">{approval.user?.name}</span>
                      <StatusBadge status={approval.status} />
                    </div>
                    <p className="text-xs text-muted">{approval.role}</p>
                    {approval.remarks && <p className="mt-1 text-gray-600">{approval.remarks}</p>}
                    <p className="mt-1 text-xs text-muted">{approval.date_bs}</p>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
