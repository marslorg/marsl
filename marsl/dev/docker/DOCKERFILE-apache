FROM php:8.0-apache

RUN apt-get update --fix-missing
RUN apt-get install -y curl
RUN apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev
RUN apt-get install -y libgmp-dev re2c libmhash-dev libmcrypt-dev file
RUN apt-get install -y msmtp

COPY marsl/dev/docker/mod_log_ipmask.c /home/mod_log_ipmask.c
RUN apt-get install -y apache2-dev
RUN apxs -i -a -c /home/mod_log_ipmask.c

RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/

RUN docker-php-ext-configure gmp
RUN docker-php-ext-install gmp
RUN docker-php-ext-install mysqli
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && docker-php-ext-install gd

RUN a2enmod rewrite
RUN a2enmod remoteip

WORKDIR /var/www/html

COPY marsl/out/ .
RUN chmod 777 -R albums
RUN chmod 777 -R files
RUN chmod 777 -R news
RUN chmod 777 -R shared

COPY marsl/dev/docker/000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY marsl/dev/docker/php.ini /usr/local/etc/php/conf.d/php.ini
COPY marsl/dev/docker/security.conf /etc/apache2/conf-available/security.conf

EXPOSE 80