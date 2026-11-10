@echo off
setlocal EnableDelayedExpansion
title LogXense Enterprise - install Reverb, the queue worker and the scheduler as Windows services

rem ===========================================================================
rem  Installs the three always-on background processes LogXense Enterprise needs on the plant PC
rem  as Windows services that start with Windows and restart themselves if they die:
rem
rem    BMS-Reverb     php artisan reverb:start    the WebSocket server for live updates
rem    BMS-Queue      php artisan queue:work      alert e-mails, AI chat replies
rem    BMS-Scheduler  php artisan schedule:work   offline detection, per-minute summaries, pruning
rem
rem  install.bat (the full installer) runs this for you; run it on its own to reinstall just these.
rem
rem  It uses NSSM ("the Non-Sucking Service Manager"), free and public domain:
rem      winget install NSSM.NSSM
rem  then copy nssm.exe (the win64 one) to C:\tools\nssm\nssm.exe.
rem
rem  Nothing to edit. The script finds:
rem    the project   - the folder this script is in (two levels up)
rem    php.exe       - "where php", else C:\xampp\php\php.exe
rem    nssm.exe      - C:\tools\nssm\nssm.exe, else "where nssm"
rem    Reverb's port - REVERB_SERVER_PORT in .env, else 8080 (Reverb's own default)
rem  To use a different php.exe or nssm.exe, set PHP=... or NSSM=... before running it.
rem
rem    install-services.bat              install or replace the services (run as administrator)
rem    install-services.bat check        show what it would use; changes nothing, no admin needed
rem    install-services.bat unattended   install without waiting for a key press (install.bat uses it)
rem
rem  Safe to re-run: existing services are replaced, which is how you apply new paths.
rem ===========================================================================

set "MODE=%~1"
set "PAUSE=pause"
if /i "%MODE%"=="unattended" set "PAUSE=rem"

rem ---- Find everything ------------------------------------------------------
for %%I in ("%~dp0..\..") do set "APP=%%~fI"

if not defined PHP for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP set "PHP=%%P"
if not defined PHP if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"

if not defined NSSM if exist "C:\tools\nssm\nssm.exe" set "NSSM=C:\tools\nssm\nssm.exe"
if not defined NSSM for /f "delims=" %%N in ('where nssm 2^>nul') do if not defined NSSM set "NSSM=%%N"

set "REVERB_PORT="
set "PORT_FROM=Reverb's default"
if exist "%APP%\.env" (
    for /f "usebackq tokens=1,* delims==" %%A in ("%APP%\.env") do (
        if /i "%%A"=="REVERB_SERVER_PORT" (
            set "REVERB_PORT=%%B"
            set "PORT_FROM=.env"
        )
    )
)
if defined REVERB_PORT (
    set "REVERB_PORT=!REVERB_PORT:"=!"
    set "REVERB_PORT=!REVERB_PORT: =!"
)
if not defined REVERB_PORT set "REVERB_PORT=8080"
echo !REVERB_PORT!| findstr /r "^[0-9][0-9]*$" >nul
if errorlevel 1 (
    echo REVERB_SERVER_PORT in .env is not a number: "!REVERB_PORT!"
    goto :fail
)

if /i "%MODE%"=="check" goto :check

rem ---- Install -------------------------------------------------------------
net session >nul 2>&1
if errorlevel 1 (
    echo This script must be run as Administrator ^(right-click -^> Run as administrator^).
    goto :fail
)

if not defined PHP              ( echo php.exe not found. Install XAMPP, or set PHP=C:\path\to\php.exe first. & goto :fail )
if not exist "%PHP%"            ( echo php.exe not found:           %PHP%  & goto :fail )
if not exist "%APP%\artisan"    ( echo Laravel app not found at:    %APP%  & goto :fail )
if not defined NSSM             ( echo nssm.exe not found. Run: winget install NSSM.NSSM, then copy nssm.exe to C:\tools\nssm\ & goto :fail )
if not exist "%NSSM%"           ( echo nssm.exe not found:          %NSSM% & goto :fail )
if not exist "%APP%\.env"       ( echo %APP%\.env is missing - copy .env.example to .env and fill it in first. & goto :fail )

echo Project : %APP%
echo PHP     : %PHP%
echo NSSM    : %NSSM%
echo Reverb  : port %REVERB_PORT% ^(%PORT_FROM%^)
echo.

rem Stop (not remove) any earlier install so its Reverb lets go of the port before the check below.
set "HAD_SERVICES="
for %%S in (BMS-Reverb BMS-Queue BMS-Scheduler) do (
    sc query %%S >nul 2>&1
    if not errorlevel 1 (
        set "HAD_SERVICES=!HAD_SERVICES! %%S"
        call :stop_service %%S
    )
)
if defined HAD_SERVICES ping -n 3 127.0.0.1 >nul

call :port_owner %REVERB_PORT%
if defined PORT_PID (
    echo Port %REVERB_PORT% is already used by !PORT_NAME! ^(PID !PORT_PID!^), so Reverb could not start.
    echo Close that program - for example a "composer run dev" or "php artisan reverb:start" window -
    echo or set another REVERB_SERVER_PORT in .env, then run this again.
    if defined HAD_SERVICES (
        for %%S in (!HAD_SERVICES!) do "%NSSM%" start %%S >nul 2>&1
        echo The services that were already installed have been started again, unchanged.
    )
    goto :fail
)

if not exist "%APP%\storage\logs" mkdir "%APP%\storage\logs"

call :install BMS-Reverb "artisan reverb:start" "%APP%\storage\logs\reverb-service.log"
if errorlevel 1 goto :fail

call :install BMS-Queue "artisan queue:work --sleep=1 --tries=3 --max-time=3600" "%APP%\storage\logs\queue-service.log"
if errorlevel 1 goto :fail

call :install BMS-Scheduler "artisan schedule:work" "%APP%\storage\logs\scheduler-service.log"
if errorlevel 1 goto :fail

rem NSSM reports a service as started even if its program then exits at once, so wait for
rem Reverb to actually listen before calling it a success. The first start as a service can
rem take 20 seconds or more (Windows scans php.exe for the new account), so allow a minute,
rem and stop early only if the service itself has stopped or NSSM paused it after crashes.
set "LISTENING="
set "SERVICE_DIED="
<nul set /p "=[..] Waiting for Reverb to listen on port %REVERB_PORT% "
for /l %%T in (1,1,60) do (
    if not defined LISTENING if not defined SERVICE_DIED (
        ping -n 2 127.0.0.1 >nul
        <nul set /p "=."
        call :port_owner %REVERB_PORT%
        if defined PORT_PID set "LISTENING=1"
        sc query BMS-Reverb | findstr /c:"STOPPED" /c:"PAUSED" >nul && set "SERVICE_DIED=1"
    )
)
echo.
if not defined LISTENING (
    if defined SERVICE_DIED (
        echo [xx] BMS-Reverb stopped instead of listening on port %REVERB_PORT%.
    ) else (
        echo [xx] BMS-Reverb is running but still not listening on port %REVERB_PORT% after a minute.
        echo      If the log below ends with "Starting server", it may just be slow:
        echo      run "install-services.bat check" in a minute, then run this script again.
    )
    echo      See %APP%\storage\logs\reverb-service.log
    goto :fail
)
echo [ok] Reverb is listening on port %REVERB_PORT%.

rem Domain and Private profiles (a company PC on the plant LAN is usually "Domain"), never Public.
netsh advfirewall firewall delete rule name="BMS Reverb (private network)" >nul 2>&1
netsh advfirewall firewall delete rule name="LogXense Reverb (LAN)" >nul 2>&1
netsh advfirewall firewall add rule name="LogXense Reverb (LAN)" dir=in action=allow protocol=TCP localport=%REVERB_PORT% profile=domain,private >nul
if errorlevel 1 ( echo Could not add the firewall rule. & goto :fail )
echo [ok] Firewall: inbound TCP %REVERB_PORT% allowed on Domain and Private networks.

echo.
echo ===========================================================================
echo  Done. The three services are running and will start automatically with Windows.
echo.
echo    Check:    sc query BMS-Reverb      sc query BMS-Queue      sc query BMS-Scheduler
echo    Logs:     %APP%\storage\logs\reverb-service.log
echo              %APP%\storage\logs\queue-service.log
echo              %APP%\storage\logs\scheduler-service.log
echo    After a code or .env change run, in %APP%:
echo              php artisan reverb:restart
echo              php artisan queue:restart
echo.
echo  Also needed on this PC: a fixed IP address ^(router DHCP reservation^), and
echo  Power Options set to never sleep. See docs/09-setup-and-operations.md.
echo ===========================================================================
%PAUSE%
exit /b 0

rem ---- check: report only ---------------------------------------------------
:check
set "PROBLEMS=0"
echo LogXense Enterprise - service check ^(nothing is changed^)
echo.

if exist "%APP%\artisan" (
    echo [ok] Project : %APP%
) else (
    echo [xx] Project : no artisan file in %APP%
    set /a PROBLEMS+=1
)

if exist "%APP%\.env" (
    echo [ok] .env    : found
) else (
    echo [xx] .env    : missing - copy .env.example to .env and fill it in
    set /a PROBLEMS+=1
)

set "PHP_VERSION="
if defined PHP if exist "%PHP%" for /f "tokens=1,2" %%A in ('""%PHP%" -v" 2^>nul') do if not defined PHP_VERSION if "%%A"=="PHP" set "PHP_VERSION=%%B"
if defined PHP_VERSION (
    echo [ok] PHP     : %PHP%  ^(PHP !PHP_VERSION!^)
) else (
    echo [xx] PHP     : php.exe not found - install XAMPP, or set PHP=C:\path\to\php.exe
    set /a PROBLEMS+=1
)

set "NSSM_VERSION="
if defined NSSM if exist "%NSSM%" for /f "tokens=1,2" %%A in ('""%NSSM%" version" 2^>nul') do if not defined NSSM_VERSION set "NSSM_VERSION=%%A %%B"
if defined NSSM_VERSION (
    echo [ok] NSSM    : %NSSM%  ^(!NSSM_VERSION!^)
) else (
    echo [xx] NSSM    : nssm.exe not found - winget install NSSM.NSSM, then copy nssm.exe to C:\tools\nssm\
    set /a PROBLEMS+=1
)

set "REVERB_SERVICE="
for /f "tokens=4" %%S in ('sc query BMS-Reverb 2^>nul ^| findstr /c:"STATE"') do set "REVERB_SERVICE=%%S"
call :port_owner %REVERB_PORT%
if not defined PORT_PID (
    echo [ok] Reverb  : port %REVERB_PORT% ^(%PORT_FROM%^) is free
) else if defined REVERB_SERVICE (
    echo [ok] Reverb  : port %REVERB_PORT% ^(%PORT_FROM%^) is in use by !PORT_NAME!, PID !PORT_PID! - probably the installed BMS-Reverb, which is replaced on install
) else (
    echo [xx] Reverb  : port %REVERB_PORT% ^(%PORT_FROM%^) is in use by !PORT_NAME! ^(PID !PORT_PID!^) - close it before installing
    set /a PROBLEMS+=1
)

for %%S in (BMS-Reverb BMS-Queue BMS-Scheduler) do (
    set "STATE=not installed"
    for /f "tokens=4" %%T in ('sc query %%S 2^>nul ^| findstr /c:"STATE"') do set "STATE=%%T"
    echo [--] %%S : !STATE!
)

echo.
if "%PROBLEMS%"=="0" (
    echo Ready. Right-click install-services.bat and choose "Run as administrator".
    exit /b 0
)
echo %PROBLEMS% problem^(s^) to fix before installing.
exit /b 1

rem ---- helpers --------------------------------------------------------------
:port_owner
rem %1 = port. Sets PORT_PID and PORT_NAME when something listens on it, clears them otherwise.
set "PORT_PID="
set "PORT_NAME="
for /f "tokens=5" %%P in ('netstat -ano ^| findstr /r /c:":%~1 .*LISTENING"') do if not defined PORT_PID set "PORT_PID=%%P"
if not defined PORT_PID exit /b 0
set "PORT_NAME=an unknown program"
for /f "tokens=1 delims=," %%N in ('tasklist /fi "PID eq %PORT_PID%" /fo csv /nh 2^>nul') do set "PORT_NAME=%%~N"
exit /b 0

:stop_service
rem %1 = service name. Stops it; if it is still not stopped after about 10 seconds, ends its
rem process tree (NSSM and the PHP under it), so a stop that hangs can't block a reinstall.
sc query %1 >nul 2>&1
if errorlevel 1 exit /b 0
"%NSSM%" stop %1 >nul 2>&1
set /a "WAITED=0"
:stop_service_wait
sc query %1 >nul 2>&1
if errorlevel 1 exit /b 0
sc query %1 | findstr /c:"STOPPED" >nul
if not errorlevel 1 exit /b 0
if !WAITED! GEQ 10 goto :stop_service_kill
ping -n 2 127.0.0.1 >nul
set /a "WAITED+=1"
goto :stop_service_wait
:stop_service_kill
echo [..] %1 did not stop on its own - ending its process.
for /f "tokens=3" %%P in ('sc queryex %1 ^| findstr /c:" PID "') do if not "%%P"=="0" taskkill /f /t /pid %%P >nul 2>&1
ping -n 3 127.0.0.1 >nul
exit /b 0

:remove_service
rem %1 = service name. Stops and deletes it, and waits until Windows has really deleted it:
rem until then a new service with the same name can't be created ("marked for deletion").
call :stop_service %1
"%NSSM%" remove %1 confirm >nul 2>&1
set /a "WAITED=0"
:remove_service_wait
sc query %1 >nul 2>&1
if errorlevel 1 exit /b 0
if !WAITED! GEQ 15 (
    echo [xx] Windows has not finished deleting the old %1 service.
    echo      Close the Services window and Task Manager if they are open, or restart the PC,
    echo      then run this again.
    exit /b 1
)
ping -n 2 127.0.0.1 >nul
set /a "WAITED+=1"
goto :remove_service_wait

:install
rem %1 = service name, %2 = php arguments, %3 = log file
sc query %1 >nul 2>&1
if not errorlevel 1 (
    echo [..] %1 already exists - replacing it.
    call :remove_service %1
    if errorlevel 1 exit /b 1
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
echo Fix the problem above and run this script again.
%PAUSE%
exit /b 1
