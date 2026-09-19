FROM composer:2.9 AS composer

FROM php:8.4-fpm AS php-build
ENV TZ=UTC

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    apcu \
    opcache \
    sockets \
    pcntl \
    pgsql \
    pdo_pgsql
RUN apt-get -qq update && apt-get -qq --yes install git unzip procps
RUN pecl channel-update pecl.php.net && pecl install xdebug && docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/
COPY --from=composer /usr/bin/composer /usr/bin/composer
ARG UID=1000
ARG GID=1000
RUN usermod --uid $UID www-data && groupmod --gid $GID www-data
RUN chown --recursive $UID:$GID /var/www
USER $UID
WORKDIR /www

FROM php-build
COPY --chown=$UID:$GID composer.json composer.lock /www/
RUN --mount=type=cache,target=/var/www/.composer/cache,uid=$UID,gid=$GID composer install --no-interaction --optimize-autoloader
COPY --chown=$UID:$GID . /www/