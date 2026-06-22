# =============================================================================
# Multi-stage Dockerfile for Laravel (Nginx + PHP-FPM)
# =============================================================================

# -----------------------------------------------
# Stage 1: Build dependencies
# -----------------------------------------------
FROM php:8.3-fpm AS build

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libfontconfig1 \
    libicu-dev \
    libxrender1 \
    libxext6 \
    locales \
    zip \
    unzip \
    git \
    && docker-php-ext-install gd pdo_mysql zip exif pcntl sockets opcache intl bcmath \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js (via fnm)
RUN curl -o- https://fnm.vercel.app/install | bash
ENV PATH="$PATH:/root/.fnm"
RUN fnm install 22 && fnm use 22

# Install Bun
RUN curl -fsSL https://bun.sh/install | bash
ENV PATH="$PATH:/root/.bun/bin:/root/.bun/install/global/node_modules/.bin"

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy package files for better caching
COPY package.json bun.lock* ./
RUN bun install --frozen-lockfile

# Copy the rest of the application
COPY . .

# Generate optimized autoloader and run post-install scripts
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi

# Build frontend assets
RUN bun run build

# -----------------------------------------------
# Stage 2: Production (Nginx + PHP-FPM)
# -----------------------------------------------
FROM php:8.3-fpm AS production

# Install system dependencies (runtime only)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libxrender1 \
    libxext6 \
    nginx \
    supervisor \
    locales \
    && docker-php-ext-install gd pdo_mysql zip exif pcntl sockets opcache intl bcmath \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Configure Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Configure Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy application from build stage
COPY --from=build /var/www/html /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port
EXPOSE 80

# Start Supervisor (manages both Nginx and PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
