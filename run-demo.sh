#!/usr/bin/env bash
# Chay trong thu muc blog
php artisan queue:work &
php artisan schedule:work &
trap 'kill 0' EXIT
php artisan serve
