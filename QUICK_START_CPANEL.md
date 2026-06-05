# 🚀 MMP Project - Quick Start for cPanel Deployment

**Domain:** mmp.sital00.com.np  
**Status:** Ready for deployment  
**Last Updated:** June 5, 2026

---

## 📋 Quick Summary

Your MMP project is fully configured for deployment to cPanel with the domain `mmp.sital00.com.np`. Here's what you need to do:

---

## ⚡ 5-Minute Quick Setup

### Step 1: Build Assets Locally (2 min)
```bash
cd "d:\DIT-2080 mmp\MMP\MMP"
npm install
npm run build
```
✅ Creates `public/build/` folder with compiled assets

### Step 2: Prepare .env for Production (1 min)
```bash
# Copy production template
copy .env.production .env

# Edit .env with your cPanel database credentials
# - DB_HOST: localhost
# - DB_USERNAME: mmp_db_user (from cPanel)
# - DB_PASSWORD: your_strong_password
# - APP_KEY: will be generated on server
```

### Step 3: Upload to cPanel via FTP (2 min)
Using FileZilla, WinSCP, or any FTP client:
```
1. Connect to your cPanel server
2. Upload: d:\DIT-2080 mmp\MMP\MMP → /home/username/laravel_app/
3. Upload: d:\DIT-2080 mmp\MMP\MMP\public\ → /home/username/public_html/
4. Upload: .env file → /home/username/laravel_app/.env
```

### Step 4: Run Setup on cPanel Terminal (0 min automated)
SSH into your cPanel server and run:
```bash
cd ~/laravel_app

# Run all setup commands in one go
php artisan key:generate --force && \
php artisan migrate --force && \
php artisan storage:link && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
chmod -R 755 storage bootstrap/cache && \
chmod 644 .env

echo "✅ Deployment complete!"
```

### Step 5: Test the Deployment (1 min)
```bash
# Test API
curl https://mmp.sital00.com.np/api/v1/public/homepage

# Test Login
curl -X POST https://mmp.sital00.com.np/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student1@mmp.edu.np","password":"password"}'
```

---

## 📱 Update Android App

Once deployed, update your Android project:

**File: `app/build.gradle`**
```gradle
release {
    buildConfigField("String", "API_BASE_URL", 
        "\"https://mmp.sital00.com.np/api\"")
}
```

**Test Credentials:**
```
Email:    student1@mmp.edu.np
Password: password
```

---

## 📚 Complete Documentation

1. **[CPANEL_DEPLOYMENT_GUIDE.md](CPANEL_DEPLOYMENT_GUIDE.md)** - Step-by-step deployment guide
2. **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Pre/post deployment checklist
3. **[ANDROID_APP_CONFIG.md](ANDROID_APP_CONFIG.md)** - Complete Android implementation guide
4. **[API_TEST_REPORT.md](API_TEST_REPORT.md)** - API testing results and documentation

---

## 🔑 Production Credentials

### cPanel Access
```
Host: mmp.sital00.com.np
Username: Check hosting email
Password: Check hosting email
Database: mmp_production (create in cPanel)
DB User: mmp_db_user (create in cPanel)
```

### Test User
```
Email:    student1@mmp.edu.np
Password: password
Role:     student
```

### API
```
Base URL: https://mmp.sital00.com.np/api
Status:   ✅ Ready
Routes:   54+ endpoints (all roles)
```

---

## ✅ Pre-Deployment Checklist

Before uploading to cPanel:

- [ ] `npm run build` executed locally
- [ ] `public/build/` folder created
- [ ] `.env.production` file prepared with credentials
- [ ] `.env` file does NOT contain sensitive data (use .env.production)
- [ ] All 6 API controllers in `app/Http/Controllers/Api/`
- [ ] Routes defined in `routes/api.php`
- [ ] Database structure reviewed

---

## 🎯 Deployment Roadmap

### Phase 1: Prepare (Today)
- [x] Code complete and tested
- [x] API endpoints verified (54+)
- [x] Android configuration ready
- [x] Documentation prepared
- [ ] **TODO:** Build frontend assets locally

### Phase 2: Upload (Tomorrow)
- [ ] Create cPanel database
- [ ] Upload files via FTP
- [ ] Configure SSL certificate (Let's Encrypt)
- [ ] Upload `.env` file

### Phase 3: Setup (Same day)
- [ ] SSH into cPanel
- [ ] Run migration commands
- [ ] Generate app key
- [ ] Cache configuration
- [ ] Set file permissions

### Phase 4: Test & Deploy
- [ ] Test API endpoints
- [ ] Test login with credentials
- [ ] Update Android app with domain
- [ ] Build Android APK for production
- [ ] Monitor logs for 24 hours

---

## 🔧 Common Commands

### Local Development
```bash
# Start server
php artisan serve

# Test login
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student1@mmp.edu.np","password":"password"}'
```

### cPanel Deployment
```bash
cd ~/laravel_app

# View logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear

# Run migrations
php artisan migrate --force

# Check routes
php artisan route:list
```

---

## 🐛 Troubleshooting

### 500 Error on Website
```bash
# Check permissions
chmod -R 755 ~/laravel_app/storage
chmod -R 755 ~/laravel_app/bootstrap/cache

# Check logs
tail -50 ~/laravel_app/storage/logs/laravel.log
```

### Database Connection Error
```bash
# Test connection
mysql -h localhost -u mmp_db_user -p mmp_production

# Check .env
cat ~/laravel_app/.env | grep DB_
```

### Assets Not Loading
```bash
# Verify build folder
ls -la ~/public_html/build/

# Rebuild if needed
cd ~/laravel_app
npm install && npm run build
```

---

## 📞 Support Resources

| Issue | Link |
|-------|------|
| Detailed Deployment | [CPANEL_DEPLOYMENT_GUIDE.md](CPANEL_DEPLOYMENT_GUIDE.md) |
| Pre-Upload Checklist | [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) |
| Android Integration | [ANDROID_APP_CONFIG.md](ANDROID_APP_CONFIG.md) |
| API Documentation | [ANDROID_INTEGRATION_GUIDE.md](docs/ANDROID_INTEGRATION_GUIDE.md) |
| Test Results | [API_TEST_REPORT.md](API_TEST_REPORT.md) |

---

## 🚀 Next Immediate Actions

1. **Build Assets**
   ```bash
   npm run build
   ```

2. **Prepare Environment**
   ```bash
   copy .env.production .env
   # Edit with database credentials
   ```

3. **Upload to cPanel**
   - Use FTP client
   - Upload laravel_app/ folder
   - Upload public/ contents to public_html/
   - Upload .env file

4. **Run Setup Commands**
   ```bash
   cd ~/laravel_app
   php artisan migrate --force
   ```

5. **Update Android App**
   - Change API base URL to `https://mmp.sital00.com.np/api`
   - Build release APK

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| Total API Endpoints | 54+ |
| Supported Roles | 6 (student, teacher, parent, hod, alumni, admin) |
| Database Tables | 40+ |
| Migrations | Ready |
| Frontend Assets | Built & optimized |
| SSL Required | Yes |
| Database Required | Yes (MySQL 5.7+) |
| PHP Version Required | 8.2+ |

---

## 💡 Key Information

**Production Domain:** mmp.sital00.com.np  
**API Endpoint:** https://mmp.sital00.com.np/api  
**Database:** mmp_production  
**Laravel Version:** 11  
**Status:** ✅ Ready for deployment

---

**Last Updated:** June 5, 2026  
**Repository:** https://github.com/sitalmahato00/MMP  
**Commit:** 4170ff94
