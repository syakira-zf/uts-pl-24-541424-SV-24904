
FROM dunglas/frankenphp:1.9-php8.2-bookworm

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    xml \
    zip \
    bcmath \
    intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/cache \
       storage/framework/sessions \
       storage/framework/views \
       storage/logs \
       bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY Caddyfile /etc/caddy/Caddyfile

ENV SERVER_NAME=:80

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
