#!/usr/bin/env bash

git fetch origin
git reset --hard origin/master
/usr/local/bin/composer install --no-interaction --no-dev
php artisan migrate --force
php artisan route:cache
php artisan config:cache
sudo chown -R apache:www ./
sudo chmod 700 -R ./storage/
sudo chmod 700 -R ./bootstrap/cache/
