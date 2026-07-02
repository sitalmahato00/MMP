import { useAuth } from '@/stores/auth'

export function usePermission() {
  const { user } = useAuth()

  const hasRole = (...roles: string[]) => {
    if (!user?.roles) return false
    return roles.some((r) => user.roles.includes(r))
  }

  const hasPermission = (...perms: string[]) => {
    if (!user?.permissions) return false
    return perms.some((p) => user.permissions.includes(p))
  }

  const isAdmin = hasRole('admin')
  const isDepartmentUser = hasRole('department-user')
  const isDepartmentHead = hasRole('department-head')
  const isRecommendationOfficer = hasRole('recommendation-officer')
  const isApprovalOfficer = hasRole('approval-officer')
  const isViewer = hasRole('viewer')

  return {
    hasRole,
    hasPermission,
    isAdmin,
    isDepartmentUser,
    isDepartmentHead,
    isRecommendationOfficer,
    isApprovalOfficer,
    isViewer,
  }
}
