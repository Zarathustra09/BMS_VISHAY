#!/usr/bin/env bash
# LogXense Enterprise - stop (Linux / macOS). Same as docker-stop.bat.
cd "$(dirname "$0")" || exit 1
echo "Stopping LogXense Enterprise (your data is kept)..."
docker compose down
