# Quick Start: Enable PWA Install Button

## The Problem
You don't see the install/download button because PWAs require **HTTPS**. Your app is currently running on `http://localhost`.

## The Solution (Choose One)

### ⚡ Fastest: Use ngrok (5 minutes)

1. **Download ngrok**
   - Visit: https://ngrok.com/download
   - Extract the zip file

2. **Start Laravel**
   ```bash
   php artisan serve
   ```

3. **Start ngrok** (in a new terminal)
   ```bash
   ngrok http 8000
   ```

4. **Open the HTTPS URL**
   - ngrok will show a URL like: `https://abc123.ngrok-free.app`
   - Copy and paste it in your browser
   - **The install button will appear!** 🎉

### 🖥️ Alternative: Use the Test Script

1. **Double-click** `test-pwa.bat` (in your project root)
2. **Follow the instructions** on screen
3. **Copy the ngrok URL** and open in browser

---

## What You'll See

Once you open the HTTPS URL:

### On Desktop:
- **Install icon** in the address bar (⊕ or ⬇)
- **Install button** in the top-right header
- **Browser menu** → "Install MMP App"

### On Mobile:
- **Banner at bottom**: "Install MMP App"
- **Install button** in the header
- **Browser menu** → "Add to Home screen"

---

## Testing Checklist

After opening the HTTPS URL:

1. ✅ Can you see the install button?
2. ✅ Click install - does it work?
3. ✅ Open the installed app - does it launch?
4. ✅ Check if it works offline (disconnect internet)

---

## For Production

When you deploy to a real server:

1. **Get SSL certificate** (free with Let's Encrypt)
2. **Update .env**:
   ```env
   APP_URL=https://yourdomain.com
   ```
3. **Clear cache**:
   ```bash
   php artisan config:clear
   ```

The install button will work automatically on HTTPS!

---

## Need Help?

- **Can't install ngrok?** Try Laragon (Windows) or Valet (Mac)
- **Still no install button?** Check `docs/PWA_SETUP_AND_TESTING.md`
- **Production deployment?** Contact: info@mmp.edu.np

---

**TL;DR:** Use ngrok to get HTTPS → Install button appears → PWA works! 🚀
