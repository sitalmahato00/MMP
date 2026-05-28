import { useMutation } from '@tanstack/react-query';
import { useNavigate, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { zodResolver } from '@hookform/resolvers/zod';
import toast from 'react-hot-toast';
import staffService from '@shared/services/staffService';
import { BsDatePicker } from '@shared/components/ui/BsDatePicker';

const schema = z.object({
  staff_code: z.string().min(1, 'Staff code is required'),
  name: z.string().min(1, 'Name is required'),
  designation: z.string().min(1, 'Designation is required'),
  department: z.string().optional().default(''),
  email: z.string().optional().default(''),
  phone: z.string().optional().default(''),
  address: z.string().optional().default(''),
  dob: z.string().optional().default(''),
  gender: z.string().optional().default(''),
  employment_type: z.string().optional().default(''),
  employment_status: z.string().optional().default('active'),
  join_date: z.string().optional().default(''),
  end_date: z.string().optional().default(''),
  salary_amount: z.string().optional().default(''),
  working_schedule_label: z.string().optional().default(''),
  working_schedule_days: z.string().optional().default(''),
  working_schedule_start: z.string().optional().default(''),
  working_schedule_end: z.string().optional().default(''),
  assigned_roles: z.string().optional().default(''),
  responsibilities: z.string().optional().default(''),
  bio: z.string().optional().default(''),
  order: z.string().optional().default('0'),
  public_visible: z.boolean().default(true),
  featured: z.boolean().default(false),
  show_email_public: z.boolean().default(true),
  show_phone_public: z.boolean().default(true),
});

type FormData = z.infer<typeof schema>;

export default function CreateStaffPage() {
  const navigate = useNavigate();
  const { register, handleSubmit, setValue, watch, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: {
      employment_status: 'active',
      order: '0',
      public_visible: true,
      show_email_public: true,
    },
  });

  const mutation = useMutation({
    mutationFn: (data: FormData) => {
      const payload: Record<string, unknown> = {
        staff_code: data.staff_code,
        name: data.name,
        designation: data.designation,
        department: data.department || undefined,
        email: data.email || undefined,
        phone: data.phone || undefined,
        address: data.address || undefined,
        dob: data.dob || undefined,
        gender: data.gender || undefined,
        employment_type: data.employment_type || undefined,
        employment_status: data.employment_status,
        join_date: data.join_date || undefined,
        end_date: data.end_date || undefined,
        salary_amount: data.salary_amount ? Number(data.salary_amount) : undefined,
        bio: data.bio || undefined,
        working_schedule: {
          label: data.working_schedule_label || undefined,
          days: data.working_schedule_days ? data.working_schedule_days.split(',').map(s => s.trim()).filter(Boolean) : [],
          start: data.working_schedule_start || undefined,
          end: data.working_schedule_end || undefined,
        },
        assigned_roles: data.assigned_roles ? data.assigned_roles.split(',').map(s => s.trim()).filter(Boolean) : [],
        responsibilities: data.responsibilities ? data.responsibilities.split(',').map(s => s.trim()).filter(Boolean) : [],
        public_visible: data.public_visible,
        featured: data.featured,
        show_email_public: data.show_email_public,
        show_phone_public: data.show_phone_public,
        order: Number(data.order),
      };
      return staffService.create(payload);
    },
    onSuccess: () => { toast.success('Staff member created.'); navigate('/admin/staff'); },
    onError: () => toast.error('Failed to create staff member.'),
  });

  const inputCls = 'w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10';
  const labelCls = 'text-sm font-medium text-slate-700';
  const errorCls = 'text-sm text-rose-600';

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:flex-row lg:items-end lg:justify-between lg:p-8">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Administrative Staff</p>
          <h1 className="mt-2 text-3xl font-semibold text-slate-900">Add staff member</h1>
          <p className="mt-2 max-w-2xl text-sm text-slate-500">Create a profile that can later be published on the staff directory with selective contact visibility.</p>
        </div>
        <Link to="/admin/staff" className="inline-flex items-center justify-center rounded-full border border-slate-300 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Back to Staff</Link>
      </div>

      <form onSubmit={handleSubmit((data) => mutation.mutate(data))} className="space-y-8">
        <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:p-8">
          <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
              <p className="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Identity</p>
              <h2 className="mt-2 text-xl font-semibold text-slate-900">Staff profile basics</h2>
              <p className="mt-1 text-sm text-slate-500">These fields drive the administrative record and public directory card.</p>
            </div>
          </div>

          <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div className="space-y-2">
              <label className={labelCls}>Staff Code <span className="text-[#8B0000]">*</span></label>
              <input {...register('staff_code')} className={inputCls} />
              {errors.staff_code && <p className={errorCls}>{errors.staff_code.message}</p>}
            </div>

            <div className="space-y-2 md:col-span-2">
              <label className={labelCls}>Full Name <span className="text-[#8B0000]">*</span></label>
              <input {...register('name')} className={inputCls} />
              {errors.name && <p className={errorCls}>{errors.name.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Designation <span className="text-[#8B0000]">*</span></label>
              <input {...register('designation')} className={inputCls} />
              {errors.designation && <p className={errorCls}>{errors.designation.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Department / Office</label>
              <input {...register('department')} className={inputCls} />
              {errors.department && <p className={errorCls}>{errors.department.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Email</label>
              <input type="email" {...register('email')} className={inputCls} />
              {errors.email && <p className={errorCls}>{errors.email.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Phone</label>
              <input {...register('phone')} className={inputCls} />
              {errors.phone && <p className={errorCls}>{errors.phone.message}</p>}
            </div>

            <div className="space-y-2 md:col-span-2 xl:col-span-3">
              <label className={labelCls}>Address</label>
              <input {...register('address')} className={inputCls} />
              {errors.address && <p className={errorCls}>{errors.address.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Profile Photo</label>
              <input type="file" className="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-[#8B0000] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white" />
              {null}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Display Order</label>
              <input type="number" {...register('order')} className={inputCls} />
              {errors.order && <p className={errorCls}>{errors.order.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Date of Birth</label>
              <BsDatePicker value={watch('dob')} onChange={(bs) => setValue('dob', bs)} inputClassName={inputCls} />
              {errors.dob && <p className={errorCls}>{errors.dob.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Gender</label>
              <select {...register('gender')} className={inputCls}>
                <option value="">Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
                <option value="prefer_not_to_say">Prefer not to say</option>
              </select>
              {errors.gender && <p className={errorCls}>{errors.gender.message}</p>}
            </div>
          </div>
        </div>

        <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:p-8">
          <div className="mb-6">
            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Employment</p>
            <h2 className="mt-2 text-xl font-semibold text-slate-900">Work and visibility settings</h2>
            <p className="mt-1 text-sm text-slate-500">These values control status filters, public cards, and the staff profile timeline.</p>
          </div>

          <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div className="space-y-2">
              <label className={labelCls}>Employment Type</label>
              <select {...register('employment_type')} className={inputCls}>
                <option value="">Select type</option>
                <option value="full_time">Full Time</option>
                <option value="part_time">Part Time</option>
                <option value="contract">Contract</option>
                <option value="temporary">Temporary</option>
              </select>
              {errors.employment_type && <p className={errorCls}>{errors.employment_type.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Employment Status <span className="text-[#8B0000]">*</span></label>
              <select {...register('employment_status')} className={inputCls}>
                <option value="active">Active</option>
                <option value="leave">On Leave</option>
                <option value="resigned">Resigned</option>
              </select>
              {errors.employment_status && <p className={errorCls}>{errors.employment_status.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Join Date</label>
              <BsDatePicker value={watch('join_date')} onChange={(bs) => setValue('join_date', bs)} inputClassName={inputCls} />
              {errors.join_date && <p className={errorCls}>{errors.join_date.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>End Date</label>
              <BsDatePicker value={watch('end_date')} onChange={(bs) => setValue('end_date', bs)} inputClassName={inputCls} />
              {errors.end_date && <p className={errorCls}>{errors.end_date.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Salary Amount</label>
              <input type="number" step="0.01" {...register('salary_amount')} className={inputCls} />
              {errors.salary_amount && <p className={errorCls}>{errors.salary_amount.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Working Schedule Label</label>
              <input {...register('working_schedule_label')} className={inputCls} />
              {errors.working_schedule_label && <p className={errorCls}>{errors.working_schedule_label.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Working Days</label>
              <input {...register('working_schedule_days')} placeholder="Mon, Tue, Wed" className={inputCls} />
              <p className="text-xs text-slate-500">Use comma-separated day names.</p>
              {errors.working_schedule_days && <p className={errorCls}>{errors.working_schedule_days.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>Start Time</label>
              <input type="time" {...register('working_schedule_start')} className={inputCls} />
              {errors.working_schedule_start && <p className={errorCls}>{errors.working_schedule_start.message}</p>}
            </div>

            <div className="space-y-2">
              <label className={labelCls}>End Time</label>
              <input type="time" {...register('working_schedule_end')} className={inputCls} />
              {errors.working_schedule_end && <p className={errorCls}>{errors.working_schedule_end.message}</p>}
            </div>

            <div className="space-y-2 md:col-span-2 xl:col-span-3">
              <label className={labelCls}>Assigned Roles</label>
              <textarea {...register('assigned_roles')} rows={2} placeholder="Admin, Accounts, Procurement" className={`${inputCls} resize-none`} />
              <p className="text-xs text-slate-500">Comma-separated values are stored as a list.</p>
              {errors.assigned_roles && <p className={errorCls}>{errors.assigned_roles.message}</p>}
            </div>

            <div className="space-y-2 md:col-span-2 xl:col-span-3">
              <label className={labelCls}>Responsibilities</label>
              <textarea {...register('responsibilities')} rows={3} placeholder="Admissions support, records management, reports" className={`${inputCls} resize-none`} />
              <p className="text-xs text-slate-500">Comma-separated values are stored as a list.</p>
              {errors.responsibilities && <p className={errorCls}>{errors.responsibilities.message}</p>}
            </div>

            <div className="space-y-2 md:col-span-2 xl:col-span-3">
              <label className={labelCls}>Bio</label>
              <textarea {...register('bio')} rows={4} className={`${inputCls} resize-none`} />
              {errors.bio && <p className={errorCls}>{errors.bio.message}</p>}
            </div>
          </div>
        </div>

        <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:p-8">
          <div className="mb-6">
            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Visibility</p>
            <h2 className="mt-2 text-xl font-semibold text-slate-900">Public directory controls</h2>
            <p className="mt-1 text-sm text-slate-500">Use these toggles to decide how the profile appears on the public staff page.</p>
          </div>

          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label className="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <input type="checkbox" {...register('public_visible')} className="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]" />
              <span>
                <span className="block text-sm font-semibold text-slate-900">Public visible</span>
                <span className="mt-1 block text-sm text-slate-500">Show in the staff directory and profile routes.</span>
              </span>
            </label>

            <label className="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <input type="checkbox" {...register('featured')} className="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]" />
              <span>
                <span className="block text-sm font-semibold text-slate-900">Featured</span>
                <span className="mt-1 block text-sm text-slate-500">Highlight this profile in prominent staff surfaces.</span>
              </span>
            </label>

            <label className="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <input type="checkbox" {...register('show_email_public')} className="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]" />
              <span>
                <span className="block text-sm font-semibold text-slate-900">Show email</span>
                <span className="mt-1 block text-sm text-slate-500">Make the email address visible on the public profile.</span>
              </span>
            </label>

            <label className="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <input type="checkbox" {...register('show_phone_public')} className="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]" />
              <span>
                <span className="block text-sm font-semibold text-slate-900">Show phone</span>
                <span className="mt-1 block text-sm text-slate-500">Make the phone number visible on the public profile.</span>
              </span>
            </label>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <button type="submit" disabled={mutation.isPending} className="inline-flex items-center justify-center rounded-full bg-[#8B0000] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#8B0000]/25 transition hover:bg-[#6f0000] disabled:opacity-50">{mutation.isPending ? 'Saving...' : 'Create Staff Profile'}</button>
          <Link to="/admin/staff" className="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
