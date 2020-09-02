FROM composer:2 AS composer

COPY composer.json composer.lock /app/

RUN set -eux; \
    composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --apcu-autoloader --ignore-platform-reqs --no-scripts;

FROM php:7.4-cli-alpine

COPY --from=composer --chown=root:www-data /app/vendor /app/vendor
COPY --chown=root:www-data . /app

WORKDIR /app

ENTRYPOINT ["/usr/local/bin/php", "/app/bin/reindexr"]
CMD ["--help"]
