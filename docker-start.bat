@echo off
setlocal EnableDelayedExpansion
title LogXense Enterprise - start with Docker
cd /d "%~dp0"

docker info >nul 2>&1
if errorlevel 1 (
    echo Docker is not running. Please start Docker Desktop, wait until it says "Engine running", then run this again.
    pause
    exit /b 1
)

rem Stop any earlier LogXense Enterprise run first (keeps the data) so it doesn't hold the ports we are about to check.
docker compose down >nul 2>&1

rem ---- Pick the website port: 80 if it is free, otherwise 8080 --------------------------------
rem (set APP_PORT yourself before running this file to force a specific port)
set "PORT_FELL_BACK=0"
if defined APP_PORT (
    call :port_free %APP_PORT%
    if errorlevel 1 goto :busy_pinned
) else (
    call :port_free 80
    if not errorlevel 1 (
        set "APP_PORT=80"
    ) else (
        call :port_free 8080
        if errorlevel 1 goto :busy_both
        set "APP_PORT=8080"
        set "PORT_FELL_BACK=1"
    )
)

rem Live chart updates (Reverb) always use 8081.
call :port_free 8081
if errorlevel 1 goto :busy_live

set "PORT_SUFFIX=:%APP_PORT%"
if "%APP_PORT%"=="80" set "PORT_SUFFIX="

if "%PORT_FELL_BACK%"=="1" echo Port 80 is in use by another program, so LogXense Enterprise will use port 8080 instead.
echo Building and starting LogXense Enterprise. The first time takes several minutes - please wait...
docker compose up -d --build --wait
if errorlevel 1 (
    echo.
    echo Something went wrong. Run "docker compose logs" to see why, and send that to whoever shared this project.
    pause
    exit /b 1
)

echo.
echo ================================================================
echo   LogXense Enterprise is running
echo.
echo   Open:     http://localhost%PORT_SUFFIX%
echo.
echo   Admin:    admin@example.com   /  password   (full access)
echo   Viewer:   viewer@example.com  /  password   (machines - no create - and alerts)
echo.
echo   NodeMCU - POST sensor readings to one of these addresses:
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do (
    set "IP=%%a"
    set "IP=!IP: =!"
    echo        http://!IP!%PORT_SUFFIX%/api/machine-logs
)
echo   Header:   X-Device-Key: nodemcu-demo-key
echo   Body:     {"log_type":"sensor_reading","readings":{"temperature":25.4,"humidity":61}}
echo ================================================================
echo.
start "" http://localhost%PORT_SUFFIX%
pause
exit /b 0

:busy_pinned
echo.
echo Port %APP_PORT% is already used by another program.
echo Close that program, or pick a different port:
echo     set APP_PORT=8090
echo     docker-start.bat
echo ^(run both lines in the same Command Prompt window^)
echo.
pause
exit /b 1

:busy_both
echo.
echo Both port 80 and port 8080 are used by other programs.
echo Close one of them, or pick a different port:
echo     set APP_PORT=8090
echo     docker-start.bat
echo ^(run both lines in the same Command Prompt window^)
echo.
pause
exit /b 1

:busy_live
echo.
echo Port 8081 ^(live chart updates^) is already used by another program. Close it and run this again.
echo.
pause
exit /b 1

rem Usage: call :port_free <port>   -> errorlevel 0 when nothing is listening on it, 1 when it is taken
:port_free
netstat -ano | findstr /r /c:":%~1 .*LISTENING" >nul 2>&1
if errorlevel 1 (exit /b 0) else (exit /b 1)
