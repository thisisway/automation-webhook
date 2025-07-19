<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'DockerManager.php';
require_once 'TraefikManager.php';

class WebhookHandler {
    private $dockerManager;
    private $traefikManager;
    
    public function __construct() {
        $this->dockerManager = new DockerManager();
        $this->traefikManager = new TraefikManager();
    }
    
    public function handleRequest() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Only POST requests are allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON payload');
            }
            
            $this->validateInput($input);
            
            $result = $this->createService($input);
            
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Service created successfully',
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function validateInput($input) {
        $required = ['client', 'vcpu', 'mem', 'soft'];
        
        foreach ($required as $field) {
            if (!isset($input[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        if (!is_numeric($input['vcpu']) || $input['vcpu'] < 1) {
            throw new Exception('vcpu must be a positive integer');
        }
        
        if (!is_numeric($input['mem']) || $input['mem'] < 512) {
            throw new Exception('mem must be a positive integer (minimum 512MB)');
        }
        
        $allowedSoft = ['n8n', 'evoapi'];
        if (!in_array($input['soft'], $allowedSoft)) {
            throw new Exception('soft must be either "n8n" or "evoapi"');
        }
        
        // Validar nome do cliente (apenas caracteres alfanuméricos e hífen)
        if (!preg_match('/^[a-zA-Z0-9-]+$/', $input['client'])) {
            throw new Exception('client name can only contain letters, numbers and hyphens');
        }
    }
    
    private function createService($input) {
        $client = strtolower($input['client']);
        $vcpu = (int)$input['vcpu'];
        $mem = (int)$input['mem'];
        $soft = $input['soft'];
        
        // Gerar hash único para o container
        $hash = uniqid();
        $containerName = "{$client}-{$soft}-{$hash}";
        $subdomain = "{$client}-{$soft}-{$hash}.bwserver.com.br";
        
        // Criar o container baseado no tipo de software
        if ($soft === 'n8n') {
            $containerData = $this->dockerManager->createN8nContainer($containerName, $vcpu, $mem, $subdomain);
        } else {
            $containerData = $this->dockerManager->createEvoApiContainer($containerName, $vcpu, $mem, $subdomain);
        }
        
        return [
            'container_name' => $containerName,
            'subdomain' => $subdomain,
            'vcpu' => $vcpu,
            'memory' => $mem . 'MB',
            'software' => $soft,
            'status' => 'created',
            'url' => "https://{$subdomain}"
        ];
    }
    
    private function sendResponse($code, $data) {
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }
}

// Inicializar o webhook
$webhook = new WebhookHandler();
$webhook->handleRequest();
?>
