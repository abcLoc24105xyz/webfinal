# Build stage - chỉ để cài Composer dependencies
FROM php:8.2-fpm-alpine AS builder

WORKDIR /app

# Cài các tools cần thiết cho Composer
RUN apk add --no-cache \
    git \
    unzip

# Cài đặt Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files và cài dependencies
COPY composer.* ./
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist --no-dev

# Runtime stage
FROM php:8.2-fpm-alpine

WORKDIR /app

# Cài runtime dependencies + build dependencies tạm thời cho php extensions
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
    && apk add --no-cache --virtual .build-deps \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install pdo pdo_mysql mbstring gd \
    && apk del .build-deps

# Copy vendor từ builder
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Copy source code
COPY . .

# Cài npm dependencies (chỉ production)
COPY package*.json ./
RUN npm install --production

# Copy nginx config
RUN mkdir -p /etc/nginx/conf.d
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Tạo storage directories
RUN mkdir -p \
    storage/logs \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache

# Set permissions
RUN chown -R www-data:www-data /app

# Composer optimize autoload (cho production)
RUN composer dump-autoload --optimize --no-dev

# Expose port (Render hoặc các platform khác sẽ inject PORT)
EXPOSE 80

# Start php-fpm và nginx
CMD ["/bin/sh", "-c", "php-fpm & nginx -g 'daemon off;'"]