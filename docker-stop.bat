@echo off
cd /d "%~dp0"
echo Stopping LogXense Enterprise (your data is kept)...
docker compose down
pause
