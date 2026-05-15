import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAppDispatch } from '@hooks/useRedux';
import { setCredentials } from '@app/store/auth.store';
import authService from '@shared/services/authService';
import { Input } from '@components/ui/Input';
import { Button } from '@components/ui/Button';
import toast from 'react-hot-toast';
import { useState } from 'react';

const schema = z.object({
  email:    z.string().email('Enter a valid email'),
  password: z.string().min(6, 'Password must be at least 6 characters'),
});

type FormValues = z.infer<typeof schema>;

export default function LoginPage() {
  const dispatch  = useAppDispatch();
  const navigate  = useNavigate();
  const location  = useLocation();
  const [loading, setLoading] = useState(false);

  const from = (location.state as { from?: { pathname: string } })?.from?.pathname ?? '/admin/dashboard';

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  async function onSubmit(values: FormValues) {
    setLoading(true);
    try {
      const res = await authService.login(values);
      if (res.success && res.data) {
        dispatch(setCredentials({ token: res.data.token, user: res.data.user }));
        toast.success(`Welcome back, ${res.data.user.name}!`);

        // Route to role-based dashboard
        const roleMap: Record<string, string> = {
          admin:    '/admin/dashboard',
          teacher:  '/teacher/dashboard',
          student:  '/student/dashboard',
          hod:      '/hod/dashboard',
          parent:   '/parent/dashboard',
        };
        navigate(roleMap[res.data.user.role] ?? from, { replace: true });
      }
    } catch (err: unknown) {
      const axiosErr = err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
      const data = axiosErr?.response?.data;
      if (data?.errors?.email) {
        setError('email', { message: data.errors.email[0] });
      } else if (data?.errors?.password) {
        setError('password', { message: data.errors.password[0] });
      } else {
        toast.error(data?.message ?? 'Login failed.');
      }
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 p-4">
      <div className="w-full max-w-md">
        {/* Card */}
        <div className="card shadow-xl">
          {/* Header */}
          <div className="mb-8 text-center">
            <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-600 text-white shadow-lg">
              <span className="text-xl font-bold">M</span>
            </div>
            <h1 className="text-2xl font-bold text-gray-900">MMP College ERP</h1>
            <p className="mt-1 text-sm text-gray-500">Sign in to your account</p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4" noValidate>
            <Input
              label="Email address"
              type="email"
              autoComplete="email"
              required
              error={errors.email?.message}
              {...register('email')}
            />
            <Input
              label="Password"
              type="password"
              autoComplete="current-password"
              required
              error={errors.password?.message}
              {...register('password')}
            />

            <Button type="submit" className="w-full mt-2" loading={loading}>
              Sign in
            </Button>
          </form>
        </div>

        <p className="mt-4 text-center text-xs text-gray-400">
          &copy; {new Date().getFullYear()} MMP College Management System
        </p>
      </div>
    </div>
  );
}
