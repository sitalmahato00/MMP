import { useMutation, useQuery } from '@tanstack/react-query';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { zodResolver } from '@hookform/resolvers/zod';
import toast from 'react-hot-toast';
import { ArrowLeft } from 'lucide-react';
import { Spinner } from '@shared/components/ui/Spinner';
import { get } from '@shared/api/axios';
import parentService from '@shared/services/parentService';

const schema = z.object({
  name: z.string().min(1, 'Full name is required'),
  email: z.string().email('Invalid email'),
  phone: z.string().optional(),
  address: z.string().optional(),
  relation_to_student: z.string().default('parent'),
  occupation: z.string().optional(),
  is_active: z.boolean().optional(),
});

type FormData = z.infer<typeof schema>;

export default function EditParentPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [students, setStudents] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [selectedStudentIds, setSelectedStudentIds] = useState<number[]>([]);
  const [avatarFile, setAvatarFile] = useState<File | null>(null);

  const { data, isLoading: loadingParent } = useQuery({
    queryKey: ['parent', id],
    queryFn: () => parentService.show(Number(id)),
    enabled: !!id,
  });

  const parent = data?.data;

  const { register, handleSubmit, reset, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
  });

  useEffect(() => {
    if (parent) {
      reset({
        name: parent.user?.name || '',
        email: parent.user?.email || '',
        phone: parent.user?.phone || '',
        address: (parent as any).user?.address || '',
        relation_to_student: parent.relation_to_student || 'parent',
        occupation: parent.occupation || '',
        is_active: parent.user?.is_active ?? true,
      });
      setSelectedStudentIds(parent.students?.map((s: any) => s.id) ?? []);
    }
  }, [parent, reset]);

  useEffect(() => {
    get<any>('/v1/students?per_page=200&status=active').then(res => {
      setStudents(res?.data?.data ?? res?.data ?? []);
    }).catch(() => {});
  }, []);

  const mutation = useMutation({
    mutationFn: (payload: FormData) => {
      const fd = new FormData();
      fd.append('_method', 'PUT');
      const entries = Object.entries(payload) as [string, string | boolean | undefined][];
      for (const [k, v] of entries) {
        if (v === undefined) continue;
        if (k === 'is_active') { fd.append(k, v ? '1' : '0'); }
        else { fd.append(k, String(v)); }
      }
      selectedStudentIds.forEach(sid => fd.append('student_ids[]', String(sid)));
      if (avatarFile) fd.append('avatar', avatarFile);
      return parentService.update(Number(id), fd);
    },
    onSuccess: () => { toast.success('Parent updated.'); navigate(`/admin/parents/${id}`); },
    onError: () => toast.error('Failed to update parent.'),
  });

  const toggleStudent = (sid: number) => {
    setSelectedStudentIds(prev =>
      prev.includes(sid) ? prev.filter(x => x !== sid) : [...prev, sid]
    );
  };

  const filteredStudents = students.filter((s: any) =>
    !search || s.user?.name?.toLowerCase().includes(search.toLowerCase())
  );

  const inputCls = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100';
  const labelCls = 'text-xs font-semibold uppercase tracking-wider text-slate-500';

  if (loadingParent) return <div className="flex justify-center py-20"><Spinner /></div>;
  if (!parent) return <div className="py-20 text-center text-sm text-slate-500">Parent not found.</div>;

  return (
    <div className="space-y-6 max-w-4xl">
      <div className="flex items-center gap-4">
        <Link to={`/admin/parents/${id}`} className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" />
        </Link>
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Edit Parent</h1>
          <p className="mt-0.5 text-sm text-slate-500">Update parent/guardian information and linked children.</p>
        </div>
      </div>

      <form onSubmit={handleSubmit(data => mutation.mutate(data))} className="space-y-6">
        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Personal Information</h3>
            <p className="mt-0.5 text-xs text-slate-500">Parent/guardian identity and contact details.</p>
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
                <input {...register('email')} type="email" className={inputCls} />
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
                {parent.user?.avatar && (
                  <div className="mb-2 flex items-center gap-3">
                    <img src={parent.user.avatar} className="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100" />
                    <span className="text-xs text-slate-500">Current photo</span>
                  </div>
                )}
                <input type="file" accept="image/*" onChange={e => setAvatarFile(e.target.files?.[0] ?? null)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                <p className="text-xs text-slate-400">Upload new photo (max 2 MB)</p>
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Parent Details</h3>
            <p className="mt-0.5 text-xs text-slate-500">Relation to student and occupation.</p>
          </div>
          <div className="p-5">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1.5">
                <label className={labelCls}>Relation to Student</label>
                <select {...register('relation_to_student')} className={inputCls}>
                  <option value="parent">Parent</option>
                  <option value="father">Father</option>
                  <option value="mother">Mother</option>
                  <option value="guardian">Guardian</option>
                </select>
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Occupation</label>
                <input {...register('occupation')} className={inputCls} />
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Account Status</h3>
            <p className="mt-0.5 text-xs text-slate-500">Enable or disable this parent account.</p>
          </div>
          <div className="p-5">
            <label className="inline-flex items-center gap-3 cursor-pointer">
              <input type="checkbox" {...register('is_active')} className="rounded border-slate-300 text-[#8B0000] focus:ring-red-200" />
              <span className="text-sm text-slate-700">Account is active and can log in</span>
            </label>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Link Children</h3>
            <p className="mt-0.5 text-xs text-slate-500">Select students linked to this parent account.</p>
          </div>
          <div className="p-5">
            {students.length > 0 ? (
              <div className="space-y-3">
                <input type="text" value={search} onChange={e => setSearch(e.target.value)} placeholder="Search students by name…"
                  className="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" />
                <div className="max-h-64 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-50">
                  {filteredStudents.map((student: any) => (
                    <label key={student.id} className="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer transition">
                      <input type="checkbox" checked={selectedStudentIds.includes(student.id)} onChange={() => toggleStudent(student.id)}
                        className="rounded border-slate-300 text-[#8B0000] focus:ring-red-200" />
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-semibold text-slate-900">{student.user?.name}</p>
                        <p className="text-xs text-slate-500">{student.student_no} · {student.department?.name} · {student.program?.name} · Sem {student.current_semester}</p>
                      </div>
                    </label>
                  ))}
                </div>
              </div>
            ) : (
              <p className="text-sm text-slate-500 italic">No active students available to link.</p>
            )}
          </div>
        </div>

        <div className="flex items-center gap-3">
          <button type="submit" disabled={mutation.isPending}
            className="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition disabled:opacity-50">
            {mutation.isPending ? <Spinner size="sm" /> : null}
            Update Parent
          </button>
          <Link to={`/admin/parents/${id}`} className="text-sm text-slate-500 hover:text-slate-700">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
