@echo off
setlocal
set "APP_ENV=local-demo"
set "DB_CONNECTION=sqlite"
set "DB_DATABASE=database/local-demo.sqlite"
set "DB_URL="
title Le Charme Suites IHMP Local Demo Preparation

cd /d "%~dp0le-charme-suites-ihmp" 2>nul
if errorlevel 1 goto :wrong_folder

where php >nul 2>nul
if errorlevel 1 goto :missing_php

php -r "exit(PHP_VERSION_ID >= 80400 ? 0 : 1);"
if errorlevel 1 goto :old_php

if not exist vendor\autoload.php goto :missing_vendor
if not exist .env.local-demo.example goto :missing_template

echo.
echo [1/6] Preparing isolated local settings...
if not exist .env.local-demo copy /Y .env.local-demo.example .env.local-demo >nul
if errorlevel 1 goto :failed

php artisan --env=local-demo config:clear >nul
if errorlevel 1 goto :failed

echo [2/6] Generating a local application key when needed...
php -r "$s=file_get_contents('.env.local-demo'); exit(preg_match('/^APP_KEY=.+/m', $s) ? 0 : 1);"
if errorlevel 1 php artisan --env=local-demo key:generate --force
if errorlevel 1 goto :failed

echo [3/6] Confirming that no shared database is configured...
php artisan --env=local-demo demo:prepare --check-only
if errorlevel 1 goto :unsafe_database

echo [4/6] Creating the dedicated SQLite file...
if not exist database\local-demo.sqlite type nul > database\local-demo.sqlite
if not exist database\local-demo.sqlite goto :database_failed

echo [5/6] Applying database tables only to database\local-demo.sqlite...
php artisan --env=local-demo migrate --force
if errorlevel 1 goto :failed

echo [6/6] Creating fictional demonstration accounts and hotel records...
php artisan --env=local-demo demo:prepare
if errorlevel 1 goto :failed

php artisan --env=local-demo optimize:clear >nul
if errorlevel 1 goto :failed

echo.
echo Local demonstration preparation completed successfully.
echo No Supabase or shared database connection was used.
echo Run run-local-demo.bat to start the portal.
exit /b 0

:unsafe_database
echo ERROR: Local demo settings failed the SQLite safety check.
echo No migration or fictional data command was run.
goto :help

:database_failed
echo ERROR: database\local-demo.sqlite could not be created.
goto :help

:missing_vendor
echo ERROR: PHP dependencies are missing.
echo Run setup-and-run.bat on an internet-connected computer first, or transfer
echo the complete vendor folder with the project.
goto :help

:missing_template
echo ERROR: le-charme-suites-ihmp\.env.local-demo.example is missing.
goto :help

:missing_php
echo ERROR: PHP was not found. Install PHP 8.4 or newer and add it to PATH.
goto :help

:old_php
echo ERROR: This project requires PHP 8.4 or newer.
php -v
goto :help

:wrong_folder
echo ERROR: The le-charme-suites-ihmp application folder was not found.
goto :help

:failed
echo ERROR: Local demonstration preparation stopped because a command failed.

:help
echo Read le-charme-suites-ihmp\LOCAL_DEMO_WINDOWS_GUIDE.md for help.
pause
exit /b 1
