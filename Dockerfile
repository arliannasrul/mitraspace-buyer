FROM php:8.4-cli-alpine

# Install system dependencies, Node.js (for building frontend assets), and PHP extensions (including PostgreSQL)
RUN apk add --no-cache bash curl git icu-dev libzip-dev oniguruma-dev postgresql-dev zip unzip nodejs npm \
    && docker-php-ext-install intl mbstring pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install NPM dependencies and build Vite assets
RUN npm install && npm run build

# Clear Laravel config cache
RUN php artisan config:clear

# Expose port (default 8082, will be overridden by cloud platform PORT env variable)
EXPOSE 8082

# Run migrations and start the Laravel dev server
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8082}"]
