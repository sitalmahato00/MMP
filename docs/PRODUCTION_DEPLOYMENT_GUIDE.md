# MMP Mobile App - Production Deployment Guide

**Date:** June 5, 2026  
**Version:** 1.0

---

## Table of Contents

1. [Pre-Deployment Setup](#pre-deployment-setup)
2. [Database Preparation](#database-preparation)
3. [API Configuration](#api-configuration)
4. [Environment Setup](#environment-setup)
5. [Security Hardening](#security-hardening)
6. [Deployment Steps](#deployment-steps)
7. [Post-Deployment Verification](#post-deployment-verification)
8. [Monitoring & Maintenance](#monitoring--maintenance)
9. [Rollback Procedures](#rollback-procedures)

---

## Pre-Deployment Setup

### Prerequisites

- [ ] Laravel 11 application fully tested
- [ ] All API endpoints working in staging
- [ ] Database migrations ready
- [ ] SSL/TLS certificates obtained
- [ ] Production domain configured
- [ ] Email service configured
- [ ] FCM credentials ready (for push notifications)
- [ ] Backup system in place
- [ ] Monitoring tools setup

### Environment Details

```
Environment: Production
Domain: api.mmp.edu.np
Server: cPanel (shared hosting) or dedicated VPS
Database: MySQL 8.0+
PHP: 8.2+
SSL: Let's Encrypt (free)
```

---

## Database Preparation

### 1. Create Production Database

```bash
# Access MySQL via cPanel or terminal
mysql -u root -p

# Create database and user
CREATE DATABASE mmp_production;
CREATE USER 'mmp_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON mmp_production.* TO 'mmp_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Database Backups

**Automatic Backups (cPanel):**
- Enable daily automated backups
- Store backups in separate location
- Test restoration monthly

**Manual Backup Command:**
```bash
mysqldump -u mmp_user -p mmp_production > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 3. Run Migrations

```bash
cd /home/username/laravel_app
php artisan migrate --force
```

### 4. Seed Initial Data (Optional)

```bash
php artisan db:seed --force
```

---

## API Configuration

### 1. Laravel Configuration

**File:** `.env` (Production)

```env
# Application
APP_NAME=MMP
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://api.mmp.edu.np

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mmp_production
DB_USERNAME=mmp_user
DB_PASSWORD=your_strong_password

# Cache
CACHE_DRIVER=redis
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail (SendGrid, Gmail, or local)
MAIL_MAILER=sendgrid
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mmp.edu.np
MAIL_FROM_NAME="MMP Portal"

# Session
SESSION_DRIVER=cookie
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true

# Sanctum (API Token Auth)
SANCTUM_STATEFUL_DOMAINS=api.mmp.edu.np
SANCTUM_GUARD=web

# Filesystem
FILESYSTEM_DISK=public
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=

# Firebase (FCM)
FIREBASE_PROJECT_ID=
FIREBASE_PRIVATE_KEY=
FIREBASE_CLIENT_EMAIL=

# Logging
LOG_CHANNEL=single
LOG_LEVEL=warning

# Sentry (Error Tracking)
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
```

### 2. Generate Application Key

```bash
php artisan key:generate --force
```

### 3. Configure CORS

**File:** `config/cors.php`

```php
'allowed_origins' => [
    'https://api.mmp.edu.np',
],

'allow_headers' => [
    'Accept',
    'Content-Type',
    'Authorization',
    'X-Requested-With',
],

'expose_headers' => [
    'X-RateLimit-Limit',
    'X-RateLimit-Remaining',
    'X-RateLimit-Reset',
],
```

### 4. Rate Limiting Configuration

**File:** `config/rate_limit.php` or kernel

```php
// In routes or middleware
Route::middleware('throttle:60,1')->group(function () {
    // API routes
});
```

---

## Environment Setup

### 1. File & Directory Permissions

```bash
# Set correct permissions
chmod -R 755 /home/username/laravel_app
chmod -R 775 /home/username/laravel_app/storage
chmod -R 775 /home/username/laravel_app/bootstrap/cache

# Set ownership
chown -R username:username /home/username/laravel_app
```

### 2. Storage Symlink

```bash
php artisan storage:link
```

### 3. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 4. Queue Setup (if using Redis)

```bash
# Start queue worker (add to supervisor/cron)
php artisan queue:work redis --tries=3 --timeout=90
```

---

## Security Hardening

### 1. HTTPS/SSL Setup

**Get SSL Certificate (Let's Encrypt via cPanel):**

1. Go to AutoSSL in cPanel
2. Install certificate for api.mmp.edu.np
3. Enable HSTS headers

**Force HTTPS:**

```php
// In AppServiceProvider.php
if ($this->app->environment('production')) {
    \Illuminate\Support\Facades\URL::forceScheme('https');
}
```

### 2. API Security Headers

**File:** `app/Http/Middleware/SecurityHeaders.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'no-referrer');
        $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
```

**Register in Kernel:**

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\SecurityHeaders::class,
];
```

### 3. Database Encryption

```env
DB_ENCRYPT=true
DB_CIPHER=AES-256-CBC
```

### 4. Hide PHP Version

**File:** `.htaccess` or Apache config

```apache
# Hide PHP version
Header always unset "X-Powered-By"
Header unset "X-Powered-By"
```

### 5. Disable Directory Listing

```apache
Options -Indexes
```

### 6. API Key Protection

- Never commit `.env` to version control
- Use separate `.env` for production
- Rotate keys periodically
- Audit access logs

---

## Deployment Steps

### Step 1: Code Deployment

```bash
# SSH into production server
ssh username@mmp.edu.np

# Navigate to app directory
cd /home/username/laravel_app

# Pull latest code from git
git pull origin main

# Or upload via FTP (for cPanel without git)
# Use FileZilla or similar tool
```

### Step 2: Dependencies Installation

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev --no-interaction --no-progress

# Clear any cached files
composer dump-autoload
```

### Step 3: Environment Configuration

```bash
# Copy and configure .env
cp .env.production .env

# Verify all values in .env
cat .env
```

### Step 4: Database Setup

```bash
# Run migrations
php artisan migrate --force

# Run seeders (if needed)
php artisan db:seed --force --class=ProductionSeeder
```

### Step 5: Cache Optimization

```bash
# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Regenerate caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Create Storage Directories

```bash
php artisan storage:link
mkdir -p storage/logs
mkdir -p storage/tmp
chmod -R 775 storage bootstrap/cache
```

### Step 7: Set Permissions

```bash
chmod 755 /home/username/laravel_app
chmod -R 775 /home/username/laravel_app/storage
chmod -R 775 /home/username/laravel_app/bootstrap/cache
```

### Step 8: Configure Web Server

**For Apache (cPanel):**

Create/Update `.htaccess` in public directory:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Step 9: Configure Supervisor (Queue Worker)

**File:** `/etc/supervisor/conf.d/laravel-worker.conf`

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/username/laravel_app/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/home/username/laravel_app/storage/logs/worker.log
```

**Restart Supervisor:**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## Post-Deployment Verification

### 1. Health Check Endpoints

```bash
# Test API is responding
curl -X GET https://api.mmp.edu.np/api/v1/public/site-settings

# Check token generation
curl -X POST https://api.mmp.edu.np/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Check student endpoint
curl -X GET https://api.mmp.edu.np/api/v1/student/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 2. Database Verification

```bash
# Verify database connection
php artisan tinker
>>> \App\Models\User::count()
>>> exit
```

### 3. Log Monitoring

```bash
# Check for errors
tail -f /home/username/laravel_app/storage/logs/laravel.log

# Look for deployment issues
grep -i error /home/username/laravel_app/storage/logs/laravel.log
```

### 4. Performance Testing

```bash
# Test with Apache Bench (if available)
ab -n 100 -c 10 https://api.mmp.edu.np/api/v1/public/site-settings

# Or use hey tool
hey -n 100 -c 10 https://api.mmp.edu.np/api/v1/public/site-settings
```

### 5. SSL Certificate Verification

```bash
# Check SSL certificate
openssl s_client -connect api.mmp.edu.np:443 -showcerts

# Or use online tool
curl -I https://api.mmp.edu.np/api/v1/public/site-settings
```

### 6. Database Backup Verification

```bash
# Create test restore
mysqldump -u mmp_user -p mmp_production > test_backup.sql

# Verify backup size
ls -lh test_backup.sql
```

---

## Monitoring & Maintenance

### 1. Application Monitoring

**Setup with Sentry (Free tier available):**

```bash
composer require sentry/sentry-laravel
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

**File:** `.env`
```env
SENTRY_LARAVEL_DSN=https://xxx@xxx.ingest.sentry.io/xxx
```

### 2. Uptime Monitoring

Use services like:
- Uptime Robot (free)
- Pingdom
- Datadog
- New Relic

### 3. Log Monitoring

**Centralized Logging:**

```env
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

### 4. Performance Monitoring

```bash
# Monitor queue jobs
php artisan queue:monitor

# Check slow queries (MySQL)
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

### 5. Regular Backups

```bash
# Add to cron (daily at 2 AM)
0 2 * * * /usr/local/bin/php /home/username/laravel_app/artisan backup:run >> /home/username/backup.log 2>&1
```

### 6. Security Updates

```bash
# Check for outdated packages
composer outdated

# Update packages safely
composer update

# Update Laravel
composer update laravel/framework
```

---

## Rollback Procedures

### Quick Rollback (Git)

```bash
# If deployment via git, revert to previous commit
cd /home/username/laravel_app
git log --oneline
git revert HEAD

# Or checkout previous version
git checkout previous_commit_hash

# Run migrations in reverse (if needed)
php artisan migrate:rollback --step=1
```

### Database Rollback

```bash
# Restore from backup
mysql -u mmp_user -p mmp_production < backup_file.sql

# Or specific table
mysql -u mmp_user -p mmp_production -e "DROP TABLE migrations; DROP TABLE users;" < backup_file.sql
```

### File Rollback (Manual)

```bash
# Restore from backup directory
cp -r /backup/laravel_app_v1/* /home/username/laravel_app/

# Clear caches
php artisan cache:clear
php artisan config:clear
```

---

## Checklist for Deployment

### Before Deployment
- [ ] All tests passing
- [ ] Code reviewed and approved
- [ ] Database migrations tested locally
- [ ] API endpoints tested
- [ ] SSL certificate ready
- [ ] Email service configured
- [ ] FCM credentials setup
- [ ] Backup created
- [ ] Rollback plan documented

### During Deployment
- [ ] Announce maintenance window
- [ ] Deploy code
- [ ] Run migrations
- [ ] Clear caches
- [ ] Verify endpoints
- [ ] Test with sample data
- [ ] Monitor logs
- [ ] Monitor performance

### After Deployment
- [ ] Verify all endpoints working
- [ ] Check error logs
- [ ] Monitor API response times
- [ ] Test mobile app connections
- [ ] Verify push notifications
- [ ] Confirm backups are running
- [ ] Document deployment notes
- [ ] Announce deployment complete

---

## Contact & Support

**Deployment Issues?**
- Check error logs: `storage/logs/laravel.log`
- Review Sentry dashboard
- Contact hosting provider support
- Review Laravel documentation

---

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Version:** _______________
