# 🚀 Automation Webhook System

Sistema automatizado de deployment e gerenciamento de containers Docker com templates, seguindo o modelo EasyPanel.

## 📁 Estrutura do Projeto

```
automation-webhook/
├── setup-server.sh           # Script principal de instalação (executar como root)
├── docker-compose.yml        # Container principal do webhook
├── templates/                 # Templates para containers
│   ├── traefik-compose.yml   # Template Traefik
│   ├── portainer-compose.yml # Template Portainer
│   ├── n8n-compose.yml       # Template N8N
│   ├── evolution-compose.yml # Template Evolution API
│   ├── traefik.yml          # Configuração Traefik
│   └── config.yml           # Configuração adicional Traefik
├── src/                      # Código PHP
│   ├── DockerManager.php    # Gerenciador Docker (socket direto)
│   ├── TemplateManager.php  # Gerenciador de templates
│   ├── SystemInitializer.php # Inicializador do sistema
│   ├── system-init.php      # API de inicialização
│   ├── system-status.php    # Status do sistema
│   ├── index.php           # API principal
│   └── test.php            # Interface de teste
└── data/                    # Arquivos gerados (criado automaticamente)
    ├── traefik-compose.yml  # Compose gerado do Traefik
    ├── portainer-compose.yml # Compose gerado do Portainer
    └── *-compose.yml        # Outros composes gerados
```

## 🛠️ Instalação

### 1. Executar Script Principal (como root)

```bash
sudo ./setup-server.sh
```

**O script faz:**
- ✅ Verifica se é executado como root (igual EasyPanel)
- ✅ Verifica se não está dentro de container
- ✅ Verifica portas 80/443 disponíveis
- ✅ Instala Docker usando script oficial
- ✅ Sobe o projeto principal
- ✅ Inicializa sistema via PHP (Traefik + Portainer)

### 2. Verificar Status

```bash
curl http://SEU_IP/src/system-status.php
```

### 3. Inicializar Sistema (se necessário)

```bash
curl -X POST http://SEU_IP/src/system-init.php \
  -H "Content-Type: application/json" \
  -d '{"action": "initialize"}'
```

## 🎯 Funcionalidades

### 🔧 Sistema Base
- **Traefik**: Proxy reverso com SSL automático
- **Portainer**: Interface de gerenciamento Docker
- **Webhook System**: API para automação

### 📦 Templates Disponíveis
- **N8N**: Automação de workflows
- **Evolution API**: WhatsApp API
- **Traefik**: Proxy reverso
- **Portainer**: Gerenciador Docker

### 🚀 Criação de Containers via API

#### Criar N8N
```bash
curl -X POST http://SEU_IP/src/system-init.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "create_n8n",
    "container_name": "n8n1",
    "subdomain": "n8n1.seudominio.com",
    "vcpu": "1.0",
    "memory": 1024
  }'
```

#### Criar Evolution API
```bash
curl -X POST http://SEU_IP/src/system-init.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "create_evolution",
    "container_name": "evo1",
    "subdomain": "evo1.seudominio.com",
    "vcpu": "1.0",
    "memory": 512
  }'
```

#### Listar Serviços
```bash
curl -X POST http://SEU_IP/src/system-init.php \
  -H "Content-Type: application/json" \
  -d '{"action": "list_services"}'
```

#### Remover Serviço
```bash
curl -X POST http://SEU_IP/src/system-init.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "remove_service",
    "service_name": "n8n1"
  }'
```

## 🔄 Como Funciona

### 1. **Template System**
- Templates em YAML com placeholders `{{VARIABLE}}`
- Substituição automática de variáveis
- Geração de docker-compose personalizados

### 2. **Docker Management**
- Conexão direta via socket Docker (`/var/run/docker.sock`)
- Fallback para comandos `exec` se socket falhar
- Execução como root (igual EasyPanel)

### 3. **Deployment Process**
1. **Setup Script** → Instala Docker + Projeto Base
2. **PHP Initializer** → Sobe Traefik + Portainer
3. **API Calls** → Cria containers personalizados

### 4. **Network Architecture**
```
Internet → Traefik (80/443) → Containers
                ↓
            SSL Automático
```

## 📊 Endpoints Disponíveis

| Endpoint | Descrição |
|----------|-----------|
| `/src/system-status.php` | Status do sistema |
| `/src/system-init.php` | API de inicialização |
| `/src/test.php` | Interface de teste |
| `/src/index.php` | API principal |

## 🌐 Domínios Padrão

Configure seu DNS para apontar para o IP do servidor:

- `webhook.bwserver.com.br` → Webhook principal
- `traefik.bwserver.com.br` → Dashboard Traefik  
- `manager.bwserver.com.br` → Portainer
- `*.bwserver.com.br` → Containers criados

## 🔍 Comandos Úteis

```bash
# Verificar status containers
docker ps

# Logs do projeto principal
docker-compose logs -f automation-webhook

# Logs do Traefik
docker-compose -f data/traefik-compose.yml logs -f traefik

# Logs do Portainer
docker-compose -f data/portainer-compose.yml logs -f portainer

# Reiniciar projeto
docker-compose down && docker-compose up -d

# Verificar rede Traefik
docker network ls | grep traefik
```

## 🛡️ Segurança

- ✅ Execução como root (necessário para Docker)
- ✅ Verificação de ambiente (não permite container)
- ✅ Socket Docker protegido
- ✅ SSL automático via Let's Encrypt
- ✅ Redes Docker isoladas

## 🐛 Troubleshooting

### Docker não funciona
```bash
# Verificar se Docker está rodando
systemctl status docker

# Verificar permissões do socket
ls -la /var/run/docker.sock
```

### Templates não encontrados
```bash
# Verificar se pasta existe
ls -la templates/

# Verificar permissões
chmod 755 templates/
```

### API não responde
```bash
# Verificar se container está rodando
docker ps | grep automation-webhook

# Verificar logs
docker-compose logs automation-webhook
```

## 📝 Logs

- Sistema: `src/system.log`
- Docker: `docker-compose logs`
- Aplicação: Logs específicos de cada container

---

**Desenvolvido seguindo o padrão EasyPanel para máxima compatibilidade e segurança.**
