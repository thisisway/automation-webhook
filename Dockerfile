# imagem base do Apache com PHP 8.1
FROM php:8.3.0-zts-bullseye

ENV PHP_MEMORY_LIMIT=256M
    
# Instala as dependências necessárias para o PHP
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    libsqlite3-dev \
    default-mysql-client \
    && docker-php-ext-install intl pdo pdo_mysql pdo_sqlite

RUN apt-get update && apt-get install -y docker.io curl


ENV COMPOSER_ALLOW_SUPERUSER=1
ENV DB_CONNECTION=sqlite
ENV SQLITE_DATABASE=/etc/automation-webhook/database/database.sqlite

# instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# copia o código do projeto para o container
COPY . /var/www/html

WORKDIR /var/www/html

RUN mkdir -p /var/www/html/storage/logs && \
    touch /var/www/html/storage/logs/error.log && \
    chmod -R 775 /var/www/html/storage

RUN composer install

# Torna o setup.php executável
RUN chmod +x setup.php

# Cria um script de entrada
RUN echo '#!/bin/bash\n\
if [ "$1" = "setup" ]; then\n\
    echo "Iniciando setup..."\n\
    mkdir -p /etc/automation-webhook/database\n\
    touch /etc/automation-webhook/database/database.sqlite\n\
    php setup.php setup\n\
    php cello migrate\n\
    php cello console seed\n\
    echo "Setup completo finalizado!"\n\
else\n\
    mkdir -p /etc/automation-webhook/database\n\
    touch /etc/automation-webhook/database/database.sqlite\n\
    php cello migrate\n\
    php cello console seed\n\
    echo "Setup completo finalizado!"\n\
    echo "Iniciando servidor web na porta 8001..."\n\
    exec php -S 0.0.0.0:8001 -t /var/www/html/public\n\
fi' > /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 8001

# Define o script de entrada
ENTRYPOINT ["/entrypoint.sh"]