#!/bin/sh
set -eu

# The MySQL-compatible TiDB database is configured as the app's primary SQL store.
php artisan migrate --force

# Seed roles, permissions, cancellation settings, and default admin/owner/driver accounts.
# firstOrCreate() makes this safe to run on every deploy.
php artisan db:seed --force

# Keep Laravel's scheduled reservation/booking cleanup active while the web service runs.
php artisan schedule:work &

exec apache2-foreground
