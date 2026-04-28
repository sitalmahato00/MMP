# cPanel Production Deployment Guide

## Pre-Deployment (Local Machine Only)

### 1. Build Assets Locally
**IMPORTANT:** Since cPanel doesn't support Node.js, build assets on your local machine first:

```bash
# On your local development machine
npm install
npm run build
```

This creates the `public/build/` folder with compiled assets.

### 2. Prepare Files for Upload
- Copy `.env.example` to `.env` and configure for production
- Ensure `public/build/` folder exists from step 1
- Remove development files if needed

## cPanel Deployment Steps

### 1. Upload Files to cPanel

**Directory Structure:**
```
/home/username/
├── public_html/ (web root)
│   ├── index.php (from Laravel's public folder)
│   ├── .htaccess (from Laravel's public folder)
│   ├── build/ (from npm run build)
│   ├── manifest.json
│   ├── sw.js
│   ├── offline.html
│   └── favicon.ico
├── laravel_app/ (Laravel app - outside public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── composer.json
```

**Upload Steps:**
1. Create `laravel_app` folder outside `public_html`
2. Upload Laravel files to `laravel_app/`
3. Move contents of `public/` folder to `public_html/`
4. Update `public_html/index.php`:
   ```php
   require __DIR__.'/../laravel_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
   ```

### 2. Configure Environment

Create `.env` file in `laravel_app/`:
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=cpanel_database_name
DB_USERNAME=cpanel_db_user
DB_PASSWORD=cpanel_db_password

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# AWS S3 (Optional)
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=your_aws_region
AWS_BUCKET=your_s3_bucket_name
```

### 3. Install Dependencies

Via cPanel Terminal:
```bash
cd ~/laravel_app
composer install --optimize-autoloader --no-dev
```

**If Composer not available:** Upload `vendor/` folder from local machine.

### 4. Setup Application

```bash
cd ~/laravel_app

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
chmod 644 .env
```

### 6. Test Deployment

- [ ] Visit your domain
- [ ] Test login with 2FA
- [ ] Test file uploads
- [ ] Check PWA installation (requires HTTPS)
- [ ] Verify all user roles work

## Troubleshooting

**500 Error:**
- Check file permissions
- Verify `.htaccess` in `public_html`
- Check cPanel error logs

**Database Issues:**
- Use `localhost` as DB_HOST
- Verify database credentials in cPanel

**Missing Assets:**
- Ensure `public/build/` folder uploaded
- Check `manifest.json` exists

## Support
- Technical Support: info@mmp.edu.np
- Phone: +977 21 590696