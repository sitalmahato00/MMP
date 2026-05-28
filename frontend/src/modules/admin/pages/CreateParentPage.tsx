import { useMutation } from '@tanstack/react-query';
import { useNavigate, Link } from 'react-router-dom';
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
  password: z.string().min(8, 'Min. 8 characters'),
  address: z.string().optional(),
  relation_to_student: z.string().default('parent'),
  occupation: z.string().optional(),
});

type FormData = z.infer<typeof schema>;

export default function CreateParentPage() {
  const navigate = useNavigate();
  const [students, setStudents] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [selectedStudentIds, setSelectedStudentIds] = useState<number[]>([]);
  const [avatarFile, setAvatarFile] = useState<File | null>(null);

  const { register, handleSubmit, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { relation_to_student: 'parent' },
  });

  useEffect(() => {
    get<any>('/v1/students?per_page=200&status=active').then(res => {
      setStudents(res?.data?.data ?? res?.data ?? []);
    }).catch(() => {});
  }, []);

  const mutation = useMutation({
    mutationFn: (payload: FormData) => {
      const fd = new FormData();
      Object.entries(payload).forEach(([k, v]) => { if (v !== undefined) fd.append(k, v); });
      selectedStudentIds.forEach(id => fd.append('student_ids[]', String(id)));
      if (avatarFile) fd.append('avatar', avatarFile);
      return parentService.create(fd);
    },
    onSuccess: () => { toast.success('Parent created.'); navigate('/admin/parents'); },
    onError: () => toast.error('Failed to create parent.'),
  });

  const toggleStudent = (id: number) => {
    setSelectedStudentIds(prev =>
      prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]
    );
  };

  const filteredStudents = students.filter((s: any) =>
    !search || s.user?.name?.toLowerCase().includes(search.toLowerCase())
  );

  const inputCls = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100';
  const labelCls = 'text-xs font-semibold uppercase tracking-wider text-slate-500';

  return (
    <div className="space-y-6 max-w-4xl">
      <div className="flex items-center gap-4">
        <Link to="/admin/parents" className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
          <ArrowLeft className="h-4 w-4" />
        </Link>
        <div>
          <h1 className="text-2xl font-black tracking-tight text-slate-900">Add Parent</h1>
          <p className="mt-0.5 text-sm text-slate-500">Create a new parent/guardian account and link to students.</p>
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
                <input {...register('name')} placeholder="Full legal name" className={inputCls} />
                {errors.name && <p className="text-xs text-red-500">{errors.name.message}</p>}
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Email Address <span className="text-red-500">*</span></label>
                <input {...register('email')} type="email" placeholder="parent@example.com" className={inputCls} />
                {errors.email && <p className="text-xs text-red-500">{errors.email.message}</p>}
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Phone Number</label>
                <input {...register('phone')} placeholder="98XXXXXXXX" className={inputCls} />
              </div>
              <div className="space-y-1.5">
                <label className={labelCls}>Password <span className="text-red-500">*</span></label>
                <input {...register('password')} type="password" placeholder="Min. 8 characters" className={inputCls} />
                {errors.password && <p className="text-xs text-red-500">{errors.password.message}</p>}
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <label className={labelCls}>Address</label>
                <textarea {...register('address')} rows={2} placeholder="District, Province, Country" className={inputCls} />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <label className={labelCls}>Profile Photo</label>
                <input type="file" accept="image/*" onChange={e => setAvatarFile(e.target.files?.[0] ?? null)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                <p className="text-xs text-slate-400">Upload photo (max 2 MB)</p>
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
                <input {...register('occupation')} placeholder="e.g. Teacher, Business, Farmer" className={inputCls} />
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div className="border-b border-slate-100 px-5 py-4">
            <h3 className="font-bold text-slate-900">Link Children</h3>
            <p className="mt-0.5 text-xs text-slate-500">Select students to link to this parent account.</p>
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
            Create Parent Account
          </button>
          <Link to="/admin/parents" className="text-sm text-slate-500 hover:text-slate-700">Cancel</Link>
        </div>
      </form>
    </div>
  );
}
