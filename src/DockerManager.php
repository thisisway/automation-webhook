<?php

class DockerManager {
    
    public function __construct() {
        // Verificar se o comando docker está disponível
        if (!$this->isDockerAvailable()) {
            throw new Exception('Docker is not available. Make sure Docker is running and accessible.');
        }
    }
    
    private function isDockerAvailable() {
        $output = shell_exec('which docker 2>/dev/null');
        return !empty(trim($output));
    }
    
    private function executeDockerCommand($command) {
        $fullCommand = "docker $command 2>&1";
        $output = shell_exec($fullCommand);
        $exitCode = 0;
        
        // Verificar se o comando foi executado com sucesso
        exec($fullCommand, $outputArray, $exitCode);
        
        if ($exitCode !== 0) {
            throw new Exception("Docker command failed: $command\nOutput: $output");
        }
        
        return trim($output);
    }
    
    public function createN8nContainer($containerName, $vcpu, $mem, $subdomain) {
        try {
            // Verificar se o container já existe
            $existingContainer = shell_exec("docker ps -aq -f name=^{$containerName}$");
            if (!empty(trim($existingContainer))) {
                throw new Exception("Container with name {$containerName} already exists");
            }
            
            // Verificar se a rede traefik existe
            $networkExists = shell_exec("docker network ls -q -f name=^traefik$");
            if (empty(trim($networkExists))) {
                throw new Exception("Traefik network not found. Please create it first.");
            }
            
            // Montar comando docker run
            $dockerCommand = "run -d" .
                " --name {$containerName}" .
                " --restart unless-stopped" .
                " --memory {$mem}m" .
                " --cpus {$vcpu}" .
                " --network traefik" .
                " --expose 5678" .
                " -e N8N_HOST={$subdomain}" .
                " -e N8N_PORT=5678" .
                " -e N8N_PROTOCOL=https" .
                " -e WEBHOOK_URL=https://{$subdomain}" .
                " -e GENERIC_TIMEZONE=America/Sao_Paulo" .
                " -l traefik.enable=true" .
                " -l traefik.http.routers.{$containerName}.rule=Host\\(\\`{$subdomain}\\`\\)" .
                " -l traefik.http.routers.{$containerName}.entrypoints=websecure" .
                " -l traefik.http.routers.{$containerName}.tls.certresolver=letsencrypt" .
                " -l traefik.http.services.{$containerName}.loadbalancer.server.port=5678" .
                " -l traefik.docker.network=traefik" .
                " n8nio/n8n:latest";
            
            $containerId = $this->executeDockerCommand($dockerCommand);
            
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
            // Verificar se o container já existe
            $existingContainer = shell_exec("docker ps -aq -f name=^{$containerName}$");
            if (!empty(trim($existingContainer))) {
                throw new Exception("Container with name {$containerName} already exists");
            }
            
            // Verificar se a rede traefik existe
            $networkExists = shell_exec("docker network ls -q -f name=^traefik$");
            if (empty(trim($networkExists))) {
                throw new Exception("Traefik network not found. Please create it first.");
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
