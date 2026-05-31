#!/bin/bash
set -e

# Fix MPM conflict at runtime (handles Railway layer caching)
find /etc/apache2/mods-enabled/ -name 'mpm_*' -delete 2>/dev/null || true
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Railway menyediakan PORT secara otomatis, default 80 jika tidak ada
PORT=${PORT:-80}

# Update Apache agar listen pada port yang benar
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
