#!/bin/bash

echo "=== Teste de Produção - Automation Webhook ==="
echo "Testando criação de diretórios e containers sem sudo..."
echo ""

# Função para testar criação de diretório
test_directory_creation() {
    local dir="$1"
    local description="$2"
    
    echo "📁 Testando criação de diretório: $description"
    echo "   Caminho: $dir"
    
    # Remover diretório se existir (para teste limpo)
    if [ -d "$dir" ]; then
        echo "   ⚠️  Diretório já existe, removendo para teste..."
        rm -rf "$dir" 2>/dev/null || echo "   ⚠️  Não foi possível remover (sem permissão)"
    fi
    
    # Tentar criar com umask permissivo
    umask 000
    if mkdir -p "$dir" 2>/dev/null; then
        echo "   ✅ Diretório criado com sucesso"
        
        # Testar escrita
        if echo "test" > "$dir/test.txt" 2>/dev/null; then
            echo "   ✅ Escrita no diretório funcionando"
            rm -f "$dir/test.txt" 2>/dev/null
        else
            echo "   ❌ Não é possível escrever no diretório"
        fi
    else
        echo "   ❌ Falha ao criar diretório (sem permissão)"
    fi
    umask 022
    echo ""
}

# Função para testar acesso ao Docker
test_docker_access() {
    echo "🐳 Testando acesso ao Docker..."
    
    if docker version >/dev/null 2>&1; then
        echo "   ✅ Docker acessível"
        
        # Testar criação de container
        if docker run --rm hello-world >/dev/null 2>&1; then
            echo "   ✅ Criação de container funcionando"
        else
            echo "   ❌ Falha ao criar container"
        fi
    else
        echo "   ❌ Docker não acessível"
    fi
    echo ""
}

# Função para testar API
test_api() {
    echo "🌐 Testando API..."
    
    # Testar endpoint de diagnóstico
    if curl -s http://localhost/api/docker-diagnostic >/dev/null 2>&1; then
        echo "   ✅ API respondendo"
        echo "   📊 Diagnóstico Docker:"
        curl -s http://localhost/api/docker-diagnostic | head -20
    else
        echo "   ❌ API não respondendo"
    fi
    echo ""
}

# Executar testes
echo "Usuário atual: $(whoami)"
echo "UID: $(id -u)"
echo "GID: $(id -g)"
echo "Grupos: $(groups)"
echo ""

# Testar diretórios
test_directory_creation "/var/www/html/volumes/test" "volumes local"
test_directory_creation "/etc/automation-webhook/volumes/test" "volumes externos"

# Testar Docker
test_docker_access

# Testar API
test_api

echo "=== Teste Concluído ==="
