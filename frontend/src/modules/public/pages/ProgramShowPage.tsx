import { useQuery } from '@tanstack/react-query';
import { useParams, Link } from 'react-router-dom';
import { getProgramBySlug } from '@shared/services/public.service';

export default function ProgramShowPage() {
  const { deptSlug, programSlug } = useParams<{ deptSlug: string; programSlug: string }>();
  const { data, isLoading } = useQuery({
    queryKey: ['public-program', deptSlug, programSlug],
    queryFn: () => getProgramBySlug(deptSlug!, programSlug!),
    enabled: !!deptSlug && !!programSlug,
  });

  const program: any = data?.program ?? null;
  const department: any = data?.department ?? null;

  if (isLoading) return <div className="flex justify-center py-32"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>;
  if (!program) return <div className="text-center py-24 text-gray-500"><p className="text-5xl mb-4">📋</p><p>Program not found.</p></div>;

  const subjects: any[] = program.subjects ?? [];
  const totalCredits = subjects.reduce((s: number, sub: any) => s + (sub.credit_hours ?? 0), 0);

  const detailItems = [
    { emoji: '📝', label: 'Program Code', value: program.code },
    program.ctevt_code && { emoji: '🏛️', label: 'CTEVT Code', value: program.ctevt_code },
    program.affiliation_type && { emoji: '🔗', label: 'Affiliation', value: program.affiliation_type },
    { emoji: '🏢', label: 'Department', value: department?.name },
    { emoji: '⏱️', label: 'Duration', value: `${program.duration_years} Years (${program.total_semesters} Semesters)` },
  ].filter(Boolean) as { emoji: string; label: string; value: string }[];

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          {/* Hero */}
          <div className="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
            <div className="h-2" style={{ backgroundColor: '#003D82' }} />
            <div className="p-8">
              <div className="flex items-center gap-2 mb-3">
                <Link to={`/departments/${department?.slug}`} className="text-xs font-bold text-blue-800 hover:underline">{department?.name}</Link>
                <span className="text-gray-300">/</span>
                <span className="text-xs text-gray-500">{program.name}</span>
              </div>
              <div className="flex items-center gap-3 mb-6">
                <h1 className="text-2xl font-bold text-gray-900">{program.name}</h1>
                <span className="rounded-md bg-blue-50 border border-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 uppercase">{program.code}</span>
                {program.is_active && <span className="rounded-md bg-emerald-50 border border-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">Active</span>}
              </div>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {[
                  { v: program.duration_years, l: 'Years Duration' },
                  { v: program.total_semesters, l: 'Semesters' },
                  { v: subjects.length, l: 'Subjects' },
                  { v: totalCredits || '—', l: 'Credit Hours' },
                ].map(s => (
                  <div key={s.l} className="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                    <div className="text-2xl font-bold text-blue-800">{s.v}</div>
                    <div className="text-xs text-gray-500 font-medium mt-1">{s.l}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Program Details */}
          <div className="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
            <div className="section-header" style={{ backgroundColor: '#003D82' }}>📋 Program Details</div>
            <div className="p-6">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {detailItems.map(d => (
                  <div key={d.label} className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <span className="text-lg">{d.emoji}</span>
                    <div>
                      <div className="text-[10px] font-bold text-gray-400 uppercase">{d.label}</div>
                      <div className="text-sm font-bold text-gray-900">{d.value}</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Eligibility */}
          {program.eligibility && (
            <div className="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
              <div className="section-header" style={{ backgroundColor: '#003D82' }}>✅ Eligibility Criteria</div>
              <div className="p-6">
                <p className="text-gray-700 leading-relaxed">{program.eligibility}</p>
              </div>
            </div>
          )}

          {/* Syllabus */}
          {program.syllabus_url && (
            <div className="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
              <div className="section-header" style={{ backgroundColor: '#003D82' }}>📖 Syllabus</div>
              <div className="p-6">
                <a href={program.syllabus_url} target="_blank" rel="noopener noreferrer"
                  className="inline-flex items-center gap-3 rounded-lg border-2 border-[#003D82] bg-white px-6 py-3 text-sm font-bold text-[#003D82] transition-colors hover:bg-[#003D82] hover:text-white">
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                  View / Download Syllabus (PDF)
                </a>
              </div>
            </div>
          )}

          {/* About */}
          {program.description && (
            <div className="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
              <div className="section-header" style={{ backgroundColor: '#003D82' }}>📝 About This Program</div>
              <div className="p-6">
                <div className="text-gray-700 leading-relaxed space-y-4">
                  {program.description.trim().split(/\n\s*\n/).filter((p: string) => p.trim()).map((para: string, i: number) => (
                    <p key={i}>{para.trim()}</p>
                  ))}
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Department */}
          <div>
            <div className="section-header" style={{ backgroundColor: '#003D82' }}>🏢 Department</div>
            <div className="bg-white border border-gray-200 border-t-0 p-5 rounded-b-lg shadow-md">
              <h3 className="font-bold text-gray-900">{department?.name}</h3>
              <p className="text-xs text-gray-500 mt-1">Code: {department?.code}</p>
              <Link to={`/departments/${department?.slug}`} className="inline-flex items-center gap-1.5 mt-3 text-sm font-bold text-blue-800 hover:underline">
                ← Back to Department
              </Link>
            </div>
          </div>

          {/* HOD */}
          {department?.hod && (
            <div>
              <div className="section-header" style={{ backgroundColor: '#003D82' }}>👔 Head of Department</div>
              <div className="bg-white border border-gray-200 border-t-0 p-5 rounded-b-lg shadow-md">
                <div className="flex items-center gap-4">
                  {department.hod.avatar ? (
                    <img src={department.hod.avatar_url ?? `/storage/${department.hod.avatar}`} alt={department.hod.name}
                      className="w-14 h-14 rounded-full object-cover border-2 flex-shrink-0" style={{ borderColor: '#003D82' }} />
                  ) : (
                    <div className="w-14 h-14 rounded-full bg-blue-50 border-2 flex items-center justify-center text-2xl flex-shrink-0" style={{ borderColor: '#003D82' }}>👔</div>
                  )}
                  <div>
                    <div className="font-bold text-gray-900">{department.hod.name}</div>
                    <div className="text-xs text-blue-700">Head of Department</div>
                    <div className="text-xs text-gray-500 mt-0.5">{department.name}</div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Summary */}
          <div>
            <div className="section-header" style={{ backgroundColor: '#003D82' }}>📊 Summary</div>
            <div className="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
              {[
                { label: 'Duration', value: `${program.duration_years} Years` },
                { label: 'Semesters', value: program.total_semesters },
                { label: 'Total Subjects', value: subjects.length },
                { label: 'Total Credit Hours', value: totalCredits || '—' },
                program.affiliation_type && { label: 'Affiliation', value: program.affiliation_type },
              ].filter(Boolean).map((row: any) => (
                <div key={row.label} className="px-4 py-3 border-b border-gray-100 last:border-0 flex justify-between">
                  <span className="text-xs text-gray-500">{row.label}</span>
                  <span className="text-xs font-bold text-gray-900">{row.value}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
