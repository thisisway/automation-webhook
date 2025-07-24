#!/bin/bash

# Script para testar e configurar acesso ao Docker
# Uso: ./test-docker-access.sh

YELLOW='\033[1;33m'
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🐳 Teste de Acesso ao Docker${NC}"
echo "=================================="

# Verificar se o Docker está instalado
if ! command -v docker > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker não está instalado${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Docker está instalado${NC}"

# Verificar versão do Docker
DOCKER_VERSION=$(docker --version 2>/dev/null)
echo -e "${BLUE}ℹ️  Versão: $DOCKER_VERSION${NC}"

# Verificar se o daemon está rodando
echo ""
echo -e "${BLUE}🔍 Verificando daemon Docker...${NC}"
if docker version > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Daemon Docker está rodando${NC}"
else
    echo -e "${RED}❌ Daemon Docker não está acessível${NC}"
    echo "Possíveis causas:"
    echo "  - Daemon não está rodando"
    echo "  - Usuário não tem permissão"
    echo "  - Socket não está configurado corretamente"
fi

# Verificar usuário atual
CURRENT_USER=$(whoami)
CURRENT_UID=$(id -u)
CURRENT_GID=$(id -g)

echo ""
echo -e "${BLUE}👤 Usuário atual: $CURRENT_USER (UID: $CURRENT_UID, GID: $CURRENT_GID)${NC}"

# Verificar grupos do usuário
USER_GROUPS=$(groups)
echo -e "${BLUE}👥 Grupos: $USER_GROUPS${NC}"

# Verificar se está no grupo docker
if groups | grep -q '\bdocker\b'; then
    echo -e "${GREEN}✅ Usuário está no grupo docker${NC}"
else
    echo -e "${RED}❌ Usuário NÃO está no grupo docker${NC}"
fi

# Verificar socket do Docker
echo ""
echo -e "${BLUE}🔌 Verificando socket Docker...${NC}"
if [ -S "/var/run/docker.sock" ]; then
    SOCKET_INFO=$(ls -la /var/run/docker.sock)
    echo -e "${GREEN}✅ Socket encontrado: $SOCKET_INFO${NC}"
    
    # Verificar permissões do socket
    if [ -r "/var/run/docker.sock" ] && [ -w "/var/run/docker.sock" ]; then
        echo -e "${GREEN}✅ Socket é legível e gravável${NC}"
    else
        echo -e "${RED}❌ Socket não tem permissões adequadas${NC}"
    fi
else
    echo -e "${RED}❌ Socket Docker não encontrado${NC}"
fi

# Teste prático de comando Docker
echo ""
echo -e "${BLUE}🧪 Testando comando Docker...${NC}"
if docker ps > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Comando 'docker ps' funcionou${NC}"
    
    # Contar containers
    CONTAINER_COUNT=$(docker ps -q | wc -l)
    echo -e "${BLUE}ℹ️  Containers rodando: $CONTAINER_COUNT${NC}"
    
    # Teste de criação de container
    echo ""
    echo -e "${BLUE}🧪 Testando criação de container...${NC}"
    if docker run --rm hello-world > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Pode criar containers${NC}"
    else
        echo -e "${RED}❌ NÃO pode criar containers${NC}"
    fi
else
    echo -e "${RED}❌ Comando 'docker ps' falhou${NC}"
    
    # Tentar com sudo
    echo -e "${BLUE}🔄 Tentando com sudo...${NC}"
    if sudo docker ps > /dev/null 2>&1; then
        echo -e "${YELLOW}⚠️  Funciona com sudo, mas não sem sudo${NC}"
        echo "Solução: Adicionar usuário ao grupo docker"
    else
        echo -e "${RED}❌ Nem com sudo funciona${NC}"
    fi
fi

# Verificar se é ambiente Docker-in-Docker
echo ""
echo -e "${BLUE}🐳 Verificando ambiente...${NC}"
if [ -f /.dockerenv ]; then
    echo -e "${YELLOW}ℹ️  Executando dentro de um container Docker${NC}"
else
    echo -e "${BLUE}ℹ️  Executando no host${NC}"
fi

# Sugestões de correção
echo ""
echo -e "${YELLOW}💡 Sugestões de Correção:${NC}"

if ! groups | grep -q '\bdocker\b'; then
    echo "1. Adicionar usuário ao grupo docker:"
    echo "   sudo usermod -aG docker $CURRENT_USER"
    echo ""
fi

if [ -S "/var/run/docker.sock" ]; then
    echo "2. Corrigir permissões do socket:"
    echo "   sudo chown root:docker /var/run/docker.sock"
    echo "   sudo chmod 660 /var/run/docker.sock"
    echo ""
fi

echo "3. Reiniciar serviço/sessão após mudanças nos grupos"
echo "4. Em containers: usar 'privileged: true' no docker-compose.yml"
echo "5. Montar socket com permissões de escrita: '/var/run/docker.sock:/var/run/docker.sock:rw'"

echo ""
echo -e "${BLUE}📋 Para testar via API:${NC}"
echo "curl http://localhost/api/docker-diagnostic"
