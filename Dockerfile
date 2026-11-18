FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-mysql \
    libapache2-mod-php8.1 \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite

EXPOSE 80

CMD sh -c "echo \"Listen \\" > /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \*:80>/<VirtualHost *:\>/g\" /etc/apache2/sites-available/000-default.conf && apache2ctl -D FOREGROUND"
