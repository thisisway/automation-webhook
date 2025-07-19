#!/bin/bash

# Script para inicializar o Traefik e configurar o ambiente
# Autor: Automation Webhook Project

echo "🚀 Iniciando configuração do Traefik..."

# Criar network do Traefik se não existir
echo "📡 Criando network traefik..."
docker network create traefik 2>/dev/null || echo "ℹ️  Network traefik já existe"

# Criar arquivo acme.json com permissões corretas se não existir
if [ ! -f "acme.json" ]; then
    echo "🔐 Criando arquivo acme.json..."
    touch acme.json
    chmod 600 acme.json
else
    echo "ℹ️  Arquivo acme.json já existe"
fi

# Verificar se o Traefik já está rodando
if [ "$(docker ps -q -f name=traefik)" ]; then
    echo "⚠️  Traefik já está rodando. Parando primeiro..."
    docker compose -f docker-compose-traefik.yml down
fi

# Subir o Traefik
echo "🌐 Subindo o Traefik..."
docker compose -f docker-compose-traefik.yml up -d

# Aguardar alguns segundos para o Traefik inicializar
sleep 3

# Verificar se o Traefik está rodando
if [ "$(docker ps -q -f name=traefik)" ]; then
    echo "✅ Traefik iniciado com sucesso!"
    echo "🎯 Dashboard disponível em: http://localhost:8080"
    echo "📊 Status dos containers:"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep traefik
    
    # Aguardar mais alguns segundos e verificar logs
    sleep 2
    echo ""
    echo "📋 Últimos logs do Traefik:"
    docker logs traefik --tail 10
else
    echo "❌ Erro ao iniciar o Traefik"
    echo "📋 Logs de erro:"
    docker logs traefik --tail 20
    exit 1
fi

echo ""
echo "🎉 Configuração concluída!"
echo "💡 Próximos passos:"
echo "   - Configure seus serviços para usar a network 'traefik'"
echo "   - Adicione labels do Traefik nos seus containers"
echo "   - Acesse o dashboard em http://localhost:8080"
