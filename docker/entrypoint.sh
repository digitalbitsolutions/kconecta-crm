#!/bin/sh
set -e

cd /var/www/html

mkdir -p public/img/uploads public/video/uploads storage/framework/cache storage/framework/sessions storage/framework/views storage/logs

if [ ! -f vendor/autoload.php ]; then
    composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader
fi

chown -R www-data:www-data storage bootstrap/cache public/img/uploads public/video/uploads
chmod -R ug+rwX storage bootstrap/cache public/img/uploads public/video/uploads
umask 0002

php artisan optimize:clear

if [ "${APP_ENV}" != "local" ] && [ "${APP_ENV}" != "testing" ]; then
    php artisan config:cache
fi

exec apache2-foreground
