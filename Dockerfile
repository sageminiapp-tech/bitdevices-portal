FROM php:8.2-cli

# Install PDO MySQL extension
RUN docker-php-ext-install pdo_mysql

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 8080

# Use entrypoint to handle environment variables
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
