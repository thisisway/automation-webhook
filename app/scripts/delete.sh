#!/bin/bash

# Script para parar e remover containers
# Usage: delete.sh

set -e

WORK_DIR="$(pwd)"

echo "Deleting container in: $WORK_DIR"

# Verificar se docker-compose.yml existe
if [ ! -f "docker-compose.yml" ]; then
    echo "Error: docker-compose.yml not found in $WORK_DIR"
    exit 1
fi

echo "Stopping and removing Docker container..."

# Parar e remover containers, redes, volumes e imagens criados por up
docker compose down -v

# Verificar se existem containers ainda rodando
RUNNING_CONTAINERS=$(docker compose ps -q)
if [ -n "$RUNNING_CONTAINERS" ]; then
    echo "Warning: Some containers are still running:"
    docker compose ps
    
    # Forçar parada
    echo "Forcing container stop..."
    docker compose kill
    docker compose rm -f
fi

echo "Container deleted successfully!"

# Opcional: remover volumes órfãos relacionados
echo "Cleaning up orphaned volumes..."
docker volume prune -f >/dev/null 2>&1 || true

echo "Cleanup completed!"
exit 0
