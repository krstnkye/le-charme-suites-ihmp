@echo off
setlocal
title Le Charme Suites IHMP Update

cd /d "%~dp0"

where git >nul 2>nul
if errorlevel 1 goto :missing_git

echo.
echo [1/2] Downloading the latest approved project updates...
git pull --ff-only origin main
if errorlevel 1 goto :pull_failed

echo [2/2] Applying dependencies, database updates, and cache cleanup...
call "%~dp0setup-and-run.bat"
exit /b %errorlevel%

:missing_git
echo ERROR: Git was not found. Install Git and reopen the terminal.
pause
exit /b 1
:pull_failed
echo.
echo Git could not update safely. If you edited files locally, commit or copy
echo those changes before trying again. Ask the group repository owner for help.
pause
exit /b 1

:end

