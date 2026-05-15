import { Outlet } from 'react-router-dom';
import { Sidebar } from '@components/sidebar/Sidebar';
import { Topbar } from '@components/navbar/Topbar';
import { useAppSelector } from '@hooks/useRedux';
import { teacherNavItems } from '@app/config/navItems';

export default function TeacherLayout() {
  const sidebarOpen = useAppSelector((s) => s.ui.sidebarOpen);
  return (
    <div className="flex h-screen overflow-hidden bg-gray-50">
      <Sidebar navItems={teacherNavItems} />
      <div className={`flex flex-1 flex-col transition-all duration-300 ${sidebarOpen ? 'ml-64' : 'ml-16'}`}>
        <Topbar />
        <main className="flex-1 overflow-y-auto p-6"><Outlet /></main>
      </div>
    </div>
  );
}
