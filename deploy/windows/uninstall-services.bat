@echo off
setlocal
title LogXense Enterprise - remove Reverb and queue worker services

rem Removes the services and firewall rule created by install-services.bat.
rem Run it by right-click -> "Run as administrator". Same NSSM path as the installer.

set "NSSM=C:\tools\nssm\nssm.exe"

net session >nul 2>&1
if errorlevel 1 (
    echo This script must be run as Administrator ^(right-click -^> Run as administrator^).
    pause
    exit /b 1
)

if not exist "%NSSM%" (
    echo nssm.exe not found: %NSSM%
    pause
    exit /b 1
)

for %%S in (BMS-Reverb BMS-Queue) do (
    sc query %%S >nul 2>&1
    if not errorlevel 1 (
        "%NSSM%" stop %%S >nul 2>&1
        "%NSSM%" remove %%S confirm >nul 2>&1
        echo [ok] Removed %%S
    ) else (
        echo [--] %%S was not installed
    )
)

netsh advfirewall firewall delete rule name="BMS Reverb (private network)" >nul 2>&1
echo [ok] Firewall rule removed.

echo.
echo The app keeps working without these - the machine page simply polls instead of live-pushing.
pause
