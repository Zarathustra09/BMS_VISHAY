@echo off
cd /d "%~dp0"
echo Stopping BMS (your data is kept)...
docker compose down
pause
