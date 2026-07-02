import { useForm } from 'react-hook-form'
import { useMutation } from '@tanstack/react-query'
import { useAuth } from '@/stores/auth'
import { authService } from '@/services/auth.service'
import { t } from '@/i18n'
import { Save } from 'lucide-react'

export function ProfilePage() {
  const { user, setUser } = useAuth()

  const { register, handleSubmit, formState: { errors } } = useForm({
    defaultValues: {
      name: user?.name || '',
      phone: user?.phone || '',
      address: user?.address || '',
    },
  })

  const mutation = useMutation({
    mutationFn: (data: { name: string; phone?: string; address?: string }) =>
      authService.updateProfile(data),
    onSuccess: (updatedUser) => setUser(updatedUser),
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-title">{t('nav.profile')}</h1>
        <p className="page-subtitle">Manage your profile</p>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="card p-6 text-center">
          <div className="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-primary text-white text-3xl font-bold">
            {user?.name?.charAt(0)?.toUpperCase()}
          </div>
          <h2 className="text-lg font-semibold">{user?.name}</h2>
          <p className="text-sm text-muted">{user?.email}</p>
          <p className="text-xs text-muted mt-1">{user?.roles?.join(', ')}</p>
        </div>

        <div className="lg:col-span-2 card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">Edit Profile</h2>
          </div>
          <div className="card-body">
            <form onSubmit={handleSubmit((data) => mutation.mutate(data))} className="space-y-4">
              <div>
                <label className="form-label">{t('auth.email')}</label>
                <input type="email" value={user?.email} className="form-input" disabled />
              </div>
              <div>
                <label className="form-label">Name</label>
                <input {...register('name', { required: true })} className="form-input" />
                {errors.name && <p className="form-error">Required</p>}
              </div>
              <div>
                <label className="form-label">Phone</label>
                <input {...register('phone')} className="form-input" />
              </div>
              <div>
                <label className="form-label">Address</label>
                <input {...register('address')} className="form-input" />
              </div>
              <button type="submit" disabled={mutation.isPending} className="btn-primary gap-2">
                <Save className="h-4 w-4" />
                {mutation.isPending ? t('common.loading') : t('common.save')}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  )
}
