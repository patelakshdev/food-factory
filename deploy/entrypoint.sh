#!/bin/sh
set -e

echo "[entrypoint] running database bootstrap..."
php /var/www/html/deploy/bootstrap.php

# Render (and most PaaS) inject $PORT and route traffic to it. Apache's php
# image listens on 80 by default, so rebind Apache to $PORT when it differs.
PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
  echo "[entrypoint] binding apache to \$PORT=$PORT"
  sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

echo "[entrypoint] starting apache..."
exec docker-php-entrypoint apache2-foreground