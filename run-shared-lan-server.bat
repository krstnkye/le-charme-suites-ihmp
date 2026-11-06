@echo off
setlocal
title Le Charme Suites IHMP Shared LAN Server

cd /d "%~dp0le-charme-suites-ihmp"
if errorlevel 1 goto :wrong_folder

where php >nul 2>nul
if errorlevel 1 goto :debug_enabled
echo ERROR: Debug mode is on. Debug error pages would show file paths, database
echo queries and settings to every device on the network.
echo Open le-charme-suites-ihmp\.env, set APP_DEBUG=false, save it, then run this again.
pause
exit /b 1

:missing_php

if not exist .env goto :not_configured

rem Only the SQLite setup needs the local database file.
php -r "$s=file_get_contents('.env'); exit(preg_match('/^DB_CONNECTION\s*=\s*\W?(pgsql|mysql|mariadb|sqlsrv)/mi', $s) || file_exists('database/database.sqlite') ? 0 : 1);"
if errorlevel 1 goto :not_configured

rem Debug pages show file paths, queries and settings, so never share them on the network.
php -r "$s=file_get_contents('.env'); exit(preg_match('/^APP_DEBUG\s*=\s*\W?(true|1|on|yes)\W?\s*$/mi', $s) ? 1 : 0);"
if errorlevel 1 goto :debug_enabled

echo.
echo Le Charme Suites IHMP - Shared Offline / LAN Mode
echo -------------------------------------------------
echo 1. Keep this window open.
echo 2. Keep all laptops connected to the same private Wi-Fi network.
echo 3. Find this laptop's IPv4 Address in the list below.
echo 4. Other laptops should open http://YOUR-IP:8000
echo.
ipconfig | findstr /I /C:"IPv4 Address"
echo.
echo Starting the shared hotel system on port 8000...
echo Windows may ask you to allow PHP on Private networks.
echo.
php artisan serve --host=0.0.0.0 --port=8000
goto :eof

:not_configured
echo ERROR: Complete setup-and-run.bat before starting shared LAN mode.
pause
exit /b 1

:missing_php
echo ERROR: PHP was not found. Install PHP 8.4 or newer and add it to PATH.
pause
exit /b 1

:wrong_folder
echo ERROR: The le-charme-suites-ihmp application folder was not found.
pause
exit /b 1
