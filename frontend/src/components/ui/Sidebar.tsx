import React from 'react';
import { NavLink } from 'react-router-dom';

export default function Sidebar() {
  const nav = [
    { to: '/', label: 'Dashboard' },
    { to: '/appointments', label: 'Appointments' },
    { to: '/programs', label: 'Programs' },
    { to: '/reports', label: 'Reports' },
    { to: '/management', label: 'Management' },
  ];

  return (
    <aside className="w-64 h-screen sticky top-0 bg-sidebar-bg text-sidebar-text flex flex-col">
      <div className="px-4 py-6 border-b border-sidebar-panel">
        <div className="text-lg font-semibold">MMP CMS</div>
        <div className="text-sm text-sidebar-muted">Admin</div>
      </div>

      <nav className="flex-1 overflow-y-auto px-2 py-4">
        {nav.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            className={({ isActive }) =>
              `block rounded-md px-3 py-2 mb-1 text-sm font-medium transition-colors ${isActive ? 'bg-sidebar-panel text-white' : 'text-sidebar-text hover:bg-sidebar-panel/40'}`
            }
          >
            {item.label}
          </NavLink>
        ))}
      </nav>

      <div className="px-4 py-4 border-t border-sidebar-panel text-sm text-sidebar-muted">
        v1.0
      </div>
    </aside>
  );
}
