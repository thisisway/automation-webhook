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
                $software = $input['software'] ?? null;
                $client = $input['client'] ?? null;

                $client = substr(trim(str_replace(' ','',strtolower($client))), 0, 15);

                $vcpu = $input['vcpus'] ?? 1;
                $memory = $input['memory'] ?? 512; // Default to 512MB

                $subdomain = $software.'-'.$client.'-'.uniqid();

                if (!is_dir('/etc/automation-webhook/volumes/'.$client)) {
                    mkdir('/etc/automation-webhook/volumes/'.$client, 0755, true);
                }

                if($software === 'n8n')
                {
                    $templatePath = '../templates/n8n.yml';
                    $destinationPath = '/etc/automation-webhook/volumes/' . $client . '/n8n.yml';
                    
                    if (file_exists($templatePath)) {
                        copy($templatePath, $destinationPath);
                    }

                    $envTemplatePath = '../templates/n8n.env';
                    $envDestinationPath = '/etc/automation-webhook/volumes/' . $client . '/.env';

                    if (file_exists($envTemplatePath)) {
                        $envContent = file_get_contents($envTemplatePath);
                        $envContent = str_replace('{SUBDOMAIN}', $subdomain, $envContent);
                        file_put_contents($envDestinationPath, $envContent);
                    }

                    $result = $docker->createContainer($software, $client, $vcpu, $memory, $subdomain);
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