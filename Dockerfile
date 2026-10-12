# syntax=docker/dockerfile:1

# ---- 1. Frontend assets -----------------------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js postcss.config.cjs ./
COPY resources ./resources
COPY public ./public
# Vite bakes these in at build time; they must match docker-compose.yml. The host is left
# blank so the browser connects to whichever address the page was opened from.
ENV VITE_REVERB_APP_KEY=bms-docker-key \
    VITE_REVERB_HOST= \
    VITE_REVERB_PORT=8081 \
    VITE_REVERB_SCHEME=http
RUN npm run build

# ---- 2. Runtime image (PHP dependencies are installed here so platform checks are real) ----
FROM php:8.2-cli AS app
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN apt-get update && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions pdo_mysql pcntl bcmath intl zip gd opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && sed -i 's/\r$//' deploy/docker/entrypoint.sh \
    && chmod +x deploy/docker/entrypoint.sh \
    && chown www-data:www-data . && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 80 8081
ENTRYPOINT ["deploy/docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80", "--no-reload"]
