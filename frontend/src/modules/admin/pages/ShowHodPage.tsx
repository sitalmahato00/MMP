import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Edit2, Building, Mail, Phone, MapPin, User as UserIcon } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import { BsDate } from '@components/ui/BsDate';
import hodService from '@shared/services/hodService';

export default function ShowHodPage() {
  const { id } = useParams();

  const { data: hodRes, isLoading } = useQuery({
    queryKey: ['hod', id],
    queryFn: () => hodService.show(Number(id)),
    enabled: !!id,
  });

  const hod = hodRes?.data;

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!hod) {
    return (
      <div className="space-y-5">
        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm p-10 text-center">
          <p className="text-sm font-medium text-slate-500">HOD not found.</p>
          <Link to="/admin/hods" className="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-800">Back to HODs</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">HOD Details</h1>
          <p className="mt-0.5 text-sm text-slate-500">View HOD account and department information.</p>
        </div>
        <div className="flex items-center gap-3">
          <Link to={`/admin/hods/${id}/edit`} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition">
            <Edit2 className="h-4 w-4" /> Edit
          </Link>
          <Link to="/admin/hods" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <ArrowLeft className="h-4 w-4" /> Back to HODs
          </Link>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div className="lg:col-span-1">
          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div className="p-6 text-center">
              {hod.avatar ? (
                <img src={hod.avatar} alt={hod.name} className="mx-auto h-20 w-20 rounded-2xl object-cover" />
              ) : (
                <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 text-2xl font-black">
                  {hod.name?.charAt(0)?.toUpperCase()}
                </div>
              )}
              <h2 className="mt-4 text-lg font-bold text-slate-900">{hod.name}</h2>
              <p className="text-sm text-slate-400">{hod.email}</p>
              <div className="mt-3 flex items-center justify-center gap-2">
                <span className="rounded-lg bg-indigo-50 text-indigo-700 px-2.5 py-1 text-xs font-bold">HOD</span>
                <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${hod.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>
                  <span className={`h-1.5 w-1.5 rounded-full ${hod.is_active ? 'bg-emerald-500' : 'bg-slate-400'}`} />
                  {hod.is_active ? 'Active' : 'Inactive'}
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
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                  <Mail className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</p>
                  <p className="text-sm font-medium text-slate-900">{hod.email}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                  <Phone className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</p>
                  <p className="text-sm font-medium text-slate-900">{hod.phone || '—'}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                  <MapPin className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</p>
                  <p className="text-sm font-medium text-slate-900">{hod.address || '—'}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                  <UserIcon className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</p>
                  <p className="text-sm font-medium text-slate-900 capitalize">{hod.gender || '—'}</p>
                </div>
              </div>
            </div>
          </div>

          <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100">
              <h3 className="text-sm font-bold text-slate-900">Department Assignment</h3>
            </div>
            <div className="p-6">
              <div className="flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                  <Building className="h-5 w-5" />
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Department</p>
                  <p className="text-sm font-medium text-slate-900">{hod.hod_department?.name || '—'}</p>
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
                <p className="text-sm font-medium text-slate-900 mt-1"><BsDate date={hod.created_at} format="Y, F d" /></p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Last Updated</p>
                <p className="text-sm font-medium text-slate-900 mt-1"><BsDate date={hod.updated_at} format="Y, F d" /></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
