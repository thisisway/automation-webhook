<?php

require_once 'TemplateManager.php';
require_once 'DockerManager.php';

class SystemInitializer {
    
    private $templateManager;
    private $dockerManager;
    
    public function __construct() {
        $this->templateManager = new TemplateManager();
        $this->dockerManager = new DockerManager();
    }
    
    /**
     * Inicializar sistema completo após instalação do Docker
     */
    public function initialize($config = []) {
        try {
            $results = [];
            
            // Configurações padrão
            $domain = $config['domain'] ?? 'bwserver.com.br';
            $email = $config['email'] ?? 'admin@bwserver.com.br';
            $traefikHost = $config['traefik_host'] ?? "traefik.{$domain}";
            $portainerHost = $config['portainer_host'] ?? "manager.{$domain}";
            $cloudflareToken = $config['cloudflare_token'] ?? null;
            
            // 1. Criar rede Traefik
            $results[] = $this->createTraefikNetwork();
            
            // 2. Subir Traefik
            $results[] = $this->deployTraefik($domain, $email, $traefikHost, $cloudflareToken);
            
            // 3. Aguardar Traefik ficar disponível
            $results[] = $this->waitForTraefik();
            
            // 4. Subir Portainer
            $results[] = $this->deployPortainer($domain, $portainerHost);
            
            // 5. Aguardar Portainer ficar disponível
            $results[] = $this->waitForPortainer();
            
            return [
                'success' => true,
                'message' => 'Sistema inicializado com sucesso!',
                'results' => $results,
                'endpoints' => [
                    'traefik' => "https://{$traefikHost}",
                    'portainer' => "https://{$portainerHost}",
                    'traefik_local' => 'http://localhost:8080',
                    'portainer_local' => 'http://localhost:9000'
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na inicialização: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Criar rede Traefik
     */
    private function createTraefikNetwork() {
        try {
            // Verificar se rede já existe
            exec('docker network ls --filter name=traefik -q', $output);
            
            if (empty($output)) {
                exec('docker network create traefik 2>&1', $output, $exitCode);
                
                if ($exitCode !== 0) {
                    throw new Exception('Falha ao criar rede Traefik: ' . implode('\n', $output));
                }
                
                return [
                    'step' => 'create_traefik_network',
                    'status' => 'success',
                    'message' => 'Rede Traefik criada com sucesso'
                ];
            } else {
                return [
                    'step' => 'create_traefik_network',
                    'status' => 'skipped',
                    'message' => 'Rede Traefik já existe'
                ];
            }
        } catch (Exception $e) {
            throw new Exception('Erro ao criar rede Traefik: ' . $e->getMessage());
        }
    }
    
    /**
     * Deploy do Traefik
     */
    private function deployTraefik($domain, $email, $traefikHost, $cloudflareToken = null) {
        try {
            // Criar compose do Traefik
            $composeFile = $this->templateManager->createTraefikCompose(
                $domain, 
                $email, 
                $traefikHost, 
                $cloudflareToken
            );
            
            // Deploy
            $result = $this->templateManager->deployCompose('traefik-compose.yml');
            
            return [
                'step' => 'deploy_traefik',
                'status' => 'success',
                'message' => 'Traefik deployado com sucesso',
                'compose_file' => $composeFile,
                'deploy_result' => $result
            ];
            
        } catch (Exception $e) {
            throw new Exception('Erro ao deployar Traefik: ' . $e->getMessage());
        }
    }
    
    /**
     * Deploy do Portainer
     */
    private function deployPortainer($domain, $portainerHost) {
        try {
            // Criar compose do Portainer
            $composeFile = $this->templateManager->createPortainerCompose($domain, $portainerHost);
            
            // Deploy
            $result = $this->templateManager->deployCompose('portainer-compose.yml');
            
            return [
                'step' => 'deploy_portainer',
                'status' => 'success',
                'message' => 'Portainer deployado com sucesso',
                'compose_file' => $composeFile,
                'deploy_result' => $result
            ];
            
        } catch (Exception $e) {
            throw new Exception('Erro ao deployar Portainer: ' . $e->getMessage());
        }
    }
    
    /**
     * Aguardar Traefik ficar disponível
     */
    private function waitForTraefik($maxAttempts = 30) {
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            if ($this->checkUrl('http://localhost:8080/api/version')) {
                return [
                    'step' => 'wait_traefik',
                    'status' => 'success',
                    'message' => 'Traefik está disponível',
                    'attempts' => $attempt + 1
                ];
            }
            
            sleep(2);
            $attempt++;
        }
        
        throw new Exception('Traefik não ficou disponível após ' . $maxAttempts . ' tentativas');
    }
    
    /**
     * Aguardar Portainer ficar disponível
     */
    private function waitForPortainer($maxAttempts = 30) {
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            if ($this->checkUrl('http://localhost:9000/api/system/status')) {
                return [
                    'step' => 'wait_portainer',
                    'status' => 'success',
                    'message' => 'Portainer está disponível',
                    'attempts' => $attempt + 1
                ];
            }
            
            sleep(2);
            $attempt++;
        }
        
        throw new Exception('Portainer não ficou disponível após ' . $maxAttempts . ' tentativas');
    }
    
    /**
     * Verificar se URL responde
     */
    private function checkUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SystemInitializer/1.0');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode >= 200 && $httpCode < 400);
    }
    
    /**
     * Criar container N8N
     */
    public function createN8nContainer($containerName, $subdomain, $vcpu = 1.0, $memory = 1024) {
        try {
            // Criar compose file
            $composeFile = $this->templateManager->createN8nCompose(
                $containerName, 
                $subdomain, 
                (string)$vcpu, 
                $memory
            );
            
            // Deploy
            $result = $this->templateManager->deployCompose($containerName . '-compose.yml');
            
            return [
                'success' => true,
                'container_name' => $containerName,
                'subdomain' => $subdomain,
                'compose_file' => $composeFile,
                'deploy_result' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Criar container Evolution API
     */
    public function createEvolutionContainer($containerName, $subdomain, $vcpu = 1.0, $memory = 512, $apiKey = null) {
        try {
            // Criar compose file
            $composeFile = $this->templateManager->createEvolutionCompose(
                $containerName, 
                $subdomain, 
                (string)$vcpu, 
                $memory,
                $apiKey
            );
            
            // Deploy
            $result = $this->templateManager->deployCompose($containerName . '-compose.yml');
            
            return [
                'success' => true,
                'container_name' => $containerName,
                'subdomain' => $subdomain,
                'api_key' => $apiKey,
                'compose_file' => $composeFile,
                'deploy_result' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Listar todos os serviços
     */
    public function listServices() {
        try {
            $composeFiles = $this->templateManager->listComposeFiles();
            $services = [];
            
            foreach ($composeFiles as $file) {
                $serviceName = str_replace('-compose.yml', '', $file);
                $isRunning = $this->templateManager->isContainerRunning($serviceName);
                
                $services[] = [
                    'name' => $serviceName,
                    'compose_file' => $file,
                    'status' => $isRunning ? 'running' : 'stopped'
                ];
            }
            
            return [
                'success' => true,
                'services' => $services
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Remover serviço
     */
    public function removeService($serviceName) {
        try {
            $composeFile = $serviceName . '-compose.yml';
            $this->templateManager->removeService($composeFile);
            
            return [
                'success' => true,
                'message' => "Serviço {$serviceName} removido com sucesso"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

?>
