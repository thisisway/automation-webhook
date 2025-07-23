#!/bin/bash

# Script para corrigir permissões dos volumes do Automation Webhook
# Uso: ./fix-permissions.sh [cliente] [containerId]

VOLUMES_PATH="/etc/automation-webhook/volumes"
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🔧 Automation Webhook - Fix Permissions${NC}"
echo "======================================="

# Verificar se o diretório base existe
if [ ! -d "$VOLUMES_PATH" ]; then
    echo -e "${RED}❌ Erro: Diretório $VOLUMES_PATH não encontrado${NC}"
    echo "Execute o setup primeiro: sudo ./setup.sh"
    exit 1
fi

# Função para corrigir permissões
fix_permissions() {
    local target_path="$1"
    local description="$2"
    
    echo -e "🔧 Corrigindo permissões: ${description}"
    
    # Definir permissões 777 (leitura/escrita completa)
    chmod -R 777 "$target_path" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo -e "  ✅ chmod 777 aplicado"
    else
        echo -e "  ${RED}❌ Erro ao aplicar chmod${NC}"
        return 1
    fi
    
    # Definir proprietário 1000:1000 (usuário padrão dos containers)
    chown -R 1000:1000 "$target_path" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo -e "  ✅ chown 1000:1000 aplicado"
    else
        echo -e "  ${RED}❌ Erro ao aplicar chown (executar como root?)${NC}"
        return 1
    fi
    
    return 0
}

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
        fix_permissions "$TARGET_DIR" "container específico ($CLIENT - $CONTAINER_ID)"
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
            fix_permissions "$container_dir" "container $container_name"
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
    
    fix_permissions "$VOLUMES_PATH" "todos os containers"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Permissões corrigidas para todos os containers!${NC}"
    fi
fi

echo ""
echo "📋 Status atual dos diretórios:"
echo "================================"
ls -la "$VOLUMES_PATH" 2>/dev/null || echo "Nenhum container encontrado"

echo ""
echo -e "${YELLOW}💡 Dicas:${NC}"
echo "- Execute como root (sudo) para corrigir owner"
echo "- Permissões: 777 (rwxrwxrwx)"
echo "- Owner: 1000:1000 (usuário padrão dos containers)"
echo ""
echo "Uso:"
echo "  ./fix-permissions.sh                    # Corrigir todos"
echo "  ./fix-permissions.sh cliente            # Corrigir cliente específico"
echo "  ./fix-permissions.sh cliente container  # Corrigir container específico"
