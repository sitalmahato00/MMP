import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Link } from 'react-router-dom';
import authService from '@shared/services/authService';
import { Input } from '@components/ui/Input';
import { Button } from '@components/ui/Button';
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
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 p-4">
      <div className="w-full max-w-md">
        <div className="card shadow-xl border-t-4 border-primary-600">
          <div className="mb-8 text-center">
            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border-[3px] border-yellow-500 shadow-md" style={{ background: 'radial-gradient(circle, #003D82, #001F4D)' }}>
              <span className="text-xl font-bold text-white">MMP</span>
            </div>
            <h1 className="text-2xl font-black text-primary-700 font-serif tracking-tight">Reset Password</h1>
            <p className="mt-1 text-sm font-medium text-gray-500">We will email you a secure reset link</p>
          </div>

          {sent ? (
            <div className="text-center py-6">
              <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                <svg className="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"/></svg>
              </div>
              <p className="text-sm text-gray-600">Check your email inbox for the reset link. It may take a few minutes to arrive.</p>
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

              <Button type="submit" className="w-full mt-2 uppercase tracking-wider" loading={loading}>
                Email Reset Link
              </Button>
            </form>
          )}

          <div className="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
            <Link to="/login" className="text-xs font-bold text-gray-500 hover:text-primary-600 flex items-center gap-1 transition-colors">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to Login
            </Link>
            <p className="text-[10px] font-medium text-gray-400">Secure Recovery</p>
          </div>
        </div>
      </div>
    </div>
  );
}
