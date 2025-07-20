<?php

require_once 'SystemInitializer.php';

header('Content-Type: application/json');

// Permitir apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Ler dados da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Configurações padrão
    $config = [
        'domain' => $input['domain'] ?? 'bwserver.com.br',
        'email' => $input['email'] ?? 'admin@bwserver.com.br',
        'traefik_host' => $input['traefik_host'] ?? null,
        'portainer_host' => $input['portainer_host'] ?? null,
        'cloudflare_token' => $input['cloudflare_token'] ?? null
    ];
    
    // Verificar ação
    $action = $input['action'] ?? 'initialize';
    
    $initializer = new SystemInitializer();
    
    switch ($action) {
        case 'initialize':
            $result = $initializer->initialize($config);
            break;
            
        case 'create_n8n':
            $result = $initializer->createN8nContainer(
                $input['container_name'] ?? 'n8n-' . uniqid(),
                $input['subdomain'] ?? 'n8n.example.com',
                $input['vcpu'] ?? 1.0,
                $input['memory'] ?? 1024
            );
            break;
            
        case 'create_evolution':
            $result = $initializer->createEvolutionContainer(
                $input['container_name'] ?? 'evolution-' . uniqid(),
                $input['subdomain'] ?? 'evolution.example.com',
                $input['vcpu'] ?? 1.0,
                $input['memory'] ?? 512,
                $input['api_key'] ?? null
            );
            break;
            
        case 'list_services':
            $result = $initializer->listServices();
            break;
            
        case 'remove_service':
            if (!isset($input['service_name'])) {
                throw new Exception('service_name is required');
            }
            $result = $initializer->removeService($input['service_name']);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
    // Log da operação
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'config' => $config,
        'result' => $result['success'] ? 'success' : 'error',
        'message' => $result['message'] ?? $result['error'] ?? ''
    ];
    
    file_put_contents('system.log', json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

?>
