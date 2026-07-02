import { useState } from 'react'
import { useMutation } from '@tanstack/react-query'
import { authService } from '@/services/auth.service'
import { t } from '@/i18n'
import { Save } from 'lucide-react'

export function ChangePasswordPage() {
  const [currentPassword, setCurrentPassword] = useState('')
  const [newPassword, setNewPassword] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')
  const [error, setError] = useState('')

  const mutation = useMutation({
    mutationFn: () => authService.changePassword(currentPassword, newPassword),
    onSuccess: () => {
      setCurrentPassword('')
      setNewPassword('')
      setConfirmPassword('')
      setError('Password changed successfully')
    },
    onError: (err: unknown) => {
      setError((err as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Failed to change password')
    },
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    if (newPassword !== confirmPassword) {
      setError('Passwords do not match')
      return
    }
    mutation.mutate()
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-title">{t('nav.changePassword')}</h1>
        <p className="page-subtitle">Update your password</p>
      </div>

      <div className="max-w-md card">
        <div className="card-body">
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && (
              <div className={`rounded-lg p-3 text-sm ${error.includes('successfully') ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`}>
                {error}
              </div>
            )}

            <div>
              <label className="form-label">Current Password</label>
              <input
                type="password"
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
                className="form-input"
                required
              />
            </div>

            <div>
              <label className="form-label">New Password</label>
              <input
                type="password"
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
                className="form-input"
                required
                minLength={8}
              />
            </div>

            <div>
              <label className="form-label">Confirm New Password</label>
              <input
                type="password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                className="form-input"
                required
              />
            </div>

            <button type="submit" disabled={mutation.isPending} className="btn-primary gap-2">
              <Save className="h-4 w-4" />
              {mutation.isPending ? t('common.loading') : 'Change Password'}
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}
