# Stage 1: Build Frontend
FROM node:20-slim AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Production Server
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install intl zip mysqli pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy built assets from frontend-builder
COPY --from=frontend-builder /app/public/build ./public/build

# Set permissions for CI4 writable folder
RUN chown -R www-data:www-data writable \
    && chmod -R 775 writable

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
