@echo off
setlocal
title Le Charme Suites IHMP Setup

cd /d "%~dp0le-charme-suites-ihmp"
if errorlevel 1 goto :wrong_folder

where php >nul 2>nul
if errorlevel 1 goto :missing_php

php -r "exit(PHP_VERSION_ID >= 80300 ? 0 : 1);"
if errorlevel 1 goto :old_php

where composer >nul 2>nul
if not errorlevel 1 (
    set "COMPOSER_MODE=global"
) else if exist "%~dp0composer.phar" (
    set "COMPOSER_MODE=local"
) else (
    goto :missing_composer
)

echo.
echo [1/5] Installing PHP dependencies...
if "%COMPOSER_MODE%"=="global" (
    call composer install --no-interaction --prefer-dist
) else (
    php "%~dp0composer.phar" install --no-interaction --prefer-dist
)
if errorlevel 1 goto :failed

echo [2/5] Preparing the environment file...
if not exist .env copy /Y .env.example .env >nul

php -r "$s=file_get_contents('.env'); exit(preg_match('/^APP_KEY=.+/m', $s) ? 0 : 1);"
if errorlevel 1 php artisan key:generate
if errorlevel 1 goto :failed

echo [3/5] Creating and updating the local database...
if not exist database\database.sqlite type nul > database\database.sqlite
if not exist database\database.sqlite goto :database_failed
php artisan migrate --seed --force
if errorlevel 1 goto :failed

echo [4/5] Clearing stale Laravel caches...
php artisan optimize:clear
if errorlevel 1 goto :failed

if /I "%~1"=="--no-serve" goto :done

echo [5/5] Starting Le Charme Suites IHMP...
echo Open http://127.0.0.1:8000 in your browser.
echo Keep this window open while using the system.
echo.
php artisan serve --host=127.0.0.1 --port=8000
goto :eof

:done
echo [5/5] Setup verification completed successfully.
exit /b 0

:wrong_folder
echo ERROR: The le-charme-suites-ihmp application folder was not found.
goto :help

:missing_php
echo ERROR: PHP was not found. Install PHP 8.3 or newer and add it to PATH.
goto :help

:old_php
echo ERROR: This project requires PHP 8.3 or newer.
php -v
goto :help

:missing_composer
echo ERROR: Composer was not found. Install Composer and reopen the terminal.
goto :help

:database_failed
echo ERROR: The local SQLite database could not be created.
echo Check that the database folder is writable, then try again.
goto :help

:failed
echo.
echo ERROR: Setup stopped because a command failed. Read the message above.

:help
echo See le-charme-suites-ihmp\GROUPMATES_SETUP.md for troubleshooting.
pause
exit /b 1
