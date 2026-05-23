import { Routes, Route, Navigate } from 'react-router-dom';
import { lazy, Suspense } from 'react';
import { RequireAuth } from '@app/router/ProtectedRoute';
import { GuestOnly } from '@app/router/GuestOnly';
import { Spinner } from '@shared/components/ui/Spinner';

// ─── Lazy-loaded pages ────────────────────────────────────────────────────────
const LoginPage            = lazy(() => import('@modules/auth/pages/LoginPage'));
const ForgotPasswordPage   = lazy(() => import('@modules/auth/pages/ForgotPasswordPage'));
const ResetPasswordPage    = lazy(() => import('@modules/auth/pages/ResetPasswordPage'));
const Verify2faPage        = lazy(() => import('@modules/auth/pages/Verify2faPage'));

// Public (CMS) pages
const PublicLayout         = lazy(() => import('@app/layouts/PublicLayout'));
const HomePage             = lazy(() => import('@modules/public/pages/HomePage'));
const NoticesPage          = lazy(() => import('@modules/public/pages/NoticesPage'));
const NoticeShowPage       = lazy(() => import('@modules/public/pages/NoticeShowPage'));
const NewsEventsPage       = lazy(() => import('@modules/public/pages/NewsEventsPage'));
const NewsEventShowPage    = lazy(() => import('@modules/public/pages/NewsEventShowPage'));
const DepartmentsPage      = lazy(() => import('@modules/public/pages/DepartmentsPage'));
const DepartmentShowPage   = lazy(() => import('@modules/public/pages/DepartmentShowPage'));
const ProgramShowPage      = lazy(() => import('@modules/public/pages/ProgramShowPage'));
const GalleryPage          = lazy(() => import('@modules/public/pages/GalleryPage'));
const DownloadsPage        = lazy(() => import('@modules/public/pages/DownloadsPage'));
const ContactPage          = lazy(() => import('@modules/public/pages/ContactPage'));
const PeoplePage           = lazy(() => import('@modules/public/pages/PeoplePage'));
const StaffPage            = lazy(() => import('@modules/public/pages/StaffPage'));
const LeadershipPage       = lazy(() => import('@modules/public/pages/LeadershipPage'));
const AlumniPage           = lazy(() => import('@modules/public/pages/AlumniPage'));
const AlumniProfilePage    = lazy(() => import('@modules/public/pages/AlumniProfilePage'));
const FacilitiesPage       = lazy(() => import('@modules/public/pages/FacilitiesPage'));
const ResultPage           = lazy(() => import('@modules/public/pages/ResultPage'));
const QuestionBankPage     = lazy(() => import('@modules/public/pages/QuestionBankPage'));
const ContentPage          = lazy(() => import('@modules/public/pages/ContentPage'));
const ForbiddenPage     = lazy(() => import('@shared/pages/error/ForbiddenPage'));
const NotFoundPage      = lazy(() => import('@shared/pages/error/NotFoundPage'));

// Admin
const AdminLayout       = lazy(() => import('@app/layouts/AdminLayout'));
const AdminDashboard    = lazy(() => import('@modules/admin/pages/DashboardPage'));

// Student module
const StudentListPage   = lazy(() => import('@modules/student/pages/StudentListPage'));
const StudentShowPage   = lazy(() => import('@modules/student/pages/StudentShowPage'));
const StudentCreatePage = lazy(() => import('@modules/student/pages/StudentCreatePage'));
const StudentEditPage   = lazy(() => import('@modules/student/pages/StudentEditPage'));

// Teacher module
const TeacherListPage   = lazy(() => import('@modules/teacher/pages/TeacherListPage'));

// Exam module
const ExamListPage      = lazy(() => import('@modules/exam/pages/ExamListPage'));

// Academic module
const AcademicPage      = lazy(() => import('@modules/academic/pages/AcademicPage'));

// Attendance module
const AttendancePage    = lazy(() => import('@modules/attendance/pages/AttendancePage'));

// Teacher Portal
const TeacherLayout     = lazy(() => import('@app/layouts/TeacherLayout'));
const TeacherDashboard  = lazy(() => import('@modules/teacher/pages/TeacherDashboardPage'));

// Student Portal
const StudentLayout     = lazy(() => import('@app/layouts/StudentLayout'));
const StudentDashboard  = lazy(() => import('@modules/student/pages/StudentDashboardPage'));

// HOD Portal
const HodLayout         = lazy(() => import('@app/layouts/HodLayout'));
const HodDashboard      = lazy(() => import('@modules/hod/pages/HodDashboardPage'));

function PageLoader() {
  return (
    <div className="flex h-64 items-center justify-center">
      <Spinner size="lg" />
    </div>
  );
}

export function AppRouter() {
  return (
    <Suspense fallback={<PageLoader />}>
      <Routes>
        {/* ── Public / Auth ──────────────────────────────────────── */}
        <Route
          path="/login"
          element={
            <GuestOnly>
              <LoginPage />
            </GuestOnly>
          }
        />
        <Route
          path="/forgot-password"
          element={
            <GuestOnly>
              <ForgotPasswordPage />
            </GuestOnly>
          }
        />
        <Route
          path="/reset-password/:token"
          element={
            <GuestOnly>
              <ResetPasswordPage />
            </GuestOnly>
          }
        />
        <Route
          path="/verify-2fa"
          element={
            <GuestOnly>
              <Verify2faPage />
            </GuestOnly>
          }
        />

        {/* ── Admin Portal ───────────────────────────────────────── */}
        <Route
          path="/admin"
          element={
            <RequireAuth roles={['admin', 'staff', 'librarian', 'accountant']}>
              <AdminLayout />
            </RequireAuth>
          }
        >
          <Route index element={<Navigate to="dashboard" replace />} />
          <Route path="dashboard" element={<AdminDashboard />} />

          {/* Students */}
          <Route path="students"         element={<StudentListPage />} />
          <Route path="students/create"  element={<StudentCreatePage />} />
          <Route path="students/:id"     element={<StudentShowPage />} />
          <Route path="students/:id/edit" element={<StudentEditPage />} />

          {/* Teachers */}
          <Route path="teachers"         element={<TeacherListPage />} />

          {/* Exams */}
          <Route path="exams"            element={<ExamListPage />} />

          {/* Academic */}
          <Route path="academic"         element={<AcademicPage />} />

          {/* Attendance */}
          <Route path="attendance"       element={<AttendancePage />} />
        </Route>

        {/* ── HOD Portal ─────────────────────────────────────────── */}
        <Route
          path="/hod"
          element={
            <RequireAuth roles={['hod']}>
              <HodLayout />
            </RequireAuth>
          }
        >
          <Route index element={<Navigate to="dashboard" replace />} />
          <Route path="dashboard" element={<HodDashboard />} />
          <Route path="students"  element={<StudentListPage />} />
          <Route path="attendance" element={<AttendancePage />} />
        </Route>

        {/* ── Teacher Portal ─────────────────────────────────────── */}
        <Route
          path="/teacher"
          element={
            <RequireAuth roles={['teacher']}>
              <TeacherLayout />
            </RequireAuth>
          }
        >
          <Route index element={<Navigate to="dashboard" replace />} />
          <Route path="dashboard" element={<TeacherDashboard />} />
          <Route path="attendance" element={<AttendancePage />} />
          <Route path="exams"     element={<ExamListPage />} />
        </Route>

        {/* ── Student Portal ─────────────────────────────────────── */}
        <Route
          path="/student"
          element={
            <RequireAuth roles={['student']}>
              <StudentLayout />
            </RequireAuth>
          }
        >
          <Route index element={<Navigate to="dashboard" replace />} />
          <Route path="dashboard" element={<StudentDashboard />} />
        </Route>

        {/* ── Public CMS Routes ──────────────────────────────────── */}
        <Route element={<PublicLayout />}>
          <Route path="/"                                                   element={<HomePage />} />
          <Route path="/notices"                                            element={<NoticesPage />} />
          <Route path="/notices/:slug"                                      element={<NoticeShowPage />} />
          <Route path="/news-events"                                        element={<NewsEventsPage />} />
          <Route path="/news-events/:slug"                                  element={<NewsEventShowPage />} />
          <Route path="/departments"                                        element={<DepartmentsPage />} />
          <Route path="/departments/:slug"                                  element={<DepartmentShowPage />} />
          <Route path="/departments/:deptSlug/programs/:programSlug"       element={<ProgramShowPage />} />
          <Route path="/gallery"                                            element={<GalleryPage />} />
          <Route path="/downloads"                                          element={<DownloadsPage />} />
          <Route path="/contact"                                            element={<ContactPage />} />
          <Route path="/people"                                             element={<PeoplePage />} />
          <Route path="/staff"                                              element={<StaffPage />} />
          <Route path="/leadership"                                         element={<LeadershipPage />} />
          <Route path="/alumni"                                             element={<AlumniPage />} />
          <Route path="/alumni/:id"                                         element={<AlumniProfilePage />} />
          <Route path="/facilities"                                         element={<FacilitiesPage />} />
          <Route path="/result"                                             element={<ResultPage />} />
          <Route path="/question-bank"                                      element={<QuestionBankPage />} />
          <Route path="/pages/:slug"                                        element={<ContentPage />} />
        </Route>

        {/* ── Redirects & Errors ─────────────────────────────────── */}
        <Route path="/403" element={<ForbiddenPage />} />
        <Route path="*"    element={<NotFoundPage />} />
      </Routes>
    </Suspense>
  );
}
