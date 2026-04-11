FROM php:8.2-fpm-alpine

WORKDIR /app

RUN apk add --no-cache \
    nginx \
    bash \
    curl \
    git \
    unzip \
    icu-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    zlib-dev \
    nodejs \
    npm \
    fcgi \
    && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        bcmath \
        exif \
        intl \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json ./
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-dev \
    --optimize-autoloader \
    --no-scripts

COPY package.json package-lock.json* ./
RUN if [ -f package.json ]; then npm ci; fi

COPY . .

RUN if [ -f vite.config.js ]; then npm run build; else echo "Skip npm build"; fi

RUN mkdir -p \
    /app/storage/logs \
    /app/storage/framework/cache \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/app/public \
    /app/bootstrap/cache \
    /etc/nginx/http.d \
    && touch /app/storage/logs/laravel.log \
    && chown -R www-data:www-data /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache \
    && chmod -R 777 /app/storage/logs \
    && chmod 666 /app/storage/logs/laravel.log

RUN cat <<'EOF' > /etc/nginx/nginx.conf
user nginx;
worker_processes auto;

error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 100M;

    include /etc/nginx/http.d/*.conf;
}
EOF

RUN cat <<'EOF' > /start.sh
#!/bin/sh
set -e

mkdir -p /app/storage/logs
mkdir -p /app/storage/framework/cache
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/app/public
mkdir -p /app/bootstrap/cache

touch /app/storage/logs/laravel.log

chmod -R 775 /app/storage /app/bootstrap/cache || true
chmod -R 777 /app/storage/logs || true
chmod 666 /app/storage/logs/laravel.log || true

php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

rm -rf /app/public/storage || true
php artisan storage:link || true

PORT_TO_USE="${PORT:-10000}"

cat > /etc/nginx/http.d/default.conf <<EON
server {
    listen ${PORT_TO_USE};
    server_name _;

    root /app/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
EON

nginx -t

php-fpm -D
exec nginx -g "daemon off;"
EOF

RUN chmod +x /start.sh

EXPOSE 10000

CMD ["sh", "/start.sh"]