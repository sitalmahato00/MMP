import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { useRef, useState } from 'react';
import toast from 'react-hot-toast';
import { ArrowLeft } from 'lucide-react';
import { Input } from '@components/ui/Input';
import { Select } from '@components/ui/Select';
import { BsDatePicker } from '@components/ui/BsDatePicker';
import { Button } from '@components/ui/Button';
import { Spinner } from '@components/ui/Spinner';
import hodService from '@shared/services/hodService';
import academicService from '@shared/services/academicService';

const schema = z.object({
  name: z.string().min(1, 'Name is required'),
  email: z.string().email('Invalid email address'),
  phone: z.string().optional(),
  gender: z.string().optional(),
  dob: z.string().optional(),
  address: z.string().optional(),
  department_id: z.string().optional(),
  is_active: z.boolean(),
  password: z.string().optional(),
  password_confirmation: z.string().optional(),
}).refine(data => !data.password || data.password === data.password_confirmation, {
  message: 'Passwords do not match',
  path: ['password_confirmation'],
}).refine(data => !data.password || data.password.length >= 8, {
  message: 'Password must be at least 8 characters',
  path: ['password'],
});

type FormValues = z.infer<typeof schema>;

const GENDER_OPTIONS = [
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' },
];

export default function EditHodPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const avatarRef = useRef<HTMLInputElement>(null);
  const [resetDone, setResetDone] = useState(false);

  const { data: hodRes, isLoading: hodLoading } = useQuery({
    queryKey: ['hod', id],
    queryFn: () => hodService.show(Number(id)),
    enabled: !!id,
  });

  const { data: deptsRes } =useQuery({
    queryKey: ['departments'],
    queryFn: academicService.departments,
    staleTime: Infinity,
  });

  const hod = hodRes?.data;
  const departments = deptsRes?.data ?? [];

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

  if (hod && !resetDone) {
    reset({
      name: hod.name || '',
      email: hod.email || '',
      phone: hod.phone || '',
      gender: hod.gender || '',
      dob: hod.dob || '',
      address: hod.address || '',
      department_id: hod.hod_department ? String(hod.hod_department.id) : '',
      is_active: hod.is_active ?? true,
      password: '',
      password_confirmation: '',
    });
    setResetDone(true);
  }

  const dob = watch('dob');

  const mutation = useMutation({
    mutationFn: (data: FormData) => hodService.update(Number(id), data),
    onSuccess: () => {
      toast.success('HOD updated successfully');
      navigate('/admin/hods');
    },
    onError: (err: any) => {
      toast.error(err?.response?.data?.message || 'Failed to update HOD');
    },
  });

  const onSubmit = (data: FormValues) => {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        formData.append(key, String(value));
      }
    });
    formData.append('_method', 'PUT');
    if (!data.password) {
      formData.delete('password');
      formData.delete('password_confirmation');
    }
    if (avatarRef.current?.files?.[0]) {
      formData.append('avatar', avatarRef.current.files[0]);
    }
    mutation.mutate(formData);
  };

  if (hodLoading) {
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
    <div className="max-w-3xl">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Edit HOD</h1>
          <p className="mt-0.5 text-sm text-slate-500">Update Head of Department information.</p>
        </div>
        <Link to="/admin/hods" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" /> Back to List
        </Link>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="bg-white rounded-xl border border-gray-100 shadow-sm p-6" encType="multipart/form-data">
        <div className="space-y-6">
          {hod.avatar_url && (
            <div className="flex items-center gap-4">
              <img src={hod.avatar_url} alt={hod.name} className="w-16 h-16 rounded-full object-cover ring-2 ring-gray-100" />
              <div>
                <p className="text-sm font-medium text-gray-900">Current Profile Photo</p>
                <p className="text-xs text-gray-500">Upload a new photo to replace</p>
              </div>
            </div>
          )}

          <div>
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="md:col-span-2">
                <Input id="name" label="Full Name" required {...register('name')} error={errors.name?.message} />
              </div>
              <div>
                <Input id="email" label="Email" type="email" required {...register('email')} error={errors.email?.message} />
              </div>
              <div>
                <Input id="phone" label="Phone" {...register('phone')} />
              </div>
              <div>
                <Select id="gender" label="Gender" placeholder="Select Gender" options={GENDER_OPTIONS} {...register('gender')} />
              </div>
              <div>
                <BsDatePicker label="Date of Birth (BS)" value={dob} onChange={(bs) => setValue('dob', bs, { shouldValidate: true })} error={errors.dob?.message} />
              </div>
              <div className="md:col-span-2">
                <Input id="address" label="Address" {...register('address')} />
              </div>
              <div className="md:col-span-2">
                <label className="form-label">Profile Photo</label>
                <input type="file" ref={avatarRef} accept="image/*"
                  className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mt-1.5" />
              </div>
            </div>
          </div>

          <div>
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Department Assignment</h3>
            <div>
              <Select id="department_id" label="Assign Department" placeholder="No Department"
                options={departments.map((d: any) => ({ value: String(d.id), label: `${d.name} (${d.code ?? ''})` }))}
                {...register('department_id')} />
              <p className="mt-1 text-xs text-gray-500">Only departments without an assigned HOD are shown (plus current department if assigned)</p>
            </div>
          </div>

          <div>
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Account Settings</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Input id="password" label="New Password" type="password" {...register('password')} hint="Leave blank to keep current password" error={errors.password?.message} />
              </div>
              <div>
                <Input id="password_confirmation" label="Confirm New Password" type="password" {...register('password_confirmation')} error={errors.password_confirmation?.message} />
              </div>
              <div className="md:col-span-2">
                <label className="flex items-center gap-2">
                  <input type="checkbox" {...register('is_active')}
                    className="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                  <span className="text-sm font-medium text-gray-700">Active Account</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div className="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
          <Link to="/admin/hods" className="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 transition">Cancel</Link>
          <Button type="submit" loading={isSubmitting}>Update HOD</Button>
        </div>
      </form>
    </div>
  );
}
