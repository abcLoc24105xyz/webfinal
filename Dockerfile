FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

RUN apk add --no-cache postgresql-dev && \
    docker-php-ext-install pdo_pgsql

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV WEBROOT=/var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate

RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

RUN chmod -R 775 storage bootstrap/cache

CMD ["sh", "-c", "php artisan migrate:fresh --seed && /start.sh"]
