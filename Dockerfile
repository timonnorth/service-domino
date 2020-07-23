FROM php:7.4-fpm
RUN docker-php-ext-install sysvsem
RUN docker-php-ext-install gettext
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y zip unzip

WORKDIR /code
COPY composer.* ./
RUN composer install --optimize-autoloader --no-dev

COPY . .
