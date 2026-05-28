import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Edit2, Mail, Phone, MapPin, User as UserIcon } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import { BsDate } from '@components/ui/BsDate';
import userService from '@shared/services/userService';

const ROLE_MAP: Record<string, { label: string; cls: string }> = {
  admin:     { label: 'Admin',     cls: 'bg-red-50 text-red-700' },
  principal: { label: 'Principal', cls: 'bg-purple-50 text-purple-700' },
  teacher:   { label: 'Teacher',   cls: 'bg-blue-50 text-blue-700' },
  student:   { label: 'Student',   cls: 'bg-green-50 text-green-700' },
  parent:    { label: 'Parent',    cls: 'bg-amber-50 text-amber-700' },
  hod:       { label: 'HOD',       cls: 'bg-indigo-50 text-indigo-700' },
  staff:     { label: 'Staff',     cls: 'bg-slate-100 text-slate-600' },
};

export default function ShowUserPage() {
  const { id } = useParams();

  const { data: userRes, isLoading } = useQuery({
    queryKey: ['user', id],
    queryFn: () => userService.show(Number(id)),
    enabled: !!id,
  });

  const user = userRes?.data;
  const rm = ROLE_MAP[user?.role] ?? { label: user?.role, cls: 'bg-slate-100 text-slate-600' };

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

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">User Details</h1>
          <p className="mt-0.5 text-sm text-slate-500">View user account information.</p>
        </div>
        <div className="flex items-center gap-3">
          <Link to={`/admin/users/${id}/edit`} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
            <Edit2 className="h-4 w-4" /> Edit
          </Link>
          <Link to="/admin/users" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <ArrowLeft className="h-4 w-4" /> Back to Users
          </Link>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div className="lg:col-span-1">
          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div className="p-6 text-center">
              {user.avatar ? (
                <img src={user.avatar} alt={user.name} className="mx-auto h-20 w-20 rounded-2xl object-cover" />
              ) : (
                <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 text-2xl font-black">
                  {user.name?.charAt(0)?.toUpperCase()}
                </div>
              )}
              <h2 className="mt-4 text-lg font-bold text-slate-900">{user.name}</h2>
              <p className="text-sm text-slate-400">{user.email}</p>
              <div className="mt-3 flex items-center justify-center gap-2">
                <span className={`rounded-lg px-2.5 py-1 text-xs font-bold ${rm.cls}`}>{rm.label}</span>
                <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${user.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>
                  <span className={`h-1.5 w-1.5 rounded-full ${user.is_active ? 'bg-emerald-500' : 'bg-slate-400'}`} />
                  {user.is_active ? 'Active' : 'Inactive'}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div className="lg:col-span-2 space-y-5">
          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100">
              <h3 className="text-sm font-bold text-slate-900">Contact Information</h3>
            </div>
            <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                  <Mail className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</p>
                  <p className="text-sm font-medium text-slate-900">{user.email}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                  <Phone className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</p>
                  <p className="text-sm font-medium text-slate-900">{user.phone || '—'}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                  <MapPin className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</p>
                  <p className="text-sm font-medium text-slate-900">{user.address || '—'}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                  <UserIcon className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</p>
                  <p className="text-sm font-medium text-slate-900 capitalize">{user.gender || '—'}</p>
                </div>
              </div>
            </div>
          </div>

          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100">
              <h3 className="text-sm font-bold text-slate-900">Account Timeline</h3>
            </div>
            <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Created</p>
                <p className="text-sm font-medium text-slate-900 mt-1"><BsDate date={user.created_at} format="Y, F d" /></p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Last Updated</p>
                <p className="text-sm font-medium text-slate-900 mt-1"><BsDate date={user.updated_at} format="Y, F d" /></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
