# NexBuild BMS

A Building Management System for manufacturing facilities: tracks Buildings → Floors → Machines, ingests sensor readings (temperature, humidity, vibration, power, etc.) from hardware devices, and provides an AI assistant for analyzing machine health.

Built with Laravel 12, Tailwind + DaisyUI + Alpine, MySQL, and Spatie's permission package.

## Documentation

Full technical documentation lives in **[docs/](docs/README.md)** — architecture, data model, routes, the device/sensor API integration guide, security, and setup/operations. Start there.

## Quick start

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

Default login after seeding: `test@example.com` / `password` (admin role).

See **[docs/09-setup-and-operations.md](docs/09-setup-and-operations.md)** for the queue worker, scheduler, and troubleshooting.

## Integrating a sensor device

If you're wiring up hardware (like the temperature sensor), see **[docs/05-device-api.md](docs/05-device-api.md)** for the API key, payload format, and code examples.
