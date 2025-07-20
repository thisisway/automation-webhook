<?php

class TemplateManager {
    
    private $templatesDir;
    private $dataDir;
    
    public function __construct($templatesDir = './templates', $dataDir = './data') {
        $this->templatesDir = $templatesDir;
        $this->dataDir = $dataDir;
        
        // Criar diretório data se não existir
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }
    
    /**
     * Processar template substituindo placeholders
     */
    public function processTemplate($templateFile, $variables = []) {
        $templatePath = $this->templatesDir . '/' . $templateFile;
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template not found: {$templatePath}");
        }
        
        $content = file_get_contents($templatePath);
        
        // Substituir variáveis no formato {{VARIABLE}}
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Criar arquivo docker-compose a partir do template
     */
    public function createComposeFile($templateName, $outputFile, $variables = []) {
        $content = $this->processTemplate($templateName . '-compose.yml', $variables);
        
        $outputPath = $this->dataDir . '/' . $outputFile;
        
        if (file_put_contents($outputPath, $content) === false) {
            throw new Exception("Failed to write compose file: {$outputPath}");
        }
        
        return $outputPath;
    }
    
    /**
     * Criar configuração do Traefik
     */
    public function createTraefikConfig($email, $domain = null) {
        // Criar traefik.yml
        $traefikConfig = $this->processTemplate('traefik.yml', [
            'EMAIL' => $email
        ]);
        
        $traefikPath = $this->dataDir . '/traefik.yml';
        file_put_contents($traefikPath, $traefikConfig);
        
        // Copiar config.yml
        $configContent = file_get_contents($this->templatesDir . '/config.yml');
        $configPath = $this->dataDir . '/config.yml';
        file_put_contents($configPath, $configContent);
        
        // Criar acme.json se não existir
        $acmePath = $this->dataDir . '/acme.json';
        if (!file_exists($acmePath)) {
            file_put_contents($acmePath, '{}');
            chmod($acmePath, 0600);
        }
        
        return [
            'traefik_config' => $traefikPath,
            'dynamic_config' => $configPath,
            'acme_file' => $acmePath
        ];
    }
    
    /**
     * Criar docker-compose para Traefik
     */
    public function createTraefikCompose($domain, $email, $traefikHost = null, $cloudflareToken = null) {
        $traefikHost = $traefikHost ?: "traefik.{$domain}";
        
        // Criar hash básico para auth (admin:admin por padrão)
        $authHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // admin
        
        $variables = [
            'DOMAIN' => $domain,
            'TRAEFIK_HOST' => $traefikHost,
            'TRAEFIK_AUTH' => "admin:{$authHash}",
            'CLOUDFLARE_TOKEN' => $cloudflareToken ?: 'your_cloudflare_token'
        ];
        
        // Criar configurações do Traefik
        $this->createTraefikConfig($email, $domain);
        
        return $this->createComposeFile('traefik', 'traefik-compose.yml', $variables);
    }
    
    /**
     * Criar docker-compose para Portainer
     */
    public function createPortainerCompose($domain, $portainerHost = null) {
        $portainerHost = $portainerHost ?: "portainer.{$domain}";
        
        $variables = [
            'PORTAINER_HOST' => $portainerHost
        ];
        
        return $this->createComposeFile('portainer', 'portainer-compose.yml', $variables);
    }
    
    /**
     * Criar docker-compose para N8N
     */
    public function createN8nCompose($containerName, $subdomain, $vcpu = '1.0', $memory = 1024) {
        $dbPassword = $this->generatePassword();
        
        $variables = [
            'CONTAINER_NAME' => $containerName,
            'SUBDOMAIN' => $subdomain,
            'VCPU' => $vcpu,
            'MEMORY' => $memory,
            'DB_PASSWORD' => $dbPassword
        ];
        
        $composeFile = $containerName . '-compose.yml';
        return $this->createComposeFile('n8n', $composeFile, $variables);
    }
    
    /**
     * Criar docker-compose para Evolution API
     */
    public function createEvolutionCompose($containerName, $subdomain, $vcpu = '1.0', $memory = 512, $apiKey = null) {
        $apiKey = $apiKey ?: $this->generateApiKey();
        
        $variables = [
            'CONTAINER_NAME' => $containerName,
            'SUBDOMAIN' => $subdomain,
            'VCPU' => $vcpu,
            'MEMORY' => $memory,
            'API_KEY' => $apiKey
        ];
        
        $composeFile = $containerName . '-compose.yml';
        return $this->createComposeFile('evolution', $composeFile, $variables);
    }
    
    /**
     * Gerar senha aleatória
     */
    private function generatePassword($length = 16) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }
    
    /**
     * Gerar API Key
     */
    private function generateApiKey() {
        return strtoupper(md5(uniqid(rand(), true)));
    }
    
    /**
     * Executar docker-compose
     */
    public function deployCompose($composeFile, $action = 'up -d') {
        $composePath = $this->dataDir . '/' . $composeFile;
        
        if (!file_exists($composePath)) {
            throw new Exception("Compose file not found: {$composePath}");
        }
        
        $command = "cd {$this->dataDir} && docker-compose -f {$composeFile} {$action}";
        
        exec($command . ' 2>&1', $output, $exitCode);
        
        if ($exitCode !== 0) {
            throw new Exception("Docker compose failed: " . implode("\n", $output));
        }
        
        return [
            'success' => true,
            'output' => $output,
            'command' => $command
        ];
    }
    
    /**
     * Verificar se container está rodando
     */
    public function isContainerRunning($containerName) {
        exec("docker ps -q --filter name={$containerName}", $output);
        return !empty($output);
    }
    
    /**
     * Listar arquivos de compose criados
     */
    public function listComposeFiles() {
        $files = glob($this->dataDir . '/*-compose.yml');
        return array_map('basename', $files);
    }
    
    /**
     * Remover compose e container
     */
    public function removeService($composeFile) {
        $composePath = $this->dataDir . '/' . $composeFile;
        
        if (file_exists($composePath)) {
            // Parar e remover containers
            $this->deployCompose($composeFile, 'down -v');
            
            // Remover arquivo compose
            unlink($composePath);
        }
        
        return true;
    }
}

?>
