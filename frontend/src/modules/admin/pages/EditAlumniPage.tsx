import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import { ArrowLeft, Save } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import alumniService from '@shared/services/alumniService';
import academicService from '@shared/services/academicService';

const editSchema = z.object({
  name: z.string().min(1, 'Full name is required'),
  email: z.string().email('Invalid email address'),
  phone: z.string().optional(),
  address: z.string().optional(),
  department_id: z.string().min(1, 'Department is required'),
  program_id: z.string().min(1, 'Program is required'),
  roll_number: z.string().optional(),
  admission_year: z.string().optional(),
  graduation_year: z.string().min(1, 'Graduation year is required'),
  current_job: z.string().optional(),
  company_name: z.string().optional(),
  work_location: z.string().optional(),
  employment_status: z.string().optional(),
  bio: z.string().optional(),
  linkedin_url: z.string().optional(),
  github_url: z.string().optional(),
  portfolio_url: z.string().optional(),
  achievements: z.string().optional(),
  is_featured: z.boolean(),
  is_active: z.boolean(),
  visibility: z.string(),
});

type EditFormData = z.infer<typeof editSchema>;

export default function EditAlumniPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [avatar, setAvatar] = useState<File | null>(null);

  const { register, handleSubmit, watch, setValue, reset, formState: { errors } } = useForm<EditFormData>({
    resolver: zodResolver(editSchema),
  });

  const { data, isLoading } = useQuery({
    queryKey: ['alumni', id],
    queryFn: () => alumniService.show(Number(id)),
    enabled: !!id,
  });
  const alumni = data?.data;

  useEffect(() => {
    if (!alumni) return;
    reset({
      name: alumni.user?.name ?? '',
      email: alumni.user?.email ?? '',
      phone: alumni.user?.phone ?? '',
      address: alumni.user?.address ?? '',
      department_id: String(alumni.department_id ?? ''),
      program_id: String(alumni.program_id ?? ''),
      roll_number: alumni.roll_number ?? '',
      admission_year: alumni.admission_year ?? '',
      graduation_year: alumni.graduation_year ?? '',
      current_job: alumni.current_job ?? '',
      company_name: alumni.company_name ?? '',
      work_location: alumni.work_location ?? '',
      employment_status: alumni.employment_status ?? 'unknown',
      bio: alumni.bio ?? '',
      linkedin_url: alumni.linkedin_url ?? '',
      github_url: alumni.github_url ?? '',
      portfolio_url: alumni.portfolio_url ?? '',
      achievements: alumni.achievements ?? '',
      is_featured: alumni.is_featured ?? false,
      is_active: alumni.user?.is_active ?? true,
      visibility: alumni.visibility ?? 'public',
    });
  }, [alumni, reset]);

  const { data: departmentsData } = useQuery({
    queryKey: ['departments'],
    queryFn: () => academicService.departments(),
  });
  const departments = departmentsData?.data ?? [];

  const departmentId = watch('department_id');
  const { data: programsData } = useQuery({
    queryKey: ['programs', departmentId],
    queryFn: () => academicService.programs(departmentId ? Number(departmentId) : undefined),
    enabled: !!departmentId,
  });
  const programs = programsData?.data ?? [];

  const mutation = useMutation({
    mutationFn: (data: EditFormData) => {
      const payload: Record<string, unknown> = {
        user: { name: data.name, email: data.email, phone: data.phone || undefined, address: data.address || undefined },
        department_id: Number(data.department_id),
        program_id: Number(data.program_id),
        graduation_year: data.graduation_year,
        admission_year: data.admission_year || undefined,
        roll_number: data.roll_number || undefined,
        current_job: data.current_job || undefined,
        company_name: data.company_name || undefined,
        work_location: data.work_location || undefined,
        employment_status: data.employment_status || 'unknown',
        bio: data.bio || undefined,
        linkedin_url: data.linkedin_url || undefined,
        github_url: data.github_url || undefined,
        portfolio_url: data.portfolio_url || undefined,
        achievements: data.achievements || undefined,
        is_featured: data.is_featured,
        is_active: data.is_active,
        visibility: data.visibility,
      };
      if (avatar) {
        const fd = new FormData();
        fd.append('avatar', avatar);
        fd.append('_method', 'PUT');
        Object.entries(payload).forEach(([k, v]) => {
          if (v !== undefined) fd.append(k, typeof v === 'object' ? JSON.stringify(v) : String(v));
        });
        return alumniService.update(Number(id), fd);
      }
      return alumniService.update(Number(id), payload);
    },
    onSuccess: () => { toast.success('Alumni updated.'); navigate(`/admin/alumni/${id}`); },
    onError: () => toast.error('Failed to update alumni.'),
  });

  const onSubmit = (data: EditFormData) => mutation.mutate(data);

  const inputCls = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100 disabled:opacity-50 disabled:bg-slate-50';
  const labelCls = 'text-xs font-semibold text-slate-500';
  const selectCls = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100';

  if (isLoading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;
  if (!alumni) return <div className="py-20 text-center text-sm text-slate-500">Alumni not found.</div>;

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Link to={`/admin/alumni/${id}`} className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" />
        </Link>
        <div>
          <h1 className="text-2xl font-black text-slate-900">Edit Alumni</h1>
          <p className="mt-0.5 text-sm text-slate-500">Update alumni record and career information.</p>
        </div>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="max-w-4xl space-y-6">
        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Personal Information</h3>
            <p className="text-xs text-slate-500 mt-0.5">Alumni identity and contact details.</p>
          </div>
          <div className="p-5">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1.5">
                <label className={labelCls}>Full Name <span className="text-red-500">*</span></label>
                <input {...register('name')} className={inputCls} />
                {errors.name && <p className="text-xs text-red-500">{errors.name.message}</p>}
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Email Address <span className="text-red-500">*</span></label>
                <input type="email" {...register('email')} className={inputCls} />
                {errors.email && <p className="text-xs text-red-500">{errors.email.message}</p>}
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Phone Number</label>
                <input {...register('phone')} className={inputCls} />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <label className={labelCls}>Address</label>
                <textarea {...register('address')} rows={2} className={inputCls} />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <label className={labelCls}>Profile Photo</label>
                {alumni.user?.avatar && (
                  <div className="mb-2 flex items-center gap-3">
                    <img src={alumni.user.avatar} className="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100" />
                    <span className="text-xs text-slate-500">Current photo</span>
                  </div>
                )}
                <input type="file" accept="image/*" onChange={e => setAvatar(e.target.files?.[0] ?? null)} className="w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                <p className="text-xs text-slate-400">Upload new photo (max 2 MB)</p>
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Academic Information</h3>
            <p className="text-xs text-slate-500 mt-0.5">Department, program, and graduation details.</p>
          </div>
          <div className="p-5">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1.5">
                <label className={labelCls}>Department <span className="text-red-500">*</span></label>
                <select {...register('department_id')} onChange={e => { setValue('department_id', e.target.value); setValue('program_id', ''); }} className={selectCls}>
                  <option value="">Select Department</option>
                  {departments.map((d: { id: number; name: string }) => <option key={d.id} value={d.id}>{d.name}</option>)}
                </select>
                {errors.department_id && <p className="text-xs text-red-500">{errors.department_id.message}</p>}
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Program <span className="text-red-500">*</span></label>
                <select {...register('program_id')} className={selectCls}>
                  <option value="">Select Program</option>
                  {programs.map((p: { id: number; name: string }) => <option key={p.id} value={p.id}>{p.name}</option>)}
                </select>
                {errors.program_id && <p className="text-xs text-red-500">{errors.program_id.message}</p>}
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Roll Number</label>
                <input {...register('roll_number')} className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Admission Year</label>
                <input {...register('admission_year')} className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Graduation Year <span className="text-red-500">*</span></label>
                <input {...register('graduation_year')} className={inputCls} />
                {errors.graduation_year && <p className="text-xs text-red-500">{errors.graduation_year.message}</p>}
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Career Information</h3>
            <p className="text-xs text-slate-500 mt-0.5">Current employment and professional details.</p>
          </div>
          <div className="p-5">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1.5">
                <label className={labelCls}>Current Job Title</label>
                <input {...register('current_job')} className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Company Name</label>
                <input {...register('company_name')} className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Work Location</label>
                <input {...register('work_location')} className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Employment Status</label>
                <select {...register('employment_status')} className={selectCls}>
                  <option value="unknown">Unknown</option>
                  <option value="employed">Employed</option>
                  <option value="studying">Studying</option>
                  <option value="freelancing">Freelancing</option>
                  <option value="unemployed">Unemployed</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Bio & Social Links</h3>
            <p className="text-xs text-slate-500 mt-0.5">Biography and online presence.</p>
          </div>
          <div className="p-5">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1.5 md:col-span-2">
                <label className={labelCls}>Bio</label>
                <textarea {...register('bio')} rows={3} className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>LinkedIn URL</label>
                <input {...register('linkedin_url')} className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>GitHub URL</label>
                <input {...register('github_url')} className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Portfolio URL</label>
                <input {...register('portfolio_url')} className={inputCls} />
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Achievements</h3>
            <p className="text-xs text-slate-500 mt-0.5">Notable achievements (text).</p>
          </div>
          <div className="p-5">
            <div className="grid grid-cols-1 gap-4">
              <div className="space-y-1.5">
                <label className={labelCls}>Achievements</label>
                <textarea {...register('achievements')} rows={3} className={inputCls} />
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Account & Options</h3>
            <p className="text-xs text-slate-500 mt-0.5">Status, visibility, and featuring.</p>
          </div>
          <div className="p-5">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1.5">
                <label className={labelCls}>Active</label>
                <label className="inline-flex items-center gap-3 cursor-pointer">
                  <input type="hidden" value={watch('is_active') ? '1' : '0'} />
                  <input type="checkbox" checked={watch('is_active')} onChange={e => setValue('is_active', e.target.checked)}
                    className="rounded border-slate-300 text-[#8B0000] focus:ring-red-200" />
                  <span className="text-sm text-slate-700">Account is active</span>
                </label>
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Featured</label>
                <label className="inline-flex items-center gap-3 cursor-pointer">
                  <input type="hidden" value={watch('is_featured') ? '1' : '0'} />
                  <input type="checkbox" checked={watch('is_featured')} onChange={e => setValue('is_featured', e.target.checked)}
                    className="rounded border-slate-300 text-[#8B0000] focus:ring-red-200" />
                  <span className="text-sm text-slate-700">Show as featured alumni</span>
                </label>
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Profile Visibility</label>
                <select {...register('visibility')} className={selectCls}>
                  <option value="public">Public</option>
                  <option value="private">Private</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <button type="submit" disabled={mutation.isPending}
            className="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition disabled:opacity-50">
            {mutation.isPending ? <Spinner size="sm" /> : <Save className="h-4 w-4" />}
            Update Alumni
          </button>
          <Link to={`/admin/alumni/${id}`} className="text-sm text-slate-500 hover:text-slate-700">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
