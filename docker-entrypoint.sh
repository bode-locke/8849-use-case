#!/bin/sh
set -e

# Attendre que MySQL soit prêt
until mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "select 1" >/dev/null 2>&1; do
  echo "Waiting for MySQL..."
  sleep 2
done

# Lancer Laravel (migrations et table de session)
php artisan migrate --force

# Lancer PHP-FPM
exec php-fpm
