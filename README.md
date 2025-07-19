# Automation Webhook

Webhook para automação de criação de serviços Docker (N8N e Evolution API) com Traefik.

## Funcionalidades

- ✅ Webhook HTTP para criação automática de containers
- ✅ Suporte para N8N e Evolution API  
- ✅ Gerenciamento automático de subdomínios via Traefik
- ✅ Controle de recursos (CPU e Memória)
- ✅ Interface de teste incluída

## Como usar

### 1. Endpoint Principal

**URL**: `POST /index.php`

**Payload**:
```json
{
  "client": "jurandir",
  "vcpu": 2,
  "mem": 4096,
  "soft": "n8n"
}
```

### 2. Parâmetros

| Campo  | Tipo   | Descrição                           | Exemplo    |
|--------|--------|-------------------------------------|------------|
| client | string | Nome do cliente (alfanumérico + -) | "jurandir" |
| vcpu   | int    | Número de vCPUs (mínimo 1)         | 2          |
| mem    | int    | Memória em MB (mínimo 512)         | 4096       |
| soft   | string | Software: "n8n" ou "evoapi"        | "n8n"      |

### 3. Resposta de Sucesso

```json
{
  "success": true,
  "message": "Service created successfully",
  "data": {
    "container_name": "jurandir-n8n-abc12345",
    "subdomain": "jurandir-n8n-abc12345.bwserver.com.br",
    "vcpu": 2,
    "memory": "4096MB",
    "software": "n8n",
    "status": "created",
    "url": "https://jurandir-n8n-abc12345.bwserver.com.br"
  }
}
```

## Instalação

### Pré-requisitos

- Docker e Docker Compose
- Traefik configurado com:
  - Rede `traefik-network` 
  - Entrypoint `websecure`
  - Cert resolver `letsencrypt`

### Passo a passo

1. **Clone o projeto**
```bash
git clone <repo-url>
cd automation-webhook
```

2. **Configure o domínio**

Edite o `docker-compose.yml` e altere `webhook.bwserver.com.br` para seu domínio.

3. **Suba o serviço**
```bash
docker-compose up -d
```

4. **Teste o webhook**

Acesse: `https://webhook.bwserver.com.br/test.php`

## Estrutura do Projeto

```
automation-webhook/
├── src/
│   ├── index.php          # Endpoint principal do webhook
│   ├── DockerManager.php  # Gerenciamento de containers
│   ├── TraefikManager.php # Configuração do Traefik
│   └── test.php          # Interface de teste
├── docker-compose.yml     # Configuração do Docker
├── Dockerfile            # Imagem customizada PHP+Apache
└── README.md            # Este arquivo
```

## Softwares Suportados

### N8N (Automação)
- **Imagem**: `n8nio/n8n:latest`
- **Porta**: 5678
- **Recursos**: Configurável via parâmetros
- **SSL**: Automático via Traefik

### Evolution API (WhatsApp)
- **Imagem**: `davidsongomes/evolution-api:v2.1.1`
- **Porta**: 8080
- **Recursos**: Configurável via parâmetros
- **SSL**: Automático via Traefik
- **API Key**: `B6D711FCDE4D4FD5936544120E713976`

## Segurança

- Validação rigorosa de entrada
- Limitação de recursos por container
- Isolamento de rede via Traefik
- Socket Docker em modo read-only

## Exemplo de Uso via cURL

```bash
curl -X POST https://webhook.bwserver.com.br/index.php \
  -H "Content-Type: application/json" \
  -d '{
    "client": "empresa-teste",
    "vcpu": 2,
    "mem": 2048,
    "soft": "evoapi"
  }'
```

## Logs e Debug

Para ver logs do container:
```bash
docker-compose logs -f automation-webhook
```

## Limitações

- Máximo de recursos por container: 16 vCPUs, 32GB RAM
- Nome do cliente: apenas letras, números e hífen
- Softwares: apenas N8N e Evolution API
- Requer Traefik pré-configurado

## Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## Licença

MIT License - veja LICENSE para detalhes.
