#!/bin/sh
set -e

APP_PORT="${PORT:-80}"

if [ "$APP_PORT" != "80" ]; then
  sed -i "s/Listen 80/Listen ${APP_PORT}/" /etc/apache2/ports.conf
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APP_PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php /var/www/html/app/migrate.php
fi

exec "$@"

