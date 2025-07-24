#!/bin/bash

# Script para criar e iniciar containers
# Usage: create.sh [software]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WORK_DIR="$(pwd)"

echo "Creating container in: $WORK_DIR"

# Verificar se docker-compose.yml existe
if [ ! -f "docker-compose.yml" ]; then
    echo "Error: docker-compose.yml not found in $WORK_DIR"
    exit 1
fi

# Criar diretórios de dados se não existirem
if [ ! -d "data" ]; then
    mkdir -p data
fi

# Definir permissões corretas
chmod -R 755 data
chown -R www-data:www-data data 2>/dev/null || true

echo "Starting Docker container..."

# Iniciar o container
docker compose up -d

# Verificar se o container foi criado
if docker compose ps | grep -q "Up"; then
    echo "Container started successfully!"
    
    # Obter o nome do container
    CONTAINER_NAME=$(docker compose ps --services | head -n1)
    CONTAINER_ID=$(docker compose ps -q | head -n1)
    
    echo "Container Name: $CONTAINER_NAME"
    echo "Container ID: $CONTAINER_ID"
    
    # Aguardar um pouco para o serviço inicializar
    echo "Waiting for service to initialize..."
    sleep 10
    
    # Verificar se o container ainda está rodando
    if docker ps | grep -q "$CONTAINER_ID"; then
        echo "Container is running healthy!"
        exit 0
    else
        echo "Warning: Container may have issues. Check logs with: docker compose logs"
        exit 1
    fi
else
    echo "Error: Failed to start container"
    docker compose logs
    exit 1
fi
