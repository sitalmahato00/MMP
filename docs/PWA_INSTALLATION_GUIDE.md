# MMP Progressive Web App (PWA) Installation Guide

## Overview
The MMP (Manmohan Memorial Polytechnic) portal is now installable as a Progressive Web App (PWA) on both desktop and mobile devices. This allows users to:

- **Install the app** like a native application
- **Launch from home screen** or desktop
- **Work offline** with cached pages
- **Receive push notifications** (when enabled)
- **Faster loading** with cached resources
- **App-like experience** with standalone window

---

## Installation Instructions

### 📱 Mobile Installation (Android & iOS)

#### Android (Chrome, Edge, Samsung Internet)

1. **Open the MMP portal** in your mobile browser (Chrome recommended)
   - Visit: `https://your-mmp-domain.com`

2. **Look for the install prompt**
   - A banner will appear at the bottom saying "Install MMP App"
   - OR tap the **Install button** (download icon) in the top-right header

3. **Tap "Install"** on the prompt

4. **Confirm installation**
   - The app will be added to your home screen
   - You can now launch it like any other app

#### iOS (Safari)

1. **Open the MMP portal** in Safari
   - Visit: `https://your-mmp-domain.com`

2. **Tap the Share button** (square with arrow pointing up)
   - Located at the bottom of the screen

3. **Scroll down** and tap **"Add to Home Screen"**

4. **Customize the name** (optional) and tap **"Add"**

5. **Launch from home screen**
   - The MMP icon will appear on your home screen
   - Tap it to open the app

---

### 💻 Desktop Installation (Windows, Mac, Linux)

#### Chrome, Edge, Brave

1. **Open the MMP portal** in your browser
   - Visit: `https://your-mmp-domain.com`

2. **Look for the install prompt**
   - A banner may appear offering to install
   - OR click the **Install button** (download icon) in the address bar
   - OR click the **three-dot menu** → **"Install MMP App"**

3. **Click "Install"**

4. **Launch the app**
   - The app will open in its own window
   - A shortcut will be added to your desktop/start menu
   - You can pin it to your taskbar

#### Firefox

1. **Open the MMP portal** in Firefox
   - Visit: `https://your-mmp-domain.com`

2. **Click the three-line menu** (top-right)

3. **Select "Install MMP App"** or **"Add to Home Screen"**

4. **Confirm installation**

---

## Features of the Installed App

### ✅ Offline Access
- Recently visited pages are cached
- You can view cached content even without internet
- An offline page appears when you're disconnected

### ✅ Standalone Window
- Opens in its own window (no browser UI)
- Looks and feels like a native app
- Dedicated taskbar/dock icon

### ✅ Fast Loading
- Static assets (CSS, JS, images) are cached
- Pages load instantly after first visit
- Reduced data usage

### ✅ Push Notifications (Coming Soon)
- Receive notifications for:
  - New notices
  - Exam results
  - Attendance updates
  - Important announcements

### ✅ Auto-Updates
- The app automatically updates when you're online
- No manual updates required
- Always get the latest features

---

## Uninstalling the App

### Mobile (Android)
1. Long-press the MMP app icon
2. Select "Uninstall" or drag to "Remove"

### Mobile (iOS)
1. Long-press the MMP app icon
2. Tap "Remove App" → "Delete App"

### Desktop (Chrome/Edge)
1. Open the installed app
2. Click the three-dot menu (top-right)
3. Select "Uninstall MMP App"

OR

1. Go to browser settings
2. Navigate to "Apps" or "Installed Apps"
3. Find MMP App and click "Uninstall"

---

## Troubleshooting

### Install button not showing?

**Possible reasons:**
- You're already using the installed app
- The site is not served over HTTPS
- Your browser doesn't support PWA
- You've dismissed the prompt (check browser settings)

**Solutions:**
- Clear browser cache and reload
- Try a different browser (Chrome recommended)
- Check if you're on HTTPS
- Look for the install option in browser menu

### App not working offline?

**Solutions:**
- Visit pages while online first (they need to be cached)
- Check if service worker is registered (Developer Tools → Application → Service Workers)
- Clear cache and reinstall the app

### App not updating?

**Solutions:**
- Close and reopen the app
- Clear browser cache
- Uninstall and reinstall the app

---

## Technical Details

### PWA Configuration Files

1. **Manifest** (`public/manifest.json`)
   - App name, icons, theme colors
   - Display mode (standalone)
   - Start URL and scope

2. **Service Worker** (`public/sw.js`)
   - Caching strategy
   - Offline fallback
   - Push notification handling

3. **Offline Page** (`public/offline.html`)
   - Displayed when offline and page not cached

### Browser Support

| Browser | Desktop | Mobile |
|---------|---------|--------|
| Chrome | ✅ Full | ✅ Full |
| Edge | ✅ Full | ✅ Full |
| Safari | ⚠️ Limited | ✅ Full |
| Firefox | ✅ Full | ✅ Full |
| Samsung Internet | N/A | ✅ Full |

### Requirements

- **HTTPS** connection (required for PWA)
- **Modern browser** (Chrome 67+, Safari 11.1+, Firefox 58+)
- **Internet connection** for initial installation

---

## For Developers

### Testing PWA Locally

1. **Serve over HTTPS** (required for service workers)
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Use ngrok** for HTTPS tunnel (development)
   ```bash
   ngrok http 8000
   ```

3. **Test in Chrome DevTools**
   - Open DevTools (F12)
   - Go to "Application" tab
   - Check "Manifest" and "Service Workers"
   - Use "Lighthouse" for PWA audit

### Updating PWA Configuration

1. **Update manifest** (`public/manifest.json`)
   - Change app name, icons, colors
   - Increment version to force update

2. **Update service worker** (`public/sw.js`)
   - Change `CACHE_NAME` to force cache refresh
   - Update precache URLs

3. **Clear cache** after updates
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

---

## Support

For issues or questions about the PWA installation:
- Contact: info@mmp.edu.np
- Phone: +977 21 590696, +977 21 590697
- Visit: Budhiganga-4, Morang, Koshi Province, Nepal

---

**Last Updated:** April 25, 2026
**Version:** 1.0.0
