# Build stage - cài Composer dependencies với các extension cần thiết
FROM php:8.2-fpm-alpine AS builder

WORKDIR /app

# Cài runtime libs + build deps tạm thời để có ext-gd và ext-zip khi chạy Composer
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libwebp \
    zlib \
    libzip \
    && apk add --no-cache --virtual .build-deps \
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
    && docker-php-ext-install gd zip \
    && apk del .build-deps

# Cài đặt Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files và cài dependencies (production)
COPY composer.* ./
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist --no-dev

# Runtime stage
FROM php:8.2-fpm-alpine

WORKDIR /app

# Cài runtime dependencies + build deps tạm thời cho các PHP extensions cần thiết
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
    libzip \
    && apk add --no-cache --virtual .build-deps \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install pdo pdo_pgsql mbstring gd zip \
    
    && apk del .build-deps

# Copy vendor và composer từ builder
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Copy source code
COPY . .

# Cài npm dependencies (production only)
COPY package*.json ./
RUN npm install --production

# Copy nginx config
RUN mkdir -p /etc/nginx/conf.d
COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p \
    storage/logs \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    bootstrap/cache

# Set permissions - TRƯỚC composer dump-autoload
RUN chmod -R 777 storage bootstrap/cache && \
    chown -R www-data:www-data /app

# Composer optimize autoload (production)
RUN composer dump-autoload --optimize --no-dev

# Expose port
EXPOSE 80

# Start php-fpm và nginx
CMD ["sh", "-c", "php artisan migrate:fresh --seed && /start.sh"]