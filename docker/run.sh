#!/bin/sh

cd /var/www

# php artisan migrate:fresh --seed
php artisan migrate
php artisan assets:minify
php artisan sitemap:generate
php artisan o:c
php artisan config:cache

/usr/bin/supervisord -c /etc/supervisord.conf
