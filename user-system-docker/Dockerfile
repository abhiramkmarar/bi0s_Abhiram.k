# Base image: PHP 8.2 + Apache
FROM php:8.2-apache

# Copy PHP source code to Apache web root
COPY src/ /var/www/html/

# Enable mysqli extension for MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Create uploads folder with proper permissions
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads

# Expose port 80 for web traffic
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
