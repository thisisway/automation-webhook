<?php

class ContainerManager
{
    private $volumesPath;
    private $scriptsPath;

    public function __construct()
    {
        $this->volumesPath = './volumes';
        $this->scriptsPath = './app/scripts';
    }

    /**
     * Cria um novo container
     */
    public function createContainer($data)
    {
        // Validação dos dados
        $this->validateInput($data);

        $client = $this->sanitizeString($data['client']);
        $software = $data['software'];
        $vcpu = (int)$data['vcpu'];
        $mem = (int)$data['mem'];

        // Gerar ID único
        $uniqueId = uniqid();
        
        // Criar nome do subdomínio
        $subdomain = $this->createSubdomain($client, $software, $uniqueId);
        
        // Criar estrutura de diretórios apenas para volumes
        $clientPath = $this->createClientDirectory($client, $uniqueId);
        
        // Criar container diretamente com comando Docker
        $result = $this->createContainerDirect($software, $vcpu, $mem, $subdomain, $uniqueId, $clientPath);
        
        if ($result['success']) {
            return [
                'status' => 'success',
                'message' => 'Container created successfully',
                'data' => [
                    'containerId' => $uniqueId,
                    'client' => $client,
                    'software' => $software,
                    'subdomain' => $subdomain . '.bwserver.com.br',
                    'vcpu' => $vcpu,
                    'memory' => $mem,
                    'path' => $clientPath
                ]
            ];
        } else {
            throw new Exception('Failed to create container: ' . $result['error'], 500);
        }
    }

    /**
     * Deleta um container
     */
    public function deleteContainer($data)
    {
        if (!isset($data['containerId']) || !isset($data['client'])) {
            throw new Exception('containerId and client are required', 400);
        }

        $containerId = $data['containerId'];
        $client = $this->sanitizeString($data['client']);
        
        $clientPath = $this->volumesPath . '/' . $client . '_' . $containerId;
        
        if (!is_dir($clientPath)) {
            throw new Exception('Container not found', 404);
        }

        // Encontrar o nome do container baseado no ID
        $containerName = $this->findContainerName($containerId);
        
        if (!$containerName) {
            throw new Exception('Container not found or not running', 404);
        }

        // Executar comando de remoção direta
        $result = $this->deleteContainerDirect($containerName);
        
        if ($result['success']) {
            // Remover diretório
            $this->removeDirectory($clientPath);
            
            return [
                'status' => 'success',
                'message' => 'Container deleted successfully',
                'containerId' => $containerId
            ];
        } else {
            throw new Exception('Failed to delete container: ' . $result['error'], 500);
        }
    }

    /**
     * Obtém status do container
     */
    public function getStatus($containerName)
    {
        if (!$containerName) {
            throw new Exception('containerName is required', 400);
        }

        $result = $this->executeCommand("docker ps -q --filter name=" . escapeshellarg($containerName));
        $isRunning = !empty(trim($result['output']));

        $containerInfo = [];
        if ($isRunning) {
            $inspectResult = $this->executeCommand("docker inspect " . escapeshellarg($containerName));
            if ($inspectResult['success'] && $inspectResult['output']) {
                $containerData = json_decode($inspectResult['output'], true);
                if ($containerData && isset($containerData[0])) {
                    $container = $containerData[0];
                    $containerInfo = [
                        'name' => $container['Name'],
                        'status' => $container['State']['Status'],
                        'created' => $container['Created'],
                        'image' => $container['Config']['Image']
                    ];
                }
            }
        }

        return [
            'status' => 'success',
            'containerName' => $containerName,
            'running' => $isRunning,
            'info' => $containerInfo
        ];
    }

    /**
     * Lista containers de um cliente
     */
    public function listContainers($client = null)
    {
        $containers = [];
        $pattern = $client ? $this->sanitizeString($client) . '_*' : '*';
        
        $dirs = glob($this->volumesPath . '/' . $pattern, GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            $dirname = basename($dir);
            $parts = explode('_', $dirname);
            
            if (count($parts) >= 2) {
                $clientName = $parts[0];
                $containerId = $parts[1];
                
                // Buscar container pelo nome diretamente
                $containerName = null;
                $domain = 'unknown';
                
                // Tentar encontrar container N8N
                $n8nName = 'n8n-' . $containerId;
                $n8nResult = $this->executeCommand("docker inspect " . escapeshellarg($n8nName) . " 2>/dev/null");
                if ($n8nResult['success']) {
                    $containerName = $n8nName;
                    // Extrair domínio das labels do container
                    $inspectData = json_decode($n8nResult['output'], true);
                    if ($inspectData && isset($inspectData[0]['Config']['Labels'])) {
                        foreach ($inspectData[0]['Config']['Labels'] as $label => $value) {
                            if (strpos($label, 'traefik.http.routers.') === 0 && strpos($label, '.rule') !== false) {
                                if (preg_match('/Host\(`([^`]+)`\)/', $value, $matches)) {
                                    $domain = $matches[1];
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    // Tentar encontrar container EvoAPI
                    $evoName = 'evoapi-' . $containerId;
                    $evoResult = $this->executeCommand("docker inspect " . escapeshellarg($evoName) . " 2>/dev/null");
                    if ($evoResult['success']) {
                        $containerName = $evoName;
                        // Extrair domínio das labels do container
                        $inspectData = json_decode($evoResult['output'], true);
                        if ($inspectData && isset($inspectData[0]['Config']['Labels'])) {
                            foreach ($inspectData[0]['Config']['Labels'] as $label => $value) {
                                if (strpos($label, 'traefik.http.routers.') === 0 && strpos($label, '.rule') !== false) {
                                    if (preg_match('/Host\(`([^`]+)`\)/', $value, $matches)) {
                                        $domain = $matches[1];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if ($containerName) {
                    $status = $this->getStatus($containerName);
                    
                    $containers[] = [
                        'containerId' => $containerId,
                        'containerName' => $containerName,
                        'client' => $clientName,
                        'domain' => $domain,
                        'running' => $status['running'],
                        'path' => $dir
                    ];
                }
            }
        }
        
        return [
            'status' => 'success',
            'containers' => $containers,
            'count' => count($containers)
        ];
    }

    /**
     * Valida dados de entrada
     */
    private function validateInput($data)
    {
        $required = ['client', 'software', 'vcpu', 'mem'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Field '$field' is required", 400);
            }
        }

        if (!in_array($data['software'], ['n8n', 'evoapi'])) {
            throw new Exception('Software must be either n8n or evoapi', 400);
        }

        $vcpu = (int)$data['vcpu'];
        if ($vcpu < 1 || $vcpu > 8) {
            throw new Exception('vCPU must be between 1 and 8', 400);
        }

        $mem = (int)$data['mem'];
        if ($mem < 256 || $mem > 16384) {
            throw new Exception('Memory must be between 256 and 16384 MB', 400);
        }
    }

    /**
     * Sanitiza string para usar como nome de diretório
     */
    private function sanitizeString($string)
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', strtolower($string));
    }

    /**
     * Cria subdomínio
     */
    private function createSubdomain($client, $software, $uniqueId)
    {
        $clientTruncated = substr($this->sanitizeString($client), 0, 15);
        $shortId = substr($uniqueId, -8);
        return $clientTruncated . '-' . $software . '-' . $shortId;
    }

    /**
     * Cria diretório do cliente
     */
    private function createClientDirectory($client)
    {
        $clientDir = $this->volumesPath . '/' . $client;
        
        if (!file_exists($clientDir)) {
            if (!mkdir($clientDir, 0755, true)) {
                throw new Exception('Failed to create client directory', 500);
            }
        }
        
        return $clientDir;
    }

    /**
     * Cria container diretamente com comandos Docker
     */
    private function createContainerDirect($software, $vcpu, $mem, $subdomain, $uniqueId, $clientPath)
    {
        $containerName = $software . '-' . $uniqueId;

        // Criar diretórios de dados necessários
        $dataDir = $clientPath . '/data';
        if (!file_exists($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        if ($software === 'n8n') {
            return $this->createN8nContainer($containerName, $subdomain, $clientPath, $vcpu, $mem);
        } elseif ($software === 'evoapi') {
            return $this->createEvoApiContainer($containerName, $subdomain, $clientPath, $vcpu, $mem);
        }
        
        return ['success' => false, 'error' => 'Software not supported'];
    }
    
    /**
     * Cria container N8N
     */
    private function createN8nContainer($containerName, $subdomain, $clientPath, $vcpu, $mem)
    {
        // Criar diretório de dados para o N8N
        $n8nDataDir = $clientPath . '/n8n_data';
        if (!file_exists($n8nDataDir)) {
            mkdir($n8nDataDir, 0755, true);
        }

        $domain = $subdomain . '.bwserver.com.br';

        $domainRule = 'Host(`' . $domain . '` || `'.$subdomain.'.localhost`) ';

        $command = "docker run -d " .
                   "--name " . escapeshellarg($containerName) . " " .
                   "--restart unless-stopped " .
                   "-v " . escapeshellarg($n8nDataDir) . ":/home/node/.n8n " .
                   "--network traefik " .
                   "--label traefik.enable=true " .
                   "--label " . escapeshellarg("traefik.http.routers." . $containerName . ".rule=" . $domainRule) . " " .
                   "--label " . escapeshellarg("traefik.http.routers." . $containerName . ".entrypoints=web") . " " .
                   "--label " . escapeshellarg("traefik.http.services." . $containerName . ".loadbalancer.server.port=5678") . " " .
                   "--cpus=" . $vcpu . " " .
                   "--memory=" . $mem . "m " .
                   "-e N8N_HOST=" . escapeshellarg($domain) . " " .
                   "docker.n8n.io/n8nio/n8n";
        
        return $this->executeCommand($command);
    }
    
    /**
     * Cria container Evolution API
     */
    private function createEvoApiContainer($containerName, $domain, $clientPath, $vcpu, $mem)
    {
        // Criar subdiretórios específicos do Evolution API
        $instancesDir = $clientPath . '/data/evolution_instances';
        $storeDir = $clientPath . '/data/evolution_store';
        
        if (!file_exists($instancesDir)) {
            mkdir($instancesDir, 0755, true);
        }
        if (!file_exists($storeDir)) {
            mkdir($storeDir, 0755, true);
        }
        
        $command = "docker run -d " .
                   "--name " . escapeshellarg($containerName) . " " .
                   "--restart unless-stopped " .
                   "--cpus=" . $vcpu . " " .
                   "--memory=" . $mem . "m " .
                   "-v " . escapeshellarg($instancesDir) . ":/evolution/instances " .
                   "-v " . escapeshellarg($storeDir) . ":/evolution/store " .
                   "--network traefik " .
                   "-e SERVER_TYPE=http " .
                   "-e SERVER_PORT=8080 " .
                   "-e CORS_ORIGIN=* " .
                   "-e CORS_METHODS=POST,GET,PUT,DELETE " .
                   "-e CORS_CREDENTIALS=true " .
                   "-e LOG_LEVEL=ERROR " .
                   "-e LOG_COLOR=true " .
                   "-e LOG_BAILEYS=error " .
                   "-e DEL_INSTANCE=false " .
                   "-e PROVIDER_ENABLED=false " .
                   "-e DATABASE_ENABLED=false " .
                   "-e REDIS_ENABLED=false " .
                   "-e RABBITMQ_ENABLED=false " .
                   "-e SQS_ENABLED=false " .
                   "-e WEBSOCKET_ENABLED=false " .
                   "-e WEBSOCKET_GLOBAL_EVENTS=false " .
                   "-e CONFIG_SESSION_PHONE_CLIENT=\"Evolution API\" " .
                   "-e CONFIG_SESSION_PHONE_NAME=Chrome " .
                   "-e QRCODE_LIMIT=30 " .
                   "-e AUTHENTICATION_TYPE=apikey " .
                   "-e AUTHENTICATION_API_KEY=429683C4C977415CAAFCCE10F7D57E11 " .
                   "-e AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES=true " .
                   "-e LANGUAGE=pt-BR " .
                   "--label traefik.enable=true " .
                   "--label \"traefik.http.routers." . $containerName . ".rule=Host(\`" . $domain . "\`)\" " .
                   "--label traefik.http.routers." . $containerName . ".entrypoints=web " .
                   "--label traefik.http.services." . $containerName . ".loadbalancer.server.port=8080 " .
                   "atendai/evolution-api:latest";
        
        return $this->executeCommand($command);
    }
    
    /**
     * Deleta container diretamente
     */
    private function deleteContainerDirect($containerName)
    {
        // Primeiro tenta parar o container se estiver rodando
        $stopCommand = "docker stop " . escapeshellarg($containerName) . " 2>/dev/null || true";
        $this->executeCommand($stopCommand);
        
        // Remove o container
        $removeCommand = "docker rm -f " . escapeshellarg($containerName) . " 2>/dev/null || true";
        $result = $this->executeCommand($removeCommand);
        
        // Limpar volumes órfãos relacionados (opcional)
        $pruneCommand = "docker volume prune -f 2>/dev/null || true";
        $this->executeCommand($pruneCommand);
        
        return $result;
    }
    
    /**
     * Encontra o nome do container baseado no ID único
     */
    private function findContainerName($containerId)
    {
        // Tentar encontrar container N8N
        $n8nName = 'n8n-' . $containerId;
        $n8nResult = $this->executeCommand("docker ps -q --filter name=" . escapeshellarg($n8nName));
        if (!empty(trim($n8nResult['output']))) {
            return $n8nName;
        }
        
        // Tentar encontrar container EvoAPI
        $evoName = 'evoapi-' . $containerId;
        $evoResult = $this->executeCommand("docker ps -q --filter name=" . escapeshellarg($evoName));
        if (!empty(trim($evoResult['output']))) {
            return $evoName;
        }
        
        return null;
    }

    /**
     * Executa script
     */
    private function executeScript($action, $clientPath, $software = '')
    {
        $scriptPath = $this->scriptsPath . '/' . $action . '.sh';
        
        if (!file_exists($scriptPath)) {
            return ['success' => false, 'error' => 'Script not found'];
        }
        
        $command = "cd " . escapeshellarg($clientPath) . " && bash " . escapeshellarg($scriptPath);
        if ($software) {
            $command .= " " . escapeshellarg($software);
        }
        
        return $this->executeCommand($command);
    }

    /**
     * Executa comando no sistema
     */
    private function executeCommand($command)
    {
        $output = [];
        $returnVar = 0;
        
        exec($command . ' 2>&1', $output, $returnVar);
        
        return [
            'success' => $returnVar === 0,
            'output' => implode("\n", $output),
            'error' => $returnVar !== 0 ? implode("\n", $output) : null
        ];
    }

    /**
     * Remove diretório recursivamente
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
}
