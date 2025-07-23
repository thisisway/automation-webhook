<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'classes/ContainerManager.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/app', '', $path); // Remove o prefixo /app

$containerManager = new ContainerManager();

try {
    switch ($path) {
        case '/api/create':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $containerManager->createContainer($input);
            break;

        case '/api/delete':
            if ($method !== 'DELETE') {
                throw new Exception('Method not allowed', 405);
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $containerManager->deleteContainer($input);
            break;

        case '/api/status':
            if ($method !== 'GET') {
                throw new Exception('Method not allowed', 405);
            }
            $containerId = $_GET['containerId'] ?? null;
            $result = $containerManager->getStatus($containerId);
            break;

        case '/api/list':
            if ($method !== 'GET') {
                throw new Exception('Method not allowed', 405);
            }
            $client = $_GET['client'] ?? null;
            $result = $containerManager->listContainers($client);
            break;

        case '/api/fix-permissions':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $client = $input['client'] ?? null;
            $containerId = $input['containerId'] ?? null;
            $result = $containerManager->fixPermissions($client, $containerId);
            break;

        case '/api/docker-diagnostic':
            if ($method !== 'GET') {
                throw new Exception('Method not allowed', 405);
            }
            $result = $containerManager->dockerDiagnostic();
            break;

        case '/':
            $result = [
                'status' => 'success',
                'message' => 'Automation Webhook API',
                'version' => '1.0.0',
                'endpoints' => [
                    'POST /api/create' => 'Create new container',
                    'DELETE /api/delete' => 'Delete container',
                    'GET /api/status' => 'Get container status',
                    'GET /api/list' => 'List containers',
                    'POST /api/fix-permissions' => 'Fix volume permissions',
                    'GET /api/docker-diagnostic' => 'Docker configuration diagnostic'
                ]
            ];
            break;

        default:
            throw new Exception('Endpoint not found', 404);
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
