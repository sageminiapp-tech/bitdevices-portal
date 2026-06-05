FROM php:8.2-cli

# Install PDO MySQL extension
RUN docker-php-ext-install pdo_mysql

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Expose port
EXPOSE 8080

# Start PHP built-in server (matching your Procfile)
CMD ["php", "-S", "0.0.0.0:8080"]
