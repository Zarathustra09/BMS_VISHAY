# LogXense Enterprise

LogXense Enterprise is a data logging and monitoring system for manufacturing facilities: tracks Buildings → Floors → Machines, ingests sensor readings (temperature, humidity, vibration, power, etc.) from hardware devices, and provides an AI assistant for analyzing machine health.

Built with Laravel 12, Tailwind + DaisyUI + Alpine, MySQL, and Spatie's permission package.

## Documentation

Full technical documentation lives in **[docs/](docs/README.md)** — architecture, data model, routes, the device/sensor API integration guide, security, and setup/operations. Start there.

## Run with Docker (easiest)

You only need **Docker Desktop** running.

1. Double-click **`docker-start.bat`**. The first run builds everything (a few minutes).
2. Your browser opens at **http://localhost** (or **http://localhost:8080** if port 80 is busy - the script tells you which). Log in:

   | Account | Email | Password | Can see |
   |---|---|---|---|
   | Admin | `admin@example.com` | `password` | everything |
   | Viewer | `viewer@example.com` | `password` | dashboard, machines (view, edit, delete - no create) and alerts |

3. A machine called **Demo Machine** (`DEMO-001`) is already set up for your NodeMCU. Send it readings:

   - **URL:** `http://<this-PC's-IP>/api/machine-logs` - add `:8080` if the start script said it had to use 8080 (it prints the exact addresses)
   - **Header:** `X-Device-Key: nodemcu-demo-key`, `Content-Type: application/json`, `Accept: application/json`
   - **Body:** `{"log_type":"sensor_reading","readings":{"temperature":25.4,"humidity":61}}`

   ESP8266 / NodeMCU example:

   ```cpp
   #include <ESP8266WiFi.h>
   #include <ESP8266HTTPClient.h>
   #include <WiFiClient.h>

   WiFiClient client;
   HTTPClient http;
   http.begin(client, "http://192.168.1.50/api/machine-logs");   // your PC's IP - add :8080 if the start script said so
   http.addHeader("Content-Type", "application/json");
   http.addHeader("Accept", "application/json");
   http.addHeader("X-Device-Key", "nodemcu-demo-key");
   int code = http.POST("{\"log_type\":\"sensor_reading\",\"readings\":{\"temperature\":25.4,\"humidity\":61}}");  // 201 = stored
   http.end();
   ```

It runs five containers: `app` (the website: runs `php artisan serve --host=0.0.0.0 --port=80` inside the container, published on port 80, or 8080 if 80 is busy), `db` (MySQL), `queue` (queue worker), `scheduler` (offline-device checks and other timed tasks) and `reverb` (live chart updates on port 8081). Data survives restarts.

- **Stop:** `docker-stop.bat` (keeps your data). **Wipe everything and start fresh:** `docker compose down -v`.
- **Ports:** `docker-start.bat` uses port 80 if it is free, otherwise 8080. To force a port, run `set APP_PORT=8090` and then `docker-start.bat` in the same Command Prompt window.
- **Other devices can't connect?** Allow the website port (80 or 8080) and 8081 in the Windows firewall (Private network).
- **Different device key?** `set DEMO_DEVICE_KEY=your-key` before the first start (the key is created on first run only).
- **Something wrong?** `docker compose logs app` (or `queue`, `reverb`, `db`).
- Optional AI chat: `set OPENAI_API_KEY=...` before starting.
- **Email alerts:** sign in as admin, open **Settings -> Email (SMTP)**, enter your mail server and press *Send test email*. Create **Groups** (sidebar -> Groups), put users in them, and pick a machine's *Assignment Group* - everyone in that group is emailed when the machine raises an alert.

## Quick start (without Docker)


```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Set DB_* in .env to a running MySQL instance

php artisan migrate
php artisan db:seed

npm run build
php artisan serve
```

Default logins after seeding: `admin@example.com` (full access) and `viewer@example.com` (machines (view, edit, delete - no create) and alerts), both with password `password`.

See **[docs/09-setup-and-operations.md](docs/09-setup-and-operations.md)** for the queue worker, scheduler, and troubleshooting.

## Integrating a sensor device

If you're wiring up hardware (like the temperature sensor), see **[docs/05-device-api.md](docs/05-device-api.md)** for the API key, payload format, and code examples.
