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

// Inicia o Swarm se ainda não estiver iniciado
if (!str_contains(shell_exec("docker info"), "Swarm: active")) {
    run("docker swarm init");
}

// Cria a rede overlay "automation-webhook" se ela ainda não existir
$networks = shell_exec("docker network ls --filter name=^automation-webhook$ --format '{{.Name}}'");
if (trim($networks) !== "automation-webhook") {
    run("docker network create --driver overlay automation-webhook");
}

$service = shell_exec("docker service ls --filter name=automation-webhook --format '{{.Name}}'");
if (trim($service) !== "automation-webhook") {
    run("docker service create \
    --name traefik \
    --constraint=node.role==manager \
    --publish 80:80 \
    --publish 443:443 \
    --publish 8080:8080 \
    --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
    --network automation-webhook \
    --label traefik.enable=true \
    --label traefik.http.routers.traefik.rule=Host\\(\\`traefik.local\\`\\) \
    --label traefik.http.routers.traefik.service=api@internal \
    --label traefik.http.services.traefik.loadbalancer.server.port=8080 \
    traefik:v2.10");

    // Cria o serviço do Automation Webhook
    run("docker service create \
    --name automation-webhook \
    --publish 8001:8001 \
    --network automation-webhook \
    --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
    --mount type=bind,source=/etc/automation-webhook,target=/etc/automation-webhook \
    --label traefik.enable=true \
    --label traefik.http.routers.automation-webhook.rule=Host\\(\\`webhook.local\\`\\) \
    --label traefik.http.services.automation-webhook.loadbalancer.server.port=8001 \
    automation-webhook:latest");
}
