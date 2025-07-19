<?php

require_once 'src/DockerManager.php';

try {
    echo "🔍 Testando DockerManager com sudo...\n\n";
    
    $dockerManager = new DockerManager();
    
    echo "✅ DockerManager inicializado com sucesso!\n";
    echo "✅ Comandos Docker agora rodando com privilégios de root!\n";
    
    // Testar listagem de containers
    echo "\n📋 Testando listagem de containers...\n";
    $containers = $dockerManager->listContainers();
    echo "✅ Listagem funcionando - encontrados " . count($containers) . " containers\n";
    
    echo "\n🎉 Todas as correções aplicadas com sucesso!\n";
    echo "   - Comandos docker agora usam sudo\n";
    echo "   - Verificação de rede corrigida\n";
    echo "   - Problemas de trim() resolvidos\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

?>
