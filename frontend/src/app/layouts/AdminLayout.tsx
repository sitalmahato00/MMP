import { Outlet } from 'react-router-dom';
import { Sidebar } from '@components/sidebar/Sidebar';
import { Topbar } from '@components/navbar/Topbar';
import { Breadcrumb } from '@components/navbar/Breadcrumb';
import { useAppSelector, useAppDispatch } from '@hooks/useRedux';
import { setSidebarOpen } from '@app/store/ui.store';
import { useEffect } from 'react';

const TOPBAR_H   = 64;
const SIDEBAR_W  = 240; // desktop always-open width

export default function AdminLayout() {
  const mobileOpen = useAppSelector((s) => s.ui.sidebarOpen);
  const dispatch   = useAppDispatch();

  // When viewport grows to desktop, close the mobile overlay state
  // (desktop sidebar is always visible via CSS, not via state)
  useEffect(() => {
    function onResize() {
      if (window.innerWidth >= 1024 && mobileOpen) {
        dispatch(setSidebarOpen(false));
      }
    }
    window.addEventListener('resize', onResize);
    return () => window.removeEventListener('resize', onResize);
  }, [mobileOpen, dispatch]);

  return (
    <div
      className="flex h-screen w-screen flex-col overflow-hidden antialiased"
      style={{ backgroundColor: '#F4F7FB', color: '#1A2B45' }}
    >
      {/* ── 1. TOPBAR — full width, 64px ── */}
      <div className="shrink-0" style={{ height: TOPBAR_H }}>
        <Topbar />
      </div>

      {/* ── 2. BODY ROW ── */}
      <div className="relative flex min-h-0 flex-1 overflow-hidden">

        {/* Mobile overlay backdrop */}
        {mobileOpen && (
          <div
            className="absolute inset-0 z-30 lg:hidden"
            style={{ backgroundColor: 'rgba(1,30,84,0.65)' }}
            onClick={() => dispatch(setSidebarOpen(false))}
          />
        )}

        {/* ── SIDEBAR ──
            Desktop (lg+): always in flow, fixed 240px
            Mobile (<lg):  zero width when closed, full overlay when open
        ── */}
        {/* Desktop sidebar — always in flow */}
        <div
          className="hidden shrink-0 lg:block"
          style={{ width: 240 }}
        >
          <Sidebar />
        </div>

        {/* Mobile sidebar — absolute overlay, zero impact on layout */}
        <div
          className="absolute inset-y-0 left-0 z-40 lg:hidden"
          style={{
            width: 240,
            transform: mobileOpen ? 'translateX(0)' : 'translateX(-100%)',
            transition: 'transform 0.3s ease',
          }}
        >
          <Sidebar />
        </div>

        {/* ── RIGHT COLUMN — always fills remaining space ── */}
        <div className="flex min-w-0 flex-1 flex-col overflow-hidden">
          {/* Breadcrumb 48px */}
          <div className="shrink-0" style={{ height: 48 }}>
            <Breadcrumb />
          </div>

          {/* Scrollable main */}
          <main
            className="min-h-0 flex-1 overflow-y-auto overflow-x-hidden"
            style={{ backgroundColor: '#F4F7FB' }}
          >
            <div className="p-4 sm:p-5 lg:p-6">
              <Outlet />
            </div>
          </main>
        </div>
      </div>
    </div>
  );
}
