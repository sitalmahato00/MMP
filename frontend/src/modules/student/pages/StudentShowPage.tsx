import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { ArrowLeft, Edit, Trash2 } from 'lucide-react';
import studentService from '@services/studentService';
import { Card } from '@components/ui/Card';
import { Button } from '@components/ui/Button';
import { StatusBadge } from '@components/ui/Badge';
import { Spinner } from '@components/ui/Spinner';
import toast from 'react-hot-toast';

export default function StudentShowPage() {
  const { id }       = useParams<{ id: string }>();
  const navigate     = useNavigate();
  const queryClient  = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['student', id],
    queryFn: () => studentService.show(Number(id)),
    enabled: !!id,
  });

  const deleteMutation = useMutation({
    mutationFn: () => studentService.destroy(Number(id)),
    onSuccess: () => {
      toast.success('Student deleted.');
      queryClient.invalidateQueries({ queryKey: ['students'] });
      navigate('..', { relative: 'path' });
    },
  });

  if (isLoading) {
    return <div className="flex h-64 items-center justify-center"><Spinner size="lg" /></div>;
  }

  const student = data?.data;
  if (!student) return <p className="text-center text-gray-500">Student not found.</p>;

  function confirmDelete() {
    if (window.confirm(`Delete student "${student!.user.name}"? This cannot be undone.`)) {
      deleteMutation.mutate();
    }
  }

  return (
    <div className="space-y-5 max-w-4xl">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <Link to=".." relative="path" className="btn-secondary">
            <ArrowLeft className="h-4 w-4" />
            Back
          </Link>
          <h1 className="text-xl font-bold text-gray-900">{student.user.name}</h1>
          <StatusBadge status={student.status} />
        </div>
        <div className="flex gap-2">
          <Button variant="secondary" onClick={() => navigate(`../students/${id}/edit`)}>
            <Edit className="h-4 w-4" /> Edit
          </Button>
          <Button variant="danger" onClick={confirmDelete} loading={deleteMutation.isPending}>
            <Trash2 className="h-4 w-4" /> Delete
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-5 lg:grid-cols-3">
        {/* Profile Card */}
        <Card className="lg:col-span-1">
          <div className="flex flex-col items-center text-center">
            {student.user.avatar ? (
              <img src={student.user.avatar} alt={student.user.name}
                className="h-24 w-24 rounded-full object-cover ring-4 ring-gray-100" />
            ) : (
              <div className="flex h-24 w-24 items-center justify-center rounded-full bg-primary-100 text-primary-700 text-3xl font-bold ring-4 ring-gray-100">
                {student.user.name.charAt(0).toUpperCase()}
              </div>
            )}
            <h2 className="mt-4 text-lg font-semibold">{student.user.name}</h2>
            <p className="text-sm text-gray-500">{student.user.email}</p>
            <p className="mt-1 font-mono text-sm text-primary-700">{student.student_no}</p>
          </div>
        </Card>

        {/* Details Card */}
        <Card title="Academic Details" className="lg:col-span-2">
          <dl className="grid grid-cols-2 gap-4 text-sm">
            {[
              ['Registration No.', student.registration_number ?? '—'],
              ['Program',         student.program?.name ?? '—'],
              ['Department',      student.department?.name ?? '—'],
              ['Session',         student.academic_session?.name ?? '—'],
              ['Semester',        `Semester ${student.current_semester}`],
              ['Section',         student.section ?? '—'],
              ['Batch',           student.batch ?? '—'],
              ['Admission Date',  student.admission_date ? new Date(student.admission_date).toLocaleDateString() : '—'],
              ['Roll No.',        student.roll_number ?? '—'],
              ['Blood Group',     student.blood_group ?? '—'],
              ['Guardian Name',   student.guardian_name ?? '—'],
              ['Guardian Phone',  student.guardian_phone ?? '—'],
            ].map(([label, value]) => (
              <div key={label}>
                <dt className="font-medium text-gray-500">{label}</dt>
                <dd className="text-gray-900">{value}</dd>
              </div>
            ))}
          </dl>
        </Card>
      </div>
    </div>
  );
}
