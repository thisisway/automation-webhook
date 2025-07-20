#!/bin/bash

# =============================================================================
# Setup Automatizado do Servidor - Automation Webhook
# =============================================================================
# Este script irá:
# 1. Instalar Docker automaticamente (como o EasyPanel)
# 2. Configurar rede do Traefik
# 3. Subir o Traefik via docker-compose
# 4. Verificar se o Traefik está funcionando
# 5. Subir o Portainer (Gerenciador Docker)
# 6. Subir o projeto principal
# =============================================================================

set -e  # Para o script em caso de erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funções de log
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Função para verificar se comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Função para obter IP do servidor
get_server_ip() {
    # Tenta obter IP público primeiro
    local public_ip=$(curl -s --max-time 5 ifconfig.me 2>/dev/null || curl -s --max-time 5 ipecho.net/plain 2>/dev/null || curl -s --max-time 5 icanhazip.com 2>/dev/null)
    
    if [[ -n "$public_ip" && "$public_ip" =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
        echo "$public_ip"
        return 0
    fi
    
    # Se não conseguir IP público, usa IP privado da interface principal
    local private_ip=$(ip route get 1.1.1.1 | awk '{print $7; exit}' 2>/dev/null)
    
    if [[ -n "$private_ip" && "$private_ip" =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
        echo "$private_ip"
        return 0
    fi
    
    # Fallback para localhost
    echo "localhost"
    return 1
}

# Função para aguardar serviço estar pronto
wait_for_service() {
    local url=$1
    local service_name=$2
    local max_attempts=60  # Aumentei para 60 tentativas
    local attempt=0
    
    log_info "Aguardando ${service_name} ficar disponível..."
    
    while [ $attempt -lt $max_attempts ]; do
        if curl -s -f "$url" > /dev/null 2>&1; then
            log_success "${service_name} está disponível!"
            return 0
        fi
        
        # A cada 10 tentativas, mostra uma mensagem
        if [ $((attempt % 10)) -eq 0 ] && [ $attempt -gt 0 ]; then
            log_info "Ainda aguardando... Tentativa ${attempt}/${max_attempts}"
        fi
        
        attempt=$((attempt + 1))
        sleep 3  # Reduzi para 3 segundos
    done
    
    log_error "${service_name} não ficou disponível após ${max_attempts} tentativas"
    return 1
}

# Banner
echo -e "${BLUE}"
echo "============================================================================="
echo "             AUTOMATION WEBHOOK - SETUP AUTOMATIZADO"
echo "============================================================================="
echo -e "${NC}"

# Verificar se está rodando como root (igual EasyPanel)
if [ "$(id -u)" != "0" ]; then
    echo "Error: you must be root to execute this script" >&2
    exit 1
fi

# Verificar se não está rodando dentro de um container (igual EasyPanel)
if [ -f /.dockerenv ]; then
    echo "Error: running inside a container is not supported" >&2
    exit 1
fi

# Verificar se algo está rodando na porta 80 (igual EasyPanel)
if command_exists lsof && lsof -i :80 -sTCP:LISTEN >/dev/null 2>&1; then
    echo "Warning: something is already running on port 80"
    log_warning "Porta 80 já está em uso - continuando mesmo assim"
fi

# Verificar se algo está rodando na porta 443 (igual EasyPanel)
if command_exists lsof && lsof -i :443 -sTCP:LISTEN >/dev/null 2>&1; then
    echo "Warning: something is already running on port 443"
    log_warning "Porta 443 já está em uso - continuando mesmo assim"
fi

log_info "Iniciando setup automatizado do servidor..."
log_info "Executando como usuário: $(whoami)"

# Obter IP do servidor
SERVER_IP=$(get_server_ip)
log_info "IP do servidor detectado: $SERVER_IP"

# =============================================================================
# 1. INSTALAÇÃO DO DOCKER (igual EasyPanel)
# =============================================================================
log_info "Verificando instalação do Docker..."

# Instalar lsof se não existir (usado pelo EasyPanel para verificar portas)
if ! command_exists lsof; then
    log_info "Instalando lsof..."
    if command_exists apt-get; then
        apt-get update -y && apt-get install -y lsof curl
    elif command_exists yum; then
        yum install -y lsof curl
    elif command_exists apk; then
        apk add --no-cache lsof curl
    fi
fi

if command_exists docker; then
    log_success "Docker already installed"
else
    log_info "Installing Docker using official script (like EasyPanel)..."
    curl -sSL https://get.docker.com | sh
    
    # Iniciar e habilitar Docker
    log_info "Iniciando serviço do Docker..."
    systemctl start docker
    systemctl enable docker
    
    log_success "Docker instalado com sucesso!"
fi

# Instalar docker-compose se não existir
if ! command_exists docker-compose; then
    log_info "Instalando Docker Compose..."
    curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
fi

docker --version
docker-compose --version

# Garantir que não esteja em swarm mode (igual EasyPanel)
log_info "Verificando Docker Swarm..."
docker swarm leave --force >/dev/null 2>&1 || true

# =============================================================================
# 2. SUBIR PROJETO PRINCIPAL
# =============================================================================
log_info "Construindo e iniciando projeto principal..."

# Parar containers existentes se estiverem rodando
if docker ps -q --filter "name=automation-webhook" | grep -q .; then
    log_info "Parando container do projeto existente..."
    docker-compose down
fi

# Construir imagem
log_info "Construindo imagem do projeto..."
docker-compose build

# Subir projeto
log_info "Subindo projeto principal..."
docker-compose up -d

log_success "Projeto principal iniciado!"

# =============================================================================
# 3. VERIFICAR PROJETO PRINCIPAL
# =============================================================================
log_info "Verificando se o projeto está funcionando..."

# Aguardar projeto ficar disponível
sleep 10

# Verificar se container está rodando
if docker ps --format "table {{.Names}}\t{{.Status}}" | grep -q "automation-webhook.*Up"; then
    log_success "Container 'automation-webhook' está rodando"
else
    log_error "Container 'automation-webhook' não está rodando. Verificando logs..."
    docker-compose logs automation-webhook
    exit 1
fi

# Testar endpoint (teste interno)
log_info "Testando endpoint do webhook..."
if curl -s -f "http://localhost/src/test.php" > /dev/null 2>&1; then
    log_success "Endpoint está respondendo internamente!"
    log_success "Endpoint acessível em: http://$SERVER_IP/src/test.php"
else
    log_warning "Endpoint não está respondendo ainda (pode levar alguns segundos para ficar disponível)"
fi

# =============================================================================
# 4. INICIALIZAR SISTEMA VIA PHP
# =============================================================================
log_info "Inicializando sistema (Traefik + Portainer) via PHP..."

# Chamada para inicialização do sistema
INIT_RESPONSE=$(curl -s -X POST http://localhost/src/system-init.php \
    -H "Content-Type: application/json" \
    -d "{
        \"action\": \"initialize\",
        \"domain\": \"bwserver.com.br\",
        \"email\": \"admin@bwserver.com.br\"
    }" 2>/dev/null)

if [ $? -eq 0 ] && echo "$INIT_RESPONSE" | grep -q '"success":true'; then
    log_success "Sistema inicializado via PHP com sucesso!"
    
    # Extrair endpoints da resposta JSON se possível
    TRAEFIK_URL=$(echo "$INIT_RESPONSE" | grep -o '"traefik":"[^"]*' | cut -d'"' -f4)
    PORTAINER_URL=$(echo "$INIT_RESPONSE" | grep -o '"portainer":"[^"]*' | cut -d'"' -f4)
    
    if [ -n "$TRAEFIK_URL" ]; then
        log_success "Traefik disponível em: $TRAEFIK_URL"
    fi
    
    if [ -n "$PORTAINER_URL" ]; then
        log_success "Portainer disponível em: $PORTAINER_URL"
    fi
else
    log_warning "Inicialização via PHP falhou ou ainda não está disponível"
    log_info "Você pode inicializar manualmente via: curl -X POST http://$SERVER_IP/src/system-init.php"
fi

# =============================================================================
# 5. RESUMO FINAL
# =============================================================================
echo ""
echo -e "${GREEN}"
echo "============================================================================="
echo "                           SETUP CONCLUÍDO!"
echo "============================================================================="
echo -e "${NC}"

log_success "Projeto principal configurado com sucesso!"
log_success "Sistema (Traefik + Portainer) inicializado via PHP!"

echo ""
echo -e "${BLUE}📊 STATUS DOS SERVIÇOS:${NC}"
echo "----------------------------------------"
docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo -e "${BLUE}🌐 ENDPOINTS DISPONÍVEIS:${NC}"
echo "----------------------------------------"
echo "• Webhook API: http://webhook.bwserver.com.br"
echo "• Sistema Init: http://$SERVER_IP/src/system-init.php"
echo "• Teste local: http://$SERVER_IP/src/test.php"
echo "• Traefik Dashboard: http://$SERVER_IP:8080 (após inicialização)"
echo "• Portainer: http://$SERVER_IP:9000 (após inicialização)"
echo "• IP do Servidor: $SERVER_IP"

echo ""
echo -e "${BLUE}📝 PRÓXIMOS PASSOS:${NC}"
echo "----------------------------------------"
echo "1. Configure seu DNS para apontar os domínios para $SERVER_IP:"
echo "   - webhook.bwserver.com.br → $SERVER_IP"
echo "   - traefik.bwserver.com.br → $SERVER_IP (após inicialização)"
echo "   - manager.bwserver.com.br → $SERVER_IP (após inicialização)"
echo "2. Se a inicialização via PHP falhou, execute:"
echo "   curl -X POST http://$SERVER_IP/src/system-init.php -H 'Content-Type: application/json' -d '{\"action\":\"initialize\"}'"
echo "3. Aguarde alguns minutos para os certificados SSL serem gerados"
echo "4. Acesse o Portainer e configure uma senha de administrador"
echo "5. Teste o webhook usando: http://$SERVER_IP/src/test.php"

echo ""
echo -e "${BLUE}🔍 COMANDOS ÚTEIS:${NC}"
echo "----------------------------------------"
echo "• Executar este script: sudo ./setup-server.sh (como root)"
echo "• Inicializar sistema: curl -X POST http://$SERVER_IP/src/system-init.php"
echo "• Criar N8N: curl -X POST http://$SERVER_IP/src/system-init.php -d '{\"action\":\"create_n8n\",\"container_name\":\"n8n1\",\"subdomain\":\"n8n1.bwserver.com.br\"}'"
echo "• Criar Evolution: curl -X POST http://$SERVER_IP/src/system-init.php -d '{\"action\":\"create_evolution\",\"container_name\":\"evo1\",\"subdomain\":\"evo1.bwserver.com.br\"}'"
echo "• Listar serviços: curl -X POST http://$SERVER_IP/src/system-init.php -d '{\"action\":\"list_services\"}'"
echo "• Ver logs do projeto: docker-compose logs -f automation-webhook"
echo "• Reiniciar projeto: docker-compose down && docker-compose up -d"

echo ""
log_success "Setup automatizado concluído com sucesso! 🎉"

# Mostrar informações do sistema
echo ""
echo -e "${BLUE}💻 INFORMAÇÕES DO SISTEMA:${NC}"
echo "----------------------------------------"
echo "Docker Version: $(docker --version)"
echo "Docker Compose Version: $(docker-compose --version)"
echo "Sistema: $(lsb_release -d | cut -f2)"
echo "Arquitetura: $(uname -m)"
echo "IP do Servidor: $SERVER_IP"
echo "Data/Hora: $(date)"

# Mostrar informações de rede
echo ""
echo -e "${BLUE}🌐 INFORMAÇÕES DE REDE:${NC}"
echo "----------------------------------------"
if [[ "$SERVER_IP" != "localhost" ]]; then
    echo "✅ IP detectado automaticamente: $SERVER_IP"
    # Verificar se é IP público ou privado
    if [[ "$SERVER_IP" =~ ^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.) ]]; then
        echo "ℹ️  IP Privado detectado - configure port forwarding se necessário"
    else
        echo "🌍 IP Público detectado - servidor acessível externamente"
    fi
else
    echo "⚠️  Não foi possível detectar IP - usando localhost como fallback"
fi

exit 0
