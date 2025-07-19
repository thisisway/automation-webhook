<?php

class TraefikManager {
    
    public function __construct() {
        // Verificar se a rede do Traefik existe
        $this->ensureTraefikNetwork();
    }
    
    private function ensureTraefikNetwork() {
        try {
            // Verificar se a rede traefik-network existe
            $dockerApiUrl = 'http://localhost/v1.41/networks/traefik-network';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $dockerApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, '/var/run/docker.sock');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 404) {
                // Criar a rede se não existir
                $this->createTraefikNetwork();
            }
            
        } catch (Exception $e) {
            error_log('Error checking Traefik network: ' . $e->getMessage());
        }
    }
    
    private function createTraefikNetwork() {
        $networkConfig = [
            'Name' => 'traefik-network',
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
                'description' => 'Network for Traefik reverse proxy'
            ]
        ];
        
        $dockerApiUrl = 'http://localhost/v1.41/networks/create';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dockerApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($networkConfig));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, '/var/run/docker.sock');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 201) {
            throw new Exception('Failed to create Traefik network: ' . $response);
        }
        
        error_log('Traefik network created successfully');
    }
    
    public function validateTraefikLabels($labels) {
        $requiredLabels = [
            'traefik.enable',
            'traefik.http.routers',
            'traefik.http.services'
        ];
        
        $labelKeys = array_keys($labels);
        
        foreach ($requiredLabels as $required) {
            $found = false;
            foreach ($labelKeys as $key) {
                if (strpos($key, $required) === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new Exception("Missing required Traefik label pattern: {$required}");
            }
        }
        
        return true;
    }
    
    public function generateTraefikConfig($containerName, $subdomain, $port = '80') {
        return [
            'traefik.enable' => 'true',
            'traefik.http.routers.' . $containerName . '.rule' => 'Host(`' . $subdomain . '`)',
            'traefik.http.routers.' . $containerName . '.entrypoints' => 'websecure',
            'traefik.http.routers.' . $containerName . '.tls.certresolver' => 'letsencrypt',
            'traefik.http.services.' . $containerName . '.loadbalancer.server.port' => $port,
            'traefik.docker.network' => 'traefik-network'
        ];
    }
    
    public function checkTraefikStatus() {
        try {
            // Verificar se o container do Traefik está rodando
            $dockerApiUrl = 'http://localhost/v1.41/containers/json?filters={"name":["traefik"]}';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $dockerApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, '/var/run/docker.sock');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return ['status' => 'error', 'message' => 'Cannot connect to Docker API'];
            }
            
            $containers = json_decode($response, true);
            
            if (empty($containers)) {
                return ['status' => 'warning', 'message' => 'Traefik container not found'];
            }
            
            $traefikContainer = $containers[0];
            $status = $traefikContainer['State'];
            
            return [
                'status' => $status === 'running' ? 'ok' : 'error',
                'message' => 'Traefik is ' . $status,
                'container_id' => $traefikContainer['Id'],
                'container_name' => $traefikContainer['Names'][0]
            ];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
?>
