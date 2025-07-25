#!/bin/sh
set -e

# check if the current user is root
if [ "$(id -u)" != "0" ]; then
    echo "Error: you must be root to execute this script" >&2
    exit 1
fi

# check if is Mac OS
if [ "$(uname)" = "Darwin" ]; then
    echo "Error: MacOS is not supported" >&2
    exit 1
fi

# check if is running inside a container
if [ -f /.dockerenv ]; then
    echo "Error: running inside a container is not supported" >&2
    exit 1
fi

# check if something is running on port 80
if lsof -i :80 -sTCP:LISTEN >/dev/null; then
    echo "Error: something is already running on port 80" >&2
    exit 1
fi

# check if something is running on port 443
if lsof -i :443 -sTCP:LISTEN >/dev/null; then
    echo "Error: something is already running on port 443" >&2
    exit 1
fi

command_exists() {
  command -v "$@" > /dev/null 2>&1
}

if command_exists docker; then
  echo "Docker already installed"
else
  curl -sSL https://get.docker.com | sh
  sudo usermod -aG docker $USER
fi



if command_exists docker; then
  if docker network ls | grep -q "traefik"; then
    echo "Docker network traefik already exists"
  else
    echo "Creating Docker network traefik"
    docker network create traefik
  fi

  if docker ps -q --filter "name=automation-webhook" | grep -q .; then
    echo "Automation webhook plataform is already running"
  else
    echo "Starting automation webhook plataform"
    docker compose up -d
  fi

  echo "Creating directories for automation webhook"

  # Create system directories
  mkdir -p /etc/automation-webhook
  chown -R $USER:$USER /etc/automation-webhook
  chmod -R 755 /etc/automation-webhook
fi

echo "Automation webhook plataform started successfully"


