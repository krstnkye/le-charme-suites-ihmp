@echo off
setlocal
title Le Charme Suites IHMS Shared LAN Server

cd /d "%~dp0le-charme-suites-ihms"
if errorlevel 1 goto :wrong_folder

where php >nul 2>nul
if errorlevel 1 goto :missing_php

if not exist .env goto :not_configured
if not exist database\database.sqlite goto :not_configured

echo.
echo Le Charme Suites IHMS - Shared Offline / LAN Mode
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
echo ERROR: PHP was not found. Install PHP 8.3 or newer and add it to PATH.
pause
exit /b 1

:wrong_folder
echo ERROR: The le-charme-suites-ihms application folder was not found.
pause
exit /b 1
