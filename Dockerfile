FROM php:7-fpm-alpine3.7

WORKDIR /var/www/html
COPY mailauth.pgsql.php auth/mailauth.pgsql.php
RUN apk add --no-cache postgresql postgresql-dev && \
    docker-php-ext-install pgsql pdo pdo_pgsql && \
    /bin/chmod +x auth/mailauth.pgsql.php

