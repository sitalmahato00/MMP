# MMP cPanel Deployment Checklist

**Domain:** mmp.sital00.com.np  
**Date:** June 5, 2026

---

## 📋 Pre-Upload Checklist (Local Machine)

### Build & Prepare

- [ ] **Build Frontend Assets**
  ```bash
  npm install
  npm run build
  # Creates: public/build/
  ```

- [ ] **Verify Laravel Version**
  ```bash
  php artisan --version  # Should be Laravel 11+
  ```

- [ ] **Create Production .env**
  ```bash
  cp .env.production .env
  # Edit .env with production database credentials
  ```

- [ ] **Check Database Structure**
  ```bash
  php artisan migrate --dry-run
  ```

- [ ] **Verify All Routes**
  ```bash
  php artisan route:list | grep api
  ```

### Files to Upload

- [x] **Local Build Ready**
  - public/build/ (compiled assets)
  - vendor/ (Composer dependencies)
  - app/ (Application code)
  - routes/ (API routes)
  - config/ (Configuration files)
  - storage/ (User uploads - if any)

- [x] **Files to EXCLUDE**
  - `.git/` (git repository)
  - `node_modules/` (npm dependencies)
  - `.env` (local environment)
  - `*.log` (log files)
  - `.DS_Store` (macOS)
  - `storage/logs/*` (log files)

---

## 🚀 Upload to cPanel (Via FTP/SFTP)

### Step 1: Create Directory Structure

```
FTP Commands:
mkdir /home/username/laravel_app
mkdir /home/username/backups
```

### Step 2: Upload Files

**Using FTP Client (FileZilla, WinSCP, etc.):**

1. **Upload Laravel Files**
   ```
   Local: d:\DIT-2080 mmp\MMP\MMP\
   Remote: /home/username/laravel_app/
   
   Include everything EXCEPT:
   - .git/
   - node_modules/
   - .env (will upload separately)
   - storage/logs/*
   - storage/cache/*
   ```

2. **Upload Public Files**
   ```
   Local: d:\DIT-2080 mmp\MMP\MMP\public\
   Remote: /home/username/public_html/
   
   Include:
   - index.php
   - .htaccess
   - build/ (compiled assets)
   - manifest.json
   - sw.js
   - offline.html
   - robots.txt
   ```

3. **Upload .env**
   ```
   Local: d:\DIT-2080 mmp\MMP\MMP\.env.production
   Remote: /home/username/laravel_app/.env
   
   Edit to add:
   - Database credentials
   - Mail credentials
   - APP_KEY (generate on server)
   ```

---

## 🔧 Setup on cPanel Server

### Via SSH Terminal

```bash
#!/bin/bash
# Quick Setup Script - Run on cPanel server

cd ~/laravel_app

# 1. Generate Application Key
echo "Generating application key..."
php artisan key:generate --force

# 2. Create database (or verify it exists)
echo "Database must be created via cPanel manually first"

# 3. Run Migrations
echo "Running migrations..."
php artisan migrate --force --verbose

# 4. Create Storage Symlink
echo "Creating storage symlink..."
php artisan storage:link

# 5. Clear & Cache Configuration
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Set Permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod 644 .env

# 7. Verify Installation
echo "Verifying installation..."
php artisan config:show APP_URL
php artisan config:show DB_DATABASE

echo "✅ Setup complete!"
```

**Or Run Commands Individually:**

```bash
cd ~/laravel_app

# Generate key
php artisan key:generate --force

# Run migrations
php artisan migrate --force --verbose

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod 644 .env
```

---

## ✅ Post-Deployment Verification

### Test API Endpoints

```bash
# Test 1: Homepage
curl https://mmp.sital00.com.np/

# Test 2: API Homepage
curl https://mmp.sital00.com.np/api/v1/public/homepage

# Test 3: Login
curl -X POST https://mmp.sital00.com.np/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "student1@mmp.edu.np",
    "password": "password"
  }'

# Test 4: Protected Endpoint (with token from login)
curl -X GET https://mmp.sital00.com.np/api/v1/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Check Server Status

```bash
# Check error logs
tail -f ~/laravel_app/storage/logs/laravel.log

# Check disk usage
df -h

# Check PHP version
php -v  # Should be 8.2+

# Check MySQL version
mysql --version  # Should be 5.7+

# Verify SSL certificate
openssl s_client -connect mmp.sital00.com.np:443
```

---

## 🚨 Troubleshooting

### 500 Error

```bash
# Check permissions
ls -la ~/laravel_app/storage/
chmod -R 755 ~/laravel_app/storage
chmod -R 755 ~/laravel_app/bootstrap/cache

# Check .env file
cat ~/laravel_app/.env | head -20

# Check error log
tail -50 ~/laravel_app/storage/logs/laravel.log
```

### Database Connection Error

```bash
# Verify connection
mysql -h localhost -u mmp_db_user -p mmp_production -e "SELECT VERSION();"

# Check .env database settings
cat ~/laravel_app/.env | grep DB_
```

### Assets Not Loading

```bash
# Verify build directory
ls -la ~/public_html/build/

# Verify manifest.json
cat ~/public_html/manifest.json

# Regenerate if needed
cd ~/laravel_app
npm install
npm run build
# Copy to public_html/build/
```

---

## 📱 Android App Configuration

Once deployed, update Android app with:

```kotlin
// build.gradle (release build type)
buildConfigField("String", "API_BASE_URL", 
    "\"https://mmp.sital00.com.np/api\"")
```

**Test Credentials:**
- Email: student1@mmp.edu.np
- Password: password

---

## 🔄 After Deployment

### Day 1 Checks
- [ ] Website loads without errors
- [ ] API endpoints responding
- [ ] Login works correctly
- [ ] Token generation working
- [ ] Check error logs for warnings

### Week 1 Tasks
- [ ] Test all user roles (student, teacher, parent, etc.)
- [ ] Verify mobile app connection
- [ ] Monitor error logs daily
- [ ] Set up automated backups

### Ongoing Maintenance
- [ ] Weekly backups
- [ ] Monthly log reviews
- [ ] Quarterly SSL certificate renewal
- [ ] Keep Laravel/PHP updated

---

## 📞 Important Information

| Item | Value |
|------|-------|
| Domain | mmp.sital00.com.np |
| API URL | https://mmp.sital00.com.np/api |
| Database | mmp_production |
| DB User | mmp_db_user |
| Laravel Path | ~/laravel_app/ |
| Public Path | ~/public_html/ |
| Error Logs | ~/laravel_app/storage/logs/laravel.log |
| Backups | ~/backups/ |

---

## 🎯 Rollback Plan

If something goes wrong:

```bash
# Restore from backup
cd ~/
mysqldump -h localhost -u mmp_db_user -p mmp_production > backups/error_$(date +%s).sql
mysql -h localhost -u mmp_db_user -p mmp_production < backups/mmp_YYYYMMDD.sql

# Restore files from FTP backup
# Or clone fresh from GitHub
cd ~
git clone https://github.com/sitalmahato00/MMP.git mmp_restore
```

---

**Status:** Ready to deploy to cPanel  
**Deployment Date:** June 5, 2026  
**Next Step:** Contact hosting provider and follow CPANEL_DEPLOYMENT_GUIDE.md
