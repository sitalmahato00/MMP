import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Link, useParams, useSearchParams, useNavigate } from 'react-router-dom';
import authService from '@shared/services/authService';
import { Input } from '@components/ui/Input';
import { Button } from '@components/ui/Button';
import toast from 'react-hot-toast';
import { useState } from 'react';

const schema = z.object({
  email: z.string().email('Enter a valid email'),
  password: z.string().min(6, 'Password must be at least 6 characters'),
  password_confirmation: z.string().min(6, 'Confirm your password'),
}).refine((d) => d.password === d.password_confirmation, {
  message: 'Passwords do not match',
  path: ['password_confirmation'],
});

type FormValues = z.infer<typeof schema>;

export default function ResetPasswordPage() {
  const { token } = useParams<{ token: string }>();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: searchParams.get('email') ?? '' },
  });

  async function onSubmit(values: FormValues) {
    if (!token) {
      toast.error('Invalid reset link.');
      return;
    }
    setLoading(true);
    try {
      const res = await authService.resetPassword({
        token,
        email: values.email,
        password: values.password,
        password_confirmation: values.password_confirmation,
      });
      if (res.success) {
        toast.success('Password reset successfully. Please login with your new password.');
        navigate('/login', { replace: true });
      } else {
        setError('email', { message: res.message });
      }
    } catch {
      toast.error('Failed to reset password. The link may have expired.');
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
            <h1 className="text-2xl font-black text-primary-700 font-serif tracking-tight">Choose a New Password</h1>
            <p className="mt-1 text-sm font-medium text-gray-500">Finish resetting your MMP portal password</p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4" noValidate>
            <Input
              label="Email Address"
              type="email"
              autoComplete="email"
              required
              error={errors.email?.message}
              {...register('email')}
            />

            <Input
              label="New Password"
              type="password"
              autoComplete="new-password"
              required
              error={errors.password?.message}
              {...register('password')}
            />

            <Input
              label="Confirm Password"
              type="password"
              autoComplete="new-password"
              required
              error={errors.password_confirmation?.message}
              {...register('password_confirmation')}
            />

            <Button type="submit" className="w-full mt-2 uppercase tracking-wider" loading={loading}>
              Save New Password
            </Button>
          </form>

          <div className="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
            <Link to="/login" className="text-xs font-bold text-gray-500 hover:text-primary-600 flex items-center gap-1 transition-colors">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to Login
            </Link>
            <p className="text-[10px] font-medium text-gray-400">Password Update</p>
          </div>
        </div>
      </div>
    </div>
  );
}
