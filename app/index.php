<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'docker.php';

$docker = new DockerManager();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    switch ($method) {
        case 'GET':
            if ($path === '/containers') {
                $result = $docker->listContainers(true);
                echo json_encode($result);
            } elseif (preg_match('/\/containers\/(.+)\/logs/', $path, $matches)) {
                $result = $docker->getContainerLogs($matches[1]);
                echo json_encode($result);
            } else {
                echo json_encode([
                    "status" => "success",
                    "message" => "API is running"
                ]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (preg_match('/\/containers\/(.+)\/start/', $path, $matches)) {
                $result = $docker->startContainer($matches[1]);
                echo json_encode($result);
            } elseif (preg_match('/\/containers\/(.+)\/stop/', $path, $matches)) {
                $result = $docker->stopContainer($matches[1]);
                echo json_encode($result);
            } elseif (preg_match('/\/containers\/(.+)\/restart/', $path, $matches)) {
                $result = $docker->restartContainer($matches[1]);
                echo json_encode($result);
            } elseif ($path === '/containers/create') {
                $volumeBase = '/etc/automation-webhook/volumes/';
                $software = $input['software'] ?? null;
                $client = $input['client'] ?? null;

                // Validate required parameters
                if (!$software || !$client) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Software and client parameters are required']);
                    break;
                }

                $client = substr(trim(str_replace(' ','',strtolower($client))), 0, 15);
                $id = uniqid();
                $vcpu = $input['vcpus'] ?? 1;
                $memory = $input['memory'] ?? 512; // Default to 512MB
                $subdomain = $software.'-'.$client.'-'.$id;

                // Create client directory if it doesn't exist
                $clientDir = $volumeBase . $client;
                if (!is_dir($clientDir)) {
                    if (!mkdir($clientDir, 0755, true)) {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to create client directory: ' . $clientDir]);
                        break;
                    }
                }

                // Create software-specific directory
                $destinationPath = $clientDir . '/' . $software . '-' . $id;
                if (!mkdir($destinationPath, 0755, true)) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to create destination directory: ' . $destinationPath]);
                    break;
                }

                if($software === 'n8n') {
                    // Define template path
                    $templatePath = '../templates/n8n.yml';
                    
                    // Check if template exists
                    if (!file_exists($templatePath)) {
                        http_response_code(500);
                        echo json_encode(['error' => 'Template file not found: ' . $templatePath]);
                        break;
                    }
                    
                    // Copy template
                    if (!copy($templatePath, $destinationPath . '/docker-compose.yml')) {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to copy template file']);
                        break;
                    }

                    // Handle .env file
                    $envTemplatePath = '../templates/n8n/.env';
                    if (file_exists($envTemplatePath)) {
                        $envContent = file_get_contents($envTemplatePath);
                        $envContent = str_replace('{SUBDOMAIN}', $subdomain, $envContent);
                        $envContent = str_replace('{CLIENT_NAME}', $client . '_' . $id, $envContent);
                        $envContent = str_replace('DOMAIN=yourdomain.com', 'DOMAIN=' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), $envContent);
                        
                        if (!file_put_contents($destinationPath . '/.env', $envContent)) {
                            http_response_code(500);
                            echo json_encode(['error' => 'Failed to create .env file']);
                            break;
                        }
                    }

                    $result = $docker->createContainer($destinationPath);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Unsupported software: ' . $software]);
                    break;
                }

                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid endpoint']);
            }
            break;
            
        case 'DELETE':
            if (preg_match('/\/containers\/(.+)/', $path, $matches)) {
                $result = $docker->removeContainer($matches[1], $_GET['force'] ?? false);
                echo json_encode($result);
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}