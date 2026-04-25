# MMP PWA Setup and Testing Guide

## Why You Don't See the Install Button

PWAs (Progressive Web Apps) require **HTTPS** to work properly. Currently, your app is running on `http://localhost`, which has limited PWA support.

### Requirements for PWA Installation:
1. ✅ Valid manifest.json (already configured)
2. ✅ Service worker registered (already configured)
3. ❌ **HTTPS connection** (missing - this is why install button doesn't show)
4. ✅ App meets installability criteria

---

## Solution 1: Test PWA Locally with HTTPS

### Option A: Use ngrok (Recommended for Testing)

1. **Install ngrok**
   - Download from: https://ngrok.com/download
   - Or use: `choco install ngrok` (Windows with Chocolatey)

2. **Start your Laravel app**
   ```bash
   php artisan serve
   ```

3. **Create HTTPS tunnel**
   ```bash
   ngrok http 8000
   ```

4. **Access the HTTPS URL**
   - ngrok will provide a URL like: `https://abc123.ngrok.io`
   - Open this URL in your browser
   - **The install button will now appear!**

### Option B: Use Laravel Valet (Mac/Linux)

1. **Install Valet**
   ```bash
   composer global require laravel/valet
   valet install
   ```

2. **Secure your site**
   ```bash
   cd /path/to/mmp
   valet link mmp
   valet secure mmp
   ```

3. **Access via HTTPS**
   - Visit: `https://mmp.test`
   - Install button will appear

### Option C: Use Laragon (Windows)

1. **Install Laragon**
   - Download from: https://laragon.org/download/

2. **Enable SSL**
   - Right-click Laragon tray icon
   - Apache → SSL → Enable
   - Right-click your site → SSL → Create Certificate

3. **Access via HTTPS**
   - Visit: `https://mmp.test`
   - Install button will appear

---

## Solution 2: Deploy to Production with HTTPS

### Option A: Deploy to Shared Hosting with SSL

Most hosting providers offer free SSL certificates:

1. **Upload your Laravel app**
2. **Enable SSL/HTTPS** in hosting control panel
3. **Update .env**
   ```env
   APP_URL=https://yourdomain.com
   ```
4. **Clear cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Option B: Deploy to VPS with Let's Encrypt

1. **Install Certbot**
   ```bash
   sudo apt install certbot python3-certbot-nginx
   ```

2. **Get SSL certificate**
   ```bash
   sudo certbot --nginx -d yourdomain.com
   ```

3. **Update .env**
   ```env
   APP_URL=https://yourdomain.com
   ```

4. **Restart services**
   ```bash
   sudo systemctl restart nginx
   php artisan config:clear
   ```

---

## How to Test if PWA is Working

### 1. Check Manifest

Open Chrome DevTools (F12):
1. Go to **Application** tab
2. Click **Manifest** in left sidebar
3. You should see:
   - App name: "MMP Academic App"
   - Icons loaded
   - No errors

### 2. Check Service Worker

In Chrome DevTools:
1. Go to **Application** tab
2. Click **Service Workers**
3. You should see:
   - `sw.js` registered
   - Status: "activated and running"

### 3. Check Installability

In Chrome DevTools:
1. Go to **Application** tab
2. Click **Manifest**
3. Look for **"Add to homescreen"** section
4. Click **"Add to homescreen"** button to test

### 4. Run Lighthouse Audit

In Chrome DevTools:
1. Go to **Lighthouse** tab
2. Select **"Progressive Web App"**
3. Click **"Generate report"**
4. Should score 90+ for PWA

---

## Testing the Install Button

Once you have HTTPS working:

### Desktop (Chrome/Edge)
1. Visit your HTTPS site
2. Look for **install icon** in address bar (⊕ or ⬇)
3. OR check the **three-dot menu** → "Install MMP App"
4. Click to install

### Mobile (Chrome Android)
1. Visit your HTTPS site
2. A banner should appear: **"Install MMP App"**
3. OR tap **three-dot menu** → "Add to Home screen"
4. Tap "Install"

### Mobile (Safari iOS)
1. Visit your HTTPS site
2. Tap **Share button** (square with arrow)
3. Scroll and tap **"Add to Home Screen"**
4. Tap "Add"

---

## Troubleshooting

### Install button still not showing?

**Check these:**

1. **Are you on HTTPS?**
   - URL must start with `https://`
   - Not `http://` or `localhost`

2. **Is service worker registered?**
   ```javascript
   // Open browser console and run:
   navigator.serviceWorker.getRegistrations().then(regs => console.log(regs))
   ```

3. **Is manifest valid?**
   - Visit: `https://yourdomain.com/manifest.json`
   - Should return JSON (not 404)

4. **Clear browser cache**
   - Chrome: Ctrl+Shift+Delete
   - Clear "Cached images and files"
   - Reload page

5. **Try incognito/private mode**
   - Sometimes cached data interferes

### Service worker not registering?

1. **Check console for errors**
   - Open DevTools → Console
   - Look for service worker errors

2. **Verify sw.js exists**
   - Visit: `https://yourdomain.com/sw.js`
   - Should return JavaScript code

3. **Check HTTPS**
   - Service workers require HTTPS
   - Exception: `localhost` works in some browsers

### Manifest not loading?

1. **Check manifest URL**
   - Visit: `https://yourdomain.com/manifest.json`
   - Should return JSON

2. **Check manifest link in HTML**
   ```html
   <link rel="manifest" href="/manifest.json">
   ```

3. **Clear cache and reload**

---

## Quick Test Commands

### Test if HTTPS is working
```bash
curl -I https://yourdomain.com
# Should return: HTTP/2 200
```

### Test manifest accessibility
```bash
curl https://yourdomain.com/manifest.json
# Should return JSON content
```

### Test service worker
```bash
curl https://yourdomain.com/sw.js
# Should return JavaScript code
```

---

## For Development: Force Install Prompt

If you want to test the install prompt without HTTPS (for development only):

### Chrome Flags (Development Only)

1. Open: `chrome://flags`
2. Search: "Insecure origins treated as secure"
3. Add: `http://localhost:8000`
4. Restart Chrome

**Note:** This only works for testing. Production MUST use HTTPS.

---

## Production Checklist

Before deploying to production:

- [ ] SSL certificate installed
- [ ] APP_URL set to HTTPS in .env
- [ ] Manifest.json accessible via HTTPS
- [ ] Service worker (sw.js) accessible via HTTPS
- [ ] Icons accessible and correct sizes
- [ ] Tested install on desktop browser
- [ ] Tested install on mobile browser
- [ ] Lighthouse PWA score > 90
- [ ] Offline page works
- [ ] Service worker caching works

---

## Current Status

✅ **Configured:**
- Manifest.json with app details
- Service worker with caching
- Install prompts in UI
- Offline fallback page

❌ **Missing:**
- HTTPS connection (required for PWA)

**Next Step:** Set up HTTPS using one of the solutions above, then the install button will appear automatically!

---

## Support

For help with PWA setup:
- Email: info@mmp.edu.np
- Phone: +977 21 590696

---

**Last Updated:** April 25, 2026
