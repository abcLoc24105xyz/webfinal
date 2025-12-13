# Build stage
FROM php:8.2-fpm-alpine AS builder

WORKDIR /app

RUN apk add --no-cache \
    curl \
    git \
    zip \
    unzip \
    oniguruma-dev

RUN docker-php-ext-install pdo pdo_mysql mbstring

# Cài dependency cho mbstring ở runtime
RUN apk add --no-cache oniguruma-dev

# Cài đặt Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.* ./
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist

# Runtime stage
FROM php:8.2-fpm-alpine

WORKDIR /app

RUN apk add --no-cache \
    curl \
    libpq \
    oniguruma \
    nginx \
    nodejs \
    npm

RUN docker-php-ext-install pdo pdo_mysql mbstring

# Copy từ builder
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Copy source code
COPY . .

# Cài đặt npm dependencies
COPY package*.json ./
RUN npm install --production

# Copy nginx config
RUN mkdir -p /etc/nginx/conf.d
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Tạo storage directories
RUN mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache

# Set permissions
RUN chown -R www-data:www-data /app

# Composer autoload
RUN composer dump-autoload --optimize

# Expose port (Render sẽ gán PORT từ env)
EXPOSE 80

# Start services
CMD ["/bin/sh", "-c", "php-fpm & nginx -g 'daemon off;'"]