@echo off
setlocal
title LogXense Enterprise - remove the Reverb, queue worker and scheduler services

rem Removes the services and firewall rule created by install-services.bat.
rem Run it by right-click -> "Run as administrator". Finds nssm.exe the same way the
rem installer does: NSSM=... if set, else C:\tools\nssm\nssm.exe, else "where nssm".
rem Apache and the website are left as they are.

if not defined NSSM if exist "C:\tools\nssm\nssm.exe" set "NSSM=C:\tools\nssm\nssm.exe"
if not defined NSSM for /f "delims=" %%N in ('where nssm 2^>nul') do if not defined NSSM set "NSSM=%%N"

net session >nul 2>&1
if errorlevel 1 (
    echo This script must be run as Administrator ^(right-click -^> Run as administrator^).
    pause
    exit /b 1
)

if not defined NSSM (
    echo nssm.exe not found. Run: winget install NSSM.NSSM, then copy nssm.exe to C:\tools\nssm\
    pause
    exit /b 1
)
if not exist "%NSSM%" (
    echo nssm.exe not found: %NSSM%
    pause
    exit /b 1
)

for %%S in (BMS-Reverb BMS-Queue BMS-Scheduler) do (
    sc query %%S >nul 2>&1
    if not errorlevel 1 (
        "%NSSM%" stop %%S >nul 2>&1
        rem A service that still has a process after the stop is hung: end it, or Windows
        rem leaves the service "marked for deletion" and it can't be installed again.
        for /f "tokens=3" %%P in ('sc queryex %%S ^| findstr /c:" PID "') do if not "%%P"=="0" taskkill /f /t /pid %%P >nul 2>&1
        "%NSSM%" remove %%S confirm >nul 2>&1
        echo [ok] Removed %%S
    ) else (
        echo [--] %%S was not installed
    )
)

netsh advfirewall firewall delete rule name="LogXense Reverb (LAN)" >nul 2>&1
netsh advfirewall firewall delete rule name="BMS Reverb (private network)" >nul 2>&1
echo [ok] Reverb firewall rule removed.

echo.
echo The website keeps working without these - the machine page simply polls instead of
echo live-pushing - but offline detection and alert e-mails need the scheduler and queue worker.
pause
