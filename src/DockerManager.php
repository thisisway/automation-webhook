<?php

class DockerManager {
    private $dockerSocket;
    
    public function __construct() {
        $this->dockerSocket = '/var/run/docker.sock';
        if (!file_exists($this->dockerSocket)) {
            throw new Exception('Docker socket not found. Make sure Docker is running.');
        }
    }
    
    public function createN8nContainer($containerName, $vcpu, $mem, $subdomain) {
        $memLimit = $mem . 'm';
        $cpuLimit = $vcpu;
        
        // Configuração do N8N
        $config = [
            'Image' => 'n8nio/n8n:latest',
            'name' => $containerName,
            'Env' => [
                'N8N_HOST=' . $subdomain,
                'N8N_PORT=5678',
                'N8N_PROTOCOL=https',
                'WEBHOOK_URL=https://' . $subdomain,
                'GENERIC_TIMEZONE=America/Sao_Paulo'
            ],
            'HostConfig' => [
                'Memory' => $mem * 1024 * 1024, // Converter MB para bytes
                'CpuShares' => $vcpu * 1024, // CPU shares
                'RestartPolicy' => [
                    'Name' => 'unless-stopped'
                ],
                'NetworkMode' => 'traefik-network'
            ],
            'Labels' => [
                'traefik.enable' => 'true',
                'traefik.http.routers.' . $containerName . '.rule' => 'Host(`' . $subdomain . '`)',
                'traefik.http.routers.' . $containerName . '.entrypoints' => 'websecure',
                'traefik.http.routers.' . $containerName . '.tls.certresolver' => 'letsencrypt',
                'traefik.http.services.' . $containerName . '.loadbalancer.server.port' => '5678',
                'traefik.docker.network' => 'traefik-network'
            ],
            'ExposedPorts' => [
                '5678/tcp' => []
            ]
        ];
        
        return $this->createContainer($config);
    }
    
    public function createEvoApiContainer($containerName, $vcpu, $mem, $subdomain) {
        $memLimit = $mem . 'm';
        $cpuLimit = $vcpu;
        
        // Configuração do Evolution API
        $config = [
            'Image' => 'davidsongomes/evolution-api:v2.1.1',
            'name' => $containerName,
            'Env' => [
                'SERVER_TYPE=https',
                'SERVER_URL=https://' . $subdomain,
                'CORS_ORIGIN=*',
                'CORS_METHODS=GET,POST,PUT,DELETE',
                'CORS_CREDENTIALS=true',
                'LOG_LEVEL=ERROR',
                'LOG_COLOR=true',
                'DEL_INSTANCE=false',
                'DATABASE_ENABLED=true',
                'DATABASE_CONNECTION_URI=file:./db/database.db',
                'DATABASE_CONNECTION_CLIENT_NAME=evolution_v2',
                'REDIS_ENABLED=false',
                'RABBITMQ_ENABLED=false',
                'WEBSOCKET_ENABLED=false',
                'WA_BUSINESS_TOKEN_WEBHOOK=evolution',
                'WA_BUSINESS_URL=https://graph.facebook.com',
                'WA_BUSINESS_VERSION=v20.0',
                'WA_BUSINESS_LANGUAGE=pt_BR',
                'WEBHOOK_GLOBAL_ENABLED=false',
                'CONFIG_SESSION_PHONE_CLIENT=Evolution API',
                'CONFIG_SESSION_PHONE_NAME=Chrome',
                'QRCODE_LIMIT=30',
                'AUTHENTICATION_TYPE=apikey',
                'AUTHENTICATION_API_KEY=B6D711FCDE4D4FD5936544120E713976',
                'AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES=true',
                'LANGUAGE=en'
            ],
            'HostConfig' => [
                'Memory' => $mem * 1024 * 1024, // Converter MB para bytes
                'CpuShares' => $vcpu * 1024, // CPU shares
                'RestartPolicy' => [
                    'Name' => 'unless-stopped'
                ],
                'NetworkMode' => 'traefik-network',
                'Binds' => [
                    $containerName . '_evolution_instances:/evolution/instances',
                    $containerName . '_evolution_store:/evolution/store'
                ]
            ],
            'Labels' => [
                'traefik.enable' => 'true',
                'traefik.http.routers.' . $containerName . '.rule' => 'Host(`' . $subdomain . '`)',
                'traefik.http.routers.' . $containerName . '.entrypoints' => 'websecure',
                'traefik.http.routers.' . $containerName . '.tls.certresolver' => 'letsencrypt',
                'traefik.http.services.' . $containerName . '.loadbalancer.server.port' => '8080',
                'traefik.docker.network' => 'traefik-network'
            ],
            'ExposedPorts' => [
                '8080/tcp' => []
            ]
        ];
        
        return $this->createContainer($config);
    }
    
    private function createContainer($config) {
        try {
            // Fazer requisição para a API do Docker
            $dockerApiUrl = 'http://localhost/v1.41/containers/create?name=' . $config['name'];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $dockerApiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($config));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, $this->dockerSocket);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 201) {
                throw new Exception('Failed to create container: ' . $response);
            }
            
            $containerInfo = json_decode($response, true);
            $containerId = $containerInfo['Id'];
            
            // Iniciar o container
            $this->startContainer($containerId);
            
            return [
                'id' => $containerId,
                'name' => $config['name'],
                'status' => 'started'
            ];
            
        } catch (Exception $e) {
            throw new Exception('Docker operation failed: ' . $e->getMessage());
        }
    }
    
    private function startContainer($containerId) {
        $dockerApiUrl = 'http://localhost/v1.41/containers/' . $containerId . '/start';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dockerApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, $this->dockerSocket);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 204) {
            throw new Exception('Failed to start container: ' . $response);
        }
    }
    
    public function listContainers() {
        $dockerApiUrl = 'http://localhost/v1.41/containers/json?all=true';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dockerApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, $this->dockerSocket);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to list containers: ' . $response);
        }
        
        return json_decode($response, true);
    }
}
?>
