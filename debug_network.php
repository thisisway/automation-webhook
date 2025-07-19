<?php

// Testando exatamente a lógica que usamos no DockerManager
echo "🔍 Testando a lógica exata de verificação de rede...\n\n";

$networkOutput = shell_exec("docker network ls --format '{{.Name}}' -f name=traefik");
echo "📋 Redes encontradas:\n";
echo "Raw output: '$networkOutput'\n";

$networks = explode("\n", trim($networkOutput ?? ''));
echo "Networks array: " . print_r($networks, true) . "\n";

$traefikExists = false;
foreach ($networks as $network) {
    echo "Checking network: '$network' (trimmed: '" . trim($network) . "')\n";
    if (trim($network) === 'traefik') {
        $traefikExists = true;
        echo "✅ Found exact match for 'traefik'!\n";
        break;
    }
}

if ($traefikExists) {
    echo "✅ Traefik network verification: PASSED\n";
} else {
    echo "❌ Traefik network verification: FAILED\n";
}

?>
