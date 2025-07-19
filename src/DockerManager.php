<?php

class DockerManager {
    
    public function __construct() {
        // Verificar se o comando docker está disponível
        if (!$this->isDockerAvailable()) {
            throw new Exception('Docker is not available. Make sure Docker is running and accessible.');
        }
    }
    
    private function isDockerAvailable() {
        // Verificar se a Docker API está disponível
        $dockerApiUrl = 'http://localhost/v1.41/version';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dockerApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, '/var/run/docker.sock');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    private function executeDockerCommand($command) {
        $fullCommand = "sudo docker $command 2>&1";
        $output = shell_exec($fullCommand);
        $exitCode = 0;
        
        // Verificar se o comando foi executado com sucesso
        exec($fullCommand, $outputArray, $exitCode);
        
        if ($exitCode !== 0) {
            throw new Exception("Docker command failed: $command\nOutput: $output");
        }
        
        return trim($output ?? '');
    }
    
    private function dockerApiRequest($endpoint, $method = 'GET', $data = null) {
        $dockerApiUrl = "http://localhost/v1.41{$endpoint}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dockerApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, '/var/run/docker.sock');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Docker API error: $error");
        }
        
        return [
            'status_code' => $httpCode,
            'body' => $response,
            'data' => json_decode($response, true)
        ];
    }
    
    private function checkContainerExists($containerName) {
        $result = $this->dockerApiRequest("/containers/json?all=true&filters=" . urlencode(json_encode(['name' => [$containerName]])));
        
        if ($result['status_code'] !== 200) {
            throw new Exception("Failed to check container existence");
        }
        
        return !empty($result['data']);
    }
    
    private function checkNetworkExists($networkName) {
        $result = $this->dockerApiRequest("/networks");
        
        if ($result['status_code'] !== 200) {
            throw new Exception("Failed to check network existence");
        }
        
        foreach ($result['data'] as $network) {
            if ($network['Name'] === $networkName) {
                return true;
            }
        }
        
        return false;
    }
    
    private function createNetwork($networkName) {
        $networkConfig = [
            'Name' => $networkName,
            'Driver' => 'bridge',
            'EnableIPv6' => false,
            'IPAM' => [
                'Config' => [
                    [
                        'Subnet' => '172.20.0.0/16'
                    ]
                ]
            ],
            'Internal' => false,
            'Attachable' => true,
            'Labels' => [
                'description' => 'Network for containers'
            ]
        ];
        
        $result = $this->dockerApiRequest("/networks/create", 'POST', $networkConfig);
        
        if ($result['status_code'] !== 201) {
            throw new Exception("Failed to create network: " . $result['body']);
        }
        
        return $result['data'];
    }
    
    private function createContainer($config) {
        $result = $this->dockerApiRequest("/containers/create", 'POST', $config);
        
        if ($result['status_code'] !== 201) {
            throw new Exception("Failed to create container: " . $result['body']);
        }
        
        $containerId = $result['data']['Id'];
        
        // Iniciar o container
        $startResult = $this->dockerApiRequest("/containers/{$containerId}/start", 'POST');
        
        if ($startResult['status_code'] !== 204) {
            throw new Exception("Failed to start container: " . $startResult['body']);
        }
        
        return $containerId;
    }
    
    public function createN8nContainer($containerName, $vcpu, $mem, $subdomain) {
        try {
            // Verificar se o container já existe usando API
            if ($this->checkContainerExists($containerName)) {
                throw new Exception("Container with name {$containerName} already exists");
            }
            
            // Verificar se a rede traefik existe, se não existir, criar
            if (!$this->checkNetworkExists('traefik')) {
                $this->createNetwork('traefik');
            }
            
            // Configuração do container usando Docker API
            $containerConfig = [
                'Image' => 'n8nio/n8n:latest',
                'name' => $containerName,
                'Env' => [
                    "N8N_HOST={$subdomain}",
                    "N8N_PORT=5678",
                    "N8N_PROTOCOL=https",
                    "WEBHOOK_URL=https://{$subdomain}",
                    "GENERIC_TIMEZONE=America/Sao_Paulo"
                ],
                'ExposedPorts' => [
                    '5678/tcp' => (object)[]
                ],
                'HostConfig' => [
                    'Memory' => $mem * 1024 * 1024, // Converter MB para bytes
                    'CpuQuota' => (int)($vcpu * 100000), // Converter para quota
                    'RestartPolicy' => [
                        'Name' => 'unless-stopped'
                    ],
                    'NetworkMode' => 'traefik'
                ],
                'Labels' => [
                    'traefik.enable' => 'true',
                    "traefik.http.routers.{$containerName}.rule" => "Host(`{$subdomain}`)",
                    "traefik.http.routers.{$containerName}.entrypoints" => 'websecure',
                    "traefik.http.routers.{$containerName}.tls.certresolver" => 'letsencrypt',
                    "traefik.http.services.{$containerName}.loadbalancer.server.port" => '5678',
                    'traefik.docker.network' => 'traefik'
                ]
            ];
            
            $containerId = $this->createContainer($containerConfig);
            
            return [
                'id' => $containerId,
                'name' => $containerName,
                'status' => 'started',
                'subdomain' => $subdomain
            ];
            
        } catch (Exception $e) {
            throw new Exception('Failed to create N8N container: ' . $e->getMessage());
        }
    }
    
    public function createEvoApiContainer($containerName, $vcpu, $mem, $subdomain) {
        try {
            // Verificar se o container já existe usando API
            if ($this->checkContainerExists($containerName)) {
                throw new Exception("Container with name {$containerName} already exists");
            }
            
            // Verificar se a rede traefik existe, se não existir, criar
            if (!$this->checkNetworkExists('traefik')) {
                $this->createNetwork('traefik');
            }
            
            // Criar volumes para persistência de dados
            $this->executeDockerCommand("volume create {$containerName}_evolution_instances");
            $this->executeDockerCommand("volume create {$containerName}_evolution_store");
            
            // Montar comando docker run
            $dockerCommand = "run -d" .
                " --name {$containerName}" .
                " --restart unless-stopped" .
                " --memory {$mem}m" .
                " --cpus {$vcpu}" .
                " --network traefik" .
                " --expose 8080" .
                " -v {$containerName}_evolution_instances:/evolution/instances" .
                " -v {$containerName}_evolution_store:/evolution/store" .
                " -e SERVER_TYPE=https" .
                " -e SERVER_URL=https://{$subdomain}" .
                " -e CORS_ORIGIN=*" .
                " -e CORS_METHODS=GET,POST,PUT,DELETE" .
                " -e CORS_CREDENTIALS=true" .
                " -e LOG_LEVEL=ERROR" .
                " -e LOG_COLOR=true" .
                " -e DEL_INSTANCE=false" .
                " -e DATABASE_ENABLED=true" .
                " -e DATABASE_CONNECTION_URI=file:./db/database.db" .
                " -e DATABASE_CONNECTION_CLIENT_NAME=evolution_v2" .
                " -e REDIS_ENABLED=false" .
                " -e RABBITMQ_ENABLED=false" .
                " -e WEBSOCKET_ENABLED=false" .
                " -e WA_BUSINESS_TOKEN_WEBHOOK=evolution" .
                " -e WA_BUSINESS_URL=https://graph.facebook.com" .
                " -e WA_BUSINESS_VERSION=v20.0" .
                " -e WA_BUSINESS_LANGUAGE=pt_BR" .
                " -e WEBHOOK_GLOBAL_ENABLED=false" .
                " -e CONFIG_SESSION_PHONE_CLIENT='Evolution API'" .
                " -e CONFIG_SESSION_PHONE_NAME=Chrome" .
                " -e QRCODE_LIMIT=30" .
                " -e AUTHENTICATION_TYPE=apikey" .
                " -e AUTHENTICATION_API_KEY=B6D711FCDE4D4FD5936544120E713976" .
                " -e AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES=true" .
                " -e LANGUAGE=en" .
                " -l traefik.enable=true" .
                " -l traefik.http.routers.{$containerName}.rule=Host\\(\\`{$subdomain}\\`\\)" .
                " -l traefik.http.routers.{$containerName}.entrypoints=websecure" .
                " -l traefik.http.routers.{$containerName}.tls.certresolver=letsencrypt" .
                " -l traefik.http.services.{$containerName}.loadbalancer.server.port=8080" .
                " -l traefik.docker.network=traefik" .
                " davidsongomes/evolution-api:v2.1.1";
            
            $containerId = $this->executeDockerCommand($dockerCommand);
            
            return [
                'id' => $containerId,
                'name' => $containerName,
                'status' => 'started',
                'subdomain' => $subdomain
            ];
            
        } catch (Exception $e) {
            throw new Exception('Failed to create Evolution API container: ' . $e->getMessage());
        }
    }
    
    public function listContainers() {
        try {
            $output = $this->executeDockerCommand("ps -a --format 'table {{.ID}}\t{{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}'");
            
            // Converter saída em array estruturado
            $lines = explode("\n", $output);
            $containers = [];
            
            // Pular o cabeçalho (primeira linha)
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (!empty($line)) {
                    $parts = preg_split('/\s+/', $line, 5);
                    if (count($parts) >= 4) {
                        $containers[] = [
                            'id' => $parts[0],
                            'name' => $parts[1],
                            'image' => $parts[2],
                            'status' => $parts[3],
                            'ports' => isset($parts[4]) ? $parts[4] : ''
                        ];
                    }
                }
            }
            
            return $containers;
            
        } catch (Exception $e) {
            throw new Exception('Failed to list containers: ' . $e->getMessage());
        }
    }
    
    public function getContainerInfo($containerName) {
        try {
            $output = $this->executeDockerCommand("inspect {$containerName}");
            return json_decode($output, true);
        } catch (Exception $e) {
            throw new Exception('Failed to get container info: ' . $e->getMessage());
        }
    }
    
    public function stopContainer($containerName) {
        try {
            $this->executeDockerCommand("stop {$containerName}");
            return ['status' => 'stopped', 'name' => $containerName];
        } catch (Exception $e) {
            throw new Exception('Failed to stop container: ' . $e->getMessage());
        }
    }
    
    public function removeContainer($containerName) {
        try {
            $this->executeDockerCommand("rm -f {$containerName}");
            return ['status' => 'removed', 'name' => $containerName];
        } catch (Exception $e) {
            throw new Exception('Failed to remove container: ' . $e->getMessage());
        }
    }
}
?>
