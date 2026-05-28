import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Link } from 'react-router-dom';
import authService from '@shared/services/authService';
import { Input } from '@components/ui/Input';
import { LogoBadge } from '@components/ui/LogoBadge';
import toast from 'react-hot-toast';
import { useState } from 'react';

const schema = z.object({
  email: z.string().email('Enter a valid email'),
});

type FormValues = z.infer<typeof schema>;

export default function ForgotPasswordPage() {
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  async function onSubmit(values: FormValues) {
    setLoading(true);
    try {
      const res = await authService.forgotPassword(values.email);
      if (res.success) {
        setSent(true);
        toast.success('Reset link sent to your email.');
      } else {
        toast.error(res.message);
      }
    } catch {
      toast.error('Failed to send reset link. Please try again.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div
      className="flex min-h-screen items-center justify-center p-4"
      style={{ background: '#F4F7FB' }}
    >
      <div className="w-full max-w-sm">
        <div
          style={{
            background: '#ffffff',
            border: '1px solid #DCE3EB',
            borderRadius: '4px',
            borderTop: '3px solid #002366',
            padding: '2rem',
            boxShadow: '0 1px 3px rgba(11,46,107,0.08)',
          }}
        >
          <div className="mb-6 text-center">
            <LogoBadge />
            <h1
              className="font-bold tracking-tight"
              style={{ fontSize: '1.25rem', color: '#002366', fontFamily: 'Inter, sans-serif' }}
            >
              Reset Password
            </h1>
            <p className="mt-1 text-sm" style={{ color: '#6B7A8D' }}>We will email you a secure reset link</p>
          </div>

          {sent ? (
            <div className="text-center py-6">
              <div
                className="mx-auto mb-4 flex h-12 w-12 items-center justify-center"
                style={{ borderRadius: '50%', background: '#dcfce7' }}
              >
                <svg className="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"/>
                </svg>
              </div>
              <p className="text-sm" style={{ color: '#6B7A8D' }}>
                Check your email inbox for the reset link. It may take a few minutes to arrive.
              </p>
            </div>
          ) : (
            <form onSubmit={handleSubmit(onSubmit)} className="space-y-4" noValidate>
              <Input
                label="Email Address"
                type="email"
                autoComplete="email"
                required
                error={errors.email?.message}
                hint="Use the same email address you use to sign in to the portal."
                {...register('email')}
              />

              <button
                type="submit"
                disabled={loading}
                className="w-full mt-2 text-sm font-medium uppercase tracking-wider transition"
                style={{
                  background: loading ? '#1e40af' : '#1D4ED8',
                  color: '#ffffff',
                  border: 'none',
                  borderRadius: '4px',
                  padding: '0.5rem 1rem',
                  cursor: loading ? 'not-allowed' : 'pointer',
                  opacity: loading ? 0.8 : 1,
                }}
              >
                {loading ? 'Sending…' : 'Email Reset Link'}
              </button>
            </form>
          )}

          <div
            className="mt-6 pt-4 flex items-center justify-between"
            style={{ borderTop: '1px solid #DCE3EB' }}
          >
            <Link
              to="/login"
              className="text-xs font-medium flex items-center gap-1 transition-colors"
              style={{ color: '#6B7A8D' }}
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back to Login
            </Link>
            <p className="text-[10px] font-medium" style={{ color: '#6B7A8D' }}>Secure Recovery</p>
          </div>
        </div>
      </div>
    </div>
  );
}
