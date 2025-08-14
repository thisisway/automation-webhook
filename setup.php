#!/usr/bin/env php
<?php

if ($argc < 2 || $argv[1] !== 'setup') {
    echo "\033[33mUso: php setup.php setup\033[0m\n";
    exit(1);
}

function run($command)
{
    echo "\033[36m> $command\033[0m\n";
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        echo "\033[31mErro ao executar: $command\033[0m\n";
        exit($exitCode);
    }
}

function cleanup_orphaned_networks()
{
    shell_exec("docker network prune -f");
}

function cleanup_orphaned_services()
{
    // Remove serviços que podem estar usando redes órfãs
    $existingServices = shell_exec("docker service ls --format '{{.Name}}'");
    if ($existingServices) {
        $services = explode("\n", trim($existingServices));
        foreach ($services as $serviceName) {
            if (!empty($serviceName) && (strpos($serviceName, 'traefik') !== false || strpos($serviceName, 'automation-webhook') !== false)) {
                shell_exec("docker service rm $serviceName 2>/dev/null");
            }
        }
    }
    sleep(2); // Espera os serviços serem removidos completamente
}

// Limpa redes órfãs primeiro
cleanup_orphaned_services();
cleanup_orphaned_networks();

// Inicia o Swarm se ainda não estiver iniciado
if (!str_contains(shell_exec("docker info"), "Swarm: active")) {
    run("docker swarm init");
    sleep(5); // Espera 5 segundos para o swarm estar totalmente ativo
}

// Cria a rede overlay "automation-webhook" se ela ainda não existir
$networks = shell_exec("docker network ls --filter name=^automation-webhook$ --format '{{.Name}}'");
$networks = $networks ?? ""; // Garante que não seja null
if (trim($networks) !== "automation-webhook") {
    run("docker network create --driver overlay --attachable automation-webhook");
    sleep(3); // Espera 3 segundos para a rede estar pronta
} else {
    echo "Rede automation-webhook já existe.\n";
    // Verifica se a rede está realmente funcional
    $networkInfo = shell_exec("docker network inspect automation-webhook 2>/dev/null");
    if (!$networkInfo) {
        echo "Rede existe mas não está acessível, recriando...\n";
        shell_exec("docker network rm automation-webhook 2>/dev/null");
        sleep(1);
        run("docker network create --driver overlay --attachable automation-webhook");
        sleep(3);
    }
}

$service = shell_exec("docker service ls --filter name=automation-webhook --format '{{.Name}}'");
$service = $service ?? "";

// Verifica se o serviço traefik já existe
$traefikService = shell_exec("docker service ls --filter name=traefik --format '{{.Name}}'");
$traefikService = $traefikService ?? "";

if (trim($traefikService) !== "traefik") {
    echo "Criando serviço traefik...\n";
    run("docker service create \
    --name traefik \
    --constraint=node.role==manager \
    --publish 80:80 \
    --publish 443:443 \
    --publish 8080:8080 \
    --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
    --network automation-webhook \
    --label traefik.enable=true \
    --label traefik.http.routers.api.rule=Host\\(\\`traefik.local\\`\\) \
    --label traefik.http.routers.api.service=api@internal \
    --label traefik.http.services.api.loadbalancer.server.port=8080 \
    traefik:v2.10 \
    --api.dashboard=true \
    --api.insecure=true \
    --providers.docker=true \
    --providers.docker.swarmMode=true \
    --providers.docker.exposedByDefault=false \
    --entrypoints.web.address=:80 \
    --entrypoints.websecure.address=:443");
    sleep(5); // Espera 5 segundos para o traefik estar rodando
} else {
    echo "Serviço traefik já existe.\n";
}

if (trim($service) !== "automation-webhook") {
    echo "Criando serviço automation-webhook...\n";
    run("docker service create \
    --name automation-webhook \
    --publish 8001:8001 \
    --network automation-webhook \
    --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
    --mount type=bind,source=/etc/automation-webhook,target=/etc/automation-webhook \
    --label traefik.enable=true \
    --label traefik.http.routers.webhook.rule=Host\\(\\`webhook.local\\`\\) \
    --label traefik.http.routers.webhook.entrypoints=web \
    --label traefik.http.services.webhook.loadbalancer.server.port=8001 \
    --label traefik.docker.network=automation-webhook \
    automation-webhook:latest");
    sleep(3); // Espera 3 segundos para o serviço estar rodando
} else {
    echo "Serviço automation-webhook já existe.\n";
}

// cria arquivo de banco de dados SQLite
$databasePath = '/etc/automation-webhook/database/database.sqlite';
if (!file_exists($databasePath)) {
    if (!is_dir(dirname($databasePath))) {
        mkdir(dirname($databasePath), 0755, true);
    }
    touch($databasePath);   
    echo "Db setup concluído\n";
} 
echo "\033[32mSetup concluído com sucesso!\033[0m\n";
exit(0);
