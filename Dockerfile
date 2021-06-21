FROM php:7-fpm-alpine3.7

RUN set -ex \
  && apk add --no-cache fcgi postgresql postgresql-dev \
  && docker-php-ext-install pgsql pdo pdo_pgsql

WORKDIR /var/www/html
COPY mailauth.pgsql.php auth/mailauth.pgsql.php
COPY healthcheck healthcheck
# Enable php fpm status page
RUN set -xe \
  && echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
  && /bin/chmod +x auth/mailauth.pgsql.php \
  && /bin/chmod +x healthcheck/php-fpm-healthcheck

