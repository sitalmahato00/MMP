@echo off
echo ========================================
echo MMP PWA Testing Script
echo ========================================
echo.
echo This script will help you test the PWA with HTTPS
echo.
echo Prerequisites:
echo 1. ngrok installed (download from https://ngrok.com/download)
echo 2. Laravel app running on port 8000
echo.
echo ========================================
echo.

echo Step 1: Starting Laravel development server...
start "Laravel Server" cmd /k "php artisan serve"
timeout /t 3 /nobreak >nul

echo.
echo Step 2: Starting ngrok HTTPS tunnel...
echo.
echo IMPORTANT: After ngrok starts, you will see a URL like:
echo   https://abc123.ngrok.io
echo.
echo Copy that URL and open it in your browser.
echo The PWA install button will appear!
echo.
echo Press any key to start ngrok...
pause >nul

ngrok http 8000

echo.
echo ========================================
echo Testing complete!
echo ========================================
