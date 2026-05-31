#!/bin/bash
set -e

# Railway menyediakan PORT secara otomatis, default 80 jika tidak ada
PORT=${PORT:-80}

# Update Apache agar listen pada port yang benar
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
