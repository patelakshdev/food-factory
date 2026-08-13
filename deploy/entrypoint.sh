#!/bin/sh
set -e

echo "[entrypoint] running database bootstrap..."
php /var/www/html/deploy/bootstrap.php

echo "[entrypoint] starting apache..."
exec docker-php-entrypoint apache2-foreground