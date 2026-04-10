FROM php:8.2-fpm-alpine

WORKDIR /app

# Cài packages hệ thống
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    curl \
    git \
    unzip \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    zlib-dev

# Cài PHP extensions cần cho Laravel
RUN docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy file composer trước để tận dụng cache
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

# Copy file npm trước để tận dụng cache
COPY package.json package-lock.json ./
RUN npm ci

# Copy source code
COPY . .

# Build frontend assets
RUN npm run build

# Tạo thư mục Laravel cần thiết
RUN mkdir -p \
    storage/logs \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    bootstrap/cache

# Phân quyền
RUN chown -R www-data:www-data /app \
    && chmod -R 775 storage bootstrap/cache

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Tạo default server block
RUN mkdir -p /etc/nginx/http.d \
    && cat << 'EOF' > /etc/nginx/http.d/default.conf
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

# Tạo script start
RUN cat << 'EOF' > /start.sh
#!/bin/sh
php artisan config:cache || true
php artisan route:cache || true
php-fpm -D
nginx -g "daemon off;"
EOF

RUN chmod +x /start.sh

EXPOSE 80

CMD ["sh", "/start.sh"]