FROM php8.2-cli

# 必要ライブラリ
RUN apt-get update && apt-get install -y 
    git unzip libzip-dev 
 && docker-php-ext-install zip pdo pdo_mysql

# Composer
COPY --from=composer2 usrbincomposer usrbincomposer

WORKDIR app
COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 10000
CMD php artisan serve --host=0.0.0.0 --port=10000
