# MMP College Management System

[![Laravel](https://img.shields.io/badge/Laravel-12-brightgreen.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-blue.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](./LICENSE)

MMP College Management System is a Laravel 12 application that combines:

- a public-facing college website
- a CMS for institutional content
- role-based academic portals
- admissions and result-check utilities
- department-scoped academic operations
- a notification and account-communication layer for every authenticated portal role

The platform is designed for Principal/Admin, HOD, Teacher, Student, Parent, and Alumni workflows while also serving guests through the public site and public API.

## Table of Contents

1. [Technology Stack](#technology-stack)
2. [Application Areas](#application-areas)
3. [Role and Portal Overview](#role-and-portal-overview)
4. [Public Website Pages](#public-website-pages)
5. [Portal Pages by Role](#portal-pages-by-role)
6. [Notifications and Email Flows](#notifications-and-email-flows)
7. [Authentication and Account Lifecycle](#authentication-and-account-lifecycle)
8. [Database Schema](#database-schema)
9. [Public API](#public-api)
10. [Storage, Media, and Branding](#storage-media-and-branding)
11. [Caching, Queues, and Rate Limits](#caching-queues-and-rate-limits)
12. [CTEVT External Sync Service](#ctevt-external-sync-service)
13. [Project Structure](#project-structure)
14. [Local Setup](#local-setup)
15. [Environment Configuration](#environment-configuration)
16. [Testing and Verification](#testing-and-verification)
17. [Deployment Notes](#deployment-notes)
18. [Known Notes](#known-notes)

## Technology Stack

| Layer | Implementation |
| --- | --- |
| Backend framework | Laravel 12 |
| Language | PHP 8.2+ |
| Frontend build | Vite |
| Styling | Tailwind CSS |
| Auth model | Laravel auth with role-based redirects |
| RBAC | Spatie Laravel Permission |
| API auth | Sanctum |
| Database | MySQL in production, SQLite supported for tests |
| Cache/session/queue | Redis-ready configuration |
| File storage | Public/private disks, S3-compatible storage supported |
| Notifications | Database + email notifications |
| Date support | AD plus Bikram Sambat helpers and BS datepicker UI |

## Application Areas

The codebase is split into six major areas:

1. Public website: homepage, notices, departments, gallery, downloads, admissions, result checker, people directory, facilities, leadership, alumni directory, and CMS pages.
2. Principal/Admin portal: full institutional administration, CMS, academic records, site settings, applications, and audit logs.
3. HOD portal: department-scoped student, teacher, subject, notice, timetable, attendance, exam, and resource management.
4. Teacher portal: classes, attendance, marks, assignments, timetable, resources, notices, and profile management.
5. Student and Parent portals: academic self-service, performance monitoring, subjects, results, notices, downloads, and settings.
6. Alumni portal: alumni profile, projects, achievements, career records, notices, settings, and community updates.

## Role and Portal Overview

| Role | Scope | Main outcomes |
| --- | --- | --- |
| Principal / Admin | Whole system | Configure academics, manage users and content, publish notices and exams, review applications, control site branding, inspect audit logs |
| HOD | Department only | Manage department students/teachers/subjects, publish department notices, manage attendance, exams, timetable, and resources |
| Teacher | Assigned subjects/classes | Record attendance, fill marks, manage assignments, review students and timetable |
| Student | Self-service | View attendance, subjects, marks, notices, assignments, timetable, downloads, profile, and settings |
| Parent | Child monitoring | Monitor child subjects, attendance, assignments, results, notices, and account preferences |
| Alumni | Alumni community | Maintain profile, share projects and achievements, update career history, follow notices and updates |
| Guest | Public access only | Browse site content, download public files, apply, and check results |

## Public Website Pages

### Public routes

| Route | Purpose |
| --- | --- |
| `/` | Homepage with banners, welcome message, principal section, notices, downloads, departments, facilities, alumni preview, and CTA blocks |
| `/notices` | Public notices listing |
| `/notices/{slug}` | Notice details |
| `/news-events` | Public news and events listing |
| `/news-events/{slug}` | News/event details |
| `/departments` | Department and program overview |
| `/departments/{slug}` | Department detail page |
| `/departments/{departmentSlug}/{programSlug}` | Program detail page |
| `/downloads` | Public downloads and resources |
| `/downloads/{download}/file` | Public file delivery |
| `/question-bank` | Question bank page |
| `/gallery` | Public gallery |
| `/result` | Public result checker form |
| `/result/submit` | Result search submission |
| `/people` | Public people directory |
| `/people/{type}/{id}` | HOD/teacher/staff profile page |
| `/staff` | Staff listing |
| `/staff/{id}` | Staff profile |
| `/leadership` | Executive/leadership listing |
| `/facilities` | Facilities showcase |
| `/contact` | Contact page |
| `/alumni` | Alumni directory |
| `/alumni/{id}` | Alumni public profile |
| `/page/{slug}` | CMS-managed standalone page |
| `/apply` | Admission form |
| `/brand-logo` | Dynamic brand logo endpoint |

### Public site features

- Homepage content comes from `PublicDataService` and site settings.
- Department pages can surface program details, subject context, and syllabus availability.
- Public notices support general notices, department/program notices, news, events, and CTEVT-related content.
- The people directory includes HOD, teacher, and staff profile pages.
- Public downloads and media use storage accessors instead of hardcoded asset paths.
- Admission and result-check pages are rate limited independently.
- CMS-managed pages are stored in the database and delivered via slug.

## Portal Pages by Role

### Shared authenticated pages

All authenticated roles now have:

- header notification bell with unread count
- recent notification dropdown
- full notification inbox page
- mark-all-read and delete actions
- role-based dashboard redirect after login
- forgot password and reset password flows

### Principal/Admin portal pages

Routes are defined in `routes/admin.php`.

- Dashboard
- Users
- Academic Sessions
- Attendance overview
- Departments
- Programs
- Students
- Teachers
- Parents
- Alumni
- Staff
- Exams and result sheets
- Notices
- News & Events
- Facilities
- Executives
- Media
- Downloads / resources
- Banners
- Roles & Permissions
- Web Control / site settings
- Applications
- Audit Logs
- Personal account settings

### HOD portal pages

Routes are defined in `routes/hod.php`.

- Dashboard
- Students
- Students export
- Teachers
- Attendance index, mark, store, sessions, reports, edit, update
- Exams, marks entry, marking scheme, analytics, results, export
- Timetable CRUD, slot delete, export, teacher conflict checks
- Notices CRUD
- News & Events CRUD
- Facilities CRUD
- Media upload/gallery/delete
- Reports shortcuts
- Alumni preparation and records
- Subjects CRUD, drawer, teacher assignment, syllabus/details management
- Downloads/resources
- Account settings

### Teacher portal pages

Routes are defined in `routes/teacher.php`.

- Dashboard
- My Classes
- Attendance CRUD
- Load students by subject
- Students list/show
- Timetable
- Exams index
- Fill marks / save marks
- Assignments CRUD
- Downloads/resources
- Notices
- News & Events
- Profile
- Change password
- Settings

### Student portal pages

Routes are defined in `routes/student.php`.

- Dashboard
- Attendance index/show
- Marks index/show
- Subjects
- Assignments index/show/submit
- Timetable
- Downloads
- Notices
- News & Events
- Profile
- Change password
- Settings

### Parent portal pages

Routes are defined in `routes/parent.php`.

- Dashboard
- Child overview
- Attendance
- Assignments
- Results index/show
- Subjects
- Notices
- News & Events
- Settings

### Alumni portal pages

Routes are defined in `routes/alumni.php`.

- Dashboard
- Profile view/edit/update
- Career history add/delete
- Projects list/edit/update
- Achievements list/store/delete
- Notices
- Settings

## Notifications and Email Flows

### Implemented notification channels

- In-app database notifications via the `notifications` table
- Email notifications via styled Blade email templates

### Notification UI

- Shared header bell in the portal navbar for every authenticated role
- Recent notification dropdown in the header
- Dedicated inbox page at `/notifications`
- Mark all as read
- Open notification target route
- Delete notification from inbox

### Notification triggers implemented

- New account creation for users created from Admin or HOD workflows
- Password reset emails
- Published internal notices
- Published exam/result notifications
- Official CTEVT general notices
- Official CTEVT result notices

### Notification targeting rules

The system supports all-user and scoped delivery:

- all portal users
- department-specific delivery
- program-specific delivery
- semester-specific delivery where relevant
- role-aware routing to the correct page when a notification is opened

### Account credential emails

When a new portal account is created for these roles, the user receives a styled email containing the login email and generated password:

- HOD
- Teacher
- Student
- Parent
- Alumni
- User accounts created directly by admin

If student creation automatically creates a linked parent account, both student and parent receive their credentials.

### Role settings and notification preferences

Notification preference forms are now persisted in `users.notification_preferences` rather than session-only storage.

Supported settings coverage:

- Principal/Admin settings
- HOD settings
- Teacher settings
- Student settings
- Parent settings
- Alumni settings

Supported preference types:

- email alert toggles
- in-app notification toggles
- SMS critical-alert toggle placeholder
- visual and locale preferences in `users.preferences`

## Authentication and Account Lifecycle

### Authentication routes

- `GET /login`
- `POST /login`
- `POST /logout`
- `GET /forgot-password`
- `POST /forgot-password`
- `GET /reset-password/{token}`
- `POST /reset-password`

### Authentication behavior

- Role-aware dashboard redirect after login
- Password reset notifications use a custom branded email template
- Settings pages support password change per role
- “Logout other devices” exists on the role settings pages

### Account creation behavior

- Admin and HOD user-management flows create portal users
- Newly created users can receive credential emails automatically
- Students can be linked to parents
- Students can be promoted to alumni through academic-session workflows

## Database Schema

The database is grouped below by functional area. Table names reflect the current migrations in `database/migrations`.

### 1. Identity, authentication, and access control

- `users`
- `password_reset_tokens`
- `sessions`
- `personal_access_tokens`
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`
- `notifications`

### 2. Framework runtime tables

- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

### 3. Academic structure

- `departments`
- `academic_sessions`
- `academic_session_semesters`
- `programs`
- `subjects`
- `subject_teacher`
- `timetables`
- `timetable_slots`

### 4. Core people tables

- `students`
- `teachers`
- `parents`
- `parent_student`
- `alumni`
- `staff`
- `executives`

### 5. Alumni portfolio and career tables

- `alumni_projects`
- `alumni_achievements`
- `alumni_employments`

### 6. Attendance and academic activity

- `attendance_sessions`
- `attendances`
- `assignments`
- `assignment_submissions`
- `exams`
- `exam_program`
- `marks`
- `exam_subject_marking_schemes`
- `staff_attendances`

### 7. Public content and CMS

- `notices`
- `notice_attachments`
- `pages`
- `banners`
- `downloads`
- `media`
- `facilities`
- `site_settings`
- `communications`

### 8. Operations and governance

- `applications`
- `audit_logs`
- `staff_documents`

### Database notes

- `subjects` includes `details` and `syllabus` fields.
- `users` includes `preferences` and `notification_preferences` JSON columns.
- `notifications` stores in-app inbox items for authenticated users.
- `downloads` supports subject/program linkage for academic resources.
- `departments`, `programs`, `subjects`, notices, and exams are inter-related for scope-aware notifications.

## Public API

Public API routes are defined in `routes/api.php` under `/api/v1/public`.

| Endpoint | Purpose |
| --- | --- |
| `GET /api/v1/public/homepage` | Homepage payload |
| `GET /api/v1/public/notices` | Public notices |
| `GET /api/v1/public/departments` | Department listing |
| `GET /api/v1/public/departments/{slug}` | Department detail |
| `GET /api/v1/public/alumni` | Featured alumni |
| `GET /api/v1/public/downloads` | Public downloads |
| `GET /api/v1/public/pages/{slug}` | CMS page content |
| `GET /api/v1/public/facilities` | Facilities payload |
| `GET /api/v1/public/staff` | Staff listing |
| `GET /api/v1/public/leadership` | Leadership listing |
| `GET /api/v1/public/site-settings` | Shared branding and content settings |

Authenticated API:

- `GET /api/v1/user`
- `GET /api/v1/subjects/{subject}/students`

## Storage, Media, and Branding

### File handling

- Public files are served through the public disk or a public object-storage URL.
- Restricted files can be delivered from a private disk through controllers.
- Models expose helper accessors such as `image_url`, `avatar_url`, `file_url`, `photo_url`, and `syllabus_url`.

### Content/media areas

- banners
- gallery/media
- downloads/resources
- facility media
- executive photos
- department photos and syllabi
- profile avatars
- notice attachments

### Branding

- Site branding is controlled via `site_settings`
- `/brand-logo` returns the active logo with fallback behavior
- shared navbar/sidebar branding uses the brand-logo route

## Caching, Queues, and Rate Limits

### Caching

- Public homepage and related public data are cached through `PublicDataService`
- Dashboard sections use short-lived cache keys in several portals
- Sidebar counters use cache for fast rendering

### Queues

- The project is queue-ready for email and asynchronous processing
- Redis-backed queue workers are recommended in production

### Rate limits

- `login`: 5 attempts per minute per email/IP
- `apply`: 10 attempts per hour per email/IP
- `result-check`: 30 requests per minute per IP
- `public-api`: 120 requests per minute per IP

## CTEVT External Sync Service

### Overview

The system includes an external sync service for CTEVT notices that solves the cPanel firewall issue (port 5580 blocked). The service fetches notices from an external server and syncs them to the production database.

### Architecture

- **Admin Dashboard**: "🔄 Sync CTEVT Notices" button in CTEVT tab
- **CtevtSyncController**: Handles sync requests, rate limiting, and logging
- **External Service**: Standalone PHP endpoint that fetches from CTEVT API
- **Database**: `ctevt_sync_logs` table tracks all sync operations

### Setup

1. Deploy external service (see `external-sync-service/README.md`)
2. Add to `.env`:
   ```env
   CTEVT_SYNC_EXTERNAL_URL=https://your-external-service.com/sync-endpoint.php
   CTEVT_SYNC_API_TOKEN=your-secret-token-here
   ```
3. Run: `php artisan migrate`
4. Test sync button in admin dashboard

### Files

- `app/Http/Controllers/Admin/CtevtSyncController.php` - Admin controller
- `app/Models/CtevtSyncLog.php` - Sync log model
- `external-sync-service/sync-endpoint.php` - External service endpoint
- `external-sync-service/README.md` - Deployment guide

## Project Structure

| Path | Purpose |
| --- | --- |
| `app/Http/Controllers/Public` | Public website controllers |
| `app/Http/Controllers/Admin` | Principal/Admin portal controllers |
| `app/Http/Controllers/HOD` | HOD portal controllers |
| `app/Http/Controllers/Teacher` | Teacher portal controllers |
| `app/Http/Controllers/Student` | Student portal controllers |
| `app/Http/Controllers/Parent` | Parent portal controllers |
| `app/Http/Controllers/Alumni` | Alumni portal controllers |
| `app/Http/Controllers/Auth` | Login and password-reset controllers |
| `app/Notifications` | Email/database notification classes |
| `app/Services` | Portal services such as public-data and notification services |
| `resources/views/public` | Public UI |
| `resources/views/admin` | Admin UI |
| `resources/views/hod` | HOD UI |
| `resources/views/teacher` | Teacher UI |
| `resources/views/student` | Student UI |
| `resources/views/parent` | Parent UI |
| `resources/views/alumni` | Alumni UI |
| `resources/views/emails` | Styled email templates |
| `routes/*.php` | Route files split by portal |
| `tests/Feature` | Feature test coverage |

### Route files

- `routes/web.php`
- `routes/admin.php`
- `routes/hod.php`
- `routes/teacher.php`
- `routes/student.php`
- `routes/parent.php`
- `routes/alumni.php`
- `routes/api.php`

## Local Setup

### Quick start

```bash
composer run setup
```

On Windows, start local development with `composer run dev:windows`.
The default `composer run dev` command includes Laravel Pail, which requires the `pcntl` extension and does not run on Windows.

### Manual setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

### Demo data

```bash
php artisan db:seed
php artisan db:seed --class=DemoDataSeeder
```

Demo login pattern in seeded demo data:

- principal: `principal@mmp.edu.np / password`
- hod: `hod.it@mmp.edu.np / password`
- teacher: `teacher.it@mmp.edu.np / password`
- student: `student01@mmp.edu.np / password`
- parent: `parent01@mmp.edu.np / password`
- alumni: `alumni01@mmp.edu.np / password`

### After local setup

- run `php artisan storage:link` for local public-disk file access
- run `php artisan optimize:clear` after config or route changes

## Environment Configuration

Important environment categories:

| Category | Important variables |
| --- | --- |
| App | `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL` |
| Database | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Cache/session/queue | `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `REDIS_*` |
| Mail | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` |
| Storage | `FILESYSTEM_DISK`, `PUBLIC_FILESYSTEM_DRIVER`, `PUBLIC_FILESYSTEM_URL`, `PRIVATE_FILESYSTEM_DRIVER`, `AWS_*` |
| External feeds | `CTEVT_*` |

### Recommended production services

- MySQL
- Redis
- S3-compatible object storage or equivalent
- SMTP/mail provider
- background queue worker

## Testing and Verification

### Useful commands

```bash
php artisan test
php artisan route:list --path=notifications
php artisan route:list --name=password
```

### Notification/auth verification completed in this branch

- password reset request and completion flow
- notification inbox list/open/delete/mark-all-read
- per-role preference persistence for admin, hod, teacher, student, parent, and alumni
- notice, exam, and CTEVT notification targeting
- credential-email notification dispatch for created accounts

### Recommended smoke tests

1. Create a HOD, Teacher, Student, Parent, or Alumni account and confirm credential email delivery.
2. Publish a general notice and verify the bell dropdown/inbox updates.
3. Publish a department/program-scoped notice and verify only matching users receive it.
4. Publish exam results and verify student/parent result notifications.
5. Open each role settings page and confirm preferences persist after refresh.
6. Trigger forgot-password and reset-password once with a real mail transport.

## Deployment Notes

Recommended production steps:

```bash
composer install --optimize-autoloader --no-dev
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Production checklist:

1. Configure real mail credentials before enabling account-creation and password-reset notifications.
2. Run queue workers continuously.
3. Set correct storage URLs and CDN/public bucket URL.
4. Confirm the `notifications` table exists in production.
5. Verify brand logo, public media, protected downloads, and email links after deployment.

## Known Notes

- On some Windows environments, Blade compilation can hit a temporary-file rename permission issue in `storage/framework/views`. This can affect broad test runs or `view:cache` even when application logic is correct.
- Some older feature tests in the repository still depend on missing factories or older fixture assumptions outside the notification/auth work.
- Alumni now have a dedicated settings page for profile, preferences, security, and notifications, matching the other portal roles.

## Contributing

1. Keep controllers thin and move shared logic into services where possible.
2. Prefer model accessors/services over hardcoded storage URLs.
3. Add feature tests for role-based flows, uploads, and notification dispatch.
4. Update the README when routes, tables, or role capabilities change.

## License

MIT
