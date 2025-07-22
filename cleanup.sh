#!/bin/bash

echo "=== Automation Webhook Cleanup Script ==="
echo "Este script irá limpar containers parados e reinicializar o sistema"

# Função para confirmar ação
confirm() {
    local message=$1
    echo -n "$message [y/N]: "
    read -r response
    case "$response" in
        [yY]|[yY][eE][sS])
            return 0
            ;;
        *)
            return 1
            ;;
    esac
}

# Parar containers principais se estiverem rodando
echo "🛑 Parando containers principais..."
docker stop traefik portainer 2>/dev/null || true

# Remover containers principais se existirem
echo "🗑️  Removendo containers principais..."
docker rm traefik portainer 2>/dev/null || true

# Limpar containers orphaned/dangling
echo "🧹 Limpando containers órfãos..."
docker container prune -f

# Limpar imagens não utilizadas
if confirm "Deseja remover imagens Docker não utilizadas?"; then
    echo "🧹 Limpando imagens não utilizadas..."
    docker image prune -f
fi

# Limpar volumes não utilizados
if confirm "Deseja remover volumes Docker não utilizados?"; then
    echo "🧹 Limpando volumes não utilizados..."
    docker volume prune -f
fi

echo ""
echo "✅ Limpeza concluída!"
echo "🚀 Execute o boot.sh para reinicializar o sistema"
