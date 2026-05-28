import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import { Save, ArrowLeft } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import staffService from '@shared/services/staffService';

export default function EditStaffPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [form, setForm] = useState<any>({});

  const { data, isLoading } = useQuery({
    queryKey: ['staff', id],
    queryFn: () => staffService.show(Number(id)),
    enabled: !!id,
  });
  const staff = data?.data?.data;

  useEffect(() => {
    if (!staff) return;
    setForm({
      staff_code: staff.staff_code ?? '',
      name: staff.name ?? '',
      designation: staff.designation ?? '',
      department: staff.department ?? '',
      email: staff.email ?? '',
      phone: staff.phone ?? '',
      address: staff.address ?? '',
      dob: staff.dob ?? '',
      gender: staff.gender ?? '',
      employment_type: staff.employment_type ?? '',
      employment_status: staff.employment_status ?? '',
      join_date: staff.join_date ?? '',
      end_date: staff.end_date ?? '',
      salary_amount: staff.salary_amount ?? '',
      bio: staff.bio ?? '',
      public_visible: staff.public_visible ?? false,
      featured: staff.featured ?? false,
      show_email_public: staff.show_email_public ?? false,
      show_phone_public: staff.show_phone_public ?? false,
      order: staff.order ?? '0',
    });
  }, [staff]);

  const mutation = useMutation({
    mutationFn: () => {
      const payload = {
        staff_code: form.staff_code || undefined,
        name: form.name,
        designation: form.designation || undefined,
        department: form.department || undefined,
        email: form.email || undefined,
        phone: form.phone || undefined,
        address: form.address || undefined,
        dob: form.dob || undefined,
        gender: form.gender || undefined,
        employment_type: form.employment_type || undefined,
        employment_status: form.employment_status || undefined,
        join_date: form.join_date || undefined,
        end_date: form.end_date || undefined,
        salary_amount: form.salary_amount ? Number(form.salary_amount) : undefined,
        bio: form.bio || undefined,
        public_visible: form.public_visible,
        featured: form.featured,
        show_email_public: form.show_email_public,
        show_phone_public: form.show_phone_public,
        order: Number(form.order),
      };
      return staffService.update(Number(id), payload as Record<string, unknown>);
    },
    onSuccess: () => { toast.success('Staff member updated.'); navigate('/admin/staff'); },
    onError: () => toast.error('Failed to update staff member.'),
  });

  const set = (k: string, v: any) => setForm((f: any) => ({ ...f, [k]: v }));
  const inputCls = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50';
  const labelCls = 'text-xs font-semibold uppercase tracking-wider text-slate-500';

  if (isLoading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="space-y-5">
      <div className="flex items-center gap-4">
        <Link to="/admin/staff" className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" />
        </Link>
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Edit Staff</h1>
          <p className="mt-0.5 text-sm text-slate-500">{staff?.name}</p>
        </div>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="space-y-1.5">
            <label className={labelCls}>Staff Code</label>
            <input value={form.staff_code} onChange={e => set('staff_code', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Name *</label>
            <input value={form.name} onChange={e => set('name', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Designation</label>
            <input value={form.designation} onChange={e => set('designation', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Department</label>
            <input value={form.department} onChange={e => set('department', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Email</label>
            <input value={form.email} onChange={e => set('email', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Phone</label>
            <input value={form.phone} onChange={e => set('phone', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Address</label>
            <input value={form.address} onChange={e => set('address', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Date of Birth</label>
            <input type="date" value={form.dob} onChange={e => set('dob', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Gender</label>
            <select value={form.gender} onChange={e => set('gender', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Employment Type</label>
            <select value={form.employment_type} onChange={e => set('employment_type', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select type</option>
              {['full_time','part_time','contract','temporary'].map(s => (
                <option key={s} value={s}>{s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())}</option>
              ))}
            </select>
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Employment Status</label>
            <select value={form.employment_status} onChange={e => set('employment_status', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select status</option>
              {['active','leave','resigned'].map(s => (
                <option key={s} value={s}>{s.charAt(0).toUpperCase() + s.slice(1)}</option>
              ))}
            </select>
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Join Date</label>
            <input type="date" value={form.join_date} onChange={e => set('join_date', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>End Date</label>
            <input type="date" value={form.end_date} onChange={e => set('end_date', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Salary Amount</label>
            <input type="number" value={form.salary_amount} onChange={e => set('salary_amount', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Order</label>
            <input type="number" value={form.order} onChange={e => set('order', e.target.value)} className={inputCls} />
          </div>
        </div>

        <div className="space-y-1.5 mt-4">
          <label className={labelCls}>Bio</label>
          <textarea value={form.bio} onChange={e => set('bio', e.target.value)} rows={4}
            className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
        </div>

        <div className="flex flex-wrap items-center gap-6 pt-4">
          <div className="flex items-center gap-3">
            <input type="checkbox" id="public_visible" checked={form.public_visible} onChange={e => set('public_visible', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
            <label htmlFor="public_visible" className="text-sm font-medium text-slate-700">Public visible</label>
          </div>
          <div className="flex items-center gap-3">
            <input type="checkbox" id="featured" checked={form.featured} onChange={e => set('featured', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
            <label htmlFor="featured" className="text-sm font-medium text-slate-700">Featured</label>
          </div>
          <div className="flex items-center gap-3">
            <input type="checkbox" id="show_email_public" checked={form.show_email_public} onChange={e => set('show_email_public', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
            <label htmlFor="show_email_public" className="text-sm font-medium text-slate-700">Show email publicly</label>
          </div>
          <div className="flex items-center gap-3">
            <input type="checkbox" id="show_phone_public" checked={form.show_phone_public} onChange={e => set('show_phone_public', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
            <label htmlFor="show_phone_public" className="text-sm font-medium text-slate-700">Show phone publicly</label>
          </div>
        </div>

        <div className="flex items-center gap-3 pt-6">
          <button onClick={() => mutation.mutate()} disabled={mutation.isPending}
            className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition disabled:opacity-50">
            {mutation.isPending ? <Spinner size="sm" /> : <Save className="h-4 w-4" />}
            Update Staff
          </button>
          <Link to="/admin/staff" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</Link>
        </div>
      </div>
    </div>
  );
}
