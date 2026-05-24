import { useState, useRef } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { ArrowLeft, User } from 'lucide-react';
import teacherService from '@services/teacherService';
import academicService from '@services/academicService';
import { Input } from '@components/ui/Input';
import { Button } from '@components/ui/Button';
import { BsDatePicker } from '@components/ui/BsDatePicker';
import { adToBS, bsToAD, bsToString } from '@shared/utils/nepaliDate';
import { Spinner } from '@components/ui/Spinner';
import toast from 'react-hot-toast';
import { clsx } from 'clsx';

const schema = z.object({
  name: z.string().min(2),
  email: z.string().email(),
  phone: z.string().optional(),
  password: z.string().optional(),
  gender: z.string().optional(),
  dob: z.string().optional(),
  address: z.string().optional(),
  department_id: z.coerce.number().min(1, 'Select department'),
  employee_id: z.string().optional(),
  employment_type: z.string().min(1),
  join_date: z.string().optional(),
  is_active: z.boolean().optional(),
  qualification: z.string().optional(),
  specialization: z.string().optional(),
});
type FormValues = z.infer<typeof schema>;

function Section({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <div className="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
        <h2 className="text-sm font-bold text-slate-700">{title}</h2>
      </div>
      <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">{children}</div>
    </div>
  );
}

function Field({ label, required, error, children, full }: { label: string; required?: boolean; error?: string; children: React.ReactNode; full?: boolean }) {
  return (
    <div className={full ? 'sm:col-span-2' : ''}>
      <label className="block text-xs font-bold text-slate-600 mb-1">{label}{required && <span className="text-red-500 ml-0.5">*</span>}</label>
      {children}
      {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
    </div>
  );
}

function Sel({ value, onChange, children, error }: { value: string; onChange: (v: string) => void; children: React.ReactNode; error?: string }) {
  return (
    <>
      <select value={value} onChange={e => onChange(e.target.value)}
        className={clsx('w-full rounded-xl border px-3.5 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition bg-slate-50', error ? 'border-red-400' : 'border-slate-200')}>
        {children}
      </select>
      {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
    </>
  );
}

export default function TeacherEditPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
  const [avatarFile, setAvatarFile] = useState<File | null>(null);
  const fileRef = useRef<HTMLInputElement>(null);

  const { data: teacherRes, isLoading } = useQuery({ queryKey: ['teacher', id], queryFn: () => teacherService.show(Number(id)) });
  const { data: deptsRes } = useQuery({ queryKey: ['departments'], queryFn: academicService.departments, staleTime: Infinity });

  const teacher = teacherRes?.data;

  const { register, handleSubmit, control, formState: { errors }, setError } = useForm<FormValues>({
    resolver: zodResolver(schema),
    values: teacher ? {
      name: teacher.user.name,
      email: teacher.user.email,
      phone: teacher.user.phone ?? '',
      gender: (teacher.user as any).gender ?? '',
      dob: (teacher.user as any).dob ? (() => { try { return bsToString(adToBS(new Date((teacher.user as any).dob))); } catch { return ''; } })() : '',
      address: (teacher.user as any).address ?? '',
      department_id: teacher.department?.id ?? 0,
      employee_id: teacher.employee_id ?? '',
      employment_type: (teacher as any).employment_type ?? 'permanent',
      join_date: teacher.joining_date ? (() => { try { return bsToString(adToBS(new Date(teacher.joining_date!))); } catch { return ''; } })() : '',
      is_active: teacher.status === 'active',
      qualification: teacher.qualification ?? '',
      specialization: (teacher as any).specialization ?? '',
    } : undefined,
  });

  const mutation = useMutation({
    mutationFn: async (values: FormValues) => {
      const fd = new FormData();
      Object.entries(values).forEach(([k, v]) => { if (v !== undefined && v !== '') fd.append(k, String(v)); });
      if (avatarFile) fd.append('avatar', avatarFile);
      if (values.dob) { try { const ad = bsToAD(...(values.dob.split('-').map(Number) as [number,number,number])); fd.set('dob', ad.toISOString().slice(0,10)); } catch {} }
      if (values.join_date) { try { const ad = bsToAD(...(values.join_date.split('-').map(Number) as [number,number,number])); fd.set('join_date', ad.toISOString().slice(0,10)); } catch {} }
      fd.append('_method', 'PUT');
      return teacherService.update(Number(id), fd as any);
    },
    onSuccess: () => {
      toast.success('Teacher updated.');
      queryClient.invalidateQueries({ queryKey: ['teachers'] });
      queryClient.invalidateQueries({ queryKey: ['teacher', id] });
      navigate(`../${id}`);
    },
    onError: (err: any) => {
      const apiErrors = err?.response?.data?.errors;
      if (apiErrors) Object.entries(apiErrors).forEach(([f, m]) => setError(f as any, { message: (m as string[])[0] }));
      else toast.error('Failed to update teacher.');
    },
  });

  if (isLoading) return <div className="flex h-64 items-center justify-center"><Spinner size="lg" /></div>;
  if (!teacher) return <div className="text-center py-20 text-slate-500">Teacher not found.</div>;

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Edit Teacher</h1>
          <p className="mt-0.5 text-sm text-slate-500">Updating profile for <span className="font-semibold text-slate-700">{teacher.user.name}</span>.</p>
        </div>
        <div className="flex gap-2">
          <Link to={`../${id}`} className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm">View Profile</Link>
          <Link to=".." className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm"><ArrowLeft className="h-4 w-4" /> Back</Link>
        </div>
      </div>

      <form onSubmit={handleSubmit(v => mutation.mutate(v))} className="space-y-5">
        <Section title="Personal Information">
          <div className="sm:col-span-2 flex items-start gap-5">
            <div className="flex flex-col items-center gap-2">
              <div className="h-24 w-24 overflow-hidden rounded-2xl border-2 border-slate-200 bg-slate-100 flex items-center justify-center cursor-pointer" onClick={() => fileRef.current?.click()}>
                {avatarPreview ? <img src={avatarPreview} className="h-full w-full object-cover" alt="Preview" />
                  : teacher.user.avatar ? <img src={teacher.user.avatar} className="h-full w-full object-cover" alt={teacher.user.name} />
                  : <User className="h-10 w-10 text-slate-400" />}
              </div>
              <button type="button" onClick={() => fileRef.current?.click()} className="text-xs font-semibold text-blue-600 hover:underline">Change Photo</button>
              <input ref={fileRef} type="file" accept="image/*" className="sr-only" onChange={e => { const f = e.target.files?.[0]; if (f) { setAvatarFile(f); setAvatarPreview(URL.createObjectURL(f)); } }} />
            </div>
            <div className="flex-1 min-w-0">
              <Field label="Full Name" required error={errors.name?.message}>
                <Input {...register('name')} error={errors.name?.message} />
              </Field>
            </div>
          </div>
          <Field label="Email" required error={errors.email?.message}><Input type="email" {...register('email')} error={errors.email?.message} /></Field>
          <Field label="Phone"><Input {...register('phone')} /></Field>
          <Field label="New Password"><Input type="password" {...register('password')} placeholder="Leave blank to keep current" /></Field>
          <Field label="Gender">
            <Controller name="gender" control={control} render={({ field }) => (
              <Sel value={field.value ?? ''} onChange={field.onChange}>
                <option value="">Select Gender</option>
                <option value="male">Male</option><option value="female">Female</option><option value="other">Other</option>
              </Sel>
            )} />
          </Field>
          <Field label="Date of Birth (BS)">
            <Controller name="dob" control={control} render={({ field }) => (
              <BsDatePicker value={field.value} onChange={bs => field.onChange(bs)} />
            )} />
          </Field>
          <Field label="Address" full>
            <textarea {...register('address')} rows={2} className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition resize-none" />
          </Field>
        </Section>

        <Section title="Employment Details">
          <Field label="Department" required error={errors.department_id?.message}>
            <Controller name="department_id" control={control} render={({ field }) => (
              <Sel value={String(field.value ?? '')} onChange={v => field.onChange(Number(v))} error={errors.department_id?.message}>
                <option value="">Select Department</option>
                {(deptsRes?.data ?? []).map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
              </Sel>
            )} />
          </Field>
          <Field label="Employee ID"><Input {...register('employee_id')} /></Field>
          <Field label="Employment Type" required error={errors.employment_type?.message}>
            <Controller name="employment_type" control={control} render={({ field }) => (
              <Sel value={field.value ?? ''} onChange={field.onChange} error={errors.employment_type?.message}>
                <option value="permanent">Permanent</option><option value="contract">Contract</option><option value="part-time">Part-time</option>
              </Sel>
            )} />
          </Field>
          <Field label="Join Date (BS)">
            <Controller name="join_date" control={control} render={({ field }) => (
              <BsDatePicker value={field.value} onChange={bs => field.onChange(bs)} />
            )} />
          </Field>
          <Field label="Active Status" full>
            <Controller name="is_active" control={control} render={({ field }) => (
              <label className="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <div><p className="text-sm font-semibold text-slate-700">Active Status</p><p className="text-xs text-slate-400">Inactive teachers cannot log in.</p></div>
                <div className="relative inline-flex cursor-pointer items-center">
                  <input type="checkbox" checked={field.value ?? false} onChange={e => field.onChange(e.target.checked)} className="sr-only peer" />
                  <div className="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full" />
                </div>
              </label>
            )} />
          </Field>
        </Section>

        <Section title="Academic Background">
          <Field label="Qualification"><Input {...register('qualification')} placeholder="e.g. PhD Computer Science" /></Field>
          <Field label="Specialization"><Input {...register('specialization')} placeholder="e.g. Machine Learning" /></Field>
        </Section>

        <div className="flex items-center gap-3">
          <Button type="submit" loading={mutation.isPending}>Save Changes</Button>
          <Link to=".." className="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
