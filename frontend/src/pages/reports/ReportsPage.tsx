import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { t } from '@/i18n'
import { NepaliDatePicker } from '@/components/nepali-date/NepaliDatePicker'
import { FileText, Download } from 'lucide-react'
import api from '@/lib/api'
import type { ApiResponse } from '@/types'

interface ReportData {
  labels: string[]
  values: number[]
}

export function ReportsPage() {
  const [reportType, setReportType] = useState('department')
  const [fromDate, setFromDate] = useState('')
  const [toDate, setToDate] = useState('')
  const [departmentId, _setDepartmentId] = useState('')

  const { data: report } = useQuery({
    queryKey: ['reports', reportType, fromDate, toDate, departmentId],
    queryFn: async () => {
      const res = await api.get<ApiResponse<ReportData>>('/forms/reports', {
        params: { type: reportType, from_date: fromDate, to_date: toDate, department_id: departmentId }
      })
      return res.data.data
    },
    enabled: false,
  })

  const exportPdf = () => {
    const params = new URLSearchParams({ type: reportType })
    if (fromDate) params.append('from_date', fromDate)
    if (toDate) params.append('to_date', toDate)
    if (departmentId) params.append('department_id', departmentId)
    window.open(`/api/v1/forms/reports/pdf?${params}`, '_blank')
  }

  const exportExcel = () => {
    const params = new URLSearchParams({ type: reportType })
    if (fromDate) params.append('from_date', fromDate)
    if (toDate) params.append('to_date', toDate)
    if (departmentId) params.append('department_id', departmentId)
    window.open(`/api/v1/forms/reports/excel?${params}`, '_blank')
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-title">{t('reports.title') || 'Reports'}</h1>
        <p className="page-subtitle">{t('app.subtitle')}</p>
      </div>

      <div className="card">
        <div className="card-header">
          <h2 className="text-lg font-semibold">{t('common.filter') || 'Filters'}</h2>
        </div>
        <div className="card-body">
          <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
              <label className="form-label">{t('common.filter') || 'Report Type'}</label>
              <select value={reportType} onChange={(e) => setReportType(e.target.value)} className="form-input">
                <option value="department">{t('nav.departments') || 'Department Wise'}</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
                <option value="status">Status Wise</option>
                <option value="user">User Wise</option>
              </select>
            </div>
            <div>
              <label className="form-label">{t('common.filter') || 'From Date'}</label>
              <NepaliDatePicker value={fromDate} onChange={setFromDate} />
            </div>
            <div>
              <label className="form-label">{t('common.filter') || 'To Date'}</label>
              <NepaliDatePicker value={toDate} onChange={setToDate} />
            </div>
            <div className="flex items-end gap-2">
              <button onClick={exportPdf} className="btn-secondary gap-2">
                <FileText className="h-4 w-4" />
                {t('common.pdf')}
              </button>
              <button onClick={exportExcel} className="btn-secondary gap-2">
                <Download className="h-4 w-4" />
                {t('common.excel')}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div className="card">
        <div className="card-header">
          <h2 className="text-lg font-semibold">{t('common.filter') || 'Report Data'}</h2>
        </div>
        <div className="card-body">
          {report?.labels?.length ? (
            <div className="space-y-2">
              {report.labels.map((label, idx) => (
                <div key={label} className="flex items-center gap-4">
                  <span className="w-48 text-sm font-medium">{label}</span>
                  <div className="flex-1 h-6 rounded-full bg-gray-100 overflow-hidden">
                    <div
                      className="h-full rounded-full bg-primary transition-all"
                      style={{ width: `${Math.min(100, ((report.values[idx] || 0) / Math.max(...report.values)) * 100)}%` }}
                    />
                  </div>
                  <span className="text-sm font-medium text-muted w-16 text-right">{report.values[idx]}</span>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-center text-muted py-8">{t('common.noData')}</p>
          )}
        </div>
      </div>
    </div>
  )
}
