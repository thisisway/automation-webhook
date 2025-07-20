<?php

class DockerManager {
    
    private $dockerSocket = '/var/run/docker.sock';
    private $apiVersion = 'v1.41';
    
    public function __construct() {
        // Configurar acesso ao Docker socket (similar ao EasyPanel)
        $this->ensureDockerAccess();
        
        // Verificar se Docker está disponível
        if (!$this->isDockerAvailable()) {
            throw new Exception('Docker is not available. Make sure Docker is running and accessible.');
        }
    }
    
    /**
     * Configurar acesso ao Docker socket
     * Usa conexão direta via socket Unix
     */
    public function ensureDockerAccess() {
        // Verificar se socket existe
        if (!file_exists($this->dockerSocket)) {
            throw new Exception("Docker socket not found at {$this->dockerSocket}");
        }
        
        // Verificar se socket é acessível
        if (!is_readable($this->dockerSocket) || !is_writable($this->dockerSocket)) {
            // Verificar se usuário está no grupo docker
            $groups = shell_exec('groups 2>/dev/null');
            if (!$groups || strpos($groups, 'docker') === false) {
                throw new Exception("User is not in docker group. Run: sudo usermod -aG docker \$USER && newgrp docker");
            }
        }
    }
    
    /**
     * Verificar disponibilidade Docker via socket ou exec
     */
    public function isDockerAvailable() {
        try {
            // Tentar via socket primeiro
            $response = $this->dockerSocketCall('/version', 'GET');
            if ($response && isset($response['ApiVersion'])) {
                return true;
            }
        } catch (Exception $e) {
            // Continua para tentar exec
        }
        
        // Fallback para comando shell
        $output = shell_exec('docker --version 2>/dev/null');
        return !empty(trim($output ?? ''));
    }
    
    /**
     * Comunicação direta via socket Docker
     */
    public function dockerSocketCall($endpoint, $method = 'GET', $data = null) {
        try {
            $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
            
            if (!$socket) {
                throw new Exception('Failed to create socket: ' . socket_strerror(socket_last_error()));
            }
            
            $result = socket_connect($socket, $this->dockerSocket);
            if (!$result) {
                socket_close($socket);
                throw new Exception('Failed to connect to Docker socket: ' . socket_strerror(socket_last_error($socket)));
            }
            
            // Construir request HTTP
            $path = "/{$this->apiVersion}{$endpoint}";
            $request = "{$method} {$path} HTTP/1.1\r\n";
            $request .= "Host: localhost\r\n";
            
            if ($data && $method === 'POST') {
                $jsonData = json_encode($data);
                $request .= "Content-Type: application/json\r\n";
                $request .= "Content-Length: " . strlen($jsonData) . "\r\n";
                $request .= "\r\n";
                $request .= $jsonData;
            } else {
                $request .= "\r\n";
            }
            
            socket_write($socket, $request, strlen($request));
            
            $response = '';
            while ($out = socket_read($socket, 2048)) {
                $response .= $out;
            }
            
            socket_close($socket);
            
            // Processar resposta HTTP
            $parts = explode("\r\n\r\n", $response, 2);
            $headers = $parts[0];
            $body = isset($parts[1]) ? $parts[1] : '';
            
            // Extrair status code
            preg_match('/HTTP\/1\.\d (\d{3})/', $headers, $matches);
            $statusCode = isset($matches[1]) ? (int)$matches[1] : 0;
            
            return [
                'status_code' => $statusCode,
                'body' => $body,
                'data' => $body ? json_decode($body, true) : null
            ];
            
        } catch (Exception $e) {
            // Se falhar com socket, usar exec como fallback
            return $this->dockerExecFallback($endpoint, $method, $data);
        }
    }
    
    /**
     * Fallback usando exec quando socket falha
     */
    private function dockerExecFallback($endpoint, $method, $data = null) {
        // Mapear endpoints da API para comandos docker
        switch ($endpoint) {
            case '/version':
                $output = shell_exec('docker version --format json 2>/dev/null');
                return [
                    'status_code' => 200,
                    'body' => $output,
                    'data' => $output ? json_decode($output, true) : null
                ];
                
            case '/containers/json':
                $output = shell_exec('docker ps -a --format json 2>/dev/null');
                $containers = [];
                if ($output) {
                    $lines = explode("\n", trim($output));
                    foreach ($lines as $line) {
                        if ($decoded = json_decode($line, true)) {
                            $containers[] = $decoded;
                        }
                    }
                }
                return [
                    'status_code' => 200,
                    'body' => json_encode($containers),
                    'data' => $containers
                ];
                
            case '/networks':
                $output = shell_exec('docker network ls --format json 2>/dev/null');
                $networks = [];
                if ($output) {
                    $lines = explode("\n", trim($output));
                    foreach ($lines as $line) {
                        if ($decoded = json_decode($line, true)) {
                            $networks[] = ['Name' => $decoded['Name']];
                        }
                    }
                }
                return [
                    'status_code' => 200,
                    'body' => json_encode($networks),
                    'data' => $networks
                ];
                
            default:
                throw new Exception("Unsupported endpoint for exec fallback: {$endpoint}");
        }
    }
    
    /**
     * Método para execução via shell (usado como fallback principal)
     */
    private function executeDockerCommand($command) {
        $fullCommand = "docker $command 2>&1";
        $output = shell_exec($fullCommand);
        
        exec($fullCommand, $outputArray, $exitCode);
        
        if ($exitCode !== 0) {
            throw new Exception("Docker command failed: $command\nOutput: $output");
        }
        
        return trim($output ?? '');
    }

    /**
     * Verificar se container existe (usando exec diretamente)
     */
    private function containerExists($name) {
        try {
            $existing = shell_exec("docker ps -aq -f name=^{$name}$ 2>/dev/null");
            return !empty(trim($existing ?? ''));
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Verificar se rede existe (usando exec diretamente)
     */
    private function networkExists($name) {
        try {
            $existing = shell_exec("docker network ls -q -f name=^{$name}$ 2>/dev/null");
            return !empty(trim($existing ?? ''));
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Criar rede se não existir (usando exec diretamente)
     */
    private function ensureNetwork($name) {
        if (!$this->networkExists($name)) {
            try {
                $this->executeDockerCommand("network create {$name}");
            } catch (Exception $e) {
                throw new Exception("Failed to create network {$name}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Criar container via exec (método simplificado)
     */
    private function createContainerViaExec($containerName, $image, $env = [], $labels = [], $ports = [], $volumes = [], $network = null, $memory = null, $cpuQuota = null) {
        $command = "run -d --name {$containerName}";
        
        // Adicionar recursos
        if ($memory) {
            $command .= " --memory={$memory}m";
        }
        
        if ($cpuQuota && $cpuQuota > 0) {
            $vcpu = $cpuQuota / 100000; // Converter de quota para vcpu
            $command .= " --cpus={$vcpu}";
        }
        
        // Adicionar restart policy
        $command .= " --restart=unless-stopped";
        
        // Adicionar rede
        if ($network) {
            $command .= " --network={$network}";
        }
        
        // Adicionar variáveis de ambiente
        foreach ($env as $envVar) {
            $command .= " -e \"" . addslashes($envVar) . "\"";
        }
        
        // Adicionar labels
        foreach ($labels as $key => $value) {
            $command .= " --label \"" . addslashes($key) . "=" . addslashes($value) . "\"";
        }
        
        // Adicionar portas
        foreach ($ports as $port) {
            $command .= " -p {$port}";
        }
        
        // Adicionar volumes
        foreach ($volumes as $volume) {
            $command .= " -v {$volume}";
        }
        
        // Adicionar imagem
        $command .= " {$image}";
        
        try {
            $output = $this->executeDockerCommand($command);
            
            // Retornar ID do container (primeira linha da saída)
            $lines = explode("\n", trim($output));
            return trim($lines[0]);
            
        } catch (Exception $e) {
            throw new Exception("Failed to create container {$containerName}: " . $e->getMessage());
        }
    }
    
    public function createN8nContainer($containerName, $vcpu, $mem, $subdomain) {
        $this->ensureDockerAccess();
        
        if (!$this->isDockerAvailable()) {
            throw new Exception("Docker não está disponível");
        }

        // Verificar se container já existe
        if ($this->containerExists($containerName)) {
            throw new Exception("Container {$containerName} já existe");
        }

        // Garantir que a rede traefik existe
        $this->ensureNetwork('traefik');

        // Preparar configurações
        $env = [
            "N8N_HOST={$subdomain}",
            'N8N_PORT=5678',
            'N8N_PROTOCOL=https',
            "WEBHOOK_URL=https://{$subdomain}",
            'GENERIC_TIMEZONE=America/Sao_Paulo'
        ];

        $labels = [
            'traefik.enable' => 'true',
            'traefik.http.routers.' . $containerName . '.rule' => 'Host(`' . $subdomain . '`)',
            'traefik.http.routers.' . $containerName . '.entrypoints' => 'websecure',
            'traefik.http.routers.' . $containerName . '.tls.certresolver' => 'letsencrypt',
            'traefik.http.services.' . $containerName . '.loadbalancer.server.port' => '5678',
            'traefik.docker.network' => 'traefik'
        ];

        try {
            // Criar container via exec
            $containerId = $this->createContainerViaExec(
                $containerName,
                'n8nio/n8n:latest',
                $env,
                $labels,
                [], // ports - não precisamos expor diretamente
                [], // volumes
                'traefik', // network
                $mem, // memory
                $vcpu * 100000 // cpuQuota
            );
            
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
        $this->ensureDockerAccess();
        
        if (!$this->isDockerAvailable()) {
            throw new Exception("Docker não está disponível");
        }

        // Verificar se container já existe
        if ($this->containerExists($containerName)) {
            throw new Exception("Container {$containerName} já existe");
        }

        // Garantir que a rede traefik existe
        $this->ensureNetwork('traefik');

        // Criar volumes via exec
        try {
            $this->executeDockerCommand("volume create {$containerName}_evolution_instances");
            $this->executeDockerCommand("volume create {$containerName}_evolution_store");
        } catch (Exception $e) {
            // Volumes podem já existir, continuar
        }

        // Preparar configurações
        $env = [
            "SERVER_TYPE=https",
            "SERVER_URL=https://{$subdomain}",
            "CORS_ORIGIN=*",
            "CORS_METHODS=GET,POST,PUT,DELETE",
            "CORS_CREDENTIALS=true",
            "LOG_LEVEL=ERROR",
            "LOG_COLOR=true",
            "DEL_INSTANCE=false",
            "DATABASE_ENABLED=true",
            "DATABASE_CONNECTION_URI=file:./db/database.db",
            "DATABASE_CONNECTION_CLIENT_NAME=evolution_v2",
            "REDIS_ENABLED=false",
            "RABBITMQ_ENABLED=false",
            "WEBSOCKET_ENABLED=false",
            "WA_BUSINESS_TOKEN_WEBHOOK=evolution",
            "WA_BUSINESS_URL=https://graph.facebook.com",
            "WA_BUSINESS_VERSION=v20.0",
            "WA_BUSINESS_LANGUAGE=pt_BR",
            "WEBHOOK_GLOBAL_ENABLED=false",
            "CONFIG_SESSION_PHONE_CLIENT=Evolution API",
            "CONFIG_SESSION_PHONE_NAME=Chrome",
            "QRCODE_LIMIT=30",
            "AUTHENTICATION_TYPE=apikey",
            "AUTHENTICATION_API_KEY=B6D711FCDE4D4FD5936544120E713976",
            "AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES=true",
            "LANGUAGE=en"
        ];

        $labels = [
            'traefik.enable' => 'true',
            'traefik.http.routers.' . $containerName . '.rule' => 'Host(`' . $subdomain . '`)',
            'traefik.http.routers.' . $containerName . '.entrypoints' => 'websecure',
            'traefik.http.routers.' . $containerName . '.tls.certresolver' => 'letsencrypt',
            'traefik.http.services.' . $containerName . '.loadbalancer.server.port' => '8080',
            'traefik.docker.network' => 'traefik'
        ];

        $volumes = [
            $containerName . '_evolution_instances:/evolution/instances',
            $containerName . '_evolution_store:/evolution/store'
        ];

        try {
            // Criar container via exec
            $containerId = $this->createContainerViaExec(
                $containerName,
                'davidsongomes/evolution-api:v2.1.1',
                $env,
                $labels,
                [], // ports - não precisamos expor diretamente
                $volumes,
                'traefik', // network
                $mem, // memory
                $vcpu * 100000 // cpuQuota
            );
            
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
        $this->ensureDockerAccess();
        
        try {
            // Usar comando docker ps diretamente
            $output = $this->executeDockerCommand("ps -a --format 'table {{.ID}}\t{{.Names}}\t{{.Image}}\t{{.Status}}\t{{.State}}\t{{.Ports}}'");
            
            // Converter saída em array estruturado
            $lines = explode("\n", $output);
            $containers = [];
            
            // Pular o cabeçalho (primeira linha)
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (!empty($line)) {
                    $parts = preg_split('/\s+/', $line, 6);
                    if (count($parts) >= 4) {
                        $containers[] = [
                            'id' => substr($parts[0], 0, 12), // Short ID
                            'name' => $parts[1],
                            'image' => $parts[2],
                            'status' => $parts[3],
                            'state' => isset($parts[4]) ? $parts[4] : 'unknown',
                            'ports' => isset($parts[5]) ? $parts[5] : ''
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
        $this->ensureDockerAccess();
        
        try {
            // Usar docker inspect diretamente
            $output = $this->executeDockerCommand("inspect {$containerName}");
            $data = json_decode($output, true);
            
            if (is_array($data) && !empty($data)) {
                return $data[0]; // docker inspect retorna array
            }
            
            throw new Exception("Container {$containerName} not found or invalid data");
            
        } catch (Exception $e) {
            throw new Exception('Failed to get container info: ' . $e->getMessage());
        }
    }
    
    public function stopContainer($containerName) {
        $this->ensureDockerAccess();
        
        try {
            // Usar docker stop diretamente
            $this->executeDockerCommand("stop {$containerName}");
            return ['status' => 'stopped', 'name' => $containerName];
            
        } catch (Exception $e) {
            throw new Exception('Failed to stop container: ' . $e->getMessage());
        }
    }
    
    public function removeContainer($containerName) {
        $this->ensureDockerAccess();
        
        try {
            // Usar docker rm diretamente
            $this->executeDockerCommand("rm -f {$containerName}");
            return ['status' => 'removed', 'name' => $containerName];
            
        } catch (Exception $e) {
            throw new Exception('Failed to remove container: ' . $e->getMessage());
        }
    }
}
?>
