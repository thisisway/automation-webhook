#!/usr/bin/env php
<?php

function run($command, $silent = false, $allowFailure = false)
{
    $output = [];
    $status = 0;

    if (!$silent) {
        echo "▶️  Executando: $command\n";
    }

    exec($command, $output, $status);

    if ($status !== 0 && !$allowFailure) {
        echo "❌ Erro ao executar: $command\n";
        exit($status);
    }

    return $output;
}

function dockerSwarmInit()
{
    $isActive = shell_exec('docker info | grep "Swarm: active"');
    if (empty($isActive)) {
        echo "🔧 Inicializando Docker Swarm...\n";
        run('docker swarm init');
    } else {
        echo "✅ Docker Swarm já está ativo.\n";
    }
}

function createOverlayNetwork($networkName)
{
    $existing = shell_exec("docker network ls | grep -w $networkName");
    
    if (empty(trim($existing))) {
        echo "🌐 Criando rede overlay: $networkName\n";
        run("docker network create --driver overlay $networkName");
    } else {
        echo "✅ Rede $networkName já existe.\n";
    }
}

function deployTraefik($networkName)
{
    echo "🚀 Subindo serviço Traefik...\n";

    run("docker service create \
        --name traefik \
        --constraint=node.role==manager \
        --publish 80:80 \
        --publish 443:443 \
        --publish 8080:8080 \
        --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
        --network $networkName \
        --label traefik.enable=true \
        --label traefik.http.routers.api.rule=Host\\(\\`traefik.local\\`\\) \
        --label traefik.http.routers.api.service=api@internal \
        traefik:v2.10 \
        --api.dashboard=true \
        --api.insecure=true \
        --providers.docker=true \
        --providers.docker.swarmMode=true \
        --providers.docker.exposedbydefault=false \
        --entrypoints.web.address=:80 \
        --entrypoints.websecure.address=:443");
}

function deployAutomationWebhook($networkName)
{
    echo "🚀 Subindo serviço automation-webhook...\n";

    run("docker service create \
        --name automation-webhook \
        --network $networkName \
        --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
        --mount type=bind,source=/etc/automation-webhook,target=/etc/automation-webhook \
        --label traefik.enable=true \
        --label traefik.http.routers.automation-webhook.rule=Host\\(\\`automation-webhook.local\\`\\) \
        --label traefik.http.services.automation-webhook.loadbalancer.server.port=8001 \
        automation-webhook:latest");
}

function setup()
{
    echo "\n🔧 Iniciando setup do Automation Webhook...\n\n";
    cleanup();
    dockerSwarmInit();
    createOverlayNetwork("automation-webhook");
    deployTraefik("automation-webhook");
    deployAutomationWebhook("automation-webhook");
    echo "\n✅ Setup concluído com sucesso!\n";
}

function cleanup()
{
    echo "\n🧹 Iniciando limpeza dos serviços...\n\n";
    
    // Remove serviço automation-webhook
    $automationService = trim(shell_exec("docker service ls --filter name=automation-webhook --format '{{.Name}}'"));
    if ($automationService === 'automation-webhook') {
        echo "🗑️  Removendo serviço automation-webhook...\n";
        run("docker service rm automation-webhook");
        sleep(10); // Aguarda remoção do serviço
    } else {
        echo "ℹ️  Serviço automation-webhook não encontrado.\n";
    }
    
    // Remove serviço traefik
    $traefikService = trim(shell_exec("docker service ls --filter name=traefik --format '{{.Name}}'"));
    if ($traefikService === 'traefik') {
        echo "🗑️  Removendo serviço traefik...\n";
        run("docker service rm traefik");
        sleep(5);
    } else {
        echo "ℹ️  Serviço traefik não encontrado.\n";
    }
    
    // Aguarda os serviços serem removidos completamente
    echo "⏳ Aguardando remoção completa dos serviços...\n";
    sleep(5);
    
    // Remove rede overlay
    $existing = shell_exec("docker network ls | grep -w automation-webhook");
    if (!empty(trim($existing))) {
        echo "🌐 Removendo rede automation-webhook...\n";
        run("docker network rm automation-webhook", false, true);
    } else {
        echo "ℹ️  Rede automation-webhook não encontrada.\n";
    }
    
    echo "\n✅ Limpeza concluída com sucesso!\n";
}

function help()
{
    echo "🛠️  Comandos disponíveis:\n";
    echo "  setup - Executa a instalação e sobe os serviços\n";
    echo "  cleanup - Remove todos os serviços e rede criados\n";
    echo "  help - Exibe esta ajuda\n";
}

// Entrypoint
$command = $argv[1] ?? 'help';

switch ($command) {
    case 'setup':
        setup();
        break;
    case 'cleanup':
        cleanup();
        break;
    case 'help':
    default:
        help();
        break;
}
