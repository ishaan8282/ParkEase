# Build Laravel's production PHP dependencies.
FROM php:8.3-cli AS backend-build

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mbstring opcache pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Vite compiles the Vue/Inertia app into Laravel's public/build directory.
FROM node:22-alpine AS frontend-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY --from=backend-build /var/www/html/vendor/tightenco/ziggy ./vendor/tightenco/ziggy
RUN npm run build

# Small Apache/PHP runtime image for Render's Docker web service.
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mbstring opcache pdo_mysql zip \
    && a2enmod rewrite headers \
    && sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=backend-build /var/www/html ./
COPY --from=frontend-build /app/public/build ./public/build
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/parkease-entrypoint
RUN chmod +x /usr/local/bin/parkease-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV PORT=10000
EXPOSE 10000
ENTRYPOINT ["parkease-entrypoint"]
