#!/bin/bash

echo "=== Automation Webhook Boot Script ==="
echo "Starting system initialization..."

# Função para verificar se um container está rodando
check_container() {
    local container_name=$1
    if docker ps --format "table {{.Names}}" | grep -q "^${container_name}$"; then
        return 0  # Container está rodando
    else
        return 1  # Container não está rodando
    fi
}

# Função para verificar se um container existe (rodando ou parado)
container_exists() {
    local container_name=$1
    if docker ps -a --format "table {{.Names}}" | grep -q "^${container_name}$"; then
        return 0  # Container existe
    else
        return 1  # Container não existe
    fi
}

# Função para iniciar um container existente
start_container() {
    local container_name=$1
    echo "🔄 Iniciando container existente: $container_name"
    docker start "$container_name"
}

# Função para verificar se uma rede existe
check_network() {
    local network_name=$1
    if docker network ls --format "{{.Name}}" | grep -q "^${network_name}$"; then
        return 0  # Rede existe
    else
        return 1  # Rede não existe
    fi
}

# Detectar grupo Docker
DOCKER_GROUP_ID=$(getent group docker | cut -d: -f3)
if [ -z "$DOCKER_GROUP_ID" ]; then
    echo "❌ Grupo docker não encontrado no sistema"
    exit 1
fi
echo "✅ Grupo Docker detectado: ID $DOCKER_GROUP_ID"

# Detectar usuário www-data
WWW_DATA_UID=$(id -u www-data)
if [ -z "$WWW_DATA_UID" ]; then
    echo "❌ Usuário www-data não encontrado"
    exit 1
fi
echo "✅ Usuário www-data detectado: UID $WWW_DATA_UID"

# Configurar permissões na pasta de volumes
VOLUMES_DIR="/var/www/html/volumes"
if [ -d "$VOLUMES_DIR" ]; then
    echo "🔧 Configurando permissões da pasta de volumes..."
    chown -R www-data:www-data "$VOLUMES_DIR"
    chmod -R 755 "$VOLUMES_DIR"
    echo "✅ Permissões configuradas para $VOLUMES_DIR"
else
    echo "📁 Criando pasta de volumes..."
    mkdir -p "$VOLUMES_DIR"
    chown -R www-data:www-data "$VOLUMES_DIR"
    chmod -R 755 "$VOLUMES_DIR"
    echo "✅ Pasta de volumes criada: $VOLUMES_DIR"
fi

# Verificar se a rede traefik existe, se não, criar
if ! check_network "traefik"; then
    echo "🌐 Criando rede traefik..."
    docker network create traefik
    echo "✅ Rede traefik criada"
else
    echo "✅ Rede traefik já existe"
fi

# Verificar e subir Traefik
if ! check_container "traefik"; then
    if container_exists "traefik"; then
        # Container existe mas está parado, vamos iniciá-lo
        start_container "traefik"
        echo "✅ Traefik iniciado"
    else
        # Container não existe, vamos criá-lo
        echo "🚀 Criando e iniciando Traefik..."
        
        # Iniciar container Traefik
        docker run -d \
            --name traefik \
            --restart unless-stopped \
            -p 80:80 \
            -p 443:443 \
            -p 8080:8080 \
            -v /var/run/docker.sock:/var/run/docker.sock:ro \
            --network traefik \
            --label "traefik.enable=true" \
            --label "traefik.http.routers.dashboard.rule=Host(\`traefik.bwserver.com.br\`) || Host(\`traefik.localhost\`)" \
            --label "traefik.http.routers.dashboard.entrypoints=web" \
            --label "traefik.http.routers.dashboard.service=api@internal" \
            --security-opt no-new-privileges:true \
            traefik:v3.0 \
            --api.dashboard=true \
            --api.insecure=true \
            --entrypoints.web.address=:80 \
            --providers.docker=true \
            --providers.docker.endpoint=unix:///var/run/docker.sock \
            --providers.docker.exposedbydefault=false \
            --log.level=DEBUG
        
        echo "✅ Traefik criado e iniciado"
    fi
else
    echo "✅ Traefik já está rodando"
fi

# Verificar e subir Portainer
if ! check_container "portainer"; then
    if container_exists "portainer"; then
        # Container existe mas está parado, vamos iniciá-lo
        start_container "portainer"
        echo "✅ Portainer iniciado"
    else
        # Container não existe, vamos criá-lo
        echo "🚀 Criando e iniciando Portainer..."
        
        # Criar volume para dados do Portainer
        if ! docker volume ls --format "{{.Name}}" | grep -q "^portainer_data$"; then
            docker volume create portainer_data
            echo "✅ Volume portainer_data criado"
        fi
        
        # Iniciar container Portainer
        docker run -d \
            --name portainer \
            --restart unless-stopped \
            -p 9000:9000 \
            -v /var/run/docker.sock:/var/run/docker.sock \
            -v portainer_data:/data \
            --network traefik \
            --label "traefik.enable=true" \
            --label "traefik.http.routers.portainer.rule=Host(\`portainer.bwserver.com.br\`) || Host(\`portainer.localhost\`)" \
            --label "traefik.http.routers.portainer.entrypoints=web" \
            --label "traefik.http.services.portainer.loadbalancer.server.port=9000" \
            portainer/portainer-ce:latest
        
        echo "✅ Portainer criado e iniciado"
    fi
else
    echo "✅ Portainer já está rodando"
fi

# Aguardar os serviços inicializarem
echo "⏳ Aguardando serviços inicializarem..."
sleep 5

# Verificar status dos serviços
echo ""
echo "=== STATUS DOS SERVIÇOS ==="
if check_container "traefik"; then
    echo "✅ Traefik: RODANDO"
    echo "   - Dashboard: http://localhost:8080"
else
    echo "❌ Traefik: PARADO"
fi

if check_container "portainer"; then
    echo "✅ Portainer: RODANDO"
    echo "   - Interface: http://localhost:9000"
else
    echo "❌ Portainer: PARADO"
fi

echo ""
echo "=== CONFIGURAÇÕES ==="
echo "🔧 Grupo Docker: $DOCKER_GROUP_ID"
echo "👤 Usuário www-data: $WWW_DATA_UID"
echo "📁 Pasta volumes: $VOLUMES_DIR"
echo "🌐 Rede traefik: ativa"

echo ""
echo "✅ Boot script concluído!"
echo "🚀 Sistema pronto para uso!"
