# imagem base do Apache com PHP 8.1
FROM php:8.3.0-zts-bullseye

ENV PHP_MEMORY_LIMIT=256M
    
# Instala as dependências necessárias para o PHP
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    libssl-dev \
    pkg-config \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        intl \
        zip \
        mysqli \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        gd

# Instalar e habilitar o Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Instalar e habilitar o MongoDB
# RUN pecl install mongodb && docker-php-ext-enable mongodb

ENV COMPOSER_ALLOW_SUPERUSER=1

# instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# copia o código do projeto para o container
COPY . /var/www/html

WORKDIR /var/www/html

RUN mkdir -p storage/temp
RUN mkdir -p storage/logs

RUN composer install

RUN echo "max_execution_time = 600" > /usr/local/etc/php/conf.d/max_execution_time.ini

# Configura o tempo de expiração da sessão PHP para 1 dia (86400 segundos)
RUN echo "session.gc_maxlifetime = 86400" > /usr/local/etc/php/conf.d/session_lifetime.ini


EXPOSE 80

# Iniciar o servidor PHP embutido
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html/public"]