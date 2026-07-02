import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { AuthProvider, useAuth } from '@/stores/auth'
import { Layout } from '@/components/layout/Layout'
import { LoginPage } from '@/pages/auth/LoginPage'
import { DashboardPage } from '@/pages/dashboard/DashboardPage'
import { MaterialRequestList } from '@/pages/material-requests/MaterialRequestList'
import { MaterialRequestForm } from '@/pages/material-requests/MaterialRequestForm'
import { MaterialRequestDetail } from '@/pages/material-requests/MaterialRequestDetail'
import { RepairOrderList } from '@/pages/repair-orders/RepairOrderList'
import { RepairOrderForm } from '@/pages/repair-orders/RepairOrderForm'
import { RepairOrderDetail } from '@/pages/repair-orders/RepairOrderDetail'
import { ApprovalsPage } from '@/pages/approvals/ApprovalsPage'
import { ReportsPage } from '@/pages/reports/ReportsPage'
import { AdminUsersPage } from '@/pages/admin/AdminUsersPage'
import { AdminDepartmentsPage } from '@/pages/admin/AdminDepartmentsPage'
import { AdminSettingsPage } from '@/pages/admin/AdminSettingsPage'
import { ProfilePage } from '@/pages/auth/ProfilePage'
import { ChangePasswordPage } from '@/pages/auth/ChangePasswordPage'
import type { ReactNode } from 'react'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 30000,
      retry: 1,
    },
  },
})

function ProtectedRoute({ children }: { children: ReactNode }) {
  const { isAuthenticated } = useAuth()
  if (!isAuthenticated) return <Navigate to="/login" replace />
  return <>{children}</>
}

function PublicRoute({ children }: { children: ReactNode }) {
  const { isAuthenticated } = useAuth()
  if (isAuthenticated) return <Navigate to="/dashboard" replace />
  return <>{children}</>
}

function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<PublicRoute><LoginPage /></PublicRoute>} />
      <Route element={<ProtectedRoute><Layout /></ProtectedRoute>}>
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/profile" element={<ProfilePage />} />
        <Route path="/change-password" element={<ChangePasswordPage />} />
        <Route path="/material-requests" element={<MaterialRequestList />} />
        <Route path="/material-requests/create" element={<MaterialRequestForm />} />
        <Route path="/material-requests/:id" element={<MaterialRequestDetail />} />
        <Route path="/material-requests/:id/edit" element={<MaterialRequestForm />} />
        <Route path="/repair-orders" element={<RepairOrderList />} />
        <Route path="/repair-orders/create" element={<RepairOrderForm />} />
        <Route path="/repair-orders/:id" element={<RepairOrderDetail />} />
        <Route path="/repair-orders/:id/edit" element={<RepairOrderForm />} />
        <Route path="/approvals" element={<ApprovalsPage />} />
        <Route path="/reports" element={<ReportsPage />} />
        <Route path="/admin/users" element={<AdminUsersPage />} />
        <Route path="/admin/departments" element={<AdminDepartmentsPage />} />
        <Route path="/admin/settings" element={<AdminSettingsPage />} />
      </Route>
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  )
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <AuthProvider>
          <AppRoutes />
        </AuthProvider>
      </BrowserRouter>
    </QueryClientProvider>
  )
}
