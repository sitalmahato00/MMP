import { useState } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { ArrowLeft, Save, UserCheck } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import hodService from '@shared/services/hodService';
import academicService from '@shared/services/academicService';

const GENDER_OPTIONS = [
  { value: '', label: 'Select gender' },
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' },
];

export default function CreateHodPage() {
  const navigate = useNavigate();
  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    gender: '',
    address: '',
    department_id: '',
    is_active: true,
    password: '',
    password_confirmation: '',
  });
  const [errors, setErrors] = useState<Record<string, string>>({});

  const { data: deptsRes } = useQuery({
    queryKey: ['departments'],
    queryFn: academicService.departments,
    staleTime: Infinity,
  });

  const departments = deptsRes?.data ?? [];

  const validate = () => {
    const errs: Record<string, string> = {};
    if (!form.name.trim()) errs.name = 'Name is required';
    if (!form.email.trim()) errs.email = 'Email is required';
    else if (!/\S+@\S+\.\S+/.test(form.email)) errs.email = 'Invalid email address';
    if (!form.department_id) errs.department_id = 'Department is required';
    if (form.password && form.password.length < 8) errs.password = 'Password must be at least 8 characters';
    if (form.password !== form.password_confirmation) errs.password_confirmation = 'Passwords do not match';
    setErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const mutation = useMutation({
    mutationFn: (data: Record<string, unknown>) => hodService.create(data),
    onSuccess: () => {
      toast.success('HOD created successfully');
      navigate('/admin/hods');
    },
    onError: (err: any) => {
      toast.error(err?.response?.data?.message || 'Failed to create HOD');
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    const payload = { ...form };
    if (!payload.password) { delete payload.password; delete payload.password_confirmation; }
    mutation.mutate(payload as unknown as Record<string, unknown>);
  };

  const set = (field: string) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) =>
    setForm(prev => ({ ...prev, [field]: e.target.type === 'checkbox' ? (e.target as HTMLInputElement).checked : e.target.value }));

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Assign HOD</h1>
          <p className="mt-0.5 text-sm text-slate-500">Create a new HOD account and assign to a department.</p>
        </div>
        <Link to=".." className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" /> Back
        </Link>
      </div>

      <form onSubmit={handleSubmit} className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div className="p-6 space-y-5">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
              <input value={form.name} onChange={set('name')} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="Full name" />
              {errors.name && <p className="text-xs text-red-500 mt-1">{errors.name}</p>}
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
              <input value={form.email} onChange={set('email')} type="email" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="email@example.com" />
              {errors.email && <p className="text-xs text-red-500 mt-1">{errors.email}</p>}
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</label>
              <input value={form.phone} onChange={set('phone')} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="Phone number" />
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</label>
              <select value={form.gender} onChange={set('gender')} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 mt-1.5">
                {GENDER_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
              </select>
            </div>

            <div className="md:col-span-2">
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</label>
              <input value={form.address} onChange={set('address')} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="Address" />
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Department</label>
              <select value={form.department_id} onChange={set('department_id')} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 mt-1.5">
                <option value="">Select department</option>
                {departments.map((d: any) => <option key={d.id} value={d.id}>{d.name}</option>)}
              </select>
              {errors.department_id && <p className="text-xs text-red-500 mt-1">{errors.department_id}</p>}
            </div>

            <div className="flex items-end pb-2.5">
              <label className="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" checked={form.is_active} onChange={set('is_active')} className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-100" />
                <span className="text-sm font-semibold text-slate-700">Active</span>
              </label>
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Password</label>
              <input value={form.password} onChange={set('password')} type="password" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="Min 8 characters" />
              {errors.password && <p className="text-xs text-red-500 mt-1">{errors.password}</p>}
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Confirm Password</label>
              <input value={form.password_confirmation} onChange={set('password_confirmation')} type="password" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="Confirm password" />
              {errors.password_confirmation && <p className="text-xs text-red-500 mt-1">{errors.password_confirmation}</p>}
            </div>
          </div>
        </div>

        <div className="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
          <div className="flex items-center gap-3">
            <button type="submit" disabled={mutation.isPending} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition disabled:opacity-50">
              {mutation.isPending ? <Spinner size="sm" /> : <UserCheck className="h-4 w-4" />}
              {mutation.isPending ? 'Saving…' : 'Create HOD'}
            </button>
            <Link to=".." className="text-sm font-semibold text-slate-500 hover:text-slate-700 transition">Cancel</Link>
          </div>
        </div>
      </form>
    </div>
  );
}
