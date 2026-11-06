@echo off
setlocal
title Le Charme Suites IHMP Setup

cd /d "%~dp0le-charme-suites-ihmp"
if errorlevel 1 goto :wrong_folder

where php >nul 2>nul
if errorlevel 1 goto :missing_php

php -r "exit(PHP_VERSION_ID >= 80400 ? 0 : 1);"
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

echo [3/5] Checking database safety before making changes...
rem Exit codes: 0 safe local SQLite, 2 shared database, 3 ambiguous settings.
rem This check boots configuration only. It does not open a database connection.
php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $connection = (string) config('database.default'); $driver = (string) config('database.connections.'.$connection.'.driver'); $database = (string) config('database.connections.'.$connection.'.database'); $environment = (string) app()->environment(); if ($driver === 'sqlite') { if (! in_array($environment, ['local', 'testing'], true) || $database === '' || $database === ':memory:') { exit(3); } $databaseDirectory = realpath(database_path()); $targetDirectory = realpath(dirname($database)); if ($databaseDirectory === false || $targetDirectory === false) { exit(3); } $databasePrefix = strtolower(rtrim($databaseDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR); $targetPrefix = strtolower(rtrim($targetDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR); exit(str_starts_with($targetPrefix, $databasePrefix) ? 0 : 3); } exit(in_array($driver, ['pgsql', 'mysql', 'mariadb', 'sqlsrv'], true) ? 2 : 3);"
if errorlevel 4 goto :app_failed
if errorlevel 3 goto :ambiguous_database
if errorlevel 2 goto :shared_database
if errorlevel 1 goto :app_failed
goto :local_sqlite_database

:local_sqlite_database
echo Safe local SQLite database detected.
php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $database = (string) config('database.connections.sqlite.database'); exit(is_file($database) || touch($database) ? 0 : 1);"
if errorlevel 1 goto :database_failed
php artisan migrate --seed --force
if errorlevel 1 goto :failed
goto :database_ready

:shared_database
echo.
echo A PostgreSQL, MySQL, MariaDB, or SQL Server database is configured.
echo No migration or seeder will run automatically against a shared database.
echo Checking migration status only. This command does not change database data.
php artisan migrate:status
if errorlevel 1 goto :shared_database_unavailable
echo.
echo Review the migration status above before continuing.
echo Type APPLY MIGRATIONS to run migrations without seeders.
echo Press Enter or type anything else to leave the shared database unchanged.
set "DATABASE_CONFIRMATION="
set /p "DATABASE_CONFIRMATION=Confirmation: "
if not "%DATABASE_CONFIRMATION%"=="APPLY MIGRATIONS" goto :shared_database_skipped
php artisan migrate --force
if errorlevel 1 goto :failed
echo Shared database migrations completed. Seeders were not run.
goto :database_ready

:shared_database_skipped
echo Shared database changes skipped. Seeders were not run.
goto :database_ready

:database_ready

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
echo ERROR: PHP was not found. Install PHP 8.4 or newer and add it to PATH.
goto :help

:old_php
echo ERROR: This project requires PHP 8.4 or newer.
php -v
goto :help

:missing_composer
echo ERROR: Composer was not found. Install Composer and reopen the terminal.
goto :help

:database_failed
echo ERROR: The local SQLite database could not be created.
echo Check that the database folder is writable, then try again.
goto :help

:ambiguous_database
echo ERROR: Database settings are ambiguous or unsafe for automatic setup.
echo Use local SQLite inside le-charme-suites-ihmp\database, or ask an administrator
echo to review .env before running any migration or seeder command.
goto :help

:shared_database_unavailable
echo ERROR: The shared database migration status could not be read.
echo No migration or seeder was run. Check the connection with an administrator.
goto :help

:app_failed
echo ERROR: The application settings could not be loaded safely.
echo No migration or seeder was run. Complete dependency setup and review .env.
goto :help

:failed
echo.
echo ERROR: Setup stopped because a command failed. Read the message above.

:help
echo See le-charme-suites-ihmp\GROUPMATES_SETUP.md for troubleshooting.
pause
exit /b 1
