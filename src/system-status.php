<?php

header('Content-Type: application/json');

// Informações básicas do sistema
$systemInfo = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'docker_available' => false,
    'socket_accessible' => false,
    'templates_exist' => false
];

// Verificar se Docker está disponível
$dockerVersion = shell_exec('docker --version 2>/dev/null');
if ($dockerVersion) {
    $systemInfo['docker_available'] = true;
    $systemInfo['docker_version'] = trim($dockerVersion);
}

// Verificar se socket Docker está acessível
if (file_exists('/var/run/docker.sock')) {
    $systemInfo['socket_accessible'] = is_readable('/var/run/docker.sock');
}

// Verificar se templates existem
$templatesDir = '../templates';
if (is_dir($templatesDir)) {
    $systemInfo['templates_exist'] = true;
    $systemInfo['templates'] = array_filter(scandir($templatesDir), function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'yml';
    });
}

// Verificar containers rodando
if ($systemInfo['docker_available']) {
    $containers = shell_exec('docker ps --format "table {{.Names}}\t{{.Status}}" 2>/dev/null');
    if ($containers) {
        $systemInfo['running_containers'] = array_filter(explode("\n", trim($containers)));
    }
}

// Verificar se arquivos principais existem
$requiredFiles = [
    'DockerManager.php',
    'TemplateManager.php', 
    'SystemInitializer.php',
    'system-init.php'
];

$systemInfo['files_status'] = [];
foreach ($requiredFiles as $file) {
    $systemInfo['files_status'][$file] = file_exists($file);
}

// Status geral
$systemInfo['status'] = 'ok';
if (!$systemInfo['docker_available']) {
    $systemInfo['status'] = 'docker_not_available';
} elseif (!$systemInfo['socket_accessible']) {
    $systemInfo['status'] = 'socket_not_accessible';
} elseif (!$systemInfo['templates_exist']) {
    $systemInfo['status'] = 'templates_missing';
}

// Resposta
echo json_encode([
    'system' => $systemInfo,
    'message' => $systemInfo['status'] === 'ok' ? 'Sistema funcionando corretamente!' : 'Sistema com problemas',
    'ready_for_initialization' => $systemInfo['status'] === 'ok'
], JSON_PRETTY_PRINT);

?>
