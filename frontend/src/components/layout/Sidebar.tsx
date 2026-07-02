import { NavLink } from 'react-router-dom'
import {
  LayoutDashboard, ClipboardList, Wrench, CheckCircle2,
  FileText, Settings, Users, Building2, ChevronLeft, LogOut, Languages,
} from 'lucide-react'
import { t } from '@/i18n'
import { useAuth } from '@/stores/auth'
import { usePermission } from '@/hooks/usePermission'
import { useLocale } from '@/hooks/useLocale'
import { cn } from '@/lib/utils'

interface SidebarProps {
  collapsed: boolean
  onToggle: () => void
}

export function Sidebar({ collapsed, onToggle }: SidebarProps) {
  const { logout } = useAuth()
  const { isAdmin } = usePermission()
  const { locale, setLocale } = useLocale()

  const navItems = [
    { to: '/dashboard', icon: LayoutDashboard, label: 'nav.dashboard' },
    { to: '/material-requests', icon: ClipboardList, label: 'nav.materialRequests' },
    { to: '/repair-orders', icon: Wrench, label: 'nav.repairOrders' },
    { to: '/approvals', icon: CheckCircle2, label: 'nav.approvals' },
    { to: '/reports', icon: FileText, label: 'nav.reports' },
    ...(isAdmin ? [
      { to: '/admin/users', icon: Users, label: 'nav.users' },
      { to: '/admin/departments', icon: Building2, label: 'nav.departments' },
      { to: '/admin/settings', icon: Settings, label: 'nav.settings' },
    ] : []),
  ]

  return (
    <aside
      className={cn(
        'fixed left-0 top-0 z-40 flex h-screen flex-col border-r border-gray-200 bg-white transition-all duration-300',
        collapsed ? 'w-16' : 'w-64',
      )}
    >
      <div className={cn('flex h-16 items-center border-b px-4', collapsed ? 'justify-center' : 'justify-between')}>
        {!collapsed && (
          <div className="flex items-center gap-2">
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white text-xs font-bold">
              MMP
            </div>
            <div>
              <p className="text-sm font-bold text-gray-900 leading-tight">MMP</p>
              <p className="text-[10px] text-muted leading-tight">{t('app.name')}</p>
            </div>
          </div>
        )}
        <button onClick={onToggle} className="rounded p-1 hover:bg-gray-100">
          <ChevronLeft className={cn('h-4 w-4 transition-transform', collapsed && 'rotate-180')} />
        </button>
      </div>

      <nav className="flex-1 space-y-1 p-2 overflow-y-auto">
        {navItems.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            className={({ isActive }) =>
              cn(
                'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                isActive
                  ? 'bg-primary/10 text-primary'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
                collapsed && 'justify-center px-2',
              )
            }
            title={t(item.label)}
          >
            <item.icon className="h-5 w-5 shrink-0" />
            {!collapsed && <span>{t(item.label)}</span>}
          </NavLink>
        ))}
      </nav>

      <div className="border-t p-2 space-y-1">
        <button
          onClick={() => setLocale(locale === 'en' ? 'ne' : 'en')}
          className={cn(
            'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors',
            collapsed && 'justify-center',
          )}
          title={t('nav.settings')}
        >
          <Languages className="h-5 w-5 shrink-0" />
          {!collapsed && <span>{locale === 'en' ? 'नेपाली' : 'English'}</span>}
        </button>

        <button
          onClick={logout}
          className={cn(
            'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors',
            collapsed && 'justify-center',
          )}
          title={t('nav.logout')}
        >
          <LogOut className="h-5 w-5 shrink-0" />
          {!collapsed && <span>{t('nav.logout')}</span>}
        </button>
      </div>
    </aside>
  )
}
