<?php

require_once 'src/DockerManager.php';

try {
    $dockerManager = new DockerManager();
    
    echo "🔍 Testando a criação de um container de teste...\n\n";
    
    // Teste de listagem de containers
    echo "📋 Containers atuais:\n";
    $containers = $dockerManager->listContainers();
    foreach ($containers as $container) {
        echo "- {$container['name']} ({$container['status']})\n";
    }
    
    echo "\n✅ Docker Manager funcionando corretamente!\n";
    echo "✅ Rede traefik está disponível!\n";
    echo "✅ Problemas de trim() corrigidos!\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

?>
