# MMP College Management System

[![Laravel](https://img.shields.io/badge/Laravel-12-brightgreen.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-blue.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)]

MMP College Management System is a Laravel 12 application for the college website, administration, academic operations, and public content delivery. It combines a public portal, CMS-driven content management, and role-based dashboards for principal/admin, HOD, teacher, student, parent, and alumni users. The current codebase is prepared for production with MySQL, Redis, object storage/CDN-friendly media delivery, route throttling, and short-lived dashboard caching.

## Platform Snapshot

- Public website for homepage content, notices, departments, facilities, leadership, gallery, downloads, question bank, result checking, contact, alumni, and admissions.
- Admin CMS for banners, media, pages, departments, executives, facilities, downloads, notices, settings, applications, and audit logs.
- Academic portals for students, teachers, HODs, parents, and alumni.
- Nepali BS date support and a custom BS datepicker flow in the UI.
- Storage accessors and a branded logo route for public and admin media.
- Automatic student-to-alumni promotion when academic sessions are closed.

## Roles and Portals

| Role | Main responsibilities | Key areas |
| --- | --- | --- |
| Principal / Admin | Full site and academic administration | Dashboard, users, academic sessions, departments, programs, students, teachers, parents, alumni, staff, exams, notices, media, downloads, banners, facilities, executives, web control, applications, audit logs |
| HOD | Department-level academic management | Department dashboard, department students and teachers, attendance, exams, marks, timetable, notices, media, alumni preparation, reports |
| Teacher | Classroom and assessment workflow | Attendance entry, marks entry, assignments, timetable, class lists, exams, notices, profile |
| Student | Academic self-service | Dashboard, profile, attendance, marks and results, timetable, assignments, downloads, notices, exams, performance |
| Parent | Child monitoring | Child profile, attendance, marks and results, timetable, notices, communication, performance analytics |
| Alumni | Former student profile and updates | Alumni profile, notices, events, directory |
| Guest / Public | Browse and apply | Public pages, admissions form, result checker, public downloads, public API consumers |

## Public Pages

| Route | Page | Purpose |
| --- | --- | --- |
| `/` | Home | Hero banners, quick links, welcome content, principal corner, news, notices, CTEVT feeds, statistics, recent downloads, departments, facilities, gallery preview, apply CTA |
| `/notices` | Notices | General, exam, news, event, CTEVT general, and CTEVT result notices |
| `/news-events` | News and Events | Public news and event listings |
| `/departments` | Departments | Program cards with photos, syllabus indicator, and summary info |
| `/departments/{slug}` | Department detail | Department photo, description, HOD, programs, and syllabus download |
| `/downloads` | Downloads | Public resources and downloadable files |
| `/question-bank` | Question bank | Question bank resources for students and visitors |
| `/gallery` | Gallery | Photo gallery with lightbox browsing |
| `/result` | Result checker | Public result checking form with throttling |
| `/people` | People directory | HOD, teachers, staff, and lab techs filtered by department |
| `/people/{type}/{id}` | People profile | Individual HOD, teacher, or staff profile page |
| `/staff` | Staff directory | Administrative and support staff listing |
| `/leadership` | Leadership | Presidents and principals listing |
| `/facilities` | Facilities | Facilities cards with photos, documents, and videos |
| `/contact` | Contact | Contact details, address, phone, email, and map embed |
| `/alumni` | Alumni directory | Featured alumni directory |
| `/alumni/{id}` | Alumni profile | Individual alumni profile |
| `/page/{slug}` | Managed page | CMS-managed page content such as about, objectives, contact, scholarships, and internships |
| `/apply` | Apply now | Public admissions form |
| `/brand-logo` | Brand logo | Current site logo with favicon fallback |

### Public Page Features

- Homepage content is built from `PublicDataService` and cached to reduce repeated queries.
- The home page highlights the admissions CTA, public notices, news, departments, facilities, alumni-related content, and current branding.
- Notices include both internal MMP content and CTEVT feeds.
- Department pages surface the department photo and syllabus if available.
- The gallery page uses CDN-backed image URLs and a lightbox viewer.
- The facilities page renders photos, attached documents, and other resource links.
- The people directory groups HODs, teachers, staff, and lab techs by department.
- The apply form is rate limited and intended for admissions intake.
- The result checker is throttled separately from the rest of the site.

## Admin and CMS Modules

| Module | Purpose |
| --- | --- |
| Dashboard | Site activity and operational overview |
| Users | User and role management |
| Academic Sessions | Academic year/session lifecycle and current session control |
| Departments | Department records, HOD assignment, photos, syllabi |
| Programs | Program definitions linked to departments |
| Students | Student records and academic profile management |
| Teachers | Teacher records and department assignment |
| Parents | Parent/guardian records and child relationships |
| Alumni | Alumni records derived from student history |
| Staff | Administrative and support staff records |
| Exams | Exam setup and publication |
| Notices | Public and internal notices |
| Facilities | Facility listings and associated media |
| Executives | President/principal and other leadership records |
| Media | Gallery and media uploads |
| Downloads | Public resources and protected files |
| Banners | Homepage hero banners |
| Web Control | Site settings, branding, and shared page content |
| Applications | Admissions submissions from the public apply form |
| Audit Logs | Activity tracking and security review |

### Web Control Content

The site settings module manages shared public content such as:

- Site logo
- Welcome message
- What is MMP section
- Objectives
- Principal name
- Principal photo
- Principal message and attachment
- Contact details
- Google Maps embed
- Scholarships and internships content
- Managed pages like About, Objectives, Contact Us, Scholarship Schemes, and Internships

## Database Model

The database is organized around academic structure, people, public content, and auditability.

### Core Data Groups

| Group | Main tables / models | Notes |
| --- | --- | --- |
| Identity and access | `users`, Spatie permission tables | Authentication and role-based access control |
| Academic structure | `academic_sessions`, `departments`, `programs`, `subjects`, `timetables`, `timetable_slots` | Defines the academic hierarchy and scheduling |
| People | `students`, `teachers`, `parents`, `alumni`, `staff`, `executives` | Role-specific people records and profiles |
| Teaching and assessment | `attendance_sessions`, `attendance`, `assignments`, `assignment_submissions`, `exams`, `marks` | Attendance, homework, exams, and result data |
| Public content | `banners`, `notices`, `pages`, `media`, `downloads`, `facilities`, `site_settings`, `communications` | Homepage, CMS, downloadable resources, and public-facing content |
| Governance and logs | `applications`, `audit_logs` | Admissions intake and action tracking |

### Relationship Summary

- A department has many programs, students, teachers, notices, media items, facilities, and alumni.
- A program belongs to a department and drives student enrollment, timetables, subjects, and assignments.
- A student belongs to a program, department, and parent profile, and can later become an alumnus.
- A teacher belongs to a department and participates in timetables, attendance, marks, and class workflows.
- Academic sessions determine the active academic year and support the student-to-alumni promotion flow.
- Site settings power global branding and shared public sections.

### Site Settings Defaults

The application seeds a default site settings set through `SiteSetting::defaultDefinitions()`. Important keys include:

- `site_logo`
- `what_is_mmp`
- `objectives`
- `welcome_message`
- `principals_message`
- `principal_photo`
- `principal_message_media`
- `president_name`
- `principal_name`
- `classrooms_labs`
- `workshops`
- `transportation`
- `scholarship_schemes`
- `internships_placements`
- `contact_us_content`
- `contact_email`
- `contact_phone`
- `contact_address`
- `google_maps_iframe`

## Public API

The public API is exposed under `/api/v1/public` and throttled with the `public-api` limiter.

| Endpoint | Purpose |
| --- | --- |
| `GET /api/v1/public/homepage` | Homepage data |
| `GET /api/v1/public/notices` | Public notices |
| `GET /api/v1/public/departments` | Department listing |
| `GET /api/v1/public/departments/{slug}` | Department details |
| `GET /api/v1/public/alumni` | Featured alumni |
| `GET /api/v1/public/downloads` | Public downloads |
| `GET /api/v1/public/pages/{slug}` | Managed CMS pages |
| `GET /api/v1/public/facilities` | Facilities data |
| `GET /api/v1/public/staff` | Staff listing |
| `GET /api/v1/public/leadership` | Leadership listing |
| `GET /api/v1/public/site-settings` | Shared branding and content settings |

## Storage, CDN, and Branding

The project splits public and private file delivery.

- Public files live on the `public` disk and should resolve through a public URL or CDN URL.
- Private files live on the `private` disk and are streamed through controller responses.
- Brand images and icons come from the `site_logo` setting and are exposed through the `/brand-logo` route.
- Model accessors are used for public URLs such as `image_url`, `avatar_url`, `file_url`, `url`, `photo_url`, `syllabus_url`, `image_urls`, `document_urls`, and `video_urls`.

Relevant files:

- [config/filesystems.php](config/filesystems.php)
- [app/Http/Controllers/Admin/DownloadController.php](app/Http/Controllers/Admin/DownloadController.php)
- [app/Models/Download.php](app/Models/Download.php)
- [app/Models/Media.php](app/Models/Media.php)
- [app/Models/Department.php](app/Models/Department.php)
- [app/Models/Facility.php](app/Models/Facility.php)

## Routing and Security

The application uses named rate limiters and route-level protection for public traffic.

- `login`: 5 attempts per minute per email and IP.
- `apply`: 10 attempts per hour per email and IP.
- `result-check`: 30 requests per minute per IP.
- `public-api`: 120 requests per minute per IP.

Other operational protections include:

- Public content caching through `PublicDataService`
- Dashboard caching for student, teacher, HOD, parent, and alumni portals
- Separate public and private download handling
- Automatic cache invalidation when content or files change

## Development Setup

For a full local bootstrap:

```bash
composer run setup
```

Manual setup if you want to run each step yourself:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

**Seeding for Complete Demo Data:**

```bash
# Basic structure (safe for production-like env)
php artisan db:seed

# Full demo data with users/roles/content (DEMO ONLY)
php artisan db:seed --class=DemoDataSeeder
# Login with: email@domain.np / password: "password"
```

Notes:

- Run `php artisan storage:link` if you are using the local public disk.
- 17+ seeders + DemoDataSeeder provide complete coverage for all tables/fields.
- New factories added for Department, Program, Student, Teacher, Exam.
- `npm run dev` starts the Vite development build.

**Demo Logins:**
| Role | Email | Password |
|------|-------|----------|
| Principal | principal@mmp.edu.np | password |
| HOD | hod.it@mmp.edu.np | password |
| Teacher | teacher.it@mmp.edu.np | password |
| Student | student01@mmp.edu.np | password |
| Parent | parent01@mmp.edu.np | password |
| Alumni | alumni01@mmp.edu.np | password |

## Environment Configuration

The repository ships with production-oriented defaults in [.env.example](.env.example). Replace the placeholders with your real values before deployment.

| Category | Important variables | Purpose |
| --- | --- | --- |
| Application | `APP_ENV`, `APP_DEBUG`, `APP_URL` | Production app settings |
| Database | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | MySQL connection details |
| Cache / Session / Queue | `CACHE_STORE`, `SESSION_DRIVER`, `SESSION_CONNECTION`, `SESSION_STORE`, `QUEUE_CONNECTION`, `REDIS_*` | Redis-backed runtime services |
| Storage | `FILESYSTEM_DISK`, `PUBLIC_FILESYSTEM_DRIVER`, `PUBLIC_FILESYSTEM_URL`, `PRIVATE_FILESYSTEM_DRIVER`, `AWS_*` | Object storage and CDN integration |
| Mail | `MAIL_*` | Outgoing email delivery |
| External feeds | `CTEVT_*` | CTEVT notices and result integration |

Recommended production storage behavior:

- Use `public` disk or an S3-compatible bucket for public assets.
- Use `private` disk for restricted files.
- Set `PUBLIC_FILESYSTEM_URL` to the CDN or public bucket URL you want browsers to use.

## Production Deployment

Recommended deployment steps:

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

1. Provision MySQL, Redis, and an S3-compatible object storage bucket.
2. Point `APP_URL` to the public site domain.
3. Set `PUBLIC_FILESYSTEM_URL` to the CDN origin or public bucket URL.
4. Fill in the `AWS_*` credentials for public and private storage.
5. Run queue workers continuously under Supervisor, systemd, Forge, or your hosting platform.
6. Add a scheduler cron entry if you use scheduled tasks.
7. Take a backup and test a restore before going live.
8. Enable error monitoring or centralized logging before the first production release.

Recommended runtime commands:

- `php artisan queue:work`
- `php artisan schedule:run` once per minute from cron
- `php artisan optimize:clear` when you need to invalidate cached config, routes, or views

## Testing and Verification

```bash
php artisan test
npm run build
```

Suggested smoke tests after deployment:

- Load the homepage and verify banners, notices, leadership, and quick links.
- Open a department page and confirm the photo and syllabus links render.
- Open public downloads and gallery items and confirm URLs resolve correctly.
- Submit the apply form and confirm rate limiting works.
- Check the result page and public API responses.
- Visit the admin web control page and confirm logo, banner, and media previews load.

## Troubleshooting

- Broken public images usually mean the storage URL or CDN URL is wrong.
- Missing private download files usually mean the stored path or private disk is wrong.
- Queue jobs not running usually means the Redis worker is not active.
- Stale content after a deploy usually means config, route, or view caches need to be rebuilt.
- If you still see old storage URLs, search for `asset('storage/...')` and replace them with model accessors or the brand-logo route.

## Contributing

1. Keep controllers thin and move reusable logic into services or model accessors.
2. Run `vendor/bin/pint` before opening a pull request.
3. Add tests for public pages, uploads, and deployment-sensitive behavior.
4. Update this README whenever the route map, role matrix, or deployment defaults change.

## License

MIT