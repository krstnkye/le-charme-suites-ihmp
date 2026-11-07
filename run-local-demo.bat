@echo off
setlocal
set "APP_ENV=local-demo"
set "DB_CONNECTION=sqlite"
set "DB_DATABASE=database/local-demo.sqlite"
set "DB_URL="
title Le Charme Suites IHMP Local Demo

cd /d "%~dp0le-charme-suites-ihmp" 2>nul
if errorlevel 1 goto :wrong_folder

where php >nul 2>nul
if errorlevel 1 goto :missing_php
if not exist vendor\autoload.php goto :missing_vendor
if not exist .env.local-demo goto :not_prepared
if not exist database\local-demo.sqlite goto :not_prepared

php artisan --env=local-demo demo:prepare --check-only
if errorlevel 1 goto :unsafe_database

set "DEMO_HOST=127.0.0.1"
if /I "%~1"=="--lan" set "DEMO_HOST=0.0.0.0"

echo.
echo Le Charme Suites IHMP - Isolated Local SQLite Demo
echo ---------------------------------------------------
echo Database: le-charme-suites-ihmp\database\local-demo.sqlite
echo Open http://127.0.0.1:8000 on this laptop.
if /I "%~1"=="--lan" echo LAN mode is enabled. APP_DEBUG remains off.
echo Keep this window open while using the portal.
echo.
php artisan --env=local-demo serve --host=%DEMO_HOST% --port=8000
exit /b %errorlevel%

:unsafe_database
echo ERROR: The local demo settings are not safe SQLite settings.
echo No server was started.
goto :help

:not_prepared
echo ERROR: The local demo has not been prepared on this laptop.
echo Run prepare-local-demo.bat first.
goto :help

:missing_vendor
echo ERROR: PHP dependencies are missing. Transfer the complete vendor folder.
goto :help

:missing_php
echo ERROR: PHP was not found. Install PHP 8.4 or newer and add it to PATH.
goto :help

:wrong_folder
echo ERROR: The le-charme-suites-ihmp application folder was not found.
goto :help

:help
echo Read le-charme-suites-ihmp\LOCAL_DEMO_WINDOWS_GUIDE.md for help.
pause
exit /b 1
