# syntax=docker/dockerfile:1
ARG PHP_IMAGE=php:8.5.13-apache-trixie@sha256:0a785eab8b7eb4836f9c6093771c8ea15ff8ba7269444f3da70a1d33d15fe664
ARG NODE_IMAGE=node:24.22.3-trixie-slim@sha256:af02d57b57719b9657a45199a79264a9437940cd55b3c7b3fc99615654f124a0
ARG COMPOSER_IMAGE=composer:2.11.2@sha256:919efe8e62179c71dfd31fad035fd671fe7454211c6b2aa30a10af0ab4acedba

FROM --platform=$BUILDPLATFORM ${NODE_IMAGE} AS frontend-dependencies
WORKDIR /src
COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm npm ci --ignore-scripts --no-audit

FROM frontend-dependencies AS frontend
COPY . .
RUN npm run production

FROM --platform=$BUILDPLATFORM ${COMPOSER_IMAGE} AS composer-bin

FROM ${PHP_IMAGE} AS php-runtime
RUN set -eux; \
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates curl $PHPIZE_DEPS libfreetype-dev libheif-plugin-libde265 libheif-plugin-x265 libicu-dev \
        libjpeg62-turbo-dev libmagickwand-dev libpng-dev libpq-dev libwebp-dev libzip-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" bcmath exif ftp gd intl pcntl pdo_mysql pdo_pgsql zip; \
    rm "$PHP_INI_DIR/conf.d/docker-php-ext-pcntl.ini"; \
    curl --fail --location --silent --show-error --retry 3 \
        https://pecl.php.net/get/imagick-3.8.1.tgz -o /tmp/imagick.tgz; \
    echo '3a3587c0a524c17d0dad9673a160b90cd776e836838474e173b549ed864352ee  /tmp/imagick.tgz' | sha256sum -c; \
    pecl install /tmp/imagick.tgz; \
    docker-php-ext-enable imagick; \
    apt-mark auto '.*' > /dev/null; \
    apt-mark manual $savedAptMark ca-certificates curl libheif-plugin-libde265 libheif-plugin-x265; \
    find /usr/local/lib/php/extensions -type f -name '*.so' -exec ldd '{}' ';' \
        | awk '/=> \// { path = $(NF-1); sub(/^\/(usr\/)?/, "", path); print "*" path }' | sort -u \
        | xargs -r dpkg-query --search \
        | cut -d: -f1 | sort -u | xargs -r apt-mark manual; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/* /tmp/pear /tmp/imagick.tgz; \
    php -d extension=pcntl -r 'foreach (["bcmath", "exif", "ftp", "gd", "imagick", "intl", "pcntl", "pdo_mysql", "pdo_pgsql", "pdo_sqlite", "zip"] as $extension) { if (!extension_loaded($extension)) { throw new RuntimeException($extension." did not load"); } }'

WORKDIR /var/www/html
ENV MAGICK_CONFIGURE_PATH=/etc/lsky/image-magick
COPY docker/image-magick/policy.xml /etc/lsky/image-magick/policy.xml

FROM php-runtime AS php-source
COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_CACHE_DIR=/root/.cache/composer
COPY . .

FROM php-source AS development
RUN --mount=type=cache,target=/root/.cache/composer \
    composer install --no-interaction --no-progress --prefer-dist --no-scripts --no-plugins
COPY --from=frontend /src/public/ ./public/

FROM php-source AS vendor
RUN --mount=type=cache,target=/root/.cache/composer \
    composer install --no-dev --no-interaction --no-progress --prefer-dist \
        --optimize-autoloader --no-scripts --no-plugins

FROM php-runtime AS production
ENV APACHE_ENVVARS=/etc/apache2/lsky-envvars \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=info
COPY docker/apache/envvars /etc/apache2/lsky-envvars
COPY docker/apache/ports.conf /etc/apache2/ports.conf
COPY docker/apache/lsky.conf /etc/apache2/sites-available/lsky.conf
COPY docker/php/production.ini /usr/local/etc/php/conf.d/99-lsky.ini
RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    a2dissite 000-default; \
    a2ensite lsky; \
    a2enmod headers rewrite; \
    install -d -o www-data -g www-data -m 0750 \
        /tmp/apache2 /tmp/apache2/lock storage storage/app storage/app/media storage/app/uploads \
        storage/app/public storage/framework storage/framework/cache storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache
COPY app/ ./app/
COPY artisan composer.json composer.lock LICENSE ./
COPY bootstrap/ ./bootstrap/
COPY config/ ./config/
COPY database/ ./database/
COPY lang/ ./lang/
COPY resources/views/ ./resources/views/
COPY routes/ ./routes/
COPY --from=vendor /var/www/html/vendor/ ./vendor/
COPY --from=frontend /src/public/ ./public/
USER www-data
EXPOSE 8080
STOPSIGNAL SIGWINCH
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --start-interval=2s --retries=3 \
    CMD host="$(php -r 'echo parse_url(getenv("APP_URL") ?: "", PHP_URL_HOST);')" \
        && test -n "$host" \
        && curl --fail --silent --show-error --max-time 4 --header "Host: $host" \
            http://127.0.0.1:8080/up >/dev/null
CMD ["apache2-foreground"]
