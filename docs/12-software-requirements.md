# Software Requirements

Everything you need to install to run LogXense Enterprise, for the two ways of installing it:

| | **1. Docker** | **2. Full manual installation** |
|---|---|---|
| Software to install yourself | Docker (and optionally Git) | PHP, Composer, MySQL, Node.js, Git, a web server, plus services for the background processes |
| Effort | Low: one script | High: you set up, configure and keep each piece running |
| Best for | Trying it out, a plant PC, anyone who only needs the system to run | Developers, or a server where Docker is not allowed |
| Updating | `git pull`, run the start script | `git pull`, `composer install`, `npm run build`, `php artisan migrate`, restart processes |
| Step-by-step guide | [README](../README.md#run-with-docker-easiest) and the Docker Installation Guide (.docx) | [09-setup-and-operations.md](09-setup-and-operations.md) |

Both need the same hardware and network, listed first.

## Hardware and network (both approaches)

| | Minimum | Recommended |
|---|---|---|
| RAM | 4 GB | 8 GB |
| Free disk | 10 GB for Docker (images and data); 3 GB for a manual install | 20 GB or more if a device posts many readings a second |
| CPU | 2 cores | 4 cores |
| Internet | Needed once, to download software (Docker also builds on first start) | Needed later only for e-mail alerts and the optional AI chat |

Network:
- The server and your NodeMCU / ESP32 must be on the same network, and the server should keep the same IP (give it a fixed IP or a DHCP reservation on the router).
- The ports below must be free, and allowed through the firewall for other devices to connect.

| Port | Used for | Notes |
|---|---|---|
| 80 | The website and the device API (`/api/machine-logs`) | Docker falls back to 8080 if 80 is busy |
| 8081 | Live chart and alert updates (Reverb) | The Docker setup. A manual install uses `REVERB_SERVER_PORT` from `.env`, which is **8080** in `.env.example`, so the website cannot also use 8080 |
| 3306 | MySQL | Docker keeps it internal. A manual install should keep it closed to the network |

---

## 1. Docker approach

### Software to install

| Software | Version | Why | Where to get it |
|---|---|---|---|
| **Docker Desktop** (Windows 10/11, macOS) or **Docker Engine + Compose plugin** (Linux) | Docker 24 or newer, **Compose v2** (`docker compose`, not the old `docker-compose`). Tested with Docker 29 and Compose v5 | Runs everything | https://www.docker.com/products/docker-desktop/ ; on Linux: `curl -fsSL https://get.docker.com \| sudo sh` |
| **Git** (optional) | Any recent version | To download the project and update it later. Without it, download the ZIP from GitHub | https://git-scm.com/downloads (Linux: `sudo apt install git`) |
| A web browser | Any current browser | To open the website | |

On Windows, Docker Desktop also installs **WSL 2** for you (virtualization must be enabled in the BIOS). On Linux, add your user to the `docker` group: `sudo usermod -aG docker $USER`, then log out and in.

That is the complete list. **You do not install PHP, MySQL, Node.js or Composer.** They live inside the Docker images, which are downloaded and built automatically on the first start.

### What Docker downloads for you (for reference)

| Image | Contains | Used for |
|---|---|---|
| `php:8.2-cli` | PHP 8.2 with `pdo_mysql`, `pcntl`, `bcmath`, `intl`, `zip`, `gd`, `opcache` | The website, queue worker, scheduler and Reverb |
| `mysql:8.0` | MySQL 8.0 | The database |
| `composer:2` | Composer 2 | Installing PHP packages (build time only) |
| `node:20-alpine` | Node.js 20 | Building the CSS and JavaScript (build time only) |

These become five containers: `app`, `db`, `queue`, `scheduler` and `reverb` (see [docker-compose.yml](../docker-compose.yml)). Data is kept in Docker volumes, so it survives restarts.

### Start it

| | Windows | Linux |
|---|---|---|
| Start | double-click `docker-start.bat` | `./docker-start.sh` |
| Stop (data kept) | `docker-stop.bat` | `./docker-stop.sh` |

### Optional extras (not needed to run it)

| Software | For |
|---|---|
| Arduino IDE (or PlatformIO) | Programming the NodeMCU / ESP32 |
| An SMTP account (for example a Gmail app password) | Real e-mail alerts, entered on the Settings page |
| An OpenAI or Gemini API key | The AI chat feature, set with `OPENAI_API_KEY` before starting |
| `curl` | Testing the device API from a terminal (already in Windows 10/11 and most Linux) |

---

## 2. Full manual installation

Use this when you can't use Docker, or you are developing the project. You install and run every piece yourself.

### Software to install

| Software | Version | Why | Where to get it |
|---|---|---|---|
| **PHP** | **8.2 or newer** (the project requires `^8.2`; Docker uses 8.2, and 8.2.12 is used for development here) | Runs the application | https://www.php.net/downloads, or the bundles below |
| **Composer** | 2.x | Installs the PHP packages | https://getcomposer.org/download/ |
| **MySQL** | **8.0** (what Docker uses and what is tested). Older MySQL 5.7.8+ and MariaDB 10.3+ meet Laravel 12's minimum but are **not tested here** | The database (`machine_logs` uses JSON columns) | https://dev.mysql.com/downloads/mysql/ |
| **Node.js** (includes npm) | **20.19 or newer, or 22.12 or newer**. Vite 7 refuses older versions | Builds the CSS and JavaScript (`npm run build`) | https://nodejs.org (LTS) |
| **Git** | Any recent version | Download and update the project | https://git-scm.com/downloads |
| A **web server** | Apache or Nginx, with PHP (PHP-FPM or `mod_php`). For quick tests you can use `php artisan serve` instead | Serves the site with `public/` as the document root. Use a real web server for production: `php artisan serve` handles little at a time, so several devices and browsers would queue | Apache https://httpd.apache.org, Nginx https://nginx.org |

#### Required PHP extensions

| Extension | Needed by |
|---|---|
| `ctype`, `filter`, `hash`, `json`, `mbstring`, `openssl`, `pcre`, `session`, `tokenizer`, `xml`, `dom`, `libxml`, `fileinfo`, `curl` | Laravel and its core packages |
| `pdo_mysql` | The MySQL connection |
| `gd`, `zip`, `zlib`, `iconv`, `simplexml`, `xmlreader`, `xmlwriter` | Excel/CSV export (PhpSpreadsheet, OpenSpout) used by the data tables |

Recommended: `bcmath`, `intl`, `opcache` (the Docker image has them), and `pcntl` on Linux/macOS (lets the queue worker and Reverb shut down cleanly; not available on Windows). To run the **test suite** you also need `pdo_sqlite`.

Check them all at once. Any line that says `MISSING` must be enabled in `php.ini`:

```bash
php -r 'foreach(["ctype","curl","dom","fileinfo","filter","gd","hash","iconv","json","libxml","mbstring","openssl","pcre","pdo_mysql","session","simplexml","tokenizer","xml","xmlreader","xmlwriter","zip","zlib"] as $e){echo str_pad($e,12), extension_loaded($e)?"ok":"MISSING", PHP_EOL;}'
```

Check the other tools:

```bash
php -v          # 8.2 or newer
composer -V     # 2.x
node -v         # v20.19+ or v22.12+
npm -v
mysql --version # 8.0
git --version
```

#### Easiest ways to get the software

**Windows**
- **Laragon Full** (https://laragon.org, free): bundles Apache or Nginx, PHP, MySQL, Node.js, Composer and Git. Put the project in `C:\laragon\www`. After installing, run the version checks above, because Laragon's bundled versions vary.
- Or **XAMPP** (PHP, Apache, MariaDB), plus Composer, Node.js and Git installed separately. XAMPP ships MariaDB, not MySQL 8.0 (untested here).

**Ubuntu / Debian**

```bash
sudo apt update
sudo apt install -y git unzip curl mysql-server composer \
  php-cli php-mysql php-mbstring php-xml php-curl php-zip php-gd php-intl php-bcmath php-sqlite3 \
  apache2 libapache2-mod-php        # or nginx + php-fpm
# Node.js from NodeSource: the version in Ubuntu's own repository is too old for Vite 7
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

Ubuntu 24.04 installs PHP 8.3, which satisfies the project's `^8.2` requirement. Debian and older Ubuntu may need the Ondřej Surý PHP repository to get PHP 8.2 or newer.

### Background processes you must keep running

A manual install is not just a website. Five things must run, and the Docker setup does all of them for you:

| Process | Command | Why | Keep it alive with |
|---|---|---|---|
| Web server | Apache / Nginx (or `php artisan serve`) | Website and device API | Its own service |
| MySQL | the MySQL service | Database | Its own service |
| **Queue worker** | `php artisan queue:work --tries=3` | Sends alert e-mails and AI replies | Windows: NSSM service. Linux: systemd or Supervisor |
| **Reverb** | `php artisan reverb:start` | Live chart and alert updates in the browser | Windows: NSSM service. Linux: systemd or Supervisor |
| **Scheduler** | `* * * * * cd /path/to/project && php artisan schedule:run` | Offline-device checks, per-minute summaries, cleanup of old data | Linux: cron. Windows: Task Scheduler, every minute |

Extra software for these:
- **Windows:** **NSSM** (https://nssm.cc/download, a single `nssm.exe`) to run the queue worker and Reverb as Windows services. The project includes [deploy/windows/install-services.bat](../deploy/windows/install-services.bat) for this. Task Scheduler is built into Windows.
- **Linux:** **Supervisor** (`sudo apt install supervisor`) or a systemd unit per process, and cron (installed by default).
- **Development only:** `npm run dev` runs Vite for live asset reloading, or `composer run dev` starts the server, queue, Vite and Reverb together. You do not need Vite running in production.

### Install the project

```bash
git clone https://github.com/Zarathustra09/BMS_VISHAY.git
cd BMS_VISHAY

composer install --no-dev --optimize-autoloader   # drop --no-dev for a development machine
npm install
npm run build

cp .env.example .env                              # Windows: copy .env.example .env
php artisan key:generate
```

Create an empty MySQL database (for example `bms_vishay`), then edit `.env`:

| Setting | Set it to |
|---|---|
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Your MySQL details |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` on a real server |
| `REVERB_APP_KEY`, `REVERB_APP_SECRET` | Your own random values, for example `php -r "echo bin2hex(random_bytes(10));"` |
| `REVERB_SERVER_PORT` / `REVERB_PORT` | Keep them the same. The default 8080 is fine unless the website uses 8080 |
| `APP_EDITION` | `full` (everything) or `lite` (machines, alerts, users and the device API only) |

Then:

```bash
php artisan migrate --force
php artisan db:seed --force        # creates the roles, the admin and viewer accounts, and the Demo Machine
php artisan storage:link           # lets the site show uploaded building photos (full edition)
```

Start the processes from the table above, point the web server at the `public/` folder, and open the site. Sign in with `admin@example.com` / `password` (and `viewer@example.com` / `password`) and **change those passwords**. The Demo Machine accepts readings with the key `nodemcu-demo-key`; the full device guide is [05-device-api.md](05-device-api.md).

After changing code or `.env`: `php artisan reverb:restart` and `php artisan queue:restart` (and `npm run build` if a `VITE_*` value changed).

Full details, the Windows plant-PC walkthrough, the scheduler and troubleshooting are in [09-setup-and-operations.md](09-setup-and-operations.md).

### Optional extras

| Software | For |
|---|---|
| An SMTP account | Real e-mail alerts (Settings page); until then e-mails are only written to `storage/logs/laravel.log` |
| An OpenAI or Gemini API key | The AI chat (`OPENAI_API_KEY` or `GEMINI_API_KEY` in `.env`) |
| Arduino IDE (or PlatformIO) | Programming the NodeMCU / ESP32 |
| PHPUnit (installed by `composer install` without `--no-dev`) and `pdo_sqlite` | Running the test suite with `php artisan test` |

---

## Quick comparison of the software lists

| Software | Docker | Manual |
|---|---|---|
| Docker Desktop / Engine + Compose v2 | **Required** | not needed |
| PHP 8.2+ and extensions | inside the image | **Required** |
| Composer 2 | inside the image | **Required** |
| MySQL 8.0 | inside the image | **Required** |
| Node.js 20.19+ / 22.12+ and npm | inside the image (build only) | **Required** (build only) |
| Web server (Apache / Nginx) | not needed (built-in server) | **Required** for production |
| Queue worker, Reverb, scheduler | containers | you run them (NSSM, Supervisor/systemd, cron / Task Scheduler) |
| Git | optional | **Required** (or download a ZIP) |
