FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Ensure only mpm_prefork is loaded (required for mod_php)
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork

# Enable Apache rewrite module
RUN a2enmod rewrite

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
