import { useState, useRef } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useNavigate, Link } from 'react-router-dom';
import { ArrowLeft, User } from 'lucide-react';
import teacherService from '@services/teacherService';
import academicService from '@services/academicService';
import { Input } from '@components/ui/Input';
import { Button } from '@components/ui/Button';
import { BsDatePicker } from '@components/ui/BsDatePicker';
import { bsToAD } from '@shared/utils/nepaliDate';
import toast from 'react-hot-toast';
import { clsx } from 'clsx';

const schema = z.object({
  name: z.string().min(2, 'Name is required'),
  email: z.string().email('Valid email required'),
  phone: z.string().optional(),
  password: z.string().min(8, 'Minimum 8 characters'),
  password_confirmation: z.string().min(8),
  gender: z.string().optional(),
  dob: z.string().optional(),
  address: z.string().optional(),
  department_id: z.coerce.number().min(1, 'Select department'),
  employee_id: z.string().optional(),
  employment_type: z.string().min(1, 'Select employment type'),
  join_date: z.string().optional(),
  qualification: z.string().optional(),
  specialization: z.string().optional(),
});
type FormValues = z.infer<typeof schema>;

function Section({ title, subtitle, children }: { title: string; subtitle?: string; children: React.ReactNode }) {
  return (
    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <div className="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
        <h2 className="text-sm font-bold text-slate-700">{title}</h2>
        {subtitle && <p className="text-xs text-slate-400 mt-0.5">{subtitle}</p>}
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
        className={clsx('w-full rounded-xl border px-3.5 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition bg-slate-50',
          error ? 'border-red-400 bg-red-50' : 'border-slate-200')}>
        {children}
      </select>
      {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
    </>
  );
}

export default function TeacherCreatePage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
  const [avatarFile, setAvatarFile] = useState<File | null>(null);
  const fileRef = useRef<HTMLInputElement>(null);

  const { data: deptsRes } = useQuery({ queryKey: ['departments'], queryFn: academicService.departments, staleTime: Infinity });

  const { register, handleSubmit, control, formState: { errors }, setError } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { employment_type: 'permanent' },
  });

  const mutation = useMutation({
    mutationFn: async (values: FormValues) => {
      const fd = new FormData();
      Object.entries(values).forEach(([k, v]) => { if (v !== undefined && v !== '') fd.append(k, String(v)); });
      if (avatarFile) fd.append('avatar', avatarFile);
      if (values.dob) { try { const ad = bsToAD(...(values.dob.split('-').map(Number) as [number,number,number])); fd.set('dob', ad.toISOString().slice(0,10)); } catch {} }
      if (values.join_date) { try { const ad = bsToAD(...(values.join_date.split('-').map(Number) as [number,number,number])); fd.set('join_date', ad.toISOString().slice(0,10)); } catch {} }
      return teacherService.create(fd as any);
    },
    onSuccess: (res: any) => {
      toast.success('Teacher added successfully.');
      queryClient.invalidateQueries({ queryKey: ['teachers'] });
      navigate(`../${res.data?.id ?? ''}`);
    },
    onError: (err: any) => {
      const apiErrors = err?.response?.data?.errors;
      if (apiErrors) Object.entries(apiErrors).forEach(([f, m]) => setError(f as any, { message: (m as string[])[0] }));
      else toast.error('Failed to add teacher.');
    },
  });

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Add Teacher</h1>
          <p className="mt-0.5 text-sm text-slate-500">Onboard a new faculty member to the system.</p>
        </div>
        <Link to=".." className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm">
          <ArrowLeft className="h-4 w-4" /> Back to Teachers
        </Link>
      </div>

      <form onSubmit={handleSubmit(v => mutation.mutate(v))} className="space-y-5">
        {/* Personal */}
        <Section title="Personal Information" subtitle="Account credentials and personal details.">
          {/* Photo + Name — full width row */}
          <div className="sm:col-span-2 flex items-start gap-5">
            <div className="flex flex-col items-center gap-2">
              <div className="h-24 w-24 overflow-hidden rounded-2xl border-2 border-slate-200 bg-slate-100 flex items-center justify-center cursor-pointer" onClick={() => fileRef.current?.click()}>
                {avatarPreview ? <img src={avatarPreview} className="h-full w-full object-cover" alt="Preview" /> : <User className="h-10 w-10 text-slate-400" />}
              </div>
              <button type="button" onClick={() => fileRef.current?.click()} className="text-xs font-semibold text-blue-600 hover:underline">Upload Photo</button>
              <input ref={fileRef} type="file" accept="image/*" className="sr-only" onChange={e => { const f = e.target.files?.[0]; if (f) { setAvatarFile(f); setAvatarPreview(URL.createObjectURL(f)); } }} />
            </div>
            <div className="flex-1 min-w-0">
              <Field label="Full Name" required error={errors.name?.message}>
                <Input {...register('name')} placeholder="Teacher's full name" error={errors.name?.message} />
              </Field>
            </div>
          </div>
          <Field label="Email" required error={errors.email?.message}>
            <Input type="email" {...register('email')} placeholder="teacher@institution.edu.np" error={errors.email?.message} />
          </Field>
          <Field label="Phone"><Input {...register('phone')} placeholder="98XXXXXXXX" /></Field>
          <Field label="Password" required error={errors.password?.message}>
            <Input type="password" {...register('password')} placeholder="Minimum 8 characters" error={errors.password?.message} />
          </Field>
          <Field label="Confirm Password" required>
            <Input type="password" {...register('password_confirmation')} placeholder="Re-enter password" />
          </Field>
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
            <textarea {...register('address')} rows={2} placeholder="Full address"
              className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition resize-none" />
          </Field>
        </Section>

        {/* Employment */}
        <Section title="Employment Details" subtitle="Department placement and role information.">
          <Field label="Department" required error={errors.department_id?.message}>
            <Controller name="department_id" control={control} render={({ field }) => (
              <Sel value={String(field.value ?? '')} onChange={v => field.onChange(Number(v))} error={errors.department_id?.message}>
                <option value="">Select Department</option>
                {(deptsRes?.data ?? []).map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
              </Sel>
            )} />
          </Field>
          <Field label="Employee ID"><Input {...register('employee_id')} placeholder="e.g. EMP-001" /></Field>
          <Field label="Designation">
            <input type="text" value="Teacher" readOnly className="w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600 cursor-not-allowed" />
            <p className="mt-1 text-xs text-slate-500">HODs are managed separately through the HOD management interface</p>
          </Field>
          <Field label="Employment Type" required error={errors.employment_type?.message}>
            <Controller name="employment_type" control={control} render={({ field }) => (
              <Sel value={field.value ?? ''} onChange={field.onChange} error={errors.employment_type?.message}>
                <option value="">Select Type</option>
                <option value="permanent">Permanent</option><option value="contract">Contract</option><option value="part-time">Part-time</option>
              </Sel>
            )} />
          </Field>
          <Field label="Join Date (BS)">
            <Controller name="join_date" control={control} render={({ field }) => (
              <BsDatePicker value={field.value} onChange={bs => field.onChange(bs)} />
            )} />
          </Field>
        </Section>

        {/* Academic */}
        <Section title="Academic Background" subtitle="Educational qualifications and expertise.">
          <Field label="Qualification"><Input {...register('qualification')} placeholder="e.g. PhD Computer Science" /></Field>
          <Field label="Specialization"><Input {...register('specialization')} placeholder="e.g. Machine Learning, Networking" /></Field>
        </Section>

        <div className="flex items-center gap-3">
          <Button type="submit" loading={mutation.isPending}>Add Teacher</Button>
          <Link to=".." className="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
