# MMP cPanel Deployment Guide

**Domain:** mmp.sital00.com.np  
**API Base URL:** https://mmp.sital00.com.np/api  
**Date:** June 5, 2026

---

## 📋 Pre-Deployment Checklist

- [ ] Domain DNS configured pointing to cPanel server
- [ ] SSL certificate obtained (Let's Encrypt via cPanel)
- [ ] Database created on cPanel
- [ ] FTP/SFTP credentials ready
- [ ] Local build assets generated (`npm run build`)
- [ ] Production `.env` file prepared
- [ ] Backups of current files (if updating)

---

## 🚀 Step-by-Step Deployment

### Step 1: Build Frontend Assets (Local Machine)

```bash
cd "d:\DIT-2080 mmp\MMP\MMP"
npm install
npm run build
```

This creates the `public/build/` folder with compiled assets.

---

### Step 2: Prepare Production Environment File

**Create `.env` for production:**

```bash
APP_NAME=MMP
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE  # Run: php artisan key:generate --force
APP_DEBUG=false
APP_URL=https://mmp.sital00.com.np

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mmp_production
DB_USERNAME=mmp_db_user
DB_PASSWORD=your_strong_password_here

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=cookie
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true

# Queue & Jobs
QUEUE_CONNECTION=sync

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mmp.sital00.com.np
MAIL_FROM_NAME="MMP Portal"

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=mmp.sital00.com.np
SANCTUM_GUARD=web

# Logging
LOG_CHANNEL=single
LOG_LEVEL=warning

# Optional: Sentry for error tracking
# SENTRY_LARAVEL_DSN=your_sentry_dsn_here
```

---

### Step 3: Upload Files to cPanel via FTP/SFTP

**Directory Structure on cPanel:**
```
/home/username/
├── public_html/
│   ├── index.php (from Laravel's public/)
│   ├── .htaccess
│   ├── build/ (from npm run build)
│   ├── manifest.json
│   ├── sw.js
│   ├── offline.html
│   └── robots.txt
│
└── laravel_app/ (outside public_html)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── artisan
    ├── composer.json
    └── .env
```

**Upload Process:**

1. **Create directories:**
   - Create `laravel_app` folder in `/home/username/`
   - Clear or backup existing `public_html/` contents

2. **Upload Laravel files:**
   ```
   - Upload everything from local project to /home/username/laravel_app/
   - Exclude: .git, .env, node_modules, storage/logs/*, storage/cache/*
   ```

3. **Upload public files:**
   ```
   - Copy contents of local public/ folder to /home/username/public_html/
   - Include: build/, manifest.json, sw.js, offline.html, index.php, .htaccess
   ```

---

### Step 4: Configure Public Index File

**Edit: `/home/username/public_html/index.php`**

Update the paths to point to the laravel_app directory:

```php
<?php

use Illuminate\Conventional\Console\MigrationRepositoryTable;

define('LARAVEL_START', microtime(true));

// Edit these two lines to point to laravel_app
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
```

---

### Step 5: Setup on cPanel Terminal

SSH into cPanel server or use cPanel Terminal:

```bash
# Navigate to Laravel app
cd ~/laravel_app

# Install composer dependencies (production mode)
composer install --optimize-autoloader --no-dev

# Generate encryption key
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Seed database (optional, if seeders exist)
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod 644 .env
```

---

### Step 6: Configure SSL Certificate

Via cPanel:
1. Go to **SSL/TLS** section
2. Select **Auto-SSL** or **Let's Encrypt**
3. Add domain: `mmp.sital00.com.np`
4. Let it auto-renew

---

### Step 7: Configure .htaccess (if needed)

**File: `/home/username/public_html/.htaccess`**

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

---

### Step 8: Verify Deployment

Test the following:

```bash
# Test homepage
curl https://mmp.sital00.com.np/

# Test API homepage
curl https://mmp.sital00.com.np/api/v1/public/homepage

# Test login
curl -X POST https://mmp.sital00.com.np/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student1@mmp.edu.np","password":"password"}'

# Check error logs
tail -f ~/laravel_app/storage/logs/laravel.log
```

---

## 🔧 Troubleshooting

### 500 Internal Server Error
```bash
# Check file permissions
ls -la ~/laravel_app/storage
chmod -R 755 ~/laravel_app/storage
chmod -R 755 ~/laravel_app/bootstrap/cache

# Check error logs
cat ~/laravel_app/storage/logs/laravel.log

# Verify PHP version
php -v  # Should be 8.2+
```

### Database Connection Error
```bash
# Verify credentials
mysql -h localhost -u mmp_db_user -p mmp_production

# Check .env file
cat ~/laravel_app/.env | grep DB_
```

### Assets Not Loading
```bash
# Verify build folder exists
ls -la ~/public_html/build/

# Regenerate manifest
cd ~/laravel_app
npm install
npm run build
# Then copy build/ to public_html/
```

### Migration Issues
```bash
# Run migrations with verbose output
php artisan migrate --force --verbose

# Rollback if needed
php artisan migrate:rollback --force
```

---

## 📱 Update Android App Configuration

Once deployed to cPanel, update your Android app:

**File: `app/build.gradle`**

```gradle
buildTypes {
    debug {
        buildConfigField("String", "API_BASE_URL", 
            "\"http://10.0.2.2:8000/api\"")  // Local development
    }
    
    release {
        buildConfigField("String", "API_BASE_URL", 
            "\"https://mmp.sital00.com.np/api\"")  // Production
        minifyEnabled true
        proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
    }
}
```

---

## 🔒 Security Checklist

- [ ] APP_DEBUG set to `false`
- [ ] APP_ENV set to `production`
- [ ] Strong database password set
- [ ] SSL certificate installed and working
- [ ] .env file has 644 permissions (not readable by others)
- [ ] Storage folder not publicly accessible
- [ ] Error logs not exposed in browser
- [ ] CORS configured for specific domains
- [ ] Rate limiting enabled on auth endpoints
- [ ] Regular backups scheduled

---

## 📊 Monitoring & Maintenance

### Daily Checks
```bash
# Check error logs
tail -f ~/laravel_app/storage/logs/laravel.log

# Check disk space
df -h

# Check database size
mysql -h localhost -u mmp_db_user -p -e "SELECT table_schema, ROUND(SUM(data_length+index_length)/1024/1024,2) AS size_mb FROM information_schema.tables GROUP BY table_schema;"
```

### Weekly Tasks
```bash
# Clear old logs
find ~/laravel_app/storage/logs -name "*.log" -mtime +30 -delete

# Backup database
mysqldump -h localhost -u mmp_db_user -p mmp_production > ~/backups/mmp_$(date +%Y%m%d).sql
```

### Monthly Tasks
- [ ] Review error logs for issues
- [ ] Check for Laravel/PHP updates
- [ ] Verify SSL certificate validity
- [ ] Test disaster recovery procedures

---

## 🚀 Deployment Commands Summary

```bash
# One-liner for quick setup
cd ~/laravel_app && \
composer install --optimize-autoloader --no-dev && \
php artisan key:generate --force && \
php artisan migrate --force && \
php artisan storage:link && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
chmod -R 755 storage bootstrap/cache && \
chmod 644 .env
```

---

## 📞 Support

**API Status:** https://mmp.sital00.com.np/api/v1/public/homepage  
**Error Logs:** `~/laravel_app/storage/logs/laravel.log`  
**Domain:** mmp.sital00.com.np  
**Test User:** student1@mmp.edu.np / password

---

**Deployment Date:** June 5, 2026  
**Status:** Ready for deployment
