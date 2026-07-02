import { useQuery } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { dashboardService } from '@/services/dashboard.service'
import { StatCard } from '@/components/ui/StatCard'
import { StatusBadge } from '@/components/ui/StatusBadge'
import { DataTable } from '@/components/ui/DataTable'
import { t } from '@/i18n'
import { formatBsDate } from '@/lib/utils'
import {
  FileText, Clock, ThumbsUp, CheckCircle2, XCircle, Printer, FileEdit, Wrench,
} from 'lucide-react'
import type { ActivityLog } from '@/types'

interface RecentForm {
  id: number
  number: string
  type: string
  applicant: string
  department: string
  status: string
  date_bs: string
}

export function DashboardPage() {
  const navigate = useNavigate()
  const { data: stats, isLoading } = useQuery({
    queryKey: ['dashboard'],
    queryFn: () => dashboardService.getStats(),
  })

  const recentFormsColumns: { key: string; header: string; render: (item: RecentForm) => React.ReactNode }[] = [
    { key: 'number', header: 'Form No', render: (item: RecentForm) => (
      <span className="font-medium">{item.number}</span>
    )},
    { key: 'type', header: 'Type', render: (item: RecentForm) => (
      <span className={item.type === 'material' ? 'text-blue-600' : 'text-orange-600'}>
        {item.type === 'material' ? 'Material' : 'Repair'}
      </span>
    )},
    { key: 'department', header: t('materialRequest.department') },
    { key: 'status', header: t('common.status'), render: (item: RecentForm) => (
      <StatusBadge status={item.status} />
    )},
    { key: 'date_bs', header: t('materialRequest.date'), render: (item: RecentForm) => (
      <span className="text-sm text-muted">{formatBsDate(item.date_bs)}</span>
    )},
  ]

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-title">{t('dashboard.title')}</h1>
        <p className="page-subtitle">{t('dashboard.quickActions')}</p>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
        <StatCard title={t('dashboard.totalForms')} value={stats?.total_requests ?? 0} icon={FileText} color="text-gray-600" />
        <StatCard title={t('dashboard.draft')} value={stats?.draft ?? 0} icon={FileEdit} color="text-gray-500" />
        <StatCard title={t('dashboard.pending')} value={stats?.pending ?? 0} icon={Clock} color="text-blue-600" />
        <StatCard title={t('dashboard.recommended')} value={stats?.recommended ?? 0} icon={ThumbsUp} color="text-yellow-600" />
        <StatCard title={t('dashboard.approved')} value={stats?.approved ?? 0} icon={CheckCircle2} color="text-green-600" />
        <StatCard title={t('dashboard.rejected')} value={stats?.rejected ?? 0} icon={XCircle} color="text-red-600" />
        <StatCard title={t('dashboard.printed')} value={stats?.printed ?? 0} icon={Printer} color="text-purple-600" />
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <div className="card">
            <div className="card-header">
              <h2 className="text-lg font-semibold">{t('dashboard.recentForms')}</h2>
            </div>
            <DataTable
              columns={recentFormsColumns}
              data={(stats?.recent_forms ?? []) as RecentForm[]}
              isLoading={isLoading}
              onRowClick={(item: RecentForm) => {
                const base = item.type === 'material' ? 'material-requests' : 'repair-orders'
                navigate(`/${base}/${item.id}`)
              }}
            />
          </div>
        </div>

        <div className="space-y-4">
          <div className="card">
            <div className="card-header">
              <h2 className="text-lg font-semibold">{t('dashboard.quickActions')}</h2>
            </div>
            <div className="card-body space-y-3">
              <button
                onClick={() => navigate('/material-requests/create')}
                className="btn-primary w-full justify-start gap-2"
              >
                <FileEdit className="h-4 w-4" />
                {t('dashboard.newMaterialRequest')}
              </button>
              <button
                onClick={() => navigate('/repair-orders/create')}
                className="btn-primary w-full justify-start gap-2"
              >
                <Wrench className="h-4 w-4" />
                {t('dashboard.newRepairOrder')}
              </button>
            </div>
          </div>

          <div className="card">
            <div className="card-header">
              <h2 className="text-lg font-semibold">{t('dashboard.recentActivities')}</h2>
            </div>
            <div className="card-body">
              {isLoading ? (
                <div className="space-y-3">
                  {[1, 2, 3].map((i) => (
                    <div key={i} className="h-12 animate-pulse rounded bg-gray-100" />
                  ))}
                </div>
              ) : stats?.recent_activities?.length ? (
                <div className="space-y-3">
                  {(stats.recent_activities as ActivityLog[]).slice(0, 5).map((activity) => (
                    <div key={activity.id} className="flex items-start gap-3 text-sm">
                      <div className="mt-0.5 h-2 w-2 rounded-full bg-primary shrink-0" />
                      <div>
                        <p className="text-gray-900">{activity.action}</p>
                        <p className="text-xs text-muted">{activity.created_at}</p>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted">{t('common.noData')}</p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
