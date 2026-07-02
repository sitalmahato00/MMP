import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { approvalService } from '@/services/approval.service'
import { StatusBadge } from '@/components/ui/StatusBadge'
import { t } from '@/i18n'
import { formatBsDate } from '@/lib/utils'
import { CheckCircle2, XCircle, ThumbsUp } from 'lucide-react'

export function ApprovalsPage() {
  const queryClient = useQueryClient()
  const [tab, setTab] = useState<'pending' | 'history'>('pending')
  const [actionModal, setActionModal] = useState<{ type: string; id: number; action: string } | null>(null)
  const [remarks, setRemarks] = useState('')
  const [signature, setSignature] = useState('')

  const { data: pendingData, isLoading: pendingLoading } = useQuery({
    queryKey: ['approvals-pending'],
    queryFn: () => approvalService.getPending(),
  })

  const { data: historyData, isLoading: historyLoading } = useQuery({
    queryKey: ['approvals-history'],
    queryFn: () => approvalService.getHistory(),
  })

  const actionMutation = useMutation({
    mutationFn: async () => {
      if (!actionModal) return
      const { type, id, action } = actionModal
      if (action === 'approve') return approvalService.approve(type, id, { remarks, signature })
      if (action === 'recommend') return approvalService.recommend(type, id, { remarks, signature })
      if (action === 'reject') return approvalService.reject(type, id, { remarks })
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['approvals-pending'] })
      queryClient.invalidateQueries({ queryKey: ['approvals-history'] })
      setActionModal(null)
      setRemarks('')
      setSignature('')
    },
  })

  const pendingList = pendingData?.submissions?.data ?? []

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-title">{t('approval.title')}</h1>
        <p className="page-subtitle">{t('app.subtitle')}</p>
      </div>

      <div className="flex gap-1 rounded-lg bg-gray-100 p-1 w-fit">
        <button
          onClick={() => setTab('pending')}
          className={`rounded-md px-4 py-2 text-sm font-medium transition-colors ${tab === 'pending' ? 'bg-white shadow-sm' : 'text-muted hover:text-gray-900'}`}
        >
          {t('approval.pending')}
        </button>
        <button
          onClick={() => setTab('history')}
          className={`rounded-md px-4 py-2 text-sm font-medium transition-colors ${tab === 'history' ? 'bg-white shadow-sm' : 'text-muted hover:text-gray-900'}`}
        >
          {t('approval.history')}
        </button>
      </div>

      {tab === 'pending' && (
        <div className="space-y-4">
          {pendingLoading ? (
            <div className="space-y-3">
              {[1, 2, 3].map((i) => <div key={i} className="h-20 animate-pulse rounded-xl bg-gray-100" />)}
            </div>
          ) : pendingList.length === 0 ? (
            <div className="card p-12 text-center text-muted">{t('approval.noPending')}</div>
          ) : (
            pendingList.map((item: { type: string; id: number; number: string; applicant: string; department: string; status: string; date_bs: string }) => (
              <div key={`${item.type}-${item.id}`} className="card p-4">
                <div className="flex items-center justify-between">
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="font-medium">{item.number}</span>
                      <StatusBadge status={item.status} />
                    </div>
                    <p className="text-sm text-muted">{item.applicant} - {item.department}</p>
                    <p className="text-xs text-muted">{formatBsDate(item.date_bs)}</p>
                  </div>
                  <div className="flex gap-2">
                    <button
                      onClick={() => setActionModal({ type: item.type, id: item.id, action: 'recommend' })}
                      className="btn-outline gap-1 text-sm"
                    >
                      <ThumbsUp className="h-4 w-4" />
                      {t('approval.recommend')}
                    </button>
                    <button
                      onClick={() => setActionModal({ type: item.type, id: item.id, action: 'approve' })}
                      className="btn-primary gap-1 text-sm"
                    >
                      <CheckCircle2 className="h-4 w-4" />
                      {t('approval.approve')}
                    </button>
                    <button
                      onClick={() => setActionModal({ type: item.type, id: item.id, action: 'reject' })}
                      className="btn-secondary gap-1 text-sm text-red-600"
                    >
                      <XCircle className="h-4 w-4" />
                      {t('approval.reject')}
                    </button>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      )}

      {tab === 'history' && (
        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('approval.history')}</h2>
          </div>
          <div className="card-body">
            {historyLoading ? (
              <div className="space-y-3">
                {[1, 2, 3].map((i) => <div key={i} className="h-12 animate-pulse rounded bg-gray-100" />)}
              </div>
            ) : (
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b bg-gray-50">
                    <th className="px-3 py-2 text-left font-medium text-muted">User</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">Action</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">Status</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">Remarks</th>
                    <th className="px-3 py-2 text-left font-medium text-muted">Date</th>
                  </tr>
                </thead>
                <tbody>
                  {historyData?.data?.length ? historyData.data.map((item: { id: number; user?: { name: string }; role: string; status: string; remarks?: string; date_bs: string }) => (
                    <tr key={item.id} className="border-b">
                      <td className="px-3 py-2">{item.user?.name}</td>
                      <td className="px-3 py-2">{item.role}</td>
                      <td className="px-3 py-2"><StatusBadge status={item.status} /></td>
                      <td className="px-3 py-2 text-muted">{item.remarks || '-'}</td>
                      <td className="px-3 py-2 text-muted">{formatBsDate(item.date_bs)}</td>
                    </tr>
                  )) : (
                    <tr>
                      <td colSpan={5} className="px-3 py-8 text-center text-muted">{t('common.noData')}</td>
                    </tr>
                  )}
                </tbody>
              </table>
            )}
          </div>
        </div>
      )}

      {actionModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div className="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h3 className="text-lg font-semibold mb-4 capitalize">{actionModal.action}</h3>
            <div className="space-y-4">
              <div>
                <label className="form-label">{t('approval.remarks')}</label>
                <textarea
                  value={remarks}
                  onChange={(e) => setRemarks(e.target.value)}
                  rows={3}
                  className="form-input"
                />
              </div>
              <div>
                <label className="form-label">{t('approval.signature')}</label>
                <input
                  type="text"
                  value={signature}
                  onChange={(e) => setSignature(e.target.value)}
                  className="form-input"
                  placeholder="Type your full name as signature"
                />
              </div>
              <div className="flex justify-end gap-2">
                <button onClick={() => setActionModal(null)} className="btn-secondary">
                  {t('common.cancel')}
                </button>
                <button
                  onClick={() => actionMutation.mutate()}
                  disabled={actionMutation.isPending}
                  className="btn-primary"
                >
                  {actionMutation.isPending ? t('common.loading') : t('approval.confirm')}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
