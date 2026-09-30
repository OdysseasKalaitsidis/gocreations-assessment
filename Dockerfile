FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libonig-dev \
        libxml2-dev \
        unzip \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        dom \
        xml \
        xmlwriter \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-progress --prefer-dist

COPY . .

EXPOSE 8000

CMD ["php", "-d", "display_errors=0", "-d", "log_errors=1", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
