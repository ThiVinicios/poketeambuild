#!/bin/bash
PORT=${PORT:-80}
echo "Listen $PORT" > /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf
apache2ctl -D FOREGROUND
