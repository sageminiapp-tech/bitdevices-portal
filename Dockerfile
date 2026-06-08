FROM php:8.2-cliFROM php:8 PDO MySQL extension
RUN docker-php-ext-install pdo_mysql

# Install system packages needed for Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Optional: zip extension (often useful with Composer packages)
RUN docker-php-ext-install zip

# Copy Composer from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better build caching
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port
EXPOSE 8080

# Start app
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

