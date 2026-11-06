@echo off
setlocal
title Le Charme Suites IHMP Shared LAN Server

cd /d "%~dp0le-charme-suites-ihmp" 2>nul
if errorlevel 1 goto :wrong_folder

where php >nul 2>nul
if errorlevel 1 goto :missing_php

if not exist .env goto :not_configured

rem Ask Laravel itself for the settings the server will really use, so quoting,
rem spacing, inline comments and cached config are all handled the same way.
rem Exit codes: 0 ready, 2 SQLite database file missing, 3 debug mode on.
php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $connection = config('database.default'); $database = (string) config('database.connections.'.$connection.'.database'); if (config('database.connections.'.$connection.'.driver') === 'sqlite' && $database !== ':memory:' && ! is_file($database)) { exit(2); } exit(config('app.debug') ? 3 : 0);"
if errorlevel 4 goto :app_failed
if errorlevel 3 goto :debug_enabled
if errorlevel 2 goto :missing_sqlite
if errorlevel 1 goto :app_failed

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

:debug_enabled
echo ERROR: Debug mode is on. Debug error pages would show file paths, database
echo queries and settings to every device on the network.
echo Open le-charme-suites-ihmp\.env, set APP_DEBUG=false, save it, then run this again.
pause
exit /b 1

:missing_sqlite
echo ERROR: This copy is set to use SQLite, but its database file was not found.
echo Run setup-and-run.bat first to create the local database.
pause
exit /b 1

:not_configured
echo ERROR: The .env settings file was not found.
echo Complete setup-and-run.bat before starting shared LAN mode.
pause
exit /b 1

:app_failed
echo ERROR: The application could not be loaded to check its settings.
echo Run setup-and-run.bat first, then try again.
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
