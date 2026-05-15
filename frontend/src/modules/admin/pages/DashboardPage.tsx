import { useQuery } from '@tanstack/react-query';
import { GraduationCap, Users, BookOpen, CalendarCheck } from 'lucide-react';
import academicService from '@services/academicService';
import { StatCard } from '@components/ui/StatCard';
import { Card } from '@components/ui/Card';
import { Spinner } from '@components/ui/Spinner';

export default function DashboardPage() {
  const { data, isLoading } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: academicService.dashboardStats,
    staleTime: 60_000,
  });

  const stats = data?.data;

  if (isLoading) {
    return <div className="flex h-64 items-center justify-center"><Spinner size="lg" /></div>;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-bold text-gray-900">Dashboard</h1>

      {/* Stat cards */}
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          title="Total Students"
          value={stats?.total_students ?? 0}
          icon={<GraduationCap className="h-6 w-6" />}
          color="blue"
        />
        <StatCard
          title="Active Students"
          value={stats?.active_students ?? 0}
          icon={<GraduationCap className="h-6 w-6" />}
          color="green"
        />
        <StatCard
          title="Teachers"
          value={stats?.total_teachers ?? 0}
          icon={<Users className="h-6 w-6" />}
          color="purple"
        />
        <StatCard
          title="Departments"
          value={stats?.total_departments ?? 0}
          icon={<BookOpen className="h-6 w-6" />}
          color="yellow"
        />
      </div>

      {/* Current Session */}
      {stats?.current_session && (
        <Card title="Current Academic Session">
          <div className="flex items-center gap-3">
            <CalendarCheck className="h-8 w-8 text-primary-600" />
            <div>
              <p className="font-semibold text-gray-900">{stats.current_session.name}</p>
              {stats.current_session.name_bs && (
                <p className="text-sm text-gray-500">{stats.current_session.name_bs}</p>
              )}
            </div>
          </div>
        </Card>
      )}
    </div>
  );
}
