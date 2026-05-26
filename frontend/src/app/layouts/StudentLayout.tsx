import { Outlet } from 'react-router-dom';
import { Sidebar } from '@components/sidebar/Sidebar';
import { Topbar } from '@components/navbar/Topbar';
import { useAppSelector, useAppDispatch } from '@hooks/useRedux';
import { setSidebarOpen } from '@app/store/ui.store';
import { clsx } from 'clsx';

export default function StudentLayout() {
  const sidebarOpen = useAppSelector((s) => s.ui.sidebarOpen);
  const dispatch = useAppDispatch();

  return (
    <div className="flex h-full w-full overflow-x-hidden bg-slate-50 text-slate-800 antialiased">
      <div
        onClick={() => dispatch(setSidebarOpen(false))}
        className={clsx(
          'fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm transition-opacity lg:hidden',
          sidebarOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
        )}
      />
      <Sidebar />
      <div className={clsx(
        'flex min-w-0 flex-1 flex-col overflow-hidden transition-all duration-300',
        sidebarOpen ? 'lg:ml-64' : 'lg:ml-[4.5rem]'
      )}>
        <Topbar />
        <main className="min-w-0 flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
          <div className="mx-auto w-full max-w-full"><Outlet /></div>
        </main>
      </div>
    </div>
  );
}
