#!/bin/bash

echo "🔨 Building new image..."
docker build -t automation-webhook:latest .

echo "🛑 Stopping old container..."
docker stop webhook-container 2>/dev/null || true
docker rm webhook-container 2>/dev/null || true

echo "🚀 Starting new container..."
docker run -d \
  --name webhook-container \
  -p 80:80 \
  -v $(pwd)/storage:/var/www/html/storage \
  -v $(pwd)/logs:/var/logs \
  -v $(pwd)/uploads:/var/www/html/uploads \
  automation-webhook:latest

echo "✅ Deploy completed!"
docker ps | grep webhook-container