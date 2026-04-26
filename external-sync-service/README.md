# CTEVT External Sync Service

This service runs on an external server (VPS, local machine, or GitHub Actions) that **CAN** access the CTEVT API on port 5580. It fetches notices from CTEVT and updates the production database.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    cPanel Production Server                  │
│  (Cannot access port 5580 - firewall blocks it)             │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Admin Dashboard                                     │  │
│  │  "🔄 Sync CTEVT Notices" Button                     │  │
│  │  ↓ POST /admin/ctevt/sync                           │  │
│  └──────────────────────────────────────────────────────┘  │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  CtevtSyncController                                 │  │
│  │  - Validates request                                │  │
│  │  - Rate limits (5 min)                              │  │
│  │  - Calls external service                           │  │
│  │  - Logs sync result                                 │  │
│  └──────────────────────────────────────────────────────┘  │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Production Database                                 │  │
│  │  - ctevt_notices table                              │  │
│  │  - ctevt_sync_logs table                            │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
         ↑                                    ↑
         │ (HTTPS with API token)            │ (Read-only)
         │                                    │
┌────────┴────────────────────────────────────┴──────────────┐
│                                                              │
│  External Sync Service (VPS / Local / GitHub Actions)      │
│  (CAN access port 5580)                                    │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  sync-endpoint.php                                   │  │
│  │  - Authenticates with API token                      │  │
│  │  - Fetches from CTEVT API (port 5580)               │  │
│  │  - Upserts notices to production DB                 │  │
│  │  - Returns sync status                              │  │
│  └──────────────────────────────────────────────────────┘  │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  CTEVT API                                           │  │
│  │  https://itms.ctevt.org.np:5580/notices/...        │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

## Deployment Options

### Option 1: VPS (Recommended)

1. **Rent a VPS** (DigitalOcean, Linode, AWS, etc.)
2. **Install PHP** with cURL support
3. **Copy `sync-endpoint.php`** to your VPS
4. **Set environment variables:**
   ```bash
   export CTEVT_SYNC_API_TOKEN="your-secret-token-here"
   export PRODUCTION_DB_HOST="your-cpanel-db-host"
   export PRODUCTION_DB_USER="your-db-user"
   export PRODUCTION_DB_PASS="your-db-password"
   export PRODUCTION_DB_NAME="your-db-name"
   ```
5. **Make it accessible via HTTPS** (use Nginx/Apache + Let's Encrypt)
6. **Configure in cPanel .env:**
   ```
   CTEVT_SYNC_EXTERNAL_URL=https://your-vps.com/sync-endpoint.php
   CTEVT_SYNC_API_TOKEN=your-secret-token-here
   ```

### Option 2: Local Machine (Development/Testing)

1. **Install PHP** locally
2. **Copy `sync-endpoint.php`** to your local machine
3. **Set environment variables** (see above)
4. **Run a local server:**
   ```bash
   php -S localhost:8000
   ```
5. **Use ngrok to expose locally:**
   ```bash
   ngrok http 8000
   ```
6. **Configure in cPanel .env:**
   ```
   CTEVT_SYNC_EXTERNAL_URL=https://your-ngrok-url.ngrok.io/sync-endpoint.php
   CTEVT_SYNC_API_TOKEN=your-secret-token-here
   ```

### Option 3: GitHub Actions (Scheduled)

1. **Create `.github/workflows/ctevt-sync.yml`:**
   ```yaml
   name: CTEVT Sync
   on:
     schedule:
       - cron: '0 * * * *'  # Every hour
   
   jobs:
     sync:
       runs-on: ubuntu-latest
       steps:
         - name: Sync CTEVT Notices
           run: |
             curl -X POST \
               -H "Authorization: Bearer ${{ secrets.CTEVT_SYNC_API_TOKEN }}" \
               -H "Content-Type: application/json" \
               -d '{"action":"sync_notices"}' \
               ${{ secrets.CTEVT_SYNC_EXTERNAL_URL }}
   ```

2. **Add GitHub Secrets:**
   - `CTEVT_SYNC_API_TOKEN`: Your secret token
   - `CTEVT_SYNC_EXTERNAL_URL`: Your endpoint URL

## Configuration

### Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `CTEVT_SYNC_API_TOKEN` | Secret token for authentication | `abc123xyz789` |
| `PRODUCTION_DB_HOST` | Production database host | `db.example.com` |
| `PRODUCTION_DB_USER` | Production database user | `portal_user` |
| `PRODUCTION_DB_PASS` | Production database password | `secure_password` |
| `PRODUCTION_DB_NAME` | Production database name | `college_portal` |

### cPanel Configuration

Add to `.env`:

```env
# CTEVT External Sync Service
CTEVT_SYNC_EXTERNAL_URL=https://your-external-service.com/sync-endpoint.php
CTEVT_SYNC_API_TOKEN=your-secret-token-here
```

## Security

### API Token

- Generate a strong, random token (32+ characters)
- Store securely in environment variables
- Rotate periodically
- Never commit to version control

### HTTPS Only

- Always use HTTPS for the endpoint
- Use valid SSL certificates (Let's Encrypt is free)
- Reject HTTP requests

### IP Allowlist (Optional)

Add to `sync-endpoint.php` if needed:

```php
$allowed_ips = ['203.0.113.0', '198.51.100.0'];
$client_ip = $_SERVER['REMOTE_ADDR'];

if (!in_array($client_ip, $allowed_ips)) {
    error_response('IP not allowed', 403);
}
```

### Rate Limiting

- cPanel enforces 5-minute rate limit between syncs
- External service can be called manually or via scheduled jobs
- Prevents abuse and database locks

## Testing

### Test the Endpoint

```bash
curl -X POST \
  -H "Authorization: Bearer your-secret-token" \
  -H "Content-Type: application/json" \
  -d '{"action":"sync_notices"}' \
  https://your-external-service.com/sync-endpoint.php
```

### Expected Response (Success)

```json
{
  "success": true,
  "message": "Sync completed successfully",
  "notices_added": 5,
  "notices_updated": 3,
  "notices_total": 8,
  "duration_seconds": 2.45,
  "breakdown": {
    "general": {"added": 3, "updated": 2},
    "result": {"added": 2, "updated": 1}
  }
}
```

### Expected Response (Error)

```json
{
  "success": false,
  "message": "Sync failed",
  "error": "Failed to fetch from CTEVT API"
}
```

## Troubleshooting

### "Connection refused" error

- Check if external service is running
- Verify HTTPS is working
- Check firewall rules

### "Invalid API token" error

- Verify token in cPanel .env matches external service
- Check Authorization header format: `Bearer token`

### "Database connection error"

- Verify database credentials
- Check if production database is accessible from external service
- Ensure database user has INSERT/UPDATE permissions on `ctevt_notices` table

### "Failed to fetch from CTEVT API"

- Verify external service can access port 5580
- Check CTEVT API is online
- Verify SSL certificate validation (disabled in endpoint for CTEVT)

## Monitoring

### Check Sync Logs

In cPanel admin dashboard:
- View sync history in "CTEVT Sync Logs"
- See last sync time and status
- Check error messages

### Manual Sync

Click "🔄 Sync CTEVT Notices" button in admin dashboard to trigger manual sync.

## Maintenance

### Update Notices

Notices are automatically upserted (inserted or updated) based on `external_id`.

### Cleanup Old Logs

Optionally clean up old sync logs:

```sql
DELETE FROM ctevt_sync_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review sync logs in admin dashboard
3. Check server error logs
4. Contact your hosting provider
