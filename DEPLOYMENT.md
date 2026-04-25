# Production Deployment Checklist

## Pre-Deployment Cleanup ✅
- [x] Removed development documentation files
- [x] Removed test scripts and mockups
- [x] Removed development database (database.sqlite)
- [x] Cleared all Laravel caches
- [x] Removed .env.testing file

## Production Server Setup

### 1. Environment Configuration
```bash
# Copy .env.example to .env
cp .env.example .env

# Update these values in .env:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database credentials
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### 2. Install Dependencies
```bash
# Install PHP dependencies (production only)
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm install

# Build assets for production
npm run build
```

### 3. Application Setup
```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 4. File Permissions
```bash
# Set correct permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. PWA Configuration
- Ensure HTTPS is enabled (required for PWA)
- Verify manifest.json is accessible at: https://yourdomain.com/manifest.json
- Verify service worker is accessible at: https://yourdomain.com/sw.js
- Test PWA installation on mobile and desktop browsers

### 6. Security Checklist
- [ ] APP_DEBUG=false in .env
- [ ] Strong APP_KEY generated
- [ ] Database credentials secured
- [ ] HTTPS/SSL certificate installed
- [ ] File permissions set correctly
- [ ] .env file not accessible via web
- [ ] Remove any test/demo accounts

### 7. Testing on Production
- [ ] Test login with 2FA (email OTP)
- [ ] Test all user roles (Admin, HOD, Teacher, Student, Parent, Alumni)
- [ ] Test PWA installation (requires HTTPS)
- [ ] Test offline functionality
- [ ] Test file uploads
- [ ] Test email notifications
- [ ] Test database connections
- [ ] Check error logs

### 8. Performance Optimization
```bash
# Enable OPcache in php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Queue workers (if using queues)
php artisan queue:work --daemon

# Schedule cron job
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Post-Deployment

### Monitor These:
- Application logs: `storage/logs/laravel.log`
- Web server error logs
- Database performance
- Disk space usage
- SSL certificate expiry

### Backup Strategy:
- Database: Daily automated backups
- Files: Weekly backups of storage/app
- .env file: Secure backup

## Rollback Plan
If issues occur:
1. Revert to previous git commit
2. Restore database backup
3. Clear all caches
4. Restart web server

## Support Contacts
- Technical Support: info@mmp.edu.np
- Phone: +977 21 590696

---

**Deployment Date:** _____________
**Deployed By:** _____________
**Version:** _____________
