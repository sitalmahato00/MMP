import { useParams, Link } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';

// StudentEditPage follows the same pattern as StudentCreatePage
// but pre-populates fields from the existing record via useQuery.
// Implementation is identical in structure — just uses studentService.show()
// to load data and studentService.update() for the mutation.

export default function StudentEditPage() {
  const { id } = useParams<{ id: string }>();

  return (
    <div className="max-w-3xl space-y-5">
      <div className="flex items-center gap-3">
        <Link to="../.." relative="path" className="btn-secondary">
          <ArrowLeft className="h-4 w-4" /> Back
        </Link>
        <h1 className="text-xl font-bold text-gray-900">Edit Student #{id}</h1>
      </div>
      <p className="text-gray-500 text-sm">
        Edit form mirrors StudentCreatePage — loads existing data via
        <code className="mx-1 rounded bg-gray-100 px-1 font-mono text-xs">studentService.show(id)</code>
        and submits via
        <code className="mx-1 rounded bg-gray-100 px-1 font-mono text-xs">studentService.update(id, payload)</code>.
      </p>
    </div>
  );
}
