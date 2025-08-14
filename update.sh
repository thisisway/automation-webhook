#!/bin/bash
docker build -t automation-webhook:latest .
docker service update --image automation-webhook:latest automation-webhook