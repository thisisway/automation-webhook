<?php

class ContainerManager
{
    private $basePath;
    private $volumesPath;
    private $scriptsPath;
    private $templatesPath;

    public function __construct()
    {
        $this->basePath = '/var/www/html';
        $this->volumesPath = $this->basePath . '/volumes';
        $this->scriptsPath = $this->basePath . '/app/scripts';
        $this->templatesPath = $this->basePath . '/app/templates';
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
        $uniqueId = $this->generateUniqueId();
        
        // Criar nome do subdomínio
        $subdomain = $this->createSubdomain($client, $software, $uniqueId);
        
        // Criar estrutura de diretórios
        $clientPath = $this->createClientDirectory($client, $uniqueId);
        
        // Criar arquivos de configuração
        $this->createConfigFiles($clientPath, $client, $software, $vcpu, $mem, $subdomain, $uniqueId);
        
        // Executar script de criação
        $result = $this->executeScript('create', $clientPath, $software);
        
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

        // Executar script de remoção
        $result = $this->executeScript('delete', $clientPath);
        
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
    public function getStatus($containerId)
    {
        if (!$containerId) {
            throw new Exception('containerId is required', 400);
        }

        $result = $this->executeCommand("docker ps -q --filter name=" . escapeshellarg($containerId));
        $isRunning = !empty(trim($result['output']));

        $containerInfo = [];
        if ($isRunning) {
            $inspectResult = $this->executeCommand("docker inspect " . escapeshellarg($containerId));
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
            'containerId' => $containerId,
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
                
                $configFile = $dir . '/docker-compose.yml';
                if (file_exists($configFile)) {
                    $config = file_get_contents($configFile);
                    // Extrair informações básicas do docker-compose
                    preg_match('/traefik\.http\.routers\..*\.rule=Host\(`([^`]+)`\)/', $config, $matches);
                    $domain = $matches[1] ?? 'unknown';
                    
                    $status = $this->getStatus($containerId);
                    
                    $containers[] = [
                        'containerId' => $containerId,
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
     * Gera ID único
     */
    private function generateUniqueId()
    {
        return uniqid('', true);
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
    private function createClientDirectory($client, $uniqueId)
    {
        $clientDir = $this->volumesPath . '/' . $client . '_' . $uniqueId;
        
        if (!file_exists($clientDir)) {
            if (!mkdir($clientDir, 0755, true)) {
                throw new Exception('Failed to create client directory', 500);
            }
        }
        
        return $clientDir;
    }

    /**
     * Cria arquivos de configuração
     */
    private function createConfigFiles($clientPath, $client, $software, $vcpu, $mem, $subdomain, $uniqueId)
    {
        // Criar docker-compose.yml
        $template = file_get_contents($this->templatesPath . '/' . $software . '/docker-compose.yml');
        if (!$template) {
            throw new Exception("Template for $software not found", 500);
        }
        
        $template = str_replace('{{CLIENT}}', $client, $template);
        $template = str_replace('{{UNIQUE_ID}}', $uniqueId, $template);
        $template = str_replace('{{SUBDOMAIN}}', $subdomain, $template);
        $template = str_replace('{{VCPU}}', $vcpu, $template);
        $template = str_replace('{{MEMORY}}', $mem . 'm', $template);
        
        file_put_contents($clientPath . '/docker-compose.yml', $template);
        
        // Criar arquivo .env se o template existir
        $envTemplate = $this->templatesPath . '/' . $software . '/.env';
        if (file_exists($envTemplate)) {
            $envContent = file_get_contents($envTemplate);
            $envContent = str_replace('{{CLIENT}}', $client, $envContent);
            $envContent = str_replace('{{UNIQUE_ID}}', $uniqueId, $envContent);
            $envContent = str_replace('{{SUBDOMAIN}}', $subdomain, $envContent);
            
            file_put_contents($clientPath . '/.env', $envContent);
        }
        
        // Criar diretórios de dados se necessário
        $dataDir = $clientPath . '/data';
        if (!file_exists($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
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
