FROM php:8.2-apache

# Install SQLite3 and PDO extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Ensure data directory exists and set proper permissions
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/data

# Render listens on PORT environment variable (default 80/10000)
ENV PORT=80
EXPOSE 80

CMD ["apache2-foreground"]
