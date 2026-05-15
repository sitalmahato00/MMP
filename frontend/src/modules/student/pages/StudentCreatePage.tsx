import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useNavigate, Link } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import studentService, { type CreateStudentPayload } from '@services/studentService';
import academicService from '@services/academicService';
import { Input } from '@components/ui/Input';
import { Select } from '@components/ui/Select';
import { Button } from '@components/ui/Button';
import { Card } from '@components/ui/Card';
import toast from 'react-hot-toast';

const schema = z.object({
  name:                 z.string().min(2, 'Name is required'),
  email:                z.string().email('Valid email required'),
  phone:                z.string().optional(),
  password:             z.string().min(8, 'Minimum 8 characters'),
  student_no:           z.string().min(1, 'Student number is required'),
  department_id:        z.number({ invalid_type_error: 'Select department' }).min(1),
  program_id:           z.number({ invalid_type_error: 'Select program' }).min(1),
  academic_session_id:  z.number({ invalid_type_error: 'Select session' }).min(1),
  current_semester:     z.number().min(1).max(12),
  section:              z.string().optional(),
  batch:                z.string().optional(),
  admission_date:       z.string().optional(),
  guardian_name:        z.string().optional(),
  guardian_phone:       z.string().optional(),
  blood_group:          z.string().optional(),
});

type FormValues = z.infer<typeof schema>;

export default function StudentCreatePage() {
  const navigate    = useNavigate();
  const queryClient = useQueryClient();

  const { data: deptsRes }    = useQuery({ queryKey: ['departments'],       queryFn: academicService.departments, staleTime: Infinity });
  const { data: programsRes } = useQuery({ queryKey: ['programs'],          queryFn: () => academicService.programs(), staleTime: Infinity });
  const { data: sessionsRes } = useQuery({ queryKey: ['academic-sessions'], queryFn: academicService.sessions,    staleTime: Infinity });

  const {
    register, handleSubmit,
    formState: { errors },
    setError,
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { current_semester: 1 },
  });

  const mutation = useMutation({
    mutationFn: (payload: CreateStudentPayload) => studentService.create(payload),
    onSuccess: (res) => {
      toast.success('Student created successfully.');
      queryClient.invalidateQueries({ queryKey: ['students'] });
      navigate(`../${res.data!.id}`);
    },
    onError: (err: unknown) => {
      const axiosErr = err as { response?: { data?: { errors?: Record<string, string[]> } } };
      const apiErrors = axiosErr?.response?.data?.errors;
      if (apiErrors) {
        Object.entries(apiErrors).forEach(([field, messages]) => {
          setError(field as keyof FormValues, { message: messages[0] });
        });
      }
    },
  });

  function onSubmit(values: FormValues) {
    mutation.mutate(values as CreateStudentPayload);
  }

  return (
    <div className="max-w-3xl space-y-5">
      <div className="flex items-center gap-3">
        <Link to=".." relative="path" className="btn-secondary">
          <ArrowLeft className="h-4 w-4" /> Back
        </Link>
        <h1 className="text-xl font-bold text-gray-900">Add New Student</h1>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
        {/* Account Info */}
        <Card title="Account Information">
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Input label="Full Name"       required error={errors.name?.message}     {...register('name')} />
            <Input label="Email"   type="email" required error={errors.email?.message}    {...register('email')} />
            <Input label="Phone"   type="tel"          error={errors.phone?.message}    {...register('phone')} />
            <Input label="Password" type="password" required error={errors.password?.message} {...register('password')} />
          </div>
        </Card>

        {/* Academic Info */}
        <Card title="Academic Information">
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Input label="Student Number" required error={errors.student_no?.message} {...register('student_no')} />

            <Select
              label="Department" required
              options={(deptsRes?.data ?? []).map((d) => ({ value: d.id, label: d.name }))}
              placeholder="Select department"
              error={errors.department_id?.message}
              {...register('department_id', { valueAsNumber: true })}
            />
            <Select
              label="Program" required
              options={(programsRes?.data ?? []).map((p) => ({ value: p.id, label: p.name }))}
              placeholder="Select program"
              error={errors.program_id?.message}
              {...register('program_id', { valueAsNumber: true })}
            />
            <Select
              label="Academic Session" required
              options={(sessionsRes?.data ?? []).map((s) => ({ value: s.id, label: s.name }))}
              placeholder="Select session"
              error={errors.academic_session_id?.message}
              {...register('academic_session_id', { valueAsNumber: true })}
            />
            <Input label="Current Semester" type="number" min={1} max={12} required
              error={errors.current_semester?.message} {...register('current_semester', { valueAsNumber: true })} />
            <Input label="Section"  error={errors.section?.message}  {...register('section')} />
            <Input label="Batch"    error={errors.batch?.message}    {...register('batch')} />
            <Input label="Admission Date" type="date" error={errors.admission_date?.message} {...register('admission_date')} />
          </div>
        </Card>

        {/* Guardian Info */}
        <Card title="Guardian & Personal Details">
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Input label="Guardian Name"  error={errors.guardian_name?.message}  {...register('guardian_name')} />
            <Input label="Guardian Phone" error={errors.guardian_phone?.message} {...register('guardian_phone')} />
            <Select
              label="Blood Group"
              options={['A+','A-','B+','B-','AB+','AB-','O+','O-'].map((g) => ({ value: g, label: g }))}
              placeholder="Select blood group"
              error={errors.blood_group?.message}
              {...register('blood_group')}
            />
          </div>
        </Card>

        <div className="flex justify-end gap-3">
          <Link to=".." relative="path" className="btn-secondary">Cancel</Link>
          <Button type="submit" loading={mutation.isPending}>Create Student</Button>
        </div>
      </form>
    </div>
  );
}
