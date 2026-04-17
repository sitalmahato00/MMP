# MMP College Management System

[![Laravel](https://img.shields.io/badge/Laravel-12-brightgreen.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-blue.svg)](https://tailwindcss.com)

MMP College Management System is a role-based Laravel application for the college website, administration, academics, and public content delivery. The current codebase is prepared for production deployment with MySQL, Redis, CDN-aware public files, private file streaming, route throttling, and short-lived dashboard caching.

## Overview

The system is split into a public portal and several authenticated portals:

- Public portal for homepage content, departments, facilities, leadership, gallery, downloads, question bank, apply form, and result checking.
- Admin CMS for banners, media, pages, departments, executives, facilities, downloads, and site settings.
- Academic portals for students, teachers, HODs, parents, and alumni.
- Automated student-to-alumni promotion when academic sessions are closed.
- Object-storage friendly media handling with CDN-backed public URLs and protected private downloads.

## Feature Highlights

- Public content is assembled through `PublicDataService` and cached for short periods to reduce repeated database work.
- Dashboards for students, teachers, HODs, parents, and alumni cache notices, assignments, timetable slots, and department lookups.
- Named rate limiters protect login, application, result checking, and public API traffic.
- Media, banner, staff, executive, department, and facility URLs are generated through model accessors instead of hardcoded `asset('storage/...')` links.
- Public downloads use the `public` disk and CDN URL, while private downloads are streamed through the admin controller.
- Department forms use the real schema fields: `photo` and `syllabus`.

## Technology Stack

| Area | Stack |
| --- | --- |
| Backend | Laravel 12, PHP 8.2+ |
| Authentication | Laravel Sanctum, spatie/laravel-permission |
| Frontend | Blade, Alpine.js, Tailwind CSS 4, Vite |
| Storage | Local storage for development, S3-compatible object storage for production |
| Cache / Session / Queue | Redis in production |
| Database | MySQL in production |
| Testing | PHPUnit |
| Tooling | Laravel Pint, Laravel Pail, Composer scripts, Vite build pipeline |

## Repository Layout

```text
app/
config/
database/
docs/
public/
resources/
routes/
storage/
tests/
```

Key files for deployment and runtime behavior:

- [.env.example](.env.example)
- [config/filesystems.php](config/filesystems.php)
- [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php)
- [routes/web.php](routes/web.php)
- [routes/api.php](routes/api.php)
- [app/Http/Controllers/Admin/DownloadController.php](app/Http/Controllers/Admin/DownloadController.php)
- [app/Models/Download.php](app/Models/Download.php)
- [docs/ctevt-result-notices-integration.md](docs/ctevt-result-notices-integration.md)

## Local Development

For a fresh local bootstrap, the Composer setup script installs PHP and Node dependencies, copies the environment file, generates an application key, runs migrations, and builds the frontend bundle:

```bash
composer run setup
```

If you want the steps manually:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

Notes for local development:

- `composer run setup` is the fastest way to bootstrap the project from scratch.
- `npm run dev` starts the Vite development server.
- If you use local public storage during development, run `php artisan storage:link` so public files are reachable.
- Seeders are available in `database/seeders` for demo or test data.

## Environment Configuration

The repository ships with production-oriented defaults in [.env.example](.env.example). Update the following values before deploying:

| Category | Important variables | Purpose |
| --- | --- | --- |
| Application | `APP_ENV`, `APP_DEBUG`, `APP_URL` | Production should use `production`, `false`, and the real public domain |
| Database | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | MySQL credentials and database name |
| Cache / Session / Queue | `CACHE_STORE`, `SESSION_DRIVER`, `SESSION_CONNECTION`, `SESSION_STORE`, `QUEUE_CONNECTION`, `REDIS_*` | Redis-backed runtime services |
| Storage | `FILESYSTEM_DISK`, `PUBLIC_FILESYSTEM_DRIVER`, `PUBLIC_FILESYSTEM_URL`, `PRIVATE_FILESYSTEM_DRIVER`, `AWS_*` | Object storage and CDN integration |
| Mail | `MAIL_*` | Outbound mail delivery |
| External feeds | `CTEVT_*` | Public notice and result integrations |

Important storage behavior:

- Public images and documents should resolve through CDN-aware URLs from the `public` disk.
- Private files should stay on the `private` disk and be served through controller responses.
- Do not add new `asset('storage/...')` references in views or models.

## Production Deployment

Use the following checklist when deploying to a production host:

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

Deployment checklist:

1. Provision MySQL, Redis, and an S3-compatible object storage bucket.
2. Point `APP_URL` to the public site domain.
3. Set `PUBLIC_FILESYSTEM_URL` to the CDN origin or public bucket URL you want browsers to use.
4. Fill in the `AWS_*` credentials for the public and private storage disks.
5. Ensure the queue worker runs continuously under Supervisor, systemd, Forge, or your platform equivalent.
6. Add a cron entry for the Laravel scheduler if you use scheduled tasks.
7. Run a backup, then test a restore on staging before going live.
8. Confirm error monitoring or centralized logging is active before the first production release.

Recommended runtime services:

- `php artisan queue:work` as a long-lived worker process.
- `php artisan schedule:run` once per minute from cron, if scheduled tasks are enabled.
- `php artisan optimize:clear` before a fresh deploy when you need to invalidate caches manually.

## Storage and CDN

The storage layer is intentionally split so public and private content behave differently:

- `public` disk: banners, staff images, department photos, facility images, public media, and public downloads.
- `private` disk: restricted download files that should not be directly exposed from storage.
- CDN URLs are generated by model accessors such as `image_url`, `avatar_url`, `file_url`, `url`, `photo_url`, `syllabus_url`, `image_urls`, `document_urls`, and `video_urls`.

Relevant runtime files:

- [config/filesystems.php](config/filesystems.php)
- [app/Models/Download.php](app/Models/Download.php)
- [app/Models/Media.php](app/Models/Media.php)
- [app/Models/Department.php](app/Models/Department.php)
- [app/Models/Facility.php](app/Models/Facility.php)

## Rate Limiting, Cache, and Sessions

The current production hardening adds named rate limiters and Redis-backed defaults:

- `login`: 5 attempts per minute per email and IP.
- `apply`: 10 attempts per hour per email and IP.
- `result-check`: 30 requests per minute per IP.
- `public-api`: 120 requests per minute per IP.

Short-lived caching is used for dashboard content and public homepage data so repeated requests do less work. This keeps the public site responsive without turning the application into a cache-only system.

## Data Flow Notes

- Student records remain the source of truth for alumni promotion.
- Academic session changes can automatically transition students to alumni when they complete the final stage.
- Department media uses `photo` and `syllabus` fields, not a legacy cover image schema.
- Public downloads and media should always use the storage accessors defined on the models.

## Testing and Verification

```bash
php artisan test
npm run build
```

Suggested smoke tests after deployment:

- Load the homepage and verify banners, leadership cards, and department content.
- Open a public download and confirm public files use the CDN URL.
- Open a private download and confirm the controller streams the file correctly.
- Submit the apply form and confirm rate limiting behaves as expected.
- Check login and result pages for correct throttling and redirect behavior.
- Confirm dashboard pages load cached notices and assignments without errors.

## Troubleshooting

- Broken public images usually mean `PUBLIC_FILESYSTEM_URL` or the `public` disk configuration is wrong.
- Missing private downloads usually mean the file is not present on the `private` disk or the stored path is stale.
- Queue jobs not processing usually means the Redis worker is not running.
- Stale content after a deploy usually means config, route, or view caches need to be cleared and rebuilt.
- If you are still seeing local storage URLs, search for `asset('storage/...')` and replace them with model accessors.

## Contributing

1. Keep controllers thin and put repeated logic in services or model accessors.
2. Run `vendor/bin/pint` before opening a pull request.
3. Add tests for new public pages, uploads, or deployment-sensitive logic.
4. Update this README whenever deployment defaults or environment variables change.