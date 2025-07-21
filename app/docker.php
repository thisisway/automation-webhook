<?php

class DockerManager {
    private $socketPath = '/var/run/docker.sock';
    
    private function makeRequest($method, $endpoint, $data = null) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_UNIX_SOCKET_PATH => $this->socketPath,
            CURLOPT_URL => "http://localhost{$endpoint}",
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    // Listar containers
    public function listContainers($all = false) {
        $endpoint = '/containers/json' . ($all ? '?all=true' : '');
        return $this->makeRequest('GET', $endpoint);
    }
    
    // Iniciar container
    public function startContainer($containerId) {
        return $this->makeRequest('POST', "/containers/{$containerId}/start");
    }
    
    // Parar container
    public function stopContainer($containerId) {
        return $this->makeRequest('POST', "/containers/{$containerId}/stop");
    }
    
    // Reiniciar container
    public function restartContainer($containerId) {
        return $this->makeRequest('POST', "/containers/{$containerId}/restart");
    }
    
    // Criar container
    public function createContainer($config, $name = null) {
        $endpoint = '/containers/create' . ($name ? "?name={$name}" : '');
        return $this->makeRequest('POST', $endpoint, $config);
    }
    
    // Remover container
    public function removeContainer($containerId, $force = false) {
        $endpoint = "/containers/{$containerId}" . ($force ? '?force=true' : '');
        return $this->makeRequest('DELETE', $endpoint);
    }
    
    // Logs do container
    public function getContainerLogs($containerId) {
        return $this->makeRequest('GET', "/containers/{$containerId}/logs?stdout=true&stderr=true");
    }
}