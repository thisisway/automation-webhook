#!/bin/bash

# Script para corrigir permissões Docker
# Execute como root: sudo ./fix-docker-permissions.sh

echo "🔧 Corrigindo permissões Docker..."

# Verificar se é root
if [ "$(id -u)" != "0" ]; then
    echo "❌ Este script deve ser executado como root"
    exit 1
fi

# Parar Docker
echo "📦 Parando serviço Docker..."
systemctl stop docker

# Corrigir permissões do socket
echo "🔑 Corrigindo permissões do socket..."
if [ -S /var/run/docker.sock ]; then
    chmod 666 /var/run/docker.sock
    chown root:docker /var/run/docker.sock
else
    echo "⚠️  Socket não encontrado, criando..."
    touch /var/run/docker.sock
    chmod 666 /var/run/docker.sock
    chown root:docker /var/run/docker.sock
fi

# Garantir que grupo docker existe
echo "👥 Verificando grupo docker..."
if ! getent group docker > /dev/null; then
    groupadd docker
    echo "✅ Grupo docker criado"
fi

# Adicionar www-data ao grupo docker
echo "👤 Adicionando www-data ao grupo docker..."
if id "www-data" >/dev/null 2>&1; then
    usermod -a -G docker www-data
    echo "✅ www-data adicionado ao grupo docker"
else
    echo "⚠️  Usuário www-data não existe"
fi

# Configurar daemon Docker
echo "⚙️  Configurando daemon Docker..."
mkdir -p /etc/docker
cat > /etc/docker/daemon.json << 'EOF'
{
    "hosts": ["unix:///var/run/docker.sock"],
    "group": "docker"
}
EOF

# Reiniciar Docker
echo "🔄 Reiniciando Docker..."
systemctl start docker
systemctl enable docker

# Aguardar Docker inicializar
sleep 3

# Aplicar permissões novamente após restart
chmod 666 /var/run/docker.sock
chown root:docker /var/run/docker.sock

# Testar acesso
echo "🧪 Testando acesso ao Docker..."
if docker ps >/dev/null 2>&1; then
    echo "✅ Docker funcionando como root"
else
    echo "❌ Erro ao acessar Docker como root"
    exit 1
fi

# Testar com www-data
echo "🧪 Testando acesso como www-data..."
if su -s /bin/bash -c "docker ps >/dev/null 2>&1" www-data; then
    echo "✅ Docker funcionando como www-data"
else
    echo "⚠️  Docker não funciona como www-data, mas socket está configurado"
fi

echo ""
echo "✅ Permissões Docker corrigidas!"
echo "📝 Socket: $(ls -la /var/run/docker.sock)"
echo "👥 Grupo docker: $(getent group docker)"

# Reiniciar containers se existirem
if docker ps -a -q --filter "name=automation-webhook" | grep -q .; then
    echo "🔄 Reiniciando container do webhook..."
    cd "$(dirname "$0")"
    docker-compose restart
fi

echo "🎉 Correção concluída!"
