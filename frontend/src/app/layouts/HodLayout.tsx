import { Outlet } from 'react-router-dom';
import { Sidebar } from '@components/sidebar/Sidebar';
import { Topbar } from '@components/navbar/Topbar';
import { useAppSelector, useAppDispatch } from '@hooks/useRedux';
import { setSidebarOpen } from '@app/store/ui.store';

export default function HodLayout() {
  const sidebarOpen = useAppSelector((s) => s.ui.sidebarOpen);
  const dispatch = useAppDispatch();

  return (
    <div className="flex h-full w-full overflow-x-hidden bg-gray-50 text-gray-800 antialiased dark:bg-slate-950 dark:text-slate-100">
      <div
        onClick={() => dispatch(setSidebarOpen(false))}
        className={`fixed inset-0 z-40 bg-gray-950/65 backdrop-blur-sm transition-opacity lg:hidden ${
          sidebarOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
        }`}
      />
      <Sidebar />
      <div className="flex min-w-0 flex-1 flex-col overflow-hidden transition-all duration-300">
        <Topbar />
        <main className="min-w-0 flex-1 overflow-y-auto overflow-x-hidden px-3 pb-[6rem] pt-6 sm:px-4 lg:p-8">
          <div className="mx-auto w-full max-w-full"><Outlet /></div>
        </main>
      </div>
    </div>
  );
}
