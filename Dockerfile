# Use official PHP image
FROM php:8.2-cli

# Install system dependencies + Node.js
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install Laravel PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
RUN npm install

# Build frontend assets (Vite)
RUN npm run build

# Generate app key (ignore if already exists)
RUN php artisan key:generate || true

# Expose dynamic port for Render
EXPOSE ${PORT}

# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=${PORT}
