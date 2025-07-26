#!/bin/bash
docker build -t automation-webhook:latest .
docker run --rm -i \
  -v /etc/automation-webhook:/etc/automation-webhook \
  -v /var/run/docker.sock:/var/run/docker.sock:ro \
  automation-webhook:latest setup