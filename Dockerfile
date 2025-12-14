# Build stage - chỉ cài Composer dependencies với các extension cần thiết cho composer install
FROM php:8.2-fpm-alpine AS builder

WORKDIR /app

# Cài build dependencies tạm thời để compile các extension mà Composer có thể cần (gd, zip)
RUN apk add --no-cache --virtual .build-deps \
        git \
        unzip \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        zlib-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install gd zip mbstring \
    && apk del .build-deps

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files và cài dependencies (production only)
COPY composer.* ./
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist --no-dev

# Runtime stage
FROM php:8.2-fpm-alpine

WORKDIR /app

# Cài các runtime packages cần thiết
RUN apk add --no-cache \
        curl \
        libpq \
        oniguruma \
        nginx \
        nodejs \
        npm \
        libpng \
        libjpeg-turbo \
        freetype \
        libwebp \
        zlib \
        libzip

# Cài các PHP extensions cần cho ứng dụng Laravel
RUN apk add --no-cache --virtual .build-deps \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip \
    && apk del .build-deps

# Copy vendor và composer từ builder stage
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Copy toàn bộ source code
COPY . .

# Cài npm dependencies (production only)
RUN npm ci --only=production

# Tạo các thư mục cần thiết cho Laravel
RUN mkdir -p \
        storage/logs \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache \
        bootstrap/cache

# Set quyền sở hữu và permissions cho các thư mục Laravel
RUN chown -R www-data:www-data /app \
    && chmod -R 775 storage bootstrap/cache

# Tạo file start.sh để khởi động nginx + php-fpm
RUN echo '#!/bin/sh' > /start.sh \
    && echo 'php-fpm -D' >> /start.sh \
    && echo 'nginx -g "daemon off;"' >> /start.sh \
    && chmod +x /start.sh

# Copy nginx config ưu tiên (nếu có file docker/nginx.conf thì dùng, nếu không thì tạo default config cho Laravel)
RUN mkdir -p /etc/nginx/conf.d

RUN cat << 'EOF' > /etc/nginx/conf.d/default.conf
server {
    listen 80;
    server_name localhost;

    root /app/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Optimize Composer autoload cho production
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist --no-dev --ignore-platform-reqs

# Expose port
EXPOSE 80

# Chạy migrate (nếu cần) rồi khởi động services
CMD ["sh", "-c", "php artisan migrate --force && /start.sh"]