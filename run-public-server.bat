@echo off
setlocal
title Le Charme Suites IHMP - Public Link
set "ROOT=%~dp0"
set "APP=%ROOT%le-charme-suites-ihmp"
set "LOG=%TEMP%\lecharme-tunnel.log"

cd /d "%APP%" || goto :eof
php -c "%ROOT%php-local.ini" artisan optimize:clear >nul
php -c "%ROOT%php-local.ini" artisan migrate --force

echo Starting the hotel system on this PC (port 8000)...
start "Le Charme Suites - Server" /min php -c "%ROOT%php-local.ini" -S 127.0.0.1:8000 -t "%APP%\public" "%APP%\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"

echo Opening the public Cloudflare link...
if exist "%LOG%" del "%LOG%"
start "Le Charme Suites - Public Tunnel" /min cloudflared tunnel --no-autoupdate --url http://127.0.0.1:8000 --logfile "%LOG%"

echo Waiting for the public link (about 10 seconds)...
powershell -NoProfile -Command "for($i=0;$i -lt 30;$i++){Start-Sleep 2; if(Test-Path $env:LOG){$m=Select-String -Path $env:LOG -Pattern 'https://[a-z0-9-]+\.trycloudflare\.com'|Select -First 1; if($m){Write-Host ''; Write-Host ('PUBLIC LINK: '+$m.Matches[0].Value) -ForegroundColor Green; exit}}}; Write-Host 'Could not read the link. Check the Public Tunnel window.'"
echo.
echo Share the link above. Keep this PC on and the two minimized windows open.
echo Close the "Server" and "Public Tunnel" windows to take the system offline.
pause
