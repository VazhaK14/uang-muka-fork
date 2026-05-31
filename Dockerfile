FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Remove ALL mpm modules explicitly (no glob), then enable only prefork
RUN find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*' -exec rm -f {} + \
    && a2enmod mpm_prefork \
    && a2enmod rewrite

# Copy all project files
COPY . /var/www/html/

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/ \
    && chmod 777 /var/www/html/uploads

# Copy and enable entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
