FROM php:8.2-fpm-alpine

WORKDIR /app

# Cài packages hệ thống và thư viện cần để build PHP extensions
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    curl \
    git \
    unzip \
    bash \
    icu-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    zlib-dev

# Cài PHP extensions cần cho Laravel / MySQL / Vite-related packages
RUN docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        bcmath \
        exif \
        intl

# Cài Composer từ image chính thức
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy file composer trước để tận dụng cache Docker
COPY composer.json composer.lock ./

# Cài PHP dependencies, bỏ scripts để tránh lỗi artisan lúc chưa có .env/app key
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-dev \
    --optimize-autoloader \
    --no-scripts

# Copy file npm trước để tận dụng cache
COPY package.json package-lock.json ./

# Cài npm dependencies
RUN npm ci

# Copy toàn bộ source code
COPY . .

# Build frontend assets
RUN npm run build

# Tạo các thư mục cần cho Laravel
RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

# Phân quyền
RUN chown -R www-data:www-data /app \
    && chmod -R 775 storage bootstrap/cache

# Cấu hình nginx
RUN mkdir -p /etc/nginx/http.d

COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN cat <<'EOF' > /etc/nginx/http.d/default.conf
server {
    listen 80;
    server_name _;

    root /app/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Script khởi động
RUN cat <<'EOF' > /start.sh
#!/bin/sh

php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

php-fpm -D
nginx -g "daemon off;"
EOF

RUN chmod +x /start.sh

EXPOSE 80

CMD ["sh", "/start.sh"]