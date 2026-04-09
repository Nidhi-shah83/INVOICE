FROM php:8.2

# Install system dependencies + Node
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql

# Set working directory
WORKDIR /var/www

# Copy project
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php

# Install PHP dependencies
RUN php composer.phar install --no-dev --optimize-autoloader

# Install Node dependencies
RUN npm install

# Build Vite assets
RUN npm run build

# Expose port
EXPOSE 10000

# Run migrations + start server
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
