@echo off
setlocal
title Le Charme Suites IHMP Update

cd /d "%~dp0"

where git >nul 2>nul
if errorlevel 1 goto :missing_git

set "CURRENT_BRANCH="
for /f "delims=" %%B in ('git rev-parse --abbrev-ref HEAD 2^>nul') do set "CURRENT_BRANCH=%%B"
if not defined CURRENT_BRANCH goto :branch_failed
if /I "%CURRENT_BRANCH%"=="HEAD" goto :detached_head

echo.
echo [1/2] Downloading approved updates for %CURRENT_BRANCH%...
git pull --ff-only origin "%CURRENT_BRANCH%"
if errorlevel 1 goto :pull_failed

echo [2/2] Applying dependencies, database updates, and cache cleanup...
call "%~dp0setup-and-run.bat"
exit /b %errorlevel%

:missing_git
echo ERROR: Git was not found. Install Git and reopen the terminal.
pause
exit /b 1

:branch_failed
echo ERROR: Git could not identify the current branch.
echo Open the project from a normal branch, then try again.
pause
exit /b 1

:detached_head
echo ERROR: This project is not currently on a named branch.
echo Switch to the approved team branch before downloading updates.
pause
exit /b 1

:pull_failed
echo.
echo Git could not fast-forward %CURRENT_BRANCH% safely.
echo If you edited files locally, commit or copy those changes before trying again.
echo Ask the group repository owner for help instead of forcing the update.
pause
exit /b 1

:end

