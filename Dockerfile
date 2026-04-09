FROM php:8.2

# Install PostgreSQL driver
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Set working directory
WORKDIR /var/www

# Copy project
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php

# Install Laravel dependencies
RUN php composer.phar install --no-dev --optimize-autoloader

# Expose port
EXPOSE 10000

# Start Laravel
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000

