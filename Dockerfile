# Use official PHP with Apache web server
FROM php:8.2-apache

# Enable Apache mod_rewrite for clean URLs if needed
RUN a2enmod rewrite

# Copy project files into Apache's default public folder
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Ensure the uploads directory exists and has write permissions for PHP/Apache
RUN mkdir -p /var/www/html/uploads && \
    chown -www-data:www-data /var/www/html/uploads && \
    chmod -R 775 /var/www/html/uploads

# Expose port 80
EXPOSE 80