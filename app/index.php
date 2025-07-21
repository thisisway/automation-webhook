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

                $client = substr(trim(str_replace(' ','',strtolower($client))), 0, 15);

                $id = uniqid();

                $vcpu = $input['vcpus'] ?? 1;
                $memory = $input['memory'] ?? 512; // Default to 512MB

                $subdomain = $software.'-'.$client.'-'.$id;

                if (!is_dir($volumeBase.$client)) {
                    mkdir($volumeBase.$client, 0755, true);
                }

                mkdir($volumeBase.$client.'/n8n-'.$id, 0755, true);
                $destinationPath = $volumeBase . $client . '/n8n-'.$id;

                if($software === 'n8n')
                {
                    copy($templatePath, $destinationPath.'/n8n.yml');

                    $envTemplatePath = '../templates/n8n.env';
                    $envContent = file_get_contents($envTemplatePath);
                    $envContent = str_replace('{SUBDOMAIN}', $subdomain, $envContent);
                    file_put_contents($destinationPath.'/.env', $envContent);

                    $result = $docker->createContainer($destinationPath);
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