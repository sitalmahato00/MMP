import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import { Save, ArrowLeft } from 'lucide-react';
import { Spinner } from '@components/ui/Spinner';
import alumniService from '@shared/services/alumniService';
import academicService from '@shared/services/academicService';

export default function EditAlumniPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [form, setForm] = useState<any>({});

  const { data, isLoading } = useQuery({
    queryKey: ['alumni', id],
    queryFn: () => alumniService.show(Number(id)),
    enabled: !!id,
  });
  const alumni = data?.data?.data;

  useEffect(() => {
    if (!alumni) return;
    setForm({
      name: alumni.user?.name ?? '',
      email: alumni.user?.email ?? '',
      phone: alumni.user?.phone ?? '',
      address: alumni.user?.address ?? '',
      department_id: alumni.department_id ?? '',
      program_id: alumni.program_id ?? '',
      graduation_year: alumni.graduation_year ?? '',
      admission_year: alumni.admission_year ?? '',
      roll_number: alumni.roll_number ?? '',
      current_job: alumni.current_job ?? '',
      company_name: alumni.company_name ?? '',
      work_location: alumni.work_location ?? '',
      employment_status: alumni.employment_status ?? '',
      bio: alumni.bio ?? '',
      linkedin_url: alumni.linkedin_url ?? '',
      github_url: alumni.github_url ?? '',
      portfolio_url: alumni.portfolio_url ?? '',
      achievements: alumni.achievements ?? '',
      is_featured: alumni.is_featured ?? false,
      is_active: alumni.is_active ?? true,
      visibility: alumni.visibility ?? 'public',
    });
  }, [alumni]);

  const { data: departmentsData } = useQuery({
    queryKey: ['departments'],
    queryFn: () => academicService.departments(),
  });
  const departments = departmentsData?.data?.data ?? [];

  const { data: programsData } = useQuery({
    queryKey: ['programs', form.department_id],
    queryFn: () => academicService.programs(form.department_id ? Number(form.department_id) : undefined),
    enabled: !!form.department_id,
  });
  const programs = programsData?.data?.data ?? [];

  const mutation = useMutation({
    mutationFn: () => {
      const payload = {
        user: { name: form.name, email: form.email, phone: form.phone, address: form.address },
        department_id: Number(form.department_id) || undefined,
        program_id: Number(form.program_id) || undefined,
        graduation_year: form.graduation_year || undefined,
        admission_year: form.admission_year || undefined,
        roll_number: form.roll_number || undefined,
        current_job: form.current_job || undefined,
        company_name: form.company_name || undefined,
        work_location: form.work_location || undefined,
        employment_status: form.employment_status || undefined,
        bio: form.bio || undefined,
        linkedin_url: form.linkedin_url || undefined,
        github_url: form.github_url || undefined,
        portfolio_url: form.portfolio_url || undefined,
        achievements: form.achievements || undefined,
        is_featured: form.is_featured,
        is_active: form.is_active,
        visibility: form.visibility,
      };
      return alumniService.update(Number(id), payload as Record<string, unknown>);
    },
    onSuccess: () => { toast.success('Alumni updated.'); navigate('/admin/alumni'); },
    onError: () => toast.error('Failed to update alumni.'),
  });

  const set = (k: string, v: any) => setForm((f: any) => ({ ...f, [k]: v }));
  const inputCls = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-50 disabled:bg-slate-50';
  const labelCls = 'text-xs font-semibold uppercase tracking-wider text-slate-500';

  if (isLoading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="space-y-5">
      <div className="flex items-center gap-4">
        <Link to="/admin/alumni" className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" />
        </Link>
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Edit Alumni</h1>
          <p className="mt-0.5 text-sm text-slate-500">{alumni?.user?.name}</p>
        </div>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="space-y-1.5">
            <label className={labelCls}>Name</label>
            <input value={form.name} onChange={e => set('name', e.target.value)} className={inputCls} />
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
            <label className={labelCls}>Department</label>
            <select value={form.department_id} onChange={e => { set('department_id', e.target.value); set('program_id', ''); }}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select department</option>
              {departments.map((d: any) => <option key={d.id} value={d.id}>{d.name}</option>)}
            </select>
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Program</label>
            <select value={form.program_id} onChange={e => set('program_id', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select program</option>
              {programs.map((p: any) => <option key={p.id} value={p.id}>{p.name}</option>)}
            </select>
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Graduation Year</label>
            <input value={form.graduation_year} onChange={e => set('graduation_year', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Admission Year</label>
            <input value={form.admission_year} onChange={e => set('admission_year', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Roll Number</label>
            <input value={form.roll_number} onChange={e => set('roll_number', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Employment Status</label>
            <select value={form.employment_status} onChange={e => set('employment_status', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="">Select status</option>
              {['employed','unemployed','self-employed','student','unknown'].map(s => (
                <option key={s} value={s}>{s.charAt(0).toUpperCase() + s.slice(1).replace('-', ' ')}</option>
              ))}
            </select>
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Current Job</label>
            <input value={form.current_job} onChange={e => set('current_job', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Company Name</label>
            <input value={form.company_name} onChange={e => set('company_name', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Work Location</label>
            <input value={form.work_location} onChange={e => set('work_location', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>LinkedIn URL</label>
            <input value={form.linkedin_url} onChange={e => set('linkedin_url', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>GitHub URL</label>
            <input value={form.github_url} onChange={e => set('github_url', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Portfolio URL</label>
            <input value={form.portfolio_url} onChange={e => set('portfolio_url', e.target.value)} className={inputCls} />
          </div>
          <div className="space-y-1.5">
            <label className={labelCls}>Achievements</label>
            <input value={form.achievements} onChange={e => set('achievements', e.target.value)} className={inputCls} />
          </div>
        </div>

        <div className="space-y-1.5 mt-4">
          <label className={labelCls}>Bio</label>
          <textarea value={form.bio} onChange={e => set('bio', e.target.value)} rows={4}
            className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" />
        </div>

        <div className="flex flex-wrap items-center gap-6 pt-4">
          <div className="flex items-center gap-3">
            <input type="checkbox" id="is_active" checked={form.is_active} onChange={e => set('is_active', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
            <label htmlFor="is_active" className="text-sm font-medium text-slate-700">Active</label>
          </div>
          <div className="flex items-center gap-3">
            <input type="checkbox" id="is_featured" checked={form.is_featured} onChange={e => set('is_featured', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
            <label htmlFor="is_featured" className="text-sm font-medium text-slate-700">Featured</label>
          </div>
          <div className="flex items-center gap-3">
            <input type="checkbox" id="visibility" checked={form.visibility === 'public'} onChange={e => set('visibility', e.target.checked ? 'public' : 'private')}
              className="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500" />
            <label htmlFor="visibility" className="text-sm font-medium text-slate-700">Public visible</label>
          </div>
        </div>

        <div className="flex items-center gap-3 pt-6">
          <button onClick={() => mutation.mutate()} disabled={mutation.isPending}
            className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 transition disabled:opacity-50">
            {mutation.isPending ? <Spinner size="sm" /> : <Save className="h-4 w-4" />}
            Update Alumni
          </button>
          <Link to="/admin/alumni" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</Link>
        </div>
      </div>
    </div>
  );
}
