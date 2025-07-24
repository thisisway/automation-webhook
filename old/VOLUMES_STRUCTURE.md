# Estrutura de Volumes - Automation Webhook

## Visão Geral

O sistema de containers agora trabalha com dois caminhos distintos para gerenciamento de volumes:

### 1. Caminho Interno do Projeto (Desenvolvimento)
- **Localização**: `app/volumes/`
- **Propósito**: Desenvolvimento local e debugging
- **Acesso**: Diretamente acessível dentro do projeto
- **Uso**: Para visualizar logs, configurações e dados durante desenvolvimento
- **Permissões**: 777 (leitura/escrita completa)

### 2. Caminho Externo do Servidor (Produção)
- **Localização**: `/etc/automation-webhook/volumes/`
- **Propósito**: Montagem nos containers Docker
- **Acesso**: Caminho absoluto no servidor
- **Uso**: Volume real montado nos containers em produção
- **Permissões**: 777 com owner 1000:1000 (usuário padrão dos containers)

## Configuração de Permissões

### Automática
- Diretórios são criados automaticamente com permissões 777
- N8N containers: owner 1000:1000 (usuário node)
- Evolution API containers: owner 1000:1000 (compatibilidade)
- Comando `chown` executado automaticamente na criação

### Manual (se necessário)
```bash
# Corrigir todas as permissões
sudo chmod -R 777 /etc/automation-webhook/volumes/
sudo chown -R 1000:1000 /etc/automation-webhook/volumes/

# Corrigir permissões de um cliente específico
sudo chmod -R 777 /etc/automation-webhook/volumes/cliente_uniqueid/
sudo chown -R 1000:1000 /etc/automation-webhook/volumes/cliente_uniqueid/
```

## Estrutura de Diretórios

```
Projeto:
├── app/volumes/                           # Volumes internos (desenvolvimento)
│   └── cliente_uniqueid/
│       └── software-uniqueid/
│           ├── n8n_data/                 # Dados específicos do N8N
│           └── data/                     # Dados específicos do Evolution API
│               ├── evolution_instances/
│               └── evolution_store/

Servidor:
├── /etc/automation-webhook/volumes/       # Volumes externos (produção)
│   └── cliente_uniqueid/
│       └── software-uniqueid/
│           ├── n8n_data/                 # Dados específicos do N8N
│           └── data/                     # Dados específicos do Evolution API
│               ├── evolution_instances/
│               └── evolution_store/
```

## Como Funciona

1. **Criação de Container**:
   - Cria diretórios em ambos os caminhos (interno e externo)
   - Monta o volume usando o caminho externo no container Docker
   - Retorna informações sobre ambos os caminhos

2. **Exclusão de Container**:
   - Remove diretórios de ambos os caminhos
   - Para o container e remove seus volumes

3. **Listagem de Containers**:
   - Lista baseado no caminho externo (onde os containers realmente estão)
   - Fornece informações sobre status e domínios

## Benefícios

- **Separação de Ambientes**: Desenvolvimento e produção isolados
- **Debugging Facilitado**: Acesso fácil aos dados durante desenvolvimento
- **Segurança**: Dados de produção em local seguro no servidor
- **Flexibilidade**: Permite diferentes configurações por ambiente

## Configuração

O sistema automaticamente:
- Cria os diretórios base se não existirem
- Gerencia permissões adequadas (755)
- Mantém sincronização entre os dois caminhos quando necessário

## Exemplo de Uso nos Containers

### N8N Container
```bash
docker run -d \
  --name n8n-uniqueid \
  -v /etc/automation-webhook/volumes/cliente_uniqueid/n8n-uniqueid/n8n_data:/home/node/.n8n \
  ...
```

### Evolution API Container
```bash
docker run -d \
  --name evoapi-uniqueid \
  -v /etc/automation-webhook/volumes/cliente_uniqueid/evoapi-uniqueid/data/evolution_instances:/evolution/instances \
  -v /etc/automation-webhook/volumes/cliente_uniqueid/evoapi-uniqueid/data/evolution_store:/evolution/store \
  ...
```
