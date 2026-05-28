import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { ArrowLeft, Save } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import userService from '@shared/services/userService';

const schema = z.object({
  name: z.string().min(1, 'Name is required'),
  email: z.string().email('Invalid email address'),
  phone: z.string().optional(),
  gender: z.string().optional(),
  address: z.string().optional(),
  role: z.string().min(1, 'Role is required'),
  is_active: z.boolean(),
});

type FormValues = z.infer<typeof schema>;

const GENDER_OPTIONS = [
  { value: '', label: 'Select gender' },
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' },
];

const ROLE_OPTIONS = [
  { value: '', label: 'Select role' },
  { value: 'principal', label: 'Principal' },
  { value: 'hod', label: 'HOD' },
  { value: 'teacher', label: 'Teacher' },
  { value: 'student', label: 'Student' },
  { value: 'parent', label: 'Parent' },
  { value: 'alumni', label: 'Alumni' },
  { value: 'staff', label: 'Staff' },
];

export default function EditUserPage() {
  const { id } = useParams();
  const navigate = useNavigate();

  const { data: userRes, isLoading: userLoading } = useQuery({
    queryKey: ['user', id],
    queryFn: () => userService.show(Number(id)),
    enabled: !!id,
  });

  const user = userRes?.data;

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
  });

  if (user && !reset._isReset) {
    reset({
      name: user.name || '',
      email: user.email || '',
      phone: user.phone || '',
      gender: user.gender || '',
      address: user.address || '',
      role: user.role || '',
      is_active: user.is_active ?? true,
    });
  }

  const mutation = useMutation({
    mutationFn: (data: FormValues) => userService.update(Number(id), data as Record<string, unknown>),
    onSuccess: () => {
      toast.success('User updated successfully');
      navigate('/admin/users');
    },
    onError: (err: any) => {
      toast.error(err?.response?.data?.message || 'Failed to update user');
    },
  });

  const onSubmit = (data: FormValues) => mutation.mutate(data);

  if (userLoading) {
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
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Edit User</h1>
          <p className="mt-0.5 text-sm text-slate-500">Update user account details.</p>
        </div>
        <Link to=".." className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" /> Back
        </Link>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div className="p-6 space-y-5">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
              <input {...register('name')} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="Full name" />
              {errors.name && <p className="text-xs text-red-500 mt-1">{errors.name.message}</p>}
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
              <input {...register('email')} type="email" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="email@example.com" />
              {errors.email && <p className="text-xs text-red-500 mt-1">{errors.email.message}</p>}
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</label>
              <input {...register('phone')} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="Phone number" />
              {errors.phone && <p className="text-xs text-red-500 mt-1">{errors.phone.message}</p>}
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</label>
              <select {...register('gender')} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 mt-1.5">
                {GENDER_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
              </select>
              {errors.gender && <p className="text-xs text-red-500 mt-1">{errors.gender.message}</p>}
            </div>

            <div className="md:col-span-2">
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</label>
              <input {...register('address')} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50 mt-1.5" placeholder="Address" />
              {errors.address && <p className="text-xs text-red-500 mt-1">{errors.address.message}</p>}
            </div>

            <div>
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Role</label>
              <select {...register('role')} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 mt-1.5">
                {ROLE_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
              </select>
              {errors.role && <p className="text-xs text-red-500 mt-1">{errors.role.message}</p>}
            </div>

            <div className="flex items-end pb-2.5">
              <label className="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" {...register('is_active')} className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-100" />
                <span className="text-sm font-semibold text-slate-700">Active</span>
              </label>
            </div>
          </div>
        </div>

        <div className="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
          <div className="flex items-center gap-3">
            <button type="submit" disabled={isSubmitting} className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition disabled:opacity-50">
              {isSubmitting ? <Spinner size="sm" /> : <Save className="h-4 w-4" />}
              {isSubmitting ? 'Saving…' : 'Update User'}
            </button>
            <Link to=".." className="text-sm font-semibold text-slate-500 hover:text-slate-700 transition">Cancel</Link>
          </div>
        </div>
      </form>
    </div>
  );
}
