FROM php:8.2-apache

# PHP extensions required by the app
RUN docker-php-ext-install pdo pdo_mysql

# Apache: mod_rewrite (.htaccess) + allow overrides so the root .htaccess
# block rule and routing work exactly like a normal Apache vhost.
RUN a2enmod rewrite headers
COPY deploy/apache.conf /etc/apache2/conf-available/food-factory.conf
RUN a2enconf food-factory

# Writable runtime dirs (apache runs as www-data)
RUN mkdir -p /var/www/html/storage/logs /var/www/html/storage/uploads \
    && chown -R www-data:www-data /var/www/html/storage

# Copy app code (excludes ignored paths via .dockerignore)
COPY . /var/www/html/

# Bootstrap: waits for DB, applies schema+seed idempotently, sets admin password
COPY deploy/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]