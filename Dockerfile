# imagem base do Apache com PHP 8.1
FROM php:8.3.0-zts-bullseye

ENV PHP_MEMORY_LIMIT=256M
    
# Instala as dependências necessárias para o PHP
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    && docker-php-ext-install intl 

RUN apt-get update && apt-get install -y docker.io curl


ENV COMPOSER_ALLOW_SUPERUSER=1

# instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# copia o código do projeto para o container
COPY . /var/www/html

WORKDIR /var/www/html

RUN composer install

EXPOSE 8001

# Configurar setup.php como entrypoint
ENTRYPOINT ["php", "/var/www/html/setup.php"]

# Iniciar o servidor PHP embutido
CMD ["php", "-S", "0.0.0.0:8001", "-t", "/var/www/html/public"]