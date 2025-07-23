#!/bin/bash

# Script para corrigir permissões dos volumes do Automation Webhook
# Uso: ./fix-permissions.sh [cliente] [containerId]

VOLUMES_PATH="/etc/automation-webhook/volumes"
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🔧 Automation Webhook - Fix Permissions (Dynamic)${NC}"
echo "=================================================="

# Verificar se o diretório base existe
if [ ! -d "$VOLUMES_PATH" ]; then
    echo -e "${RED}❌ Erro: Diretório $VOLUMES_PATH não encontrado${NC}"
    echo "Execute o setup primeiro: sudo ./setup.sh"
    exit 1
fi

# Detectar informações do Docker dinamicamente
detect_docker_info() {
    echo -e "${BLUE}🔍 Detectando configurações do Docker...${NC}"
    
    # Detectar grupo docker
    DOCKER_GID=$(getent group docker 2>/dev/null | cut -d: -f3)
    if [ -n "$DOCKER_GID" ]; then
        echo "  ✅ Grupo docker encontrado (GID: $DOCKER_GID)"
    else
        echo -e "  ${RED}❌ Grupo docker não encontrado${NC}"
        DOCKER_GID=999  # Fallback
    fi
    
    # Detectar usuário atual
    CURRENT_USER=$(whoami)
    CURRENT_UID=$(id -u)
    CURRENT_GID=$(id -g)
    
    echo "  👤 Usuário atual: $CURRENT_USER (UID: $CURRENT_UID, GID: $CURRENT_GID)"
    
    # Verificar socket do Docker
    if [ -S "/var/run/docker.sock" ]; then
        SOCKET_INFO=$(ls -la /var/run/docker.sock)
        echo "  🔌 Socket Docker: $SOCKET_INFO"
    else
        echo -e "  ${RED}❌ Socket Docker não encontrado${NC}"
    fi
    
    # Para containers, usar UID 1000 mas GID do docker
    CONTAINER_UID=1000
    CONTAINER_GID=$DOCKER_GID
    
    echo "  🎯 Usando para containers: UID=$CONTAINER_UID, GID=$CONTAINER_GID"
    echo ""
}

# Função para corrigir permissões
fix_permissions() {
    local target_path="$1"
    local description="$2"
    local for_container="$3"
    
    echo -e "🔧 Corrigindo permissões: ${description}"
    
    # Definir permissões 777 (leitura/escrita completa)
    chmod -R 777 "$target_path" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo -e "  ✅ chmod 777 aplicado"
    else
        echo -e "  ${RED}❌ Erro ao aplicar chmod${NC}"
        return 1
    fi
    
    # Aplicar owner baseado no contexto
    if [ "$for_container" = "true" ]; then
        # Para containers: UID 1000 + GID do docker
        chown -R $CONTAINER_UID:$CONTAINER_GID "$target_path" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo -e "  ✅ chown $CONTAINER_UID:$CONTAINER_GID aplicado (container)"
        else
            echo -e "  ${RED}❌ Erro ao aplicar chown (executar como root?)${NC}"
            return 1
        fi
    else
        # Para diretórios base: usuário atual + grupo docker
        chown -R $CURRENT_UID:$DOCKER_GID "$target_path" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo -e "  ✅ chown $CURRENT_UID:$DOCKER_GID aplicado (base)"
        else
            echo -e "  ${RED}❌ Erro ao aplicar chown (executar como root?)${NC}"
            return 1
        fi
    fi
    
    return 0
}

# Detectar configurações
detect_docker_info

# Verificar se foi especificado cliente e containerId
if [ -n "$1" ] && [ -n "$2" ]; then
    CLIENT="$1"
    CONTAINER_ID="$2"
    TARGET_DIR="$VOLUMES_PATH/${CLIENT}_${CONTAINER_ID}"
    
    echo "Cliente: $CLIENT"
    echo "Container ID: $CONTAINER_ID"
    echo "Diretório: $TARGET_DIR"
    echo ""
    
    if [ -d "$TARGET_DIR" ]; then
        fix_permissions "$TARGET_DIR" "container específico ($CLIENT - $CONTAINER_ID)" "true"
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ Permissões corrigidas com sucesso!${NC}"
        fi
    else
        echo -e "${RED}❌ Erro: Container não encontrado${NC}"
        exit 1
    fi
    
elif [ -n "$1" ]; then
    CLIENT="$1"
    echo "Cliente: $CLIENT"
    echo "Diretório: $VOLUMES_PATH/${CLIENT}_*"
    echo ""
    
    # Encontrar todos os containers do cliente
    found_containers=false
    for container_dir in "$VOLUMES_PATH"/${CLIENT}_*; do
        if [ -d "$container_dir" ]; then
            found_containers=true
            container_name=$(basename "$container_dir")
            fix_permissions "$container_dir" "container $container_name" "true"
        fi
    done
    
    if [ "$found_containers" = false ]; then
        echo -e "${RED}❌ Erro: Nenhum container encontrado para o cliente $CLIENT${NC}"
        exit 1
    else
        echo -e "${GREEN}✅ Permissões corrigidas para todos os containers do cliente!${NC}"
    fi
    
else
    echo "Corrigindo permissões para todos os containers..."
    echo "Diretório: $VOLUMES_PATH"
    echo ""
    
    # Corrigir diretório base
    fix_permissions "$VOLUMES_PATH" "diretório base" "false"
    
    # Corrigir todos os subdiretórios como containers
    for container_dir in "$VOLUMES_PATH"/*/; do
        if [ -d "$container_dir" ]; then
            container_name=$(basename "$container_dir")
            fix_permissions "$container_dir" "container $container_name" "true"
        fi
    done
    
    echo -e "${GREEN}✅ Permissões corrigidas para todos os containers!${NC}"
fi

echo ""
echo "📋 Status atual dos diretórios:"
echo "================================"
ls -la "$VOLUMES_PATH" 2>/dev/null || echo "Nenhum container encontrado"

echo ""
echo -e "${YELLOW}💡 Dicas:${NC}"
echo "- Execute como root (sudo) para corrigir owner"
echo "- Permissões: 777 (rwxrwxrwx)"
echo "- Owner containers: 1000:docker_gid (dinâmico)"
echo "- Owner base: current_user:docker_gid (dinâmico)"
echo ""
echo "Configuração detectada:"
echo "- Docker GID: $DOCKER_GID"
echo "- Container UID:GID: $CONTAINER_UID:$CONTAINER_GID"
echo "- Base UID:GID: $CURRENT_UID:$DOCKER_GID"
echo ""
echo "Uso:"
echo "  ./fix-permissions.sh                    # Corrigir todos"
echo "  ./fix-permissions.sh cliente            # Corrigir cliente específico"
echo "  ./fix-permissions.sh cliente container  # Corrigir container específico"

# Testar diagnóstico via API se disponível
echo ""
echo -e "${BLUE}🔧 Diagnóstico Docker via API:${NC}"
if command -v curl > /dev/null 2>&1; then
    curl -s http://localhost/api/docker-diagnostic 2>/dev/null | grep -q "status" && \
        echo "Execute: curl http://localhost/api/docker-diagnostic" || \
        echo "API não disponível ou não respondendo"
else
    echo "curl não disponível para teste da API"
fi
