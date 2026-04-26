# CTEVT External Sync Service - cPanel Deployment Guide

## Overview
This guide walks through deploying and testing the CTEVT External Sync Service on your cPanel production server.

---

## PHASE 1: Push Code to Production

### Step 1: Commit and Push Changes
```bash
# From your local machine
git add .
git commit -m "feat: implement CTEVT external sync service with rate limiting and logging"
git push origin main
```

### Step 2: Deploy to cPanel
```bash
# SSH into your cPanel server
ssh user@your-domain.com

# Navigate to your application directory
cd /home/username/public_html

# Pull latest changes
git pull origin main

# Run migrations (creates ctevt_sync_logs table)
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
```

---

## PHASE 2: Set Up External Sync Service

### Option A: Deploy on Separate VPS (Recommended)

#### Step 1: Create External Service Directory
```bash
# SSH into your external VPS
ssh user@external-vps.com

# Create service directory
mkdir -p /var/www/ctevt-sync
cd /var/www/ctevt-sync
```

#### Step 2: Copy Sync Endpoint File
```bash
# From your local machine, copy the file to VPS
scp external-sync-service/sync-endpoint.php user@external-vps.com:/var/www/ctevt-sync/

# Or manually create it on VPS and paste content
nano /var/www/ctevt-sync/sync-endpoint.php
# Paste the entire content from external-sync-service/sync-endpoint.php
```

#### Step 3: Set Up Web Server (Apache/Nginx)
```bash
# For Apache - create .htaccess
cat > /var/www/ctevt-sync/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ sync-endpoint.php [QSA,L]
</IfModule>
EOF

# Set permissions
chmod 755 /var/www/ctevt-sync
chmod 644 /var/www/ctevt-sync/sync-endpoint.php
```

#### Step 4: Configure Environment Variables
```bash
# Create .env file for external service
cat > /var/www/ctevt-sync/.env << 'EOF'
CTEVT_SYNC_API_TOKEN=your-secret-token-here-change-this
PRODUCTION_DB_HOST=your-cpanel-db-host.com
PRODUCTION_DB_USER=your_db_user
PRODUCTION_DB_PASS=your_db_password
PRODUCTION_DB_NAME=your_db_name
EOF

# Secure the .env file
chmod 600 /var/www/ctevt-sync/.env
```

#### Step 5: Enable HTTPS (SSL Certificate)
```bash
# Use Let's Encrypt (free)
sudo certbot certonly --standalone -d ctevt-sync.yourdomain.com

# Or use cPanel's AutoSSL if available
```

---

### Option B: Deploy on Same cPanel Server (Alternative)

#### Step 1: Create Public Directory
```bash
# SSH into cPanel
ssh user@your-domain.com
cd /home/username/public_html

# Create sync service directory
mkdir -p ctevt-sync
cd ctevt-sync
```

#### Step 2: Copy Sync Endpoint
```bash
# Copy from local
scp external-sync-service/sync-endpoint.php user@your-domain.com:/home/username/public_html/ctevt-sync/

# Set permissions
chmod 644 /home/username/public_html/ctevt-sync/sync-endpoint.php
```

#### Step 3: Create .env File
```bash
# SSH into cPanel
ssh user@your-domain.com

# Create .env in sync directory
cat > /home/username/public_html/ctevt-sync/.env << 'EOF'
CTEVT_SYNC_API_TOKEN=your-secret-token-here-change-this
PRODUCTION_DB_HOST=localhost
PRODUCTION_DB_USER=your_db_user
PRODUCTION_DB_PASS=your_db_password
PRODUCTION_DB_NAME=your_db_name
EOF

chmod 600 /home/username/public_html/ctevt-sync/.env
```

---

## PHASE 3: Configure cPanel Application

### Step 1: Update .env File
```bash
# SSH into cPanel
ssh user@your-domain.com
cd /home/username/public_html

# Edit .env
nano .env

# Add these lines (or update if they exist):
CTEVT_SYNC_EXTERNAL_URL=https://ctevt-sync.yourdomain.com/sync-endpoint.php
CTEVT_SYNC_API_TOKEN=your-secret-token-here-change-this
```

**Important**: Use the SAME token in both `.env` files!

### Step 2: Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
```

### Step 3: Verify Configuration
```bash
# Check if config is loaded correctly
php artisan tinker
>>> config('services.ctevt_sync')
# Should output:
# => [
#      "external_url" => "https://ctevt-sync.yourdomain.com/sync-endpoint.php",
#      "api_token" => "your-secret-token-here-change-this",
#    ]
```

---

## PHASE 4: Test the Integration

### Test 1: Check Admin Dashboard
```
1. Log in to cPanel admin panel
2. Navigate to Dashboard
3. Look for "Notices & Updates" panel on the right
4. Click the "CTEVT" tab
5. You should see a "🔄 Sync" button
```

### Test 2: Test Sync Button (Manual)
```
1. Click the "🔄 Sync" button
2. Watch for loading state ("⏳ Syncing...")
3. Wait for response (should take 10-30 seconds)
4. You should see success message: "CTEVT notices synced successfully"
5. Check the notice counts displayed
```

### Test 3: Check Sync Logs
```bash
# SSH into cPanel
ssh user@your-domain.com
cd /home/username/public_html

# Check database for sync logs
php artisan tinker
>>> App\Models\CtevtSyncLog::latest()->first()
# Should show the sync record with status, counts, etc.
```

### Test 4: Verify Rate Limiting
```
1. Click "🔄 Sync" button
2. Immediately click again
3. Should see error: "Sync already in progress or recently completed. Please wait 5 minutes."
4. Wait 5 minutes and try again - should work
```

### Test 5: Check CTEVT Notices Display
```
1. Log in as different user roles (Student, Parent, Teacher, HOD)
2. Check their dashboards
3. All should display CTEVT notices in their respective panels
4. Should show "📦 Showing Cached Notices" if API unavailable
```

### Test 6: Direct API Test (cURL)
```bash
# Test external service endpoint directly
curl -X POST https://ctevt-sync.yourdomain.com/sync-endpoint.php \
  -H "Authorization: Bearer your-secret-token-here-change-this" \
  -H "Content-Type: application/json" \
  -d '{"action":"sync_notices"}'

# Should return JSON response with success status and notice counts
```

---

## PHASE 5: Troubleshooting

### Issue: "External sync service not configured"
**Solution**: 
```bash
# Verify .env has both variables
grep CTEVT_SYNC .env

# Clear config cache
php artisan config:clear
```

### Issue: "External service returned error HTTP 401"
**Solution**: 
```bash
# Check token matches in both .env files
# cPanel .env: CTEVT_SYNC_API_TOKEN
# External .env: CTEVT_SYNC_API_TOKEN
# They must be identical
```

### Issue: "Failed to fetch from CTEVT API"
**Solution**: 
```bash
# External service can't reach CTEVT API
# This is expected if external server also has firewall restrictions
# Verify external server can reach: https://itms.ctevt.org.np:5580
# Test from external server:
curl -k https://itms.ctevt.org.np:5580/notices/get-ajax-notices
```

### Issue: "Database connection error"
**Solution**: 
```bash
# Verify database credentials in external .env
# Test connection from external server:
mysql -h your-cpanel-db-host.com -u your_db_user -p your_db_name

# Check if cPanel database is accessible from external IP
# May need to add external IP to cPanel's "Remote MySQL" whitelist
```

### Issue: Sync button not appearing
**Solution**: 
```bash
# Check if CTEVT_SYNC_EXTERNAL_URL is configured
php artisan tinker
>>> config('services.ctevt_sync.external_url')
# Should return your URL, not null

# If null, clear config cache:
php artisan config:clear
```

---

## PHASE 6: Monitoring & Maintenance

### View Sync History
```bash
# SSH into cPanel
php artisan tinker

# Get last 10 syncs
>>> App\Models\CtevtSyncLog::latest()->limit(10)->get()

# Get failed syncs
>>> App\Models\CtevtSyncLog::where('status', 'failed')->latest()->get()

# Get sync statistics
>>> App\Models\CtevtSyncLog::latest()->first()->toArray()
```

### Check Notice Counts
```bash
php artisan tinker

# Total notices
>>> App\Models\CtevtNotice::count()

# By type
>>> App\Models\CtevtNotice::where('type', 'general')->count()
>>> App\Models\CtevtNotice::where('type', 'result')->count()

# Recently updated
>>> App\Models\CtevtNotice::latest()->limit(5)->get()
```

### View Application Logs
```bash
# Check Laravel logs for errors
tail -f /home/username/public_html/storage/logs/laravel.log

# Search for CTEVT errors
grep -i ctevt /home/username/public_html/storage/logs/laravel.log
```

---

## PHASE 7: Production Checklist

- [ ] Code pushed to production
- [ ] Migrations run successfully
- [ ] External service deployed and accessible via HTTPS
- [ ] `.env` files configured on both cPanel and external server
- [ ] API tokens match on both servers
- [ ] Database credentials verified
- [ ] Sync button appears in admin dashboard
- [ ] Manual sync test successful
- [ ] Rate limiting works (5 minute wait)
- [ ] CTEVT notices display on all dashboards
- [ ] Sync logs recorded in database
- [ ] Error handling tested
- [ ] Monitoring setup complete

---

## PHASE 8: Automated Syncing (Optional)

To automatically sync CTEVT notices periodically:

### Option A: cPanel Cron Job
```bash
# SSH into cPanel
crontab -e

# Add this line to sync every 6 hours
0 */6 * * * curl -X POST https://your-domain.com/admin/ctevt/sync \
  -H "Authorization: Bearer your-admin-token" \
  -H "Content-Type: application/json" \
  -d '{}' >> /home/username/ctevt-sync.log 2>&1
```

### Option B: Laravel Scheduler
```bash
# Add to crontab
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1

# Then add to app/Console/Kernel.php:
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        // Trigger sync via HTTP
    })->everyFourHours();
}
```

---

## Support & Documentation

- **Controller**: `app/Http/Controllers/Admin/CtevtSyncController.php`
- **External Service**: `external-sync-service/sync-endpoint.php`
- **Database Model**: `app/Models/CtevtSyncLog.php`
- **Configuration**: `config/services.php`
- **Routes**: `routes/admin.php` (lines 114-119)

---

## Security Notes

1. **API Token**: Change `your-secret-token-here-change-this` to a strong random token
2. **HTTPS Only**: Always use HTTPS for external service communication
3. **Database Access**: Restrict database access to known IPs only
4. **Rate Limiting**: 5-minute limit prevents abuse
5. **Error Logging**: All errors logged to database and Laravel logs

---

## Quick Reference

| Component | Location | Purpose |
|-----------|----------|---------|
| Admin Controller | `app/Http/Controllers/Admin/CtevtSyncController.php` | Handles sync requests |
| External Service | `external-sync-service/sync-endpoint.php` | Fetches from CTEVT API |
| Sync Logs | `ctevt_sync_logs` table | Tracks all operations |
| Configuration | `config/services.php` | Service configuration |
| Routes | `routes/admin.php` | API endpoints |
| Dashboard | `resources/views/admin/dashboard-modern.blade.php` | UI with sync button |

