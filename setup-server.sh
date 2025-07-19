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

# Verificar se está rodando como root
if [[ $EUID -ne 0 ]]; then
   log_error "Este script deve ser executado como root"
   exit 1
fi

log_info "Iniciando setup automatizado do servidor..."

# Obter IP do servidor
SERVER_IP=$(get_server_ip)
log_info "IP do servidor detectado: $SERVER_IP"

# =============================================================================
# 1. INSTALAÇÃO DO DOCKER
# =============================================================================
log_info "Verificando instalação do Docker..."

if command_exists docker && command_exists docker-compose; then
    log_success "Docker já está instalado"
    docker --version
    docker-compose --version
    
    # Verificar se curl está instalado
    if ! command_exists curl; then
        log_info "Instalando curl..."
        apt-get update -y
        apt-get install -y curl net-tools
    fi
else
    log_info "Docker não encontrado. Iniciando instalação..."
    
    # Atualizar repositórios
    log_info "Atualizando repositórios do sistema..."
    apt-get update -y
    
    # Instalar dependências
    log_info "Instalando dependências..."
    apt-get install -y \
        ca-certificates \
        curl \
        gnupg \
        lsb-release \
        software-properties-common \
        apt-transport-https \
        net-tools
    
    # Adicionar chave GPG oficial do Docker
    log_info "Adicionando chave GPG do Docker..."
    mkdir -p /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    
    # Adicionar repositório do Docker
    log_info "Adicionando repositório do Docker..."
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Atualizar repositórios novamente
    apt-get update -y
    
    # Instalar Docker
    log_info "Instalando Docker..."
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    
    # Instalar Docker Compose standalone (para compatibilidade)
    log_info "Instalando Docker Compose standalone..."
    curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    
    # Iniciar e habilitar Docker
    log_info "Iniciando serviço do Docker..."
    systemctl start docker
    systemctl enable docker
    
    # Adicionar usuário atual ao grupo docker (se não for root)
    if [[ "$SUDO_USER" ]]; then
        usermod -aG docker $SUDO_USER
        log_info "Usuário $SUDO_USER adicionado ao grupo docker"
    fi
    
    log_success "Docker instalado com sucesso!"
    docker --version
    docker-compose --version
fi

# =============================================================================
# 2. CONFIGURAÇÃO DA REDE TRAEFIK
# =============================================================================
log_info "Configurando rede do Traefik..."

# Verificar se a rede já existe
if docker network ls | grep -q "traefik"; then
    log_warning "Rede 'traefik' já existe"
else
    log_info "Criando rede 'traefik'..."
    docker network create traefik
    log_success "Rede 'traefik' criada com sucesso!"
fi

# =============================================================================
# 3. PREPARAR ARQUIVOS DE CONFIGURAÇÃO
# =============================================================================
log_info "Preparando arquivos de configuração..."

# Verificar se acme.json existe e tem permissões corretas
if [[ ! -f "acme.json" ]]; then
    log_info "Criando arquivo acme.json..."
    touch acme.json
fi

log_info "Configurando permissões do acme.json..."
chmod 600 acme.json

log_success "Arquivos de configuração preparados!"

# =============================================================================
# 4. SUBIR TRAEFIK
# =============================================================================
log_info "Iniciando Traefik..."

# Parar containers existentes se estiverem rodando
if docker ps -q --filter "name=traefik" | grep -q .; then
    log_info "Parando container Traefik existente..."
    docker-compose -f docker-compose-traefik.yml down
fi

# Subir Traefik
log_info "Subindo Traefik com docker-compose..."
docker-compose -f docker-compose-traefik.yml up -d

log_success "Traefik iniciado!"

# =============================================================================
# 5. VERIFICAR TRAEFIK
# =============================================================================
log_info "Verificando se o Traefik está funcionando..."

# Primeiro verificar se o container está rodando
log_info "Verificando status do container Traefik..."
if docker ps --format "table {{.Names}}\t{{.Status}}" | grep -q "traefik.*Up"; then
    log_success "Container Traefik está rodando"
else
    log_error "Container Traefik não está rodando. Verificando logs..."
    docker-compose -f docker-compose-traefik.yml logs traefik
    exit 1
fi

# Aguardar um pouco para o Traefik inicializar
log_info "Aguardando inicialização do Traefik..."
sleep 15

# Aguardar Traefik ficar disponível (teste interno)
log_info "Testando acesso ao Dashboard do Traefik..."
if wait_for_service "http://localhost:8080/dashboard/" "Traefik Dashboard"; then
    log_success "Traefik Dashboard disponível internamente"
    log_success "Traefik Dashboard acessível em: http://$SERVER_IP:8080/dashboard/"
else
    log_warning "Dashboard não respondeu na URL /dashboard/, tentando /..."
    if curl -s -f "http://localhost:8080/" > /dev/null 2>&1; then
        log_success "Traefik API disponível em: http://$SERVER_IP:8080/"
    else
        log_warning "Dashboard não está respondendo. Verificando logs..."
        docker-compose -f docker-compose-traefik.yml logs --tail=20 traefik
    fi
fi

# Verificar se as portas estão abertas
log_info "Verificando portas do Traefik..."
if netstat -tuln | grep -q ":80 "; then
    log_success "Porta 80 (HTTP) está aberta"
else
    log_warning "Porta 80 não está disponível"
fi

if netstat -tuln | grep -q ":443 "; then
    log_success "Porta 443 (HTTPS) está aberta"
else
    log_warning "Porta 443 não está disponível"
fi

if netstat -tuln | grep -q ":8080 "; then
    log_success "Porta 8080 (Dashboard) está aberta"
else
    log_warning "Porta 8080 não está disponível"
fi

# =============================================================================
# 6. SUBIR PORTAINER
# =============================================================================
log_info "Iniciando Portainer (Gerenciador Docker)..."

# Parar container existente se estiver rodando
if docker ps -q --filter "name=portainer" | grep -q .; then
    log_info "Parando container Portainer existente..."
    docker-compose -f docker-compose-portainer.yml down
fi

# Subir Portainer
log_info "Subindo Portainer com docker-compose..."
docker-compose -f docker-compose-portainer.yml up -d

log_success "Portainer iniciado!"

# Verificar se Portainer está rodando
log_info "Verificando status do container Portainer..."
if docker ps --format "table {{.Names}}\t{{.Status}}" | grep -q "portainer.*Up"; then
    log_success "Container Portainer está rodando"
else
    log_warning "Container Portainer pode estar inicializando..."
    docker-compose -f docker-compose-portainer.yml logs --tail=10 portainer
fi

# Aguardar um pouco para o Portainer inicializar
log_info "Aguardando inicialização do Portainer..."
sleep 10

# Testar acesso ao Portainer
log_info "Testando acesso ao Portainer..."
if curl -s -f "http://localhost:9000/" > /dev/null 2>&1; then
    log_success "Portainer está disponível em: http://$SERVER_IP:9000"
    log_success "Portainer será acessível via HTTPS em: https://manager.bwserver.com.br"
else
    log_warning "Portainer ainda não está respondendo (pode levar alguns segundos)"
fi

# =============================================================================
# 7. CONSTRUIR E SUBIR PROJETO PRINCIPAL
# =============================================================================
# =============================================================================
# 7. CONSTRUIR E SUBIR PROJETO PRINCIPAL
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
# 8. VERIFICAR PROJETO PRINCIPAL
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
# 9. RESUMO FINAL
# =============================================================================
echo ""
echo -e "${GREEN}"
echo "============================================================================="
echo "                           SETUP CONCLUÍDO!"
echo "============================================================================="
echo -e "${NC}"

log_success "Todos os serviços foram configurados com sucesso!"

echo ""
echo -e "${BLUE}📊 STATUS DOS SERVIÇOS:${NC}"
echo "----------------------------------------"
docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo -e "${BLUE}🌐 ENDPOINTS DISPONÍVEIS:${NC}"
echo "----------------------------------------"
echo "• Traefik Dashboard: http://$SERVER_IP:8080/dashboard/"
echo "• Traefik API: http://$SERVER_IP:8080/"
echo "• Portainer (Gerenciador): http://$SERVER_IP:9000"
echo "• Portainer HTTPS: https://manager.bwserver.com.br"
echo "• Webhook API: http://webhook.bwserver.com.br"
echo "• Teste local: http://$SERVER_IP/src/test.php"
echo "• IP do Servidor: $SERVER_IP"

echo ""
echo -e "${BLUE}📝 PRÓXIMOS PASSOS:${NC}"
echo "----------------------------------------"
echo "1. Configure seu DNS para apontar os domínios para $SERVER_IP:"
echo "   - webhook.bwserver.com.br → $SERVER_IP"
echo "   - manager.bwserver.com.br → $SERVER_IP"
echo "2. Aguarde alguns minutos para os certificados SSL serem gerados"
echo "3. Acesse o Portainer e configure uma senha de administrador"
echo "4. Teste o webhook usando: http://$SERVER_IP/src/test.php"
echo "5. Acesse o Traefik Dashboard em: http://$SERVER_IP:8080/dashboard/"

echo ""
echo -e "${BLUE}🔍 COMANDOS ÚTEIS:${NC}"
echo "----------------------------------------"
echo "• Ver logs do Traefik: docker-compose -f docker-compose-traefik.yml logs -f traefik"
echo "• Ver logs do Portainer: docker-compose -f docker-compose-portainer.yml logs -f portainer"
echo "• Ver logs do projeto: docker-compose logs -f automation-webhook"
echo "• Reiniciar tudo: docker-compose down && docker-compose up -d"
echo "• Reiniciar Portainer: docker-compose -f docker-compose-portainer.yml restart"

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
