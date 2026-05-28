import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { Spinner } from '@components/ui/Spinner';
import { BsDate } from '@components/ui/BsDate';
import userService from '@shared/services/userService';

const ROLE_COLORS: Record<string, string> = {
  principal: 'red',
  hod: 'blue',
  teacher: 'green',
  student: 'purple',
  parent: 'yellow',
  alumni: 'gray',
};

export default function ShowUserPage() {
  const { id } = useParams();

  const { data: userRes, isLoading } = useQuery({
    queryKey: ['user', id],
    queryFn: () => userService.show(Number(id)),
    enabled: !!id,
  });

  const user = userRes?.data;

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!user) {
    return (
      <div className="space-y-5">
        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm p-10 text-center">
          <p className="text-sm font-medium text-slate-500">User not found.</p>
          <Link to="/admin/users" className="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-800">Back to Users</Link>
        </div>
      </div>
    );
  }

  const roleNames = user.roles?.map(r => r.name) ?? (user.role ? [user.role] : []);

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">{user.name}</h1>
          <p className="mt-0.5 text-sm text-slate-500">Account detail and activity overview.</p>
        </div>
        <div className="flex items-center gap-3">
          <Link to={`/admin/users/${id}/edit`} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
          </Link>
          <Link to="/admin/users" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
          </Link>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-1 space-y-4">
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex flex-col items-center text-center">
            {user.avatar_url ? (
              <img src={user.avatar_url} alt={user.name} className="w-20 h-20 rounded-full object-cover ring-2 ring-gray-100 mb-3" />
            ) : (
              <div className="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-2xl font-black text-white shadow mb-3">
                {user.name?.charAt(0)?.toUpperCase()}
              </div>
            )}
            <h2 className="text-lg font-bold text-gray-900">{user.name}</h2>
            <p className="text-sm text-gray-400">{user.email}</p>
            <div className="mt-3 flex flex-wrap gap-2 justify-center">
              {roleNames.map(role => {
                const color = ROLE_COLORS[role] ?? 'gray';
                return (
                  <span key={role} className={`badge-${color}`}>{role}</span>
                );
              })}
            </div>
            <div className="mt-3">
              <span className={`inline-flex items-center gap-1.5 badge-${user.is_active ? 'green' : 'red'}`}>
                <span className={`h-1.5 w-1.5 rounded-full ${user.is_active ? 'bg-green-500' : 'bg-red-500'}`}></span>
                {user.is_active ? 'Active' : 'Inactive'}
              </span>
            </div>
          </div>
        </div>

        <div className="lg:col-span-2 space-y-4">
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-100">
              <h3 className="text-base font-bold text-gray-900">Account Information</h3>
            </div>
            <dl className="divide-y divide-gray-50">
              <InfoRow label="User ID">{user.id}</InfoRow>
              <InfoRow label="Full Name">{user.name}</InfoRow>
              <InfoRow label="Email">{user.email}</InfoRow>
              <InfoRow label="Phone">{user.phone || '—'}</InfoRow>
              <InfoRow label="Gender">{user.gender ? user.gender.charAt(0).toUpperCase() + user.gender.slice(1) : '—'}</InfoRow>
              <InfoRow label="Date of Birth">{user.dob ? <BsDate date={user.dob} format="Y, F d" /> : '—'}</InfoRow>
              <InfoRow label="Address">{user.address || '—'}</InfoRow>
              <InfoRow label="Status">
                <span className={`inline-flex items-center gap-1.5 badge-${user.is_active ? 'green' : 'red'}`}>
                  <span className={`h-1.5 w-1.5 rounded-full ${user.is_active ? 'bg-green-500' : 'bg-red-500'}`}></span>
                  {user.is_active ? 'Active' : 'Inactive'}
                </span>
              </InfoRow>
              <InfoRow label="Member Since"><BsDate date={user.created_at} format="Y, F d" /></InfoRow>
              <InfoRow label="Last Updated"><BsDate date={user.updated_at} format="Y, F d" /></InfoRow>
            </dl>
          </div>
        </div>
      </div>
    </div>
  );
}

function InfoRow({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div className="px-6 py-3.5 grid grid-cols-3 gap-4">
      <dt className="text-sm font-medium text-gray-500">{label}</dt>
      <dd className="col-span-2 text-sm text-gray-900">{children}</dd>
    </div>
  );
}
