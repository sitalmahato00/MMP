import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAppDispatch } from '@hooks/useRedux';
import { setCredentials } from '@app/store/auth.store';
import authService from '@shared/services/authService';
import { Button } from '@components/ui/Button';
import { LogoBadge } from '@components/ui/LogoBadge';
import toast from 'react-hot-toast';
import { useEffect, useState, useRef } from 'react';

const schema = z.object({
  otp: z.string().regex(/^\d{6}$/, 'Enter a valid 6-digit code'),
});

type FormValues = z.infer<typeof schema>;

export default function Verify2faPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const state = location.state as { email?: string; password?: string } | null;

  const [loading, setLoading] = useState(false);
  const [resending, setResending] = useState(false);
  const [timeLeft, setTimeLeft] = useState(60);
  const [expired, setExpired] = useState(false);
  const intervalRef = useRef<ReturnType<typeof setInterval>>();

  useEffect(() => {
    if (!state?.email || !state?.password) {
      navigate('/login', { replace: true });
      return;
    }
    intervalRef.current = setInterval(() => {
      setTimeLeft((t) => {
        if (t <= 1) {
          clearInterval(intervalRef.current);
          setExpired(true);
          return 0;
        }
        return t - 1;
      });
    }, 1000);
    return () => clearInterval(intervalRef.current);
  }, []);

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  async function onSubmit(values: FormValues) {
    if (!state?.email || !state?.password) return;
    setLoading(true);
    try {
      const res = await authService.login({
        email: state.email,
        password: state.password,
        otp: values.otp,
      });
      if (res.success && res.data) {
        dispatch(setCredentials({ token: res.data.token, user: res.data.user }));
        toast.success(`Welcome back, ${res.data.user.name}!`);
        const roleMap: Record<string, string> = {
          admin: '/admin/dashboard', teacher: '/teacher/dashboard',
          student: '/student/dashboard', hod: '/hod/dashboard', parent: '/parent/dashboard',
        };
        navigate(roleMap[res.data.user.role] ?? '/admin/dashboard', { replace: true });
      }
    } catch (err: unknown) {
      const axiosErr = err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
      const data = axiosErr?.response?.data;
      if (data?.errors?.otp) {
        setError('otp', { message: data.errors.otp[0] });
      } else {
        toast.error(data?.message ?? 'Verification failed.');
      }
    } finally {
      setLoading(false);
    }
  }

  async function handleResend() {
    if (!state?.email || !state?.password) return;
    setResending(true);
    try {
      await authService.login({ email: state.email, password: state.password });
      setTimeLeft(60);
      setExpired(false);
      toast.success('New verification code sent.');
      clearInterval(intervalRef.current);
      intervalRef.current = setInterval(() => {
        setTimeLeft((t) => {
          if (t <= 1) { clearInterval(intervalRef.current); setExpired(true); return 0; }
          return t - 1;
        });
      }, 1000);
    } catch {
      toast.error('Failed to resend code. Please try again.');
    } finally {
      setResending(false);
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 p-4">
      <div className="w-full max-w-md">
        <div className="card shadow-xl">
          <div className="mb-8 text-center">
            <LogoBadge />
            <h2 className="text-2xl font-extrabold text-gray-900">Two-Factor Authentication</h2>
            <p className="mt-2 text-sm text-gray-600">Enter the verification code sent to your email</p>
            <div className="mt-4">
              <p className="text-sm text-gray-600">
                Code expires in:{' '}
                <span className={`font-bold ${expired ? 'text-red-800' : timeLeft <= 10 ? 'text-red-700' : 'text-red-600'}`}>
                  {expired ? 'EXPIRED' : `${timeLeft}s`}
                </span>
              </p>
            </div>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4" noValidate>
            <div>
              <label htmlFor="otp" className="form-label">Verification Code</label>
              <input
                id="otp"
                type="text"
                inputMode="numeric"
                maxLength={6}
                disabled={expired}
                placeholder="Enter 6-digit code"
                className="form-input text-center text-lg tracking-[0.5em]"
                {...register('otp')}
              />
              {errors.otp?.message && <p className="mt-1 text-xs text-red-600">{errors.otp.message}</p>}
            </div>

            <Button type="submit" className="w-full mt-2 uppercase tracking-wider" loading={loading} disabled={expired}>
              Verify Code
            </Button>
          </form>

          <div className="mt-6">
            <Button
              type="button"
              variant="ghost"
              className="w-full justify-center border border-primary-600 text-primary-600 hover:bg-primary-50"
              loading={resending}
              onClick={handleResend}
            >
              Resend Code
            </Button>
          </div>

          <div className="mt-6 pt-6 border-t border-gray-100 text-center">
            <Link to="/login" className="text-sm font-bold text-primary-600 hover:text-primary-800 hover:underline">
              Back to Login
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
