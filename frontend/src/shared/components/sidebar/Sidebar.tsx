import { NavLink } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '@hooks/useRedux';
import { toggleSidebar } from '@app/store/ui.store';
import type { NavItem } from '@app/config/navItems';
import {
  LayoutDashboard, GraduationCap, Users, BookOpen, CalendarCheck, ClipboardList,
  Library, Building, Banknote, Package, DollarSign, Globe, Settings, BarChart2,
  Bell, FileText, PanelLeftClose, PanelLeftOpen, type LucideProps,
} from 'lucide-react';
import { clsx } from 'clsx';

const ICON_MAP: Record<string, React.ComponentType<LucideProps>> = {
  LayoutDashboard, GraduationCap, Users, BookOpen, CalendarCheck, ClipboardList,
  Library, Building, Banknote, Package, DollarSign, Globe, Settings, BarChart2,
  Bell, FileText, PanelLeftClose, PanelLeftOpen,
};

interface Props { navItems: NavItem[]; }

function DynamicIcon({ name, className }: { name: string; className?: string }) {
  const Icon = ICON_MAP[name];
  return Icon ? <Icon className={clsx('h-5 w-5', className)} /> : null;
}

export function Sidebar({ navItems }: Props) {
  const dispatch    = useAppDispatch();
  const sidebarOpen = useAppSelector((s) => s.ui.sidebarOpen);

  return (
    <aside
      className={clsx(
        'fixed inset-y-0 left-0 z-30 flex flex-col bg-gray-900 text-white transition-all duration-300',
        sidebarOpen ? 'w-64' : 'w-16'
      )}
    >
      {/* Logo */}
      <div className="flex h-16 items-center justify-between px-4 border-b border-gray-800">
        {sidebarOpen && (
          <span className="text-lg font-bold tracking-tight text-white">MMP ERP</span>
        )}
        <button
          onClick={() => dispatch(toggleSidebar())}
          className="ml-auto rounded-lg p-1.5 text-gray-400 hover:bg-gray-800 hover:text-white transition"
          aria-label="Toggle sidebar"
        >
          <DynamicIcon name={sidebarOpen ? 'PanelLeftClose' : 'PanelLeftOpen'} />
        </button>
      </div>

      {/* Nav */}
      <nav className="flex-1 overflow-y-auto py-4">
        <ul className="space-y-1 px-2">
          {navItems.map((item) => (
            <li key={item.path}>
              <NavLink
                to={item.path}
                className={({ isActive }) =>
                  clsx(
                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                    isActive
                      ? 'bg-primary-600 text-white'
                      : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                  )
                }
                title={!sidebarOpen ? item.label : undefined}
              >
                <DynamicIcon name={item.icon} className="shrink-0" />
                {sidebarOpen && <span className="truncate">{item.label}</span>}
              </NavLink>
            </li>
          ))}
        </ul>
      </nav>
    </aside>
  );
}
