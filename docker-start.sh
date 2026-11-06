#!/usr/bin/env bash
# LogXense Enterprise - start with Docker (Linux / macOS). Same steps as docker-start.bat.
#   ./docker-start.sh                  (or: bash docker-start.sh)
#   APP_PORT=8090 ./docker-start.sh    to force a specific website port
set -u

cd "$(dirname "$0")" || exit 1

if ! docker info >/dev/null 2>&1; then
    echo "Docker is not running, or this user is not allowed to use it."
    echo "  - Start it:                  sudo systemctl start docker"
    echo "  - Allow your user to use it: sudo usermod -aG docker \$USER   (then log out and back in)"
    echo "  - Not installed yet? See the installation guide, or run: curl -fsSL https://get.docker.com | sudo sh"
    exit 1
fi

# Stop any earlier LogXense Enterprise run first (keeps the data) so it doesn't hold the ports we are about to check.
docker compose down >/dev/null 2>&1

# port_free <port>: succeeds when nothing is listening on it. If neither ss nor netstat exists, assume it is free.
port_free() {
    if command -v ss >/dev/null 2>&1; then
        ! ss -ltn 2>/dev/null | awk '{print $4}' | grep -Eq "[:.]$1\$"
    elif command -v netstat >/dev/null 2>&1; then
        ! netstat -ltn 2>/dev/null | awk '{print $4}' | grep -Eq "[:.]$1\$"
    else
        return 0
    fi
}

# ---- Pick the website port: 80 if it is free, otherwise 8080 ------------------------------
port_fell_back=0

if [ -n "${APP_PORT:-}" ]; then
    if ! port_free "$APP_PORT"; then
        echo
        echo "Port $APP_PORT is already used by another program."
        echo "Close that program, or pick a different port:"
        echo "    APP_PORT=8090 ./docker-start.sh"
        echo
        exit 1
    fi
elif port_free 80; then
    APP_PORT=80
elif port_free 8080; then
    APP_PORT=8080
    port_fell_back=1
else
    echo
    echo "Both port 80 and port 8080 are used by other programs."
    echo "Close one of them, or pick a different port:"
    echo "    APP_PORT=8090 ./docker-start.sh"
    echo
    exit 1
fi

# Live chart updates (Reverb) always use 8081.
if ! port_free 8081; then
    echo
    echo "Port 8081 (live chart updates) is already used by another program. Close it and run this again."
    echo
    exit 1
fi

export APP_PORT
port_suffix=":$APP_PORT"
[ "$APP_PORT" = "80" ] && port_suffix=""

[ "$port_fell_back" = "1" ] && echo "Port 80 is in use by another program, so LogXense Enterprise will use port 8080 instead."
echo "Building and starting LogXense Enterprise. The first time takes several minutes - please wait..."

if ! docker compose up -d --build --wait; then
    echo
    echo 'Something went wrong. Run "docker compose logs" to see why, and send that to whoever shared this project.'
    exit 1
fi

echo
echo "================================================================"
echo "  LogXense Enterprise is running"
echo
echo "  Open:     http://localhost${port_suffix}"
echo
echo "  Admin:    admin@example.com   /  password   (full access)"
echo "  Viewer:   viewer@example.com  /  password   (machines - no create - and alerts)"
echo
echo "  NodeMCU - POST sensor readings to one of these addresses:"

addresses=""
if command -v hostname >/dev/null 2>&1; then
    addresses="$(hostname -I 2>/dev/null)"
fi
if [ -z "$addresses" ] && command -v ip >/dev/null 2>&1; then
    addresses="$(ip -4 -o addr show scope global 2>/dev/null | awk '{print $4}' | cut -d/ -f1)"
fi
for address in $addresses; do
    case "$address" in
        *:*) continue ;; # skip IPv6
    esac
    echo "       http://${address}${port_suffix}/api/machine-logs"
done

echo "  Header:   X-Device-Key: nodemcu-demo-key"
echo '  Body:     {"log_type":"sensor_reading","readings":{"temperature":25.4,"humidity":61}}'
echo "================================================================"
echo

# Only on a desktop with a browser; never a reason to fail.
if [ -n "${DISPLAY:-}${WAYLAND_DISPLAY:-}" ] && command -v xdg-open >/dev/null 2>&1; then
    xdg-open "http://localhost${port_suffix}" >/dev/null 2>&1 || true
fi

exit 0
