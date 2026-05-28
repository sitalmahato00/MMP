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
              MMP Portal
            </h1>
            <p className="mt-1 text-sm" style={{ color: '#6B7A8D' }}>Secure System Authentication</p>
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
                <label
                  className="form-label mb-0"
                  htmlFor="password"
                  style={{ color: '#1A2B45', fontSize: '0.8125rem', fontWeight: 500 }}
                >
                  Password
                </label>
                <Link
                  to="/forgot-password"
                  className="text-xs font-medium transition-colors"
                  style={{ color: '#1D4ED8' }}
                >
                  Forgot password?
                </Link>
              </div>
              <input
                id="password"
                type="password"
                autoComplete="current-password"
                required
                style={{
                  display: 'block',
                  width: '100%',
                  border: errors.password ? '1px solid #DC2626' : '1px solid #DCE3EB',
                  borderRadius: '3px',
                  padding: '0.4375rem 0.75rem',
                  fontSize: '0.8125rem',
                  lineHeight: '1.5',
                  background: '#ffffff',
                  color: '#1A2B45',
                  outline: 'none',
                }}
                onFocus={e => {
                  e.currentTarget.style.borderColor = errors.password ? '#DC2626' : '#1D4ED8';
                  e.currentTarget.style.boxShadow = errors.password
                    ? '0 0 0 2px rgba(220,38,38,0.15)'
                    : '0 0 0 2px rgba(29,78,216,0.15)';
                }}
                onBlur={e => {
                  e.currentTarget.style.borderColor = errors.password ? '#DC2626' : '#DCE3EB';
                  e.currentTarget.style.boxShadow = 'none';
                }}
                {...register('password')}
              />
              {errors.password?.message && (
                <p className="mt-1 text-xs text-red-600">{errors.password.message}</p>
              )}
            </div>

            <div className="flex items-center">
              <input
                id="remember"
                type="checkbox"
                className="h-4 w-4 rounded border-gray-300"
                style={{ accentColor: '#1D4ED8' }}
                {...register('remember')}
              />
              <label
                htmlFor="remember"
                className="ml-2 block text-sm font-medium"
                style={{ color: '#1A2B45' }}
              >
                Remember me
              </label>
            </div>

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
              {loading ? 'Signing in…' : 'Sign in to Account'}
            </button>
          </form>

          <div
            className="mt-6 pt-4 flex items-center justify-between"
            style={{ borderTop: '1px solid #DCE3EB' }}
          >
            <Link
              to="/"
              className="text-xs font-medium flex items-center gap-1 transition-colors"
              style={{ color: '#6B7A8D' }}
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back to Home
            </Link>
            <p className="text-[10px] font-medium" style={{ color: '#6B7A8D' }}>Restricted Access</p>
          </div>
        </div>
      </div>
    </div>
  );
}
