import { useQuery } from '@tanstack/react-query';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, Download } from 'lucide-react';
import studentService, { type StudentFilters } from '@services/studentService';
import academicService from '@services/academicService';
import { DataTable } from '@components/ui/DataTable';
import { Pagination } from '@components/ui/Pagination';
import { Button } from '@components/ui/Button';
import { Select } from '@components/ui/Select';
import { Card } from '@components/ui/Card';
import { StatusBadge } from '@components/ui/Badge';
import type { Student } from '@/types';

export default function StudentListPage() {
  const navigate = useNavigate();
  const [filters, setFilters] = useState<StudentFilters>({ page: 1, per_page: 20 });

  // ─── Data fetching ───────────────────────────────────────────────
  const { data, isLoading } = useQuery({
    queryKey: ['students', filters],
    queryFn: () => studentService.list(filters),
  });

  const { data: deptsRes } = useQuery({
    queryKey: ['departments'],
    queryFn: academicService.departments,
    staleTime: Infinity,
  });

  const { data: programsRes } = useQuery({
    queryKey: ['programs'],
    queryFn: () => academicService.programs(),
    staleTime: Infinity,
  });

  useQuery({
    queryKey: ['academic-sessions'],
    queryFn: academicService.sessions,
    staleTime: Infinity,
  });

  const students = data?.data?.data ?? [];
  const meta     = data?.data?.meta;

  // ─── Columns ─────────────────────────────────────────────────────
  const columns = [
    {
      key: 'student_no',
      header: 'Student No.',
      render: (row: Student) => (
        <span className="font-mono text-xs font-medium text-gray-700">{row.student_no}</span>
      ),
    },
    {
      key: 'user',
      header: 'Name',
      render: (row: Student) => (
        <div className="flex items-center gap-3">
          {row.user.avatar ? (
            <img src={row.user.avatar} alt={row.user.name} className="h-8 w-8 rounded-full object-cover" />
          ) : (
            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-primary-700 text-xs font-bold">
              {row.user.name.charAt(0).toUpperCase()}
            </div>
          )}
          <div>
            <p className="font-medium text-gray-900">{row.user.name}</p>
            <p className="text-xs text-gray-500">{row.user.email}</p>
          </div>
        </div>
      ),
    },
    {
      key: 'program',
      header: 'Program',
      render: (row: Student) => row.program?.name ?? '—',
    },
    {
      key: 'current_semester',
      header: 'Semester',
      render: (row: Student) => `Sem ${row.current_semester}`,
    },
    {
      key: 'status',
      header: 'Status',
      render: (row: Student) => <StatusBadge status={row.status} />,
    },
    {
      key: 'admission_date',
      header: 'Admitted',
      render: (row: Student) =>
        row.admission_date ? new Date(row.admission_date).toLocaleDateString() : '—',
    },
  ];

  function applyFilter(key: keyof StudentFilters, value: string | number | undefined) {
    setFilters((prev) => ({ ...prev, [key]: value || undefined, page: 1 }));
  }

  async function handleExport() {
    const blob = await studentService.exportCsv(filters);
    const url  = URL.createObjectURL(blob as Blob);
    const a    = document.createElement('a');
    a.href = url;
    a.download = 'students.csv';
    a.click();
    URL.revokeObjectURL(url);
  }

  return (
    <div className="space-y-5">
      {/* Page header */}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-bold text-gray-900">Students</h1>
          {meta && (
            <p className="text-sm text-gray-500">{meta.total} total students</p>
          )}
        </div>
        <div className="flex gap-2">
          <Button variant="secondary" onClick={handleExport}>
            <Download className="h-4 w-4" />
            Export
          </Button>
          <Button onClick={() => navigate('create')}>
            <Plus className="h-4 w-4" />
            Add Student
          </Button>
        </div>
      </div>

      {/* Filters */}
      <Card>
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5">
          <div className="relative sm:col-span-2">
            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <input
              type="search"
              placeholder="Search name, email, student no..."
              className="form-input pl-9"
              value={filters.search ?? ''}
              onChange={(e) => applyFilter('search', e.target.value)}
            />
          </div>
          <Select
            options={(deptsRes?.data ?? []).map((d) => ({ value: d.id, label: d.name }))}
            placeholder="All Departments"
            value={filters.department_id ?? ''}
            onChange={(e) => applyFilter('department_id', Number(e.target.value) || undefined)}
          />
          <Select
            options={(programsRes?.data ?? []).map((p) => ({ value: p.id, label: p.name }))}
            placeholder="All Programs"
            value={filters.program_id ?? ''}
            onChange={(e) => applyFilter('program_id', Number(e.target.value) || undefined)}
          />
          <Select
            options={[
              { value: 'active',      label: 'Active'      },
              { value: 'inactive',    label: 'Inactive'    },
              { value: 'graduated',   label: 'Graduated'   },
              { value: 'suspended',   label: 'Suspended'   },
              { value: 'transferred', label: 'Transferred' },
            ]}
            placeholder="All Statuses"
            value={filters.status ?? ''}
            onChange={(e) => applyFilter('status', e.target.value)}
          />
        </div>
      </Card>

      {/* Table */}
      <Card noPadding>
        <DataTable
          columns={columns}
          data={students}
          isLoading={isLoading}
          onRowClick={(row) => navigate(`${row.id}`)}
          emptyMessage="No students found. Adjust filters or add a new student."
        />
        {meta && (
          <Pagination meta={meta} onPageChange={(p) => setFilters((f) => ({ ...f, page: p }))} />
        )}
      </Card>
    </div>
  );
}
