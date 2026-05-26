import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAppDispatch } from '@hooks/useRedux';
import { setCredentials } from '@app/store/auth.store';
import authService from '@shared/services/authService';
import { Input } from '@components/ui/Input';
import { Button } from '@components/ui/Button';
import { LogoBadge } from '@components/ui/LogoBadge';
import toast from 'react-hot-toast';
import { useState } from 'react';

const schema = z.object({
  email:    z.string().email('Enter a valid email'),
  password: z.string().min(6, 'Password must be at least 6 characters'),
  remember: z.boolean().optional(),
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
      if (res.requires_2fa) {
        navigate('/verify-2fa', {
          state: { email: values.email, password: values.password, remember: values.remember },
          replace: true,
        });
        return;
      }
      if (res.success && res.data) {
        dispatch(setCredentials({ token: res.data.token, user: res.data.user }));
        toast.success(`Welcome back, ${res.data.user.name}!`);

        const roleMap: Record<string, string> = {
          admin:    '/admin/dashboard',
          principal:'/admin/dashboard',
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
        <div className="card shadow-xl border-t-4 border-primary-600">
          <div className="mb-8 text-center">
            <LogoBadge />
            <h1 className="text-2xl font-black text-primary-700 font-serif tracking-tight">MMP Portal</h1>
            <p className="mt-1 text-sm font-medium text-gray-500">Secure System Authentication</p>
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
            <div>
              <div className="flex items-center justify-between mb-1">
                <label className="form-label mb-0" htmlFor="password">Password</label>
                <Link to="/forgot-password" className="text-xs font-bold text-primary-600 hover:text-primary-800 hover:underline">
                  Forgot password?
                </Link>
              </div>
              <input
                id="password"
                type="password"
                autoComplete="current-password"
                required
                className="form-input"
                {...register('password')}
              />
              {errors.password?.message && <p className="mt-1 text-xs text-red-600">{errors.password.message}</p>}
            </div>

            <div className="flex items-center">
              <input
                id="remember"
                type="checkbox"
                className="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                {...register('remember')}
              />
              <label htmlFor="remember" className="ml-2 block text-sm font-medium text-gray-700">Remember me</label>
            </div>

            <Button type="submit" className="w-full mt-2 uppercase tracking-wider" loading={loading}>
              Sign in to Account
            </Button>
          </form>

          <div className="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
            <Link to="/" className="text-xs font-bold text-gray-500 hover:text-primary-600 flex items-center gap-1 transition-colors">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to Home
            </Link>
            <p className="text-[10px] font-medium text-gray-400">Restricted Access</p>
          </div>
        </div>
      </div>
    </div>
  );
}
