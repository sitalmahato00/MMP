import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import { Spinner } from '@components/ui/Spinner';
import { Input } from '@components/ui/Input';
import { Select } from '@components/ui/Select';
import { Button } from '@components/ui/Button';
import { BsDatePicker } from '@shared/components/ui/BsDatePicker';
import userService from '@shared/services/userService';

const schema = z.object({
  name: z.string().min(1, 'Name is required'),
  email: z.string().email('Invalid email address'),
  phone: z.string().optional(),
  gender: z.string().optional(),
  dob: z.string().optional(),
  address: z.string().optional(),
  role: z.string().min(1, 'Role is required'),
  is_active: z.boolean(),
  password: z.string().optional(),
  password_confirmation: z.string().optional(),
});

type FormValues = z.infer<typeof schema>;

const GENDER_OPTIONS = [
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' },
];

const ROLE_OPTIONS = [
  { value: 'principal', label: 'Principal' },
  { value: 'hod', label: 'Hod' },
  { value: 'teacher', label: 'Teacher' },
  { value: 'student', label: 'Student' },
  { value: 'parent', label: 'Parent' },
  { value: 'alumni', label: 'Alumni' },
];

export default function EditUserPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [tab, setTab] = useState<'basic' | 'access' | 'security'>('basic');
  const [serverError, setServerError] = useState('');
  const [avatarFile, setAvatarFile] = useState<File | null>(null);

  const { data: userRes, isLoading: userLoading } = useQuery({
    queryKey: ['user', id],
    queryFn: () => userService.show(Number(id)),
    enabled: !!id,
  });

  const user = userRes?.data;

  const {
    register,
    handleSubmit,
    setValue,
    watch,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
  });

  const isActive = watch('is_active');

  useEffect(() => {
    if (user) {
      const primaryRole = user.roles?.[0]?.name || user.role || '';
      reset({
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
        gender: user.gender || '',
        dob: user.dob || '',
        address: user.address || '',
        role: primaryRole,
        is_active: user.is_active ?? true,
        password: '',
        password_confirmation: '',
      });
    }
  }, [user, reset]);

  const mutation = useMutation({
    mutationFn: (data: FormData) => userService.update(Number(id), data),
    onSuccess: () => {
      toast.success('User updated successfully');
      navigate('/admin/users');
    },
    onError: (err: any) => {
      setServerError(err?.response?.data?.message || 'Failed to update user');
    },
  });

  const onSubmit = (data: FormValues) => {
    const formData = new FormData();
    formData.append('name', data.name);
    formData.append('email', data.email);
    if (data.phone) formData.append('phone', data.phone);
    if (data.gender) formData.append('gender', data.gender);
    if (data.dob) formData.append('dob', data.dob);
    if (data.address) formData.append('address', data.address);
    formData.append('role', data.role);
    formData.append('is_active', data.is_active ? '1' : '0');
    if (data.password) formData.append('password', data.password);
    if (data.password_confirmation) formData.append('password_confirmation', data.password_confirmation);
    if (avatarFile) formData.append('avatar', avatarFile);
    formData.append('_method', 'PUT');
    mutation.mutate(formData);
  };

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
          <p className="mt-0.5 text-sm text-slate-500">{user.name}</p>
        </div>
        <Link to="/admin/users" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Back
        </Link>
      </div>

      {serverError && (
        <div className="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{serverError}</div>
      )}

      <form onSubmit={handleSubmit(onSubmit)} encType="multipart/form-data">
        <div className="mb-6 flex gap-1 rounded-xl bg-slate-100 p-1">
          <button type="button" onClick={() => setTab('basic')}
            className={`flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition ${tab === 'basic' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'}`}>
            Basic Info
          </button>
          <button type="button" onClick={() => setTab('access')}
            className={`flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition ${tab === 'access' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'}`}>
            Role & Access
          </button>
          <button type="button" onClick={() => setTab('security')}
            className={`flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition ${tab === 'security' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'}`}>
            Security
          </button>
        </div>

        {tab === 'basic' && (
          <div>
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-slate-100">
                <h3 className="text-base font-bold text-slate-900">Basic Information</h3>
                <p className="mt-0.5 text-sm text-slate-500">Identity, contact details, and profile photo.</p>
              </div>
              <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <Input label="Full Name" required {...register('name')} error={errors.name?.message} />
                <Input label="Email Address" type="email" required {...register('email')} error={errors.email?.message} />
                <Input label="Phone Number" {...register('phone')} error={errors.phone?.message} />
                <Select label="Gender" placeholder="Select Gender" options={GENDER_OPTIONS} {...register('gender')} error={errors.gender?.message} />
                <div>
                  <BsDatePicker
                    label="Date of Birth (BS)"
                    value={watch('dob')}
                    onChange={(bsDate) => setValue('dob', bsDate)}
                  />
                </div>
                <div className="md:col-span-2">
                  <label className="form-label">Address</label>
                  <textarea {...register('address')} rows={2} className="form-input w-full" />
                  {errors.address && <p className="mt-1 text-xs text-red-600">{errors.address.message}</p>}
                </div>
                <div className="md:col-span-2">
                  <label className="form-label">Profile Picture</label>
                  <input type="file" accept="image/*" onChange={(e) => setAvatarFile(e.target.files?.[0] ?? null)} className="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition cursor-pointer" />
                  {user.avatar_url && <p className="mt-1 text-xs text-slate-400">Current: {user.avatar_url}</p>}
                </div>
              </div>
            </div>
            <div className="mt-4 flex gap-3">
              <button type="button" onClick={() => setTab('access')} className="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#7a0000] transition">Next: Role & Access →</button>
            </div>
          </div>
        )}

        {tab === 'access' && (
          <div>
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-slate-100">
                <h3 className="text-base font-bold text-slate-900">Role & Access</h3>
                <p className="mt-0.5 text-sm text-slate-500">Assign a system role and set account status.</p>
              </div>
              <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <Select label="Role" placeholder="— Select Role —" required options={ROLE_OPTIONS} {...register('role')} error={errors.role?.message} />
                <div>
                  <label className="flex items-center gap-3 mt-2 cursor-pointer">
                    <input type="checkbox" checked={isActive} onChange={(e) => setValue('is_active', e.target.checked)} className="w-4 h-4 accent-[#8B0000] rounded" />
                    <span className="text-sm text-gray-600">Active (can login)</span>
                  </label>
                </div>
              </div>
            </div>
            <div className="mt-4 flex gap-3">
              <button type="button" onClick={() => setTab('basic')} className="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
              <button type="button" onClick={() => setTab('security')} className="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#7a0000] transition">Next: Security →</button>
            </div>
          </div>
        )}

        {tab === 'security' && (
          <div>
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-slate-100">
                <h3 className="text-base font-bold text-slate-900">Change Password</h3>
                <p className="mt-0.5 text-sm text-slate-500">Leave blank to keep the current password.</p>
              </div>
              <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <Input label="New Password" type="password" {...register('password')} error={errors.password?.message} placeholder="Leave blank to keep current" />
                <Input label="Confirm Password" type="password" {...register('password_confirmation')} error={errors.password_confirmation?.message} />
              </div>
            </div>
            <div className="mt-6 flex items-center gap-3">
              <button type="button" onClick={() => setTab('access')} className="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
              <Button type="submit" loading={isSubmitting}>Save Changes</Button>
              <Link to="/admin/users" className="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</Link>
            </div>
          </div>
        )}
      </form>
    </div>
  );
}
