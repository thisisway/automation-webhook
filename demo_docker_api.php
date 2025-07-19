<?php

require_once 'src/DockerManager.php';

echo "🔍 Demonstração: Docker API vs Comandos Shell\n\n";

echo "✅ VANTAGENS DA DOCKER API:\n";
echo "   1. ❌ Não precisa de sudo/privilégios root\n";
echo "   2. ⚡ Mais rápida (sem overhead de shell)\n";
echo "   3. 🛡️ Mais segura (sem injeção de comandos)\n";
echo "   4. 📊 Respostas estruturadas (JSON)\n";
echo "   5. 🔄 Melhor tratamento de erros\n";
echo "   6. 🐳 Acesso direto ao Docker daemon\n\n";

try {
    $dockerManager = new DockerManager();
    
    echo "📋 Testando Docker API...\n";
    
    // Exemplo de como usar métodos da API diretamente
    $reflection = new ReflectionClass($dockerManager);
    $checkNetworkMethod = $reflection->getMethod('checkNetworkExists');
    $checkNetworkMethod->setAccessible(true);
    
    $networkExists = $checkNetworkMethod->invoke($dockerManager, 'traefik');
    echo "✅ Rede 'traefik' existe: " . ($networkExists ? 'SIM' : 'NÃO') . "\n";
    
    $checkContainerMethod = $reflection->getMethod('checkContainerExists');  
    $checkContainerMethod->setAccessible(true);
    
    $containerExists = $checkContainerMethod->invoke($dockerManager, 'traefik');
    echo "✅ Container 'traefik' existe: " . ($containerExists ? 'SIM' : 'NÃO') . "\n";
    
    echo "\n🎯 COMPARAÇÃO:\n";
    echo "ANTES (shell): sudo docker network ls --format '{{.Name}}' -f name=traefik\n";
    echo "AGORA (API):   curl --unix-socket /var/run/docker.sock http://localhost/v1.41/networks\n\n";
    
    echo "✅ Docker API funcionando perfeitamente!\n";
    echo "✅ Sem necessidade de sudo!\n";
    echo "✅ Comunicação direta com Docker daemon!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

?>
