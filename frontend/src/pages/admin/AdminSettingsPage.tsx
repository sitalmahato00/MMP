import { useQuery } from '@tanstack/react-query'
import { t } from '@/i18n'
import { useLocale } from '@/hooks/useLocale'
import type { FormSetting, ApiResponse } from '@/types'
import api from '@/lib/api'

export function AdminSettingsPage() {
  const { locale, setLocale } = useLocale()

  const { data: settings } = useQuery({
    queryKey: ['settings'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<FormSetting[]>>('/settings')
      return res.data.data
    },
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-title">{t('settings.title')}</h1>
        <p className="page-subtitle">System configuration</p>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('settings.language')}</h2>
          </div>
          <div className="card-body space-y-4">
            <div className="flex items-center gap-4">
              <button
                onClick={() => setLocale('en')}
                className={`flex-1 rounded-lg border p-4 text-center transition-all ${locale === 'en' ? 'border-primary bg-primary/5' : 'hover:border-gray-300'}`}
              >
                <p className="font-semibold">{t('language.en')}</p>
                <p className="text-xs text-muted mt-1">English</p>
              </button>
              <button
                onClick={() => setLocale('ne')}
                className={`flex-1 rounded-lg border p-4 text-center transition-all ${locale === 'ne' ? 'border-primary bg-primary/5' : 'hover:border-gray-300'}`}
              >
                <p className="font-semibold">{t('language.ne')}</p>
                <p className="text-xs text-muted mt-1">नेपाली</p>
              </button>
            </div>
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('settings.theme')}</h2>
          </div>
          <div className="card-body">
            <div className="flex items-center gap-3">
              <div className="h-10 w-10 rounded-lg bg-[#A00000]" />
              <div>
                <p className="text-sm font-medium">Deep Red (#A00000)</p>
                <p className="text-xs text-muted">Primary theme color</p>
              </div>
            </div>
          </div>
        </div>

        <div className="card lg:col-span-2">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('settings.title')}</h2>
          </div>
          <div className="card-body">
            {settings?.length ? (
              <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                {settings.map((s) => (
                  <div key={s.id}>
                    <label className="form-label">{s.label}</label>
                    <input
                      type={s.type === 'text' ? 'text' : 'text'}
                      defaultValue={s.value}
                      className="form-input"
                    />
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-center text-muted py-4">{t('common.noData')}</p>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
