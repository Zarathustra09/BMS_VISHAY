@echo off
setlocal
title LogXense Enterprise - database backup

rem ===========================================================================
rem  Backs up the database named in .env. It needs no admin rights and changes nothing in the
rem  database. Each run makes a new folder, storage\app\backups\<database>-<date and time>\, with:
rem
rem    schema.sql   the structure only (tables, views, triggers, routines) - no rows
rem    data.sql     the rows only (INSERT statements) - no tables
rem    full.sql     structure and rows together
rem
rem  Nothing is ever deleted: old backups stay until you remove them. Copy the folder off this PC
rem  now and then (a USB drive, another PC) - a backup on the same disk does not survive the disk.
rem
rem    backup-database.bat              run it and wait for a key at the end
rem    backup-database.bat unattended   no waiting (for Windows Task Scheduler)
rem
rem  To restore (into an empty database, from a Command Prompt):
rem    mysql -u USER -p DATABASE < storage\app\backups\<folder>\full.sql
rem  or schema.sql first, then data.sql. To use a mysqldump.exe in another place, set
rem  MYSQLDUMP=C:\path\to\mysqldump.exe first.
rem ===========================================================================

set "PAUSE=pause"
if /i "%~1"=="unattended" set "PAUSE=rem"

if not defined PHP for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP set "PHP=%%P"
if not defined PHP if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"
if not defined PHP (
    echo [xx] php.exe not found. Install XAMPP, or set PHP=C:\path\to\php.exe first.
    %PAUSE%
    exit /b 1
)

echo LogXense Enterprise - database backup
echo.
"%PHP%" "%~dp0installer.php" backup
set "RESULT=%errorlevel%"
echo.
%PAUSE%
exit /b %RESULT%
