@echo off
setlocal
title LogXense Enterprise - installation test

rem ===========================================================================
rem  Checks that a Windows install of LogXense Enterprise is complete and working. It needs no
rem  admin rights. It changes nothing except:
rem    - it sends one test reading to the demo machine as the ESP32 would; the reading shows in
rem      its logs as "Installation test"
rem    - it runs one empty job through the queue
rem
rem    test-install.bat             full test
rem    test-install.bat --no-post   skip the test reading
rem
rem  What it checks:
rem    files      .env, vendor, the built CSS/JS, no leftover public\hot
rem    services   Apache2.4, BMS-Reverb, BMS-Queue, BMS-Scheduler running and set to auto-start
rem    app        database connection, migrations, users, the queue worker, the scheduler
rem    website    /up and the login page, on 127.0.0.1 and on the IP in APP_URL
rem    live       a browser-style WebSocket to Reverb, a signed private-channel subscription,
rem               a device POST, and that the reading is pushed back over the WebSocket
rem    firewall   the inbound rules for the website and Reverb ports
rem ===========================================================================

if not defined PHP for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP set "PHP=%%P"
if not defined PHP if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"
if not defined PHP (
    echo [xx] php.exe not found. Install XAMPP, or set PHP=C:\path\to\php.exe first.
    pause
    exit /b 1
)

echo LogXense Enterprise - installation test
echo.
"%PHP%" "%~dp0installer.php" test %*
set "RESULT=%errorlevel%"
echo.
pause
exit /b %RESULT%
