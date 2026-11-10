@echo off
setlocal EnableDelayedExpansion
title LogXense Enterprise - installer

rem ===========================================================================
rem  Full install (or update) of LogXense Enterprise on a Windows PC with XAMPP, no Docker:
rem
rem    1. checks the prerequisites       PHP 8.2+ (XAMPP), Composer, Node.js, XAMPP Apache, NSSM
rem    2. PHP settings                   offers to switch on OPcache and intl in php.ini
rem    3. asks a few questions           website port (80 or 8080), this PC's IP, MySQL login
rem                                      and writes .env (production mode, fresh secrets)
rem    4. PHP packages                   composer install --no-dev
rem    5. database                       creates it, migrates, seeds the admin and demo machine once
rem    6. front-end                      npm ci, npm run build
rem    7. website                        XAMPP Apache as a Windows service, on your port only
rem    8. background services            Reverb (live updates, port 8081), queue worker, scheduler
rem    9. tests it                       test-install.bat
rem
rem    install.bat          install, or update after a "git pull" (run as administrator)
rem    install.bat check    only check the prerequisites; changes nothing, no admin needed
rem
rem  Nothing to edit. Safe to run again: it keeps .env (and backs it up), never re-seeds,
rem  and replaces the services. Prerequisites are listed in docs\12-software-requirements.md.
rem ===========================================================================

set "MODE=%~1"
for %%I in ("%~dp0..\..") do set "APP=%%~fI"
set "HELPER=%~dp0installer.php"
set "IS_DOWN="

if not defined PHP for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP set "PHP=%%P"
if not defined PHP if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"
if not defined NSSM if exist "C:\tools\nssm\nssm.exe" set "NSSM=C:\tools\nssm\nssm.exe"
if not defined NSSM for /f "delims=" %%N in ('where nssm 2^>nul') do if not defined NSSM set "NSSM=%%N"
if not defined HTTPD if exist "C:\xampp\apache\bin\httpd.exe" set "HTTPD=C:\xampp\apache\bin\httpd.exe"

echo ===========================================================================
echo  LogXense Enterprise - installer
echo  Project: %APP%
echo ===========================================================================
echo.

if not defined PHP (
    echo [xx] php.exe not found. Install XAMPP with PHP 8.2 ^(https://www.apachefriends.org/^),
    echo      or set PHP=C:\path\to\php.exe first.
    goto :fail
)

if /i "%MODE%"=="check" (
    "%PHP%" "%HELPER%" check --httpd="%HTTPD%" --nssm="%NSSM%"
    exit /b !errorlevel!
)

net session >nul 2>&1
if errorlevel 1 (
    echo This installer must be run as Administrator ^(right-click install.bat -^> Run as administrator^).
    goto :fail
)

cd /d "%APP%"

echo [1/9] Prerequisites
"%PHP%" "%HELPER%" check --httpd="%HTTPD%" --nssm="%NSSM%"
if errorlevel 1 goto :fail
echo.

echo [2/9] PHP settings
"%PHP%" "%HELPER%" php-ini
echo.

echo [3/9] Settings
"%PHP%" "%HELPER%" configure
if errorlevel 1 goto :fail
set "WEB_PORT="
for /f "delims=" %%P in ('""%PHP%" "%HELPER%" web-port"') do set "WEB_PORT=%%P"
if not defined WEB_PORT ( echo [xx] Could not read the website port from APP_URL in .env. & goto :fail )
echo.

echo [4/9] PHP packages
rem Stop the background services so they don't run half-updated code, and show visitors a
rem "back in a moment" page during an update.
for %%S in (BMS-Reverb BMS-Queue BMS-Scheduler) do (
    sc query %%S >nul 2>&1
    if not errorlevel 1 if defined NSSM "%NSSM%" stop %%S >nul 2>&1
)
if exist "%APP%\vendor\autoload.php" (
    "%PHP%" artisan down --retry=15 >nul 2>&1
    if not errorlevel 1 set "IS_DOWN=1"
)
if exist "%APP%\vendor\laravel\boost" (
    echo [--] This copy has the development packages ^(tests, tooling^). A production install removes them.
    choice /c YN /m "     Continue"
    if errorlevel 2 goto :fail
)
rem Cached config/routes from an earlier install could name packages that are about to go.
del /q "%APP%\bootstrap\cache\*.php" >nul 2>&1
call composer install --no-dev --optimize-autoloader --no-interaction --no-progress
if errorlevel 1 ( echo [xx] composer install failed - see above. & goto :fail )
echo [ok] PHP packages installed
echo.

echo [5/9] Database
set "FIRST_RUN="
"%PHP%" artisan migrate:status >nul 2>&1
if errorlevel 1 set "FIRST_RUN=1"
"%PHP%" artisan migrate --force
if errorlevel 1 ( echo [xx] The database migration failed - see above. & goto :fail )
if defined FIRST_RUN (
    echo [..] First install: adding the admin and viewer accounts and the demo machine
    "%PHP%" artisan db:seed --force
    if errorlevel 1 ( echo [xx] Seeding failed - see above. & goto :fail )
)
"%PHP%" artisan storage:link >nul 2>&1
echo [ok] Database ready
echo.

echo [6/9] Front-end (CSS and JavaScript)
rem public\hot is left by "npm run dev" and would make every page load from a dev server.
if exist "%APP%\public\hot" del /q "%APP%\public\hot"
call npm ci --no-audit --no-fund
if errorlevel 1 ( echo [xx] npm ci failed - see above. & goto :fail )
call npm run build
if errorlevel 1 ( echo [xx] npm run build failed - see above. & goto :fail )
"%PHP%" artisan optimize
if errorlevel 1 ( echo [xx] php artisan optimize failed - see above. & goto :fail )
if defined IS_DOWN (
    "%PHP%" artisan up >nul 2>&1
    set "IS_DOWN="
)
echo [ok] Front-end built and caches warmed
echo.

echo [7/9] Website (XAMPP Apache on port %WEB_PORT%)
"%PHP%" "%HELPER%" apache --httpd="%HTTPD%"
if errorlevel 1 goto :fail
sc query Apache2.4 >nul 2>&1
if errorlevel 1 (
    tasklist /fi "imagename eq httpd.exe" 2>nul | find /i "httpd.exe" >nul
    if not errorlevel 1 (
        echo [xx] Apache is running from the XAMPP Control Panel. Click Stop next to Apache there,
        echo      close the Control Panel, then run this again - it is installed as a service next.
        goto :fail
    )
    "%HTTPD%" -k install -n Apache2.4 >nul 2>&1
    if errorlevel 1 ( echo [xx] Could not install Apache as a Windows service. & goto :fail )
    echo [ok] Apache installed as the Windows service Apache2.4
)
net stop Apache2.4 >nul 2>&1
call :port_owner %WEB_PORT%
if defined PORT_PID (
    echo [xx] Port %WEB_PORT% is already used by !PORT_NAME! ^(PID !PORT_PID!^).
    if "!PORT_PID!"=="4" echo      PID 4 is Windows' own web server ^(IIS / "World Wide Web Publishing Service"^): stop and disable it, or run this again and choose port 8080.
    echo      Close that program, or run this again and choose another website port.
    goto :fail
)
sc config Apache2.4 start= auto >nul
net start Apache2.4 >nul 2>&1
if errorlevel 1 (
    echo [xx] Apache did not start. The end of its error log:
    powershell -NoProfile -Command "Get-Content -Tail 12 '%HTTPD%\..\..\logs\error.log'"
    goto :fail
)
set "WEB_OK="
<nul set /p "=[..] Waiting for the website "
for /l %%T in (1,1,30) do (
    if not defined WEB_OK (
        for /f %%C in ('curl.exe -s -o nul -w "%%{http_code}" --max-time 5 http://127.0.0.1:%WEB_PORT%/up 2^>nul') do if "%%C"=="200" set "WEB_OK=1"
        if not defined WEB_OK (
            <nul set /p "=."
            ping -n 2 127.0.0.1 >nul
        )
    )
)
echo.
if not defined WEB_OK (
    echo [xx] Apache is running but http://127.0.0.1:%WEB_PORT%/up does not answer 200.
    echo      See %APP%\storage\logs\laravel.log and the XAMPP logs\logxense-error.log
    goto :fail
)
echo [ok] Website answers on port %WEB_PORT%
netsh advfirewall firewall delete rule name="LogXense website (LAN)" >nul 2>&1
netsh advfirewall firewall add rule name="LogXense website (LAN)" dir=in action=allow protocol=TCP localport=%WEB_PORT% profile=domain,private >nul
if errorlevel 1 ( echo [xx] Could not add the firewall rule for port %WEB_PORT%. & goto :fail )
echo [ok] Firewall: inbound TCP %WEB_PORT% allowed on Domain and Private networks
echo.

echo [8/9] Background services
call "%~dp0install-services.bat" unattended
if errorlevel 1 goto :fail
echo.

echo [9/9] Testing the installation
"%PHP%" "%HELPER%" test
set "TEST_FAILED=%errorlevel%"
echo.

set "APP_URL="
set "DEVICE_KEY="
for /f "delims=" %%U in ('""%PHP%" "%HELPER%" get APP_URL"') do set "APP_URL=%%U"
for /f "delims=" %%K in ('""%PHP%" "%HELPER%" get DEMO_DEVICE_KEY"') do set "DEVICE_KEY=%%K"

echo ===========================================================================
if "%TEST_FAILED%"=="0" (
    echo  LogXense Enterprise is installed and running.
) else (
    echo  LogXense Enterprise is installed, but some checks failed - see [xx] above.
    echo  Fix them, then run test-install.bat to check again.
)
echo.
echo    Open:        !APP_URL!   ^(from any PC on the network^)
if defined FIRST_RUN (
    echo    Sign in:     admin@example.com / password    ^(change it: Users^)
    echo                 viewer@example.com / password   ^(read-only^)
)
echo    ESP32 posts: !APP_URL!/api/machine-logs
if defined DEVICE_KEY echo                 header X-Device-Key: !DEVICE_KEY!   ^(demo machine DEMO-001^)
echo    Live updates use port 8081 - nothing to set in the browser.
echo.
echo    After an update ^("git pull"^), run install.bat again - it is safe to repeat.
echo    Check any time:  deploy\windows\test-install.bat
echo ===========================================================================
pause
exit /b %TEST_FAILED%

rem ---- helpers ------------------------------------------------------------------
:port_owner
rem %1 = port. Sets PORT_PID and PORT_NAME when something listens on it, clears them otherwise.
set "PORT_PID="
set "PORT_NAME="
for /f "tokens=5" %%P in ('netstat -ano ^| findstr /r /c:":%~1 .*LISTENING"') do if not defined PORT_PID set "PORT_PID=%%P"
if not defined PORT_PID exit /b 0
set "PORT_NAME=an unknown program"
for /f "tokens=1 delims=," %%N in ('tasklist /fi "PID eq %PORT_PID%" /fo csv /nh 2^>nul') do set "PORT_NAME=%%~N"
exit /b 0

:fail
if defined IS_DOWN "%PHP%" "%APP%\artisan" up >nul 2>&1
echo.
echo Fix the problem above and run install.bat again ^(it is safe to repeat^).
pause
exit /b 1
