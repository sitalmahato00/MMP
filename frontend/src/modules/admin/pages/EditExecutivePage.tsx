import { useEffect, useRef } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { Input, Select, BsDatePicker, Spinner } from '@shared/components/ui';
import executiveService from '@shared/services/executiveService';

const schema = z.object({
  name: z.string().min(1, 'Full name is required'),
  type: z.enum(['principal', 'president']),
  designation: z.string().optional().default(''),
  order: z.coerce.number(),
  start_date_bs: z.string().min(1, 'Start date is required'),
  end_date_bs: z.string().optional().default(''),
  is_current: z.boolean(),
  message: z.string().optional().default(''),
});

type FormValues = z.infer<typeof schema>;

export default function EditExecutivePage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const avatarRef = useRef<HTMLInputElement>(null);

  const { data, isLoading } = useQuery({
    queryKey: ['executive', id],
    queryFn: () => executiveService.show(Number(id)),
    enabled: !!id,
  });

  const executive = data?.data;

  const { register, handleSubmit, control, reset, formState: { errors } } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: '',
      type: 'principal',
      designation: '',
      order: 0,
      start_date_bs: '',
      end_date_bs: '',
      is_current: false,
      message: '',
    },
  });

  useEffect(() => {
    if (executive) {
      reset({
        name: executive.name,
        type: executive.type,
        designation: executive.designation ?? '',
        order: executive.order,
        start_date_bs: executive.start_date_bs,
        end_date_bs: executive.end_date_bs ?? '',
        is_current: executive.is_current,
        message: executive.message ?? '',
      });
    }
  }, [executive, reset]);

  const mutation = useMutation({
    mutationFn: (payload: FormData) => executiveService.update(Number(id), payload),
    onSuccess: () => { toast.success('Executive record updated.'); navigate('/admin/executives'); },
    onError: () => toast.error('Failed to update executive.'),
  });

  const deleteMutation = useMutation({
    mutationFn: () => executiveService.destroy(Number(id)),
    onSuccess: () => { toast.success('Executive record deleted.'); queryClient.invalidateQueries({ queryKey: ['executives'] }); navigate('/admin/executives'); },
    onError: () => toast.error('Failed to delete executive.'),
  });

  const onSubmit = (data: FormValues) => {
    const fd = new FormData();
    fd.append('name', data.name);
    fd.append('type', data.type);
    if (data.designation) fd.append('designation', data.designation);
    fd.append('order', String(data.order));
    fd.append('start_date_bs', data.start_date_bs);
    if (data.end_date_bs) fd.append('end_date_bs', data.end_date_bs);
    fd.append('is_current', data.is_current ? '1' : '0');
    if (data.message) fd.append('message', data.message);
    fd.append('_method', 'PUT');
    if (avatarRef.current?.files?.[0]) fd.append('avatar', avatarRef.current.files[0]);
    mutation.mutate(fd);
  };

  if (isLoading) return <div className="flex justify-center py-20"><Spinner /></div>;

  return (
    <div className="max-w-4xl space-y-6">
      <div>
        <Link to="/admin/executives" className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 transition mb-3">
          <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back
        </Link>
        <h1 className="text-2xl font-black tracking-tight text-slate-900">Update Leadership Record</h1>
        <p className="mt-0.5 text-sm text-slate-500">{executive?.name}</p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} encType="multipart/form-data" className="space-y-6">
        <section className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
          <h2 className="text-lg font-bold text-slate-900">Identity &amp; Title</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <Input
              label="Full Name"
              required
              error={errors.name?.message}
              {...register('name')}
            />
            <Select
              label="Leadership Type"
              required
              options={[
                { value: 'principal', label: 'Principal / Director Type' },
                { value: 'president', label: 'President / Chairman Type' },
              ]}
              error={errors.type?.message}
              {...register('type')}
            />
            <Input
              label="Specific Designation"
              error={errors.designation?.message}
              {...register('designation')}
            />
            <Input
              label="Numerical Display Order"
              type="number"
              required
              error={errors.order?.message}
              {...register('order')}
            />
            <div className="md:col-span-2 space-y-1.5">
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Official Profile Portrait</label>
              {executive?.avatar && (
                <div className="mb-3">
                  <img
                    src={`/storage/${executive.avatar}`}
                    alt={executive.name}
                    className="w-20 h-20 rounded-full object-cover ring-2 ring-gray-100"
                  />
                </div>
              )}
              <input
                type="file"
                ref={avatarRef}
                accept="image/*"
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition"
              />
              <p className="text-xs text-gray-400 mt-1">Leave blank to keep existing photo.</p>
            </div>
          </div>
        </section>

        <section className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
          <h2 className="text-lg font-bold text-slate-900">Tenure (Bikram Sambat Dates)</h2>
          <p className="text-sm text-gray-500 px-1">Please enter all tenure dates using the BS Calendar (YYYY-MM-DD).</p>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            <Controller
              name="start_date_bs"
              control={control}
              render={({ field }) => (
                <BsDatePicker
                  label="Start Date (BS)"
                  required
                  value={field.value}
                  onChange={(bsDate) => field.onChange(bsDate)}
                  error={errors.start_date_bs?.message}
                />
              )}
            />
            <Controller
              name="end_date_bs"
              control={control}
              render={({ field }) => (
                <BsDatePicker
                  label="End Date (BS) - Leave empty if present"
                  value={field.value}
                  onChange={(bsDate) => field.onChange(bsDate)}
                  error={errors.end_date_bs?.message}
                />
              )}
            />
            <label className="flex items-center gap-3 mt-4 cursor-pointer p-4 bg-gray-50 rounded-xl border border-gray-100 md:col-span-2">
              <input
                type="checkbox"
                className="w-5 h-5 accent-[#8B0000] rounded border-gray-300"
                {...register('is_current')}
              />
              <div className="flex flex-col">
                <span className="text-sm font-bold text-gray-900">Current Incumbent</span>
                <span className="text-xs text-gray-500">Flags this person securely as the currently serving leader in this role.</span>
              </div>
            </label>
          </div>
        </section>

        <section className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
          <h2 className="text-lg font-bold text-slate-900">Institutional Message (Optional)</h2>
          <div className="grid grid-cols-1 gap-5">
            <div className="space-y-1.5">
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-500">Message from the Desk</label>
              <textarea
                rows={8}
                placeholder="Enter their primary address or welcome message here..."
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none"
                {...register('message')}
              />
            </div>
          </div>
        </section>

        <div className="flex items-center gap-3">
          <button
            type="submit"
            disabled={mutation.isPending}
            className="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#6B0000] transition disabled:opacity-50"
          >
            {mutation.isPending ? 'Saving...' : 'Save Changes'}
          </button>
          <Link
            to="/admin/executives"
            className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition"
          >
            Cancel
          </Link>
        </div>
      </form>

      <div className="mt-8 border-t pt-8">
        <div className="flex items-center justify-between">
          <div>
            <h4 className="text-sm font-bold text-red-600">Danger Zone</h4>
            <p className="text-xs text-gray-500">Permanently delete this executive record from the history books.</p>
          </div>
          <button
            type="button"
            onClick={() => {
              if (confirm('Are you sure you want to permanently delete this historical record?')) {
                deleteMutation.mutate();
              }
            }}
            className="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition"
          >
            Delete History Record
          </button>
        </div>
      </div>
    </div>
  );
}
