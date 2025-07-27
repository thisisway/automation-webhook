run this command to start app:

docker run --rm -i \
  -v /etc/automation-webhook:/etc/automation-webhook \
  -v /var/run/docker.sock:/var/run/docker.sock:ro \
  automation-webhook:latest setup