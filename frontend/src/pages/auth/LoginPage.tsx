import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useAuth } from '@/stores/auth'
import { useLocale } from '@/hooks/useLocale'
import { t } from '@/i18n'
import { Languages } from 'lucide-react'

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(6),
})

type LoginForm = z.infer<typeof loginSchema>

export function LoginPage() {
  const { login, isLoading } = useAuth()
  const { locale, setLocale } = useLocale()
  const navigate = useNavigate()
  const [error, setError] = useState('')

  const { register, handleSubmit, formState: { errors } } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
  })

  const onSubmit = async (data: LoginForm) => {
    try {
      setError('')
      await login(data.email, data.password)
      navigate('/dashboard')
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message
      setError(msg || t('auth.invalidCredentials'))
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-primary/5 to-gray-100 p-4">
      <div className="w-full max-w-md">
        <div className="card p-8">
          <div className="mb-8 text-center">
            <div className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-primary text-white text-2xl font-bold">
              MMP
            </div>
            <h1 className="text-xl font-bold text-gray-900">{t('app.fullName')}</h1>
            <p className="mt-1 text-sm text-muted">{t('app.subtitle')}</p>
            <p className="text-xs text-muted">Hatimuda, Morang, Nepal</p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            {error && (
              <div className="rounded-lg bg-red-50 p-3 text-sm text-red-600">{error}</div>
            )}

            <div>
              <label className="form-label">{t('auth.email')}</label>
              <input
                {...register('email')}
                type="email"
                className="form-input"
                placeholder="you@example.com"
              />
              {errors.email && <p className="form-error">{errors.email.message}</p>}
            </div>

            <div>
              <label className="form-label">{t('auth.password')}</label>
              <input
                {...register('password')}
                type="password"
                className="form-input"
                placeholder="••••••••"
              />
              {errors.password && <p className="form-error">{errors.password.message}</p>}
            </div>

            <button type="submit" disabled={isLoading} className="btn-primary w-full">
              {isLoading ? t('common.loading') : t('auth.loginButton')}
            </button>
          </form>

          <div className="mt-6 text-center">
            <button
              onClick={() => setLocale(locale === 'en' ? 'ne' : 'en')}
              className="inline-flex items-center gap-1 text-sm text-primary hover:underline"
            >
              <Languages className="h-4 w-4" />
              {locale === 'en' ? 'नेपाली' : 'English'}
            </button>
          </div>
        </div>

        <p className="mt-4 text-center text-xs text-muted">
          &copy; {new Date().getFullYear()} {t('app.fullName')}. All rights reserved.
        </p>
      </div>
    </div>
  )
}
