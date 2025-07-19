# Dockerfile para Automation Webhook
FROM php:8.2-apache

# Instalar dependências necessárias
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    && docker-php-ext-install \
    pdo \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Configurar o DocumentRoot para /var/www/html/src
ENV APACHE_DOCUMENT_ROOT /var/www/html/src
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar os arquivos da aplicação
COPY src/ /var/www/html/src/

# Definir permissões
RUN chown -R www-data:www-data /var/www/html/src \
    && chmod -R 755 /var/www/html/src

# Expor a porta 80
EXPOSE 80

# Comando para iniciar o Apache
CMD ["apache2-foreground"]