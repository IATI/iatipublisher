ARG PHP_EXTS="bcmath pdo_mysql pdo_pgsql pcntl"
ARG PHP_PECL_EXTS="redis"
ARG MIX_ENCRYPTION_KEY

FROM composer:2.1 as composer_base

ARG PHP_EXTS
ARG PHP_PECL_EXTS

RUN mkdir -p /opt/apps/laravel-in-kubernetes /opt/apps/laravel-in-kubernetes/bin

WORKDIR /opt/apps/laravel-in-kubernetes

RUN addgroup -S composer \
    && adduser -S composer -G composer \
    && chown -R composer /opt/apps/laravel-in-kubernetes \
    && apk add --virtual build-dependencies --no-cache ${PHPIZE_DEPS} openssl ca-certificates libxml2-dev oniguruma-dev postgresql-dev \
    && docker-php-ext-install -j$(nproc) ${PHP_EXTS} \
    && pecl install -f ${PHP_PECL_EXTS} \
    && docker-php-ext-enable ${PHP_PECL_EXTS} \
    && apk del build-dependencies \
    && apk add --no-cache libpq

USER composer

COPY --chown=composer composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY --chown=composer . .

RUN composer install --no-dev --prefer-dist --optimize-autoloader

FROM node:16 as frontend

ARG MIX_ENCRYPTION_KEY

COPY --from=composer_base /opt/apps/laravel-in-kubernetes /opt/apps/laravel-in-kubernetes

WORKDIR /opt/apps/laravel-in-kubernetes

RUN npm install && \
    npm run prod


FROM php:8.1-alpine as cli

ARG PHP_EXTS
ARG PHP_PECL_EXTS

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini
COPY docker/custom_php.ini $PHP_INI_DIR/conf.d/custom_php.ini

WORKDIR /opt/apps/laravel-in-kubernetes

RUN apk add --virtual build-dependencies --no-cache ${PHPIZE_DEPS} openssl ca-certificates libxml2-dev oniguruma-dev postgresql-dev && \
    docker-php-ext-install -j$(nproc) ${PHP_EXTS} && \
    pecl install -f ${PHP_PECL_EXTS} && \
    docker-php-ext-enable ${PHP_PECL_EXTS} && \
    apk del build-dependencies \
    && apk add --no-cache libpq

COPY --from=composer_base /opt/apps/laravel-in-kubernetes /opt/apps/laravel-in-kubernetes
COPY --from=frontend /opt/apps/laravel-in-kubernetes/public /opt/apps/laravel-in-kubernetes/public

FROM php:8.1-fpm-alpine as fpm_server

ARG PHP_EXTS
ARG PHP_PECL_EXTS

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini
COPY docker/custom_php.ini $PHP_INI_DIR/conf.d/custom_php.ini

RUN echo "pm = static" >> /usr/local/etc/php-fpm.d/zz-docker.conf
RUN echo "pm.max_children = 20" >> /usr/local/etc/php-fpm.d/zz-docker.conf
RUN echo "pm.max_requests = 1000" >> /usr/local/etc/php-fpm.d/zz-docker.conf
RUN echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /opt/apps/laravel-in-kubernetes

RUN apk add --virtual build-dependencies --no-cache ${PHPIZE_DEPS} openssl ca-certificates libxml2-dev oniguruma-dev postgresql-dev && \
    docker-php-ext-install -j$(nproc) ${PHP_EXTS} && \
    pecl install -f ${PHP_PECL_EXTS} && \
    docker-php-ext-enable ${PHP_PECL_EXTS} && \
    apk del build-dependencies \
    && apk add --no-cache libpq

USER  www-data

COPY --from=composer_base --chown=www-data /opt/apps/laravel-in-kubernetes /opt/apps/laravel-in-kubernetes
COPY --from=frontend --chown=www-data /opt/apps/laravel-in-kubernetes/public /opt/apps/laravel-in-kubernetes/public

RUN php artisan event:cache && \
    php artisan route:cache && \
    php artisan view:cache

FROM nginx:1.20-alpine as web_server

WORKDIR /opt/apps/laravel-in-kubernetes

COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template

COPY --from=frontend /opt/apps/laravel-in-kubernetes/public /opt/apps/laravel-in-kubernetes/public

FROM cli as cron

WORKDIR /opt/apps/laravel-in-kubernetes

RUN touch laravel.cron && \
    echo "* * * * * cd /opt/apps/laravel-in-kubernetes && php artisan schedule:run" >> laravel.cron && \
    echo "@reboot cd /opt/apps/laravel-in-kubernetes && php artisan optimize:clear" >> laravel.cron && \
    crontab laravel.cron

CMD ["crond", "-l", "2", "-f"]


FROM cli as horizon

WORKDIR /opt/apps/laravel-in-kubernetes

RUN apk add --no-cache supervisor

COPY docker/supervisord.conf /etc/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisord.conf"]
