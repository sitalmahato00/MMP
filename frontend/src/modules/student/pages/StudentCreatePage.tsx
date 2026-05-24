import { useState, useRef } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useNavigate, Link } from 'react-router-dom';
import { ArrowLeft, User } from 'lucide-react';
import studentService from '@services/studentService';
import academicService from '@services/academicService';
import { Input } from '@components/ui/Input';
import { Button } from '@components/ui/Button';
import { BsDatePicker } from '@components/ui/BsDatePicker';
import { bsToAD } from '@shared/utils/nepaliDate';
import toast from 'react-hot-toast';
import { clsx } from 'clsx';

const TABS = ['personal', 'enrollment', 'health', 'parent'] as const;
type Tab = typeof TABS[number];
const TAB_LABELS: Record<Tab, string> = {
  personal: 'Personal', enrollment: 'Enrollment',
  health: 'Health & Emergency', parent: 'Parent Account',
};

const schema = z.object({
  name: z.string().min(2, 'Name is required'),
  email: z.string().email('Valid email required'),
  phone: z.string().optional(),
  password: z.string().min(8, 'Minimum 8 characters'),
  gender: z.string().optional(),
  dob: z.string().optional(),
  address: z.string().optional(),
  student_no: z.string().min(1, 'Student number is required'),
  registration_number: z.string().optional(),
  program_id: z.coerce.number().min(1, 'Select program'),
  current_semester: z.coerce.number().min(1).max(6),
  section: z.string().optional(),
  batch: z.string().optional(),
  admission_date: z.string().optional(),
  status: z.string().optional(),
  blood_group: z.string().optional(),
  guardian_name: z.string().optional(),
  guardian_phone: z.string().optional(),
  create_parent: z.boolean().optional(),
  parent_name: z.string().optional(),
  parent_email: z.string().optional(),
  parent_phone: z.string().optional(),
  parent_relation: z.string().optional(),
  parent_occupation: z.string().optional(),
});
type FormValues = z.infer<typeof schema>;

function Section({ title, subtitle, children }: { title: string; subtitle?: string; children: React.ReactNode }) {
  return (
    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <div className="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
        <h2 className="text-sm font-bold text-slate-700">{title}</h2>
        {subtitle && <p className="text-xs text-slate-400 mt-0.5">{subtitle}</p>}
      </div>
      <div className="p-6">{children}</div>
    </div>
  );
}

function Grid({ children }: { children: React.ReactNode }) {
  return <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">{children}</div>;
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

function Sel({ name, value, onChange, children, error }: { name: string; value: string; onChange: (v: string) => void; children: React.ReactNode; error?: string }) {
  return (
    <>
      <select name={name} value={value} onChange={e => onChange(e.target.value)}
        className={clsx('w-full rounded-xl border px-3.5 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition bg-slate-50',
          error ? 'border-red-400 bg-red-50' : 'border-slate-200')}>
        {children}
      </select>
      {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
    </>
  );
}

export default function StudentCreatePage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [tab, setTab] = useState<Tab>('personal');
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
  const [avatarFile, setAvatarFile] = useState<File | null>(null);
  const [createParent, setCreateParent] = useState(false);
  const fileRef = useRef<HTMLInputElement>(null);

  const { data: programsRes } = useQuery({ queryKey: ['programs'], queryFn: () => academicService.programs(), staleTime: Infinity });
  const { data: sessionsRes } = useQuery({ queryKey: ['academic-sessions'], queryFn: academicService.sessions, staleTime: Infinity });

  const { register, handleSubmit, control, formState: { errors }, setError } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { current_semester: 1, status: 'active' },
  });

  const mutation = useMutation({
    mutationFn: async (values: FormValues) => {
      const fd = new FormData();
      Object.entries(values).forEach(([k, v]) => { if (v !== undefined && v !== '') fd.append(k, String(v)); });
      if (avatarFile) fd.append('avatar', avatarFile);
      // Convert BS dates to AD
      if (values.dob) { try { const ad = bsToAD(...(values.dob.split('-').map(Number) as [number,number,number])); fd.set('dob', ad.toISOString().slice(0,10)); } catch {} }
      if (values.admission_date) { try { const ad = bsToAD(...(values.admission_date.split('-').map(Number) as [number,number,number])); fd.set('admission_date', ad.toISOString().slice(0,10)); } catch {} }
      return studentService.create(fd as any);
    },
    onSuccess: (res) => {
      toast.success('Student enrolled successfully.');
      queryClient.invalidateQueries({ queryKey: ['students'] });
      navigate(`../${res.data!.id}`);
    },
    onError: (err: any) => {
      const apiErrors = err?.response?.data?.errors;
      if (apiErrors) Object.entries(apiErrors).forEach(([f, m]) => setError(f as any, { message: (m as string[])[0] }));
      else toast.error('Failed to enroll student.');
    },
  });

  function handleAvatar(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (file) { setAvatarFile(file); setAvatarPreview(URL.createObjectURL(file)); }
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Enroll Student</h1>
          <p className="mt-0.5 text-sm text-slate-500">Add a new student to the college system.</p>
        </div>
        <Link to=".." className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm">
          <ArrowLeft className="h-4 w-4" /> Back to Students
        </Link>
      </div>

      {/* Tab nav */}
      <div className="flex gap-1 rounded-xl bg-slate-100 p-1">
        {TABS.map(t => (
          <button key={t} type="button" onClick={() => setTab(t)}
            className={clsx('flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition',
              tab === t ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700')}>
            {TAB_LABELS[t]}
          </button>
        ))}
      </div>

      <form onSubmit={handleSubmit(v => mutation.mutate(v))} className="space-y-5">

        {/* ── PERSONAL TAB ── */}
        {tab === 'personal' && (
          <Section title="Personal Information" subtitle="Student's identity, contact details, and login credentials.">
            {/* Profile photo — full width at top */}
            <div className="mb-6 flex items-start gap-5 sm:col-span-2">
              <div className="flex flex-col items-center gap-2">
                <div className="h-24 w-24 overflow-hidden rounded-2xl border-2 border-slate-200 bg-slate-100 flex items-center justify-center cursor-pointer" onClick={() => fileRef.current?.click()}>
                  {avatarPreview
                    ? <img src={avatarPreview} className="h-full w-full object-cover" alt="Preview" />
                    : <User className="h-10 w-10 text-slate-400" />}
                </div>
                <button type="button" onClick={() => fileRef.current?.click()} className="text-xs font-semibold text-blue-600 hover:underline">Upload Photo</button>
                <input ref={fileRef} type="file" accept="image/*" className="sr-only" onChange={handleAvatar} />
              </div>
              <div className="flex-1 min-w-0">
                <Field label="Full Name" required error={errors.name?.message}>
                  <Input {...register('name')} placeholder="Full legal name" error={errors.name?.message} />
                </Field>
              </div>
            </div>
            <Grid>
              <Field label="Email Address" required error={errors.email?.message}>
                <Input type="email" {...register('email')} placeholder="student@example.com" error={errors.email?.message} />
              </Field>
              <Field label="Phone Number" error={errors.phone?.message}>
                <Input {...register('phone')} placeholder="98XXXXXXXX" />
              </Field>
              <Field label="Password" required error={errors.password?.message}>
                <Input type="password" {...register('password')} placeholder="Min. 8 characters" error={errors.password?.message} />
              </Field>
              <Field label="Gender">
                <Controller name="gender" control={control} render={({ field }) => (
                  <Sel name="gender" value={field.value ?? ''} onChange={field.onChange}>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </Sel>
                )} />
              </Field>
              <Field label="Date of Birth (BS)" error={errors.dob?.message}>
                <Controller name="dob" control={control} render={({ field }) => (
                  <BsDatePicker value={field.value} onChange={(bs) => field.onChange(bs)} error={errors.dob?.message} />
                )} />
              </Field>
              <Field label="Permanent Address" full>
                <textarea {...register('address')} rows={2} placeholder="District, Province, Country"
                  className="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition resize-none" />
              </Field>
            </Grid>
          </Section>
        )}

        {/* ── ENROLLMENT TAB ── */}
        {tab === 'enrollment' && (
          <Section title="Enrollment Details" subtitle="Academic program, semester, and session assignment.">
            <Grid>
              <Field label="Student ID" required error={errors.student_no?.message}>
                <Input {...register('student_no')} placeholder="e.g. S-2081-001" error={errors.student_no?.message} />
              </Field>
              <Field label="Registration Number">
                <Input {...register('registration_number')} placeholder="CTEVT reg. number (optional)" />
              </Field>
              <Field label="Program" required error={errors.program_id?.message}>
                <Controller name="program_id" control={control} render={({ field }) => (
                  <Sel name="program_id" value={String(field.value ?? '')} onChange={v => field.onChange(Number(v))} error={errors.program_id?.message}>
                    <option value="">Select Program</option>
                    {(programsRes?.data ?? []).map(p => <option key={p.id} value={p.id}>{p.name} — {(p as any).department?.name}</option>)}
                  </Sel>
                )} />
              </Field>
              <Field label="Current Semester" required error={errors.current_semester?.message}>
                <Controller name="current_semester" control={control} render={({ field }) => (
                  <Sel name="current_semester" value={String(field.value ?? 1)} onChange={v => field.onChange(Number(v))}>
                    {[1,2,3,4,5,6].map(i => <option key={i} value={i}>Semester {i}</option>)}
                  </Sel>
                )} />
              </Field>
              <Field label="Section"><Input {...register('section')} placeholder="e.g. A, B" /></Field>
              <Field label="Batch / Year"><Input {...register('batch')} placeholder="e.g. 2081" /></Field>
              <Field label="Admission Date (BS)">
                <Controller name="admission_date" control={control} render={({ field }) => (
                  <BsDatePicker value={field.value} onChange={(bs) => field.onChange(bs)} />
                )} />
              </Field>
              <Field label="Initial Status">
                <Controller name="status" control={control} render={({ field }) => (
                  <Sel name="status" value={field.value ?? 'active'} onChange={field.onChange}>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                  </Sel>
                )} />
              </Field>
            </Grid>
          </Section>
        )}

        {/* ── HEALTH TAB ── */}
        {tab === 'health' && (
          <Section title="Health & Emergency Contact" subtitle="Stored on the student record for quick access.">
            <Grid>
              <Field label="Blood Group">
                <Controller name="blood_group" control={control} render={({ field }) => (
                  <Sel name="blood_group" value={field.value ?? ''} onChange={field.onChange}>
                    <option value="">Not specified</option>
                    {['A+','A-','B+','B-','O+','O-','AB+','AB-'].map(g => <option key={g} value={g}>{g}</option>)}
                  </Sel>
                )} />
              </Field>
              <Field label="Emergency Contact Name">
                <Input {...register('guardian_name')} placeholder="Parent or guardian name" />
              </Field>
              <Field label="Emergency Contact Phone">
                <Input {...register('guardian_phone')} placeholder="98XXXXXXXX" />
              </Field>
            </Grid>
          </Section>
        )}

        {/* ── PARENT TAB ── */}
        {tab === 'parent' && (
          <Section title="Parent / Guardian Account" subtitle="Optionally create a parent portal account.">
            <label className="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:bg-slate-50 mb-5">
              <input type="checkbox" checked={createParent} onChange={e => setCreateParent(e.target.checked)}
                className="mt-0.5 h-4 w-4 flex-shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
              <div>
                <span className="font-semibold text-slate-800 text-sm">Auto-create a parent / guardian account</span>
                <p className="mt-0.5 text-xs text-slate-500 leading-relaxed">A login account will be created for the parent and linked to this student.</p>
              </div>
            </label>
            {createParent && (
              <Grid>
                <Field label="Parent Full Name" required><Input {...register('parent_name')} placeholder="Full name" /></Field>
                <Field label="Parent Email" required><Input type="email" {...register('parent_email')} placeholder="parent@example.com" /></Field>
                <Field label="Parent Phone"><Input {...register('parent_phone')} placeholder="98XXXXXXXX" /></Field>
                <Field label="Relation to Student">
                  <Controller name="parent_relation" control={control} render={({ field }) => (
                    <Sel name="parent_relation" value={field.value ?? 'father'} onChange={field.onChange}>
                      {['father','mother','guardian','uncle','sibling','other'].map(r => <option key={r} value={r}>{r.charAt(0).toUpperCase()+r.slice(1)}</option>)}
                    </Sel>
                  )} />
                </Field>
                <Field label="Occupation"><Input {...register('parent_occupation')} placeholder="e.g. Teacher, Farmer" /></Field>
              </Grid>
            )}
          </Section>
        )}

        {/* Navigation buttons */}
        <div className="flex items-center gap-3">
          {tab !== 'personal' && (
            <button type="button" onClick={() => setTab(TABS[TABS.indexOf(tab) - 1])}
              className="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
          )}
          {tab !== 'parent' ? (
            <button type="button" onClick={() => setTab(TABS[TABS.indexOf(tab) + 1])}
              className="rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 transition">
              Next: {TAB_LABELS[TABS[TABS.indexOf(tab) + 1]]} →
            </button>
          ) : (
            <Button type="submit" loading={mutation.isPending}>Enroll Student</Button>
          )}
          <Link to=".." className="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
