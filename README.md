# MMP College Management System (IT-DMS)

[![Laravel](https://img.shields.io/badge/Laravel-12-brightgreen.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-blue.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

> **Modern, role-based college management system with public portal, CMS, and CTEVT integrations.**

## 🌟 Quick Start (Local Development)

```bash
# Clone & Install
git clone <repo> d:/MMP
cd d:/MMP

# Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed  # Includes DemoDataSeeder, RolesAndPermissionsSeeder

# Frontend
npm install
npm run dev  # or npm run build for prod

# Run Server
php artisan serve
# Visit http://127.0.0.1:8000 (public home) or /login
```

**Demo Login**: Use seeded data (run `php artisan db:seed --class=DemoDataSeeder`):
- Admin/Principal: `principal@mmp.edu.np` / `password`
- Teacher: `teacher1@mmp.edu.np` / `password`
- Student: `student1@mmp.edu.np` / `password`

## 📁 Project Structure

```
d:/MMP/
├── app/
│   ├── Http/Controllers/     # Admin/Public/HOD/Student/etc.
│   ├── Models/               # 24+ (User, Student, Teacher, Attendance, etc.)
│   ├── Services/             # PublicDataService, AttendanceService, etc.
│   └── Policies/             # StaffPolicy, FacilityPolicy, etc.
├── database/
│   ├── migrations/           # Up to create_cms_tables.php
│   └── seeders/              # DemoDataSeeder, ExecutiveSeeder, etc.
├── resources/
│   ├── views/public/         # home.blade.php, facilities.blade.php, result.blade.php
│   └── views/admin/          # web-control/index.blade.php, etc.
├── routes/                   # web.php, admin.php, hod.php, etc.
├── public/                   # Assets
├── tests/                    # Feature/PublicManagedPagesTest.php, etc.
├── docs/                     # ctevt-result-notices-integration.md
├── TODO.md                   # Current tasks
├── composer.json             # Laravel 12 + spatie/laravel-permission
└── README.md                 # This file
```

## 🚀 Features

### Core Academic Management
- **Roles & Portals**: Admin/Principal (global), HOD (dept timetables), Teacher (attendance/assignments/marks), Student (schedule/results), Parent (monitoring), Alumni (directory).
- **Academic Entities**: Departments → Programs → Subjects → Timetables → Attendance/Assignments/Exams/Marks.
- **Middleware**: Role checks, audit logs, dept isolation, active sessions.

### Public Portal (SEO-Optimized)
- Homepage with banners, stats, notices (via PublicDataService).
- Pages: Departments/{slug}, Facilities, Staff/Leadership, Gallery, Downloads, Alumni, News/Events, Result Checker, CTEVT notices/results.
- Routes: `routes/web.php` via `HomeController`.

### CMS & Admin Tools
- Banners, Media, Site Settings, Pages, Executives, Facilities (Admin controllers).
- WebControl dashboard for content management.

### Integrations
- **CTEVt Result Notices**: See [docs/ctevt-result-notices-integration.md](docs/ctevt-result-notices-integration.md). Fetches notices/results via PublicDataService.

## 🛠 Technology Stack

| Backend | Laravel 12, PHP 8.2+, Eloquent, Sanctum, spatie/laravel-permission |
| Frontend | Vite, TailwindCSS, Alpine.js, Blade Components |
| Database | MySQL/SQLite (24+ migrations) |
| Testing | PHPUnit (public pages, home settings tests) |
| Other | Audit logs, caching, file uploads |

## 📖 Detailed Architecture (Original Spec)

### Database Schema (Key Models)
| Model | Key Fields | Relations |
|-------|------------|-----------|
| User | id, name, email, is_active | Base for all roles |
| AcademicSession | name (2080/81), is_current | Sessions |
| Department | code (CSIT), hod_id | Programs, Teachers |
| Student | admission_number, program_id, current_semester | Attendance, Marks, Parent |
| Teacher | department_id, qualification | Timetables, Attendance |
| Attendance | status (P/A/L/E), remarks | Session, Student |
| Marks | exam_id, student_id, status | Student, Exam |
| Notice | title, type (general/exam), target (students/parents) | Attachments |

*(Full schema/workflows/roles from original README preserved here - see historical version for exhaustive details on timetables, attendance loops, exam checking, security, UI glassmorphism, etc.)*

## 🧪 Testing

```bash
# Run tests
php artisan test
# Specific: public pages
php artisan test --filter=PublicManagedPagesTest
# Coverage
php artisan test --coverage
```

Key tests: `PublicManagedPagesTest`, `HomeLandingWebSettingsTest`.

## 🔧 Local Development

- **Hot Reload**: `npm run dev` + `php artisan serve`.
- **Demo Data**: `php artisan db:seed --class=DemoDataSeeder`.
- **Queues/Jobs**: `php artisan queue:work` (if needed).
- **Logs**: `tail -f storage/logs/laravel.log`.
- **Vite Assets**: Ensure `VITE_APP_URL=http://127.0.0.1:8000` in .env.

## 🚀 Production Deployment

```bash
composer install --optimize-autoloader --no-dev
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:restart
```

**Env Vars**: `APP_ENV=production`, `DB_*`, `FILESYSTEM_DISK=s3` (optional).

## 📸 Screenshots
*(Add to /public/screenshots/)*

![Home](public/screenshots/home.png)
![Admin Dashboard](public/screenshots/admin.png)
![Public Facilities](resources/views/public/facilities.blade.php screenshot)

## 🤝 Contributing

1. Fork & PR to `main`.
2. Follow PSR-12/Laravel Pint: `vendor/bin/pint`.
3. Add tests for new features.
4. Update README/TODO.md.
5. Prefix branches: `feature/readme-update`.

**Coding Standards**: Thin controllers, services for logic, FormRequests for validation.

## 🔒 Security & Auditing
- RBAC via Spatie.
- AuditActivity middleware on all web routes.
- Policies for Staff/Facility/Executive.
- Rate limiting on login/result.

## ❗ Troubleshooting
- **Seeds Fail**: `php artisan migrate:fresh --seed`.
- **Assets Missing**: `npm run build`.
- **Permissions**: `chmod -R 775 storage/ bootstrap/cache/`.
- **CTEVt Issues**: Check [docs/ctevt-result-notices-integration.md](docs/ctevt-result-notices-integration.md).

## 📄 License
MIT - See [LICENSE](LICENSE).

**Built with ❤️ for MMP College. Questions? Open an issue.**

