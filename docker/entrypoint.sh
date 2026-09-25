#!/bin/sh
set -eu

# The MySQL-compatible TiDB database is configured as the app's primary SQL store.
php artisan migrate --force

# Keep Laravel's scheduled reservation/booking cleanup active while the web service runs.
php artisan schedule:work &

exec apache2-foreground
