FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies and Node.js
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    locales \
    zip \
    unzip \
    git \
    curl \
    vim \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pgsql mbstring zip exif pcntl gd bcmath

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN groupadd -g 1000 www \
    && useradd -u 1000 -ms /bin/bash -g www www

# Copy composer files first for better caching
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-dev

# Copy package.json for npm dependencies
COPY package*.json ./
RUN npm install --legacy-peer-deps

# Copy application files
COPY . /var/www/

# Copy environment file
COPY .env.example /var/www/.env

# Complete composer installation
RUN composer dump-autoload --optimize --no-dev

# Create required directories with proper permissions
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    && mkdir -p storage/{logs,app/public,app/private} \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache

# Build frontend assets
RUN npm run build

# Generate Laravel application key
RUN php artisan key:generate --force

# Set proper ownership
RUN chown -R www:www /var/www

# Switch to www user
USER www

# Expose port for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]