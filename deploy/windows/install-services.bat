@echo off
setlocal EnableDelayedExpansion
title LogXense Enterprise - install Reverb and queue worker as Windows services

rem ===========================================================================
rem  Installs the two always-on background processes LogXense Enterprise needs on the plant PC
rem  as Windows services that start with Windows and restart themselves if they die:
rem
rem    BMS-Reverb  php artisan reverb:start   the WebSocket server for live updates
rem    BMS-Queue   php artisan queue:work     AI chat replies, alert e-mails
rem
rem  It uses NSSM ("the Non-Sucking Service Manager") - free, public domain, a single
rem  nssm.exe from https://nssm.cc/download (no install, no licence, no account).
rem
rem  Safe to re-run: an existing service is replaced, which is how you apply new paths.
rem  Run it by right-click -> "Run as administrator".
rem ===========================================================================

rem ---- EDIT THESE THREE PATHS FOR THIS PC ------------------------------------
set "PHP=C:\laragon\bin\php\php-8.2.0\php.exe"
set "APP=C:\laragon\www\BMS_VISHAY"
set "NSSM=C:\tools\nssm\nssm.exe"
rem ---- the port Reverb listens on (must match REVERB_SERVER_PORT in .env) -----
set "REVERB_PORT=8080"
rem -----------------------------------------------------------------------------

net session >nul 2>&1
if errorlevel 1 (
    echo This script must be run as Administrator ^(right-click -^> Run as administrator^).
    goto :fail
)

if not exist "%PHP%"            ( echo php.exe not found:           %PHP%  & goto :fail )
if not exist "%APP%\artisan"    ( echo Laravel app not found at:    %APP%  & goto :fail )
if not exist "%NSSM%"           ( echo nssm.exe not found:          %NSSM% & goto :fail )
if not exist "%APP%\.env"       ( echo %APP%\.env is missing - copy .env.example to .env and fill it in first. & goto :fail )

if not exist "%APP%\storage\logs" mkdir "%APP%\storage\logs"

call :install BMS-Reverb "artisan reverb:start" "%APP%\storage\logs\reverb-service.log"
if errorlevel 1 goto :fail

call :install BMS-Queue "artisan queue:work --sleep=1 --tries=3 --max-time=3600" "%APP%\storage\logs\queue-service.log"
if errorlevel 1 goto :fail

rem Private network profile only - the plant LAN - never public Wi-Fi.
netsh advfirewall firewall delete rule name="BMS Reverb (private network)" >nul 2>&1
netsh advfirewall firewall add rule name="BMS Reverb (private network)" dir=in action=allow protocol=TCP localport=%REVERB_PORT% profile=private >nul
if errorlevel 1 ( echo Could not add the firewall rule. & goto :fail )
echo [ok] Firewall: inbound TCP %REVERB_PORT% allowed on the Private network profile.

echo.
echo ===========================================================================
echo  Done. Both services are running and will start automatically with Windows.
echo.
echo    Check:    sc query BMS-Reverb      sc query BMS-Queue
echo    Logs:     %APP%\storage\logs\reverb-service.log
echo    After a code or .env change run, in %APP%:
echo              php artisan reverb:restart
echo              php artisan queue:restart
echo.
echo  Also needed on this PC: a fixed IP address ^(router DHCP reservation^), and
echo  Power Options set to never sleep. See docs/09-setup-and-operations.md.
echo ===========================================================================
pause
exit /b 0

:install
rem %1 = service name, %2 = php arguments, %3 = log file
sc query %1 >nul 2>&1
if not errorlevel 1 (
    echo [..] %1 already exists - replacing it.
    "%NSSM%" stop %1 >nul 2>&1
    "%NSSM%" remove %1 confirm >nul 2>&1
)
"%NSSM%" install %1 "%PHP%" %~2 >nul
if errorlevel 1 ( echo Could not install %1. & exit /b 1 )
"%NSSM%" set %1 AppDirectory "%APP%" >nul
"%NSSM%" set %1 DisplayName "LogXense Enterprise %~1" >nul
"%NSSM%" set %1 Start SERVICE_AUTO_START >nul
rem Restart whenever the process exits, whatever the exit code - the queue worker exits on
rem purpose every hour (--max-time) to release memory.
"%NSSM%" set %1 AppExit Default Restart >nul
"%NSSM%" set %1 AppRestartDelay 5000 >nul
"%NSSM%" set %1 AppStdout %3 >nul
"%NSSM%" set %1 AppStderr %3 >nul
"%NSSM%" set %1 AppRotateFiles 1 >nul
"%NSSM%" set %1 AppRotateOnline 1 >nul
"%NSSM%" set %1 AppRotateBytes 5242880 >nul
"%NSSM%" start %1 >nul
if errorlevel 1 ( echo %1 installed but did not start - see %3 & exit /b 1 )
echo [ok] %1 installed and started.
exit /b 0

:fail
echo.
echo Nothing more was changed. Fix the problem above and run this script again.
pause
exit /b 1
