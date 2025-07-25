#!/bin/bash
docker service rm automation-webhook traefik
docker network rm automation-webhook
rm -rf /etc/automation-webhook
docker swarm leave --force