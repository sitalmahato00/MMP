import { Link } from 'react-router-dom';
import { ShieldOff } from 'lucide-react';

export default function ForbiddenPage() {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center text-center p-6">
      <ShieldOff className="h-16 w-16 text-red-400 mb-4" />
      <h1 className="text-3xl font-bold text-gray-900">403 — Forbidden</h1>
      <p className="mt-2 text-gray-500">You don't have permission to access this page.</p>
      <Link to="/" className="mt-6 btn-primary">Go Home</Link>
    </div>
  );
}
