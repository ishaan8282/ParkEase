FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install fast PHP extension installer
RUN curl -sSL https://github.com/mlocati/docker-php-extension-installer/releases/download/5.1.0/install-php-extensions -o /usr/local/bin/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions

# Install required PHP extensions using fast installer
RUN install-php-extensions \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy the rest of the application
COPY . .

# Set permissions
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
