#!/usr/bin/env bash
# Cai dat nhanh (Mac/Linux) - can PHP 8.3+, Composer, extension gd/sqlite
set -e
[ -d blog ] && { echo "Thu muc blog da ton tai"; exit 1; }
composer create-project laravel/laravel blog
cp -r code/. blog/
cd blog
php artisan migrate --seed --force
php artisan storage:link
echo "XONG. Chay: ./run-demo.sh"
cp ../run-demo.sh .
