FROM php:8.1-cli


# Instala extensiones necesarias
RUN apt-get update && apt-get install -y \
    zip unzip git curl libicu-dev libonig-dev libzip-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql intl opcache zip

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Arranca php-fpm por defecto
CMD ["php-fpm"]
