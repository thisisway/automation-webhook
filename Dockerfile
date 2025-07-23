FROM php:8.3-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    ca-certificates \
    gnupg \
    lsb-release \
    && rm -rf /var/lib/apt/lists/*

# Instalar Docker CLI e Docker Compose
RUN curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg \
    && echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null \
    && apt-get update \
    && apt-get install -y docker-ce-cli docker-compose-plugin \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP necessárias
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite e mod_headers do Apache
RUN a2enmod rewrite headers

# Configurar Apache para usar o document root correto
ENV APACHE_DOCUMENT_ROOT=/var/www/html/app
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configurar permissões para o usuário www-data acessar Docker socket
RUN usermod -aG root www-data

# Criar diretório de trabalho
WORKDIR /var/www/html

# Copiar composer.json primeiro (se existir) para cache de dependências
COPY composer.json* ./

# Instalar dependências do Composer (se existir composer.json)
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader --no-scripts; fi

# Copiar arquivos do projeto
COPY . .

RUN groupadd -g 1001 docker && usermod -aG docker www-data

# Copiar script de boot
COPY boot.sh /usr/local/bin/boot.sh
RUN chmod +x /usr/local/bin/boot.sh

# Criar diretórios necessários e definir permissões corretas
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html

# Expor porta 80
EXPOSE 80

# Script de inicialização que executa o boot e depois o Apache
RUN echo '#!/bin/bash\n/usr/local/bin/boot.sh &\nexec apache2-foreground' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Comando para iniciar o sistema
CMD ["/usr/local/bin/start.sh"]