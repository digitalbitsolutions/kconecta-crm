#!/bin/sh
set -e

cd /var/www/html

php artisan optimize:clear
php artisan config:cache

exec apache2-foreground
