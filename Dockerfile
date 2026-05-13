FROM php:8.3-apache AS runtime

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '%s\n' \
        '#!/bin/sh' \
        'set -e' \
        'APP_PORT="${PORT:-80}"' \
        'if [ "$APP_PORT" != "80" ]; then' \
        '  sed -i "s/Listen 80/Listen ${APP_PORT}/" /etc/apache2/ports.conf' \
        '  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APP_PORT}>/" /etc/apache2/sites-available/000-default.conf' \
        'fi' \
        'if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then' \
        '  php /var/www/html/app/migrate.php' \
        'fi' \
        'exec "$@"' \
        > /usr/local/bin/app-entrypoint \
    && chmod +x /usr/local/bin/app-entrypoint

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
