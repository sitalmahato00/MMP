import { Link } from 'react-router-dom';
import { FileQuestion } from 'lucide-react';

export default function NotFoundPage() {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center text-center p-6">
      <FileQuestion className="h-16 w-16 text-gray-400 mb-4" />
      <h1 className="text-3xl font-bold text-gray-900">404 — Page Not Found</h1>
      <p className="mt-2 text-gray-500">The page you're looking for doesn't exist.</p>
      <Link to="/" className="mt-6 btn-primary">Go Home</Link>
    </div>
  );
}
