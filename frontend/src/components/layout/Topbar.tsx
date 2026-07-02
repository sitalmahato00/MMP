import { Bell, User, Search } from 'lucide-react'
import { useAuth } from '@/stores/auth'
import { t } from '@/i18n'

export function Topbar() {
  const { user } = useAuth()

  return (
    <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-white px-6">
      <div className="flex items-center gap-4">
        <div className="relative">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
          <input
            type="text"
            placeholder={t('common.search')}
            className="form-input w-64 pl-9"
          />
        </div>
      </div>

      <div className="flex items-center gap-4">
        <button className="relative rounded-lg p-2 hover:bg-gray-100">
          <Bell className="h-5 w-5 text-gray-600" />
          <span className="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-danger" />
        </button>

        <div className="flex items-center gap-3">
          <div className="text-right">
            <p className="text-sm font-medium text-gray-900">{user?.name}</p>
            <p className="text-xs text-muted">{user?.roles?.[0]}</p>
          </div>
          <div className="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-white text-sm font-medium">
            {user?.name?.charAt(0)?.toUpperCase() || <User className="h-5 w-5" />}
          </div>
        </div>
      </div>
    </header>
  )
}
