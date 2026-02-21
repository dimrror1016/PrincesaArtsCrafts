# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html/

# Copy all project files into the container
COPY . /var/www/html/

# Enable Apache mod_rewrite (needed if you use .htaccess)
RUN a2enmod rewrite

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/

# Expose port 10000 (Render default)
EXPOSE 10000

# Start Apache in foreground
CMD ["apache2-foreground"]
