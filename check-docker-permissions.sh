#!/bin/bash

# Script de diagnóstico Docker
# Pode ser executado como usuário normal

echo "🔍 DIAGNÓSTICO DOCKER"
echo "===================="

# Informações do usuário
echo "👤 Usuário atual: $(whoami)"
echo "🆔 UID/GID: $(id)"
echo ""

# Socket Docker
echo "🔌 Socket Docker:"
if [ -S /var/run/docker.sock ]; then
    ls -la /var/run/docker.sock
    echo "✅ Socket existe"
else
    echo "❌ Socket não existe"
fi
echo ""

# Grupo docker
echo "👥 Grupo docker:"
if getent group docker > /dev/null; then
    getent group docker
    echo "✅ Grupo docker existe"
else
    echo "❌ Grupo docker não existe"
fi
echo ""

# Teste acesso Docker
echo "🧪 Teste acesso Docker:"
if docker ps >/dev/null 2>&1; then
    echo "✅ Docker acessível"
    docker --version
else
    echo "❌ Docker não acessível"
    echo "Erro:"
    docker ps 2>&1 | head -5
fi
echo ""

# Daemon Docker
echo "⚙️  Daemon Docker:"
if systemctl is-active docker >/dev/null 2>&1; then
    echo "✅ Docker ativo"
else
    echo "❌ Docker inativo"
fi
echo ""

# Teste PHP/www-data (se disponível)
echo "🐘 Teste PHP/www-data:"
if command -v php >/dev/null && id www-data >/dev/null 2>&1; then
    if su -s /bin/bash -c "docker ps >/dev/null 2>&1" www-data 2>/dev/null; then
        echo "✅ PHP/www-data pode acessar Docker"
    else
        echo "❌ PHP/www-data NÃO pode acessar Docker"
    fi
else
    echo "⚠️  PHP ou www-data não disponível"
fi
echo ""

# Configuração daemon
echo "📁 Configuração daemon:"
if [ -f /etc/docker/daemon.json ]; then
    echo "✅ daemon.json existe:"
    cat /etc/docker/daemon.json
else
    echo "❌ daemon.json não existe"
fi
echo ""

echo "🎯 RECOMENDAÇÕES:"
echo "=================="

# Verificar se precisa corrigir
if ! docker ps >/dev/null 2>&1; then
    echo "❌ Execute: sudo ./fix-docker-permissions.sh"
elif ! su -s /bin/bash -c "docker ps >/dev/null 2>&1" www-data 2>/dev/null; then
    echo "⚠️  Execute: sudo ./fix-docker-permissions.sh"
else
    echo "✅ Permissões Docker OK!"
fi
