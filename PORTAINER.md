# Portainer - Gerenciador Docker

O Portainer é uma interface web para gerenciar containers Docker de forma visual e intuitiva.

## 🌐 Acesso

- **Local**: http://localhost:9000
- **Produção**: https://manager.bwserver.com.br

## 🔧 Configuração Inicial

1. **Primeira execução**: Acesse o Portainer e crie uma senha de administrador
2. **Conectar ao Docker**: Selecione "Docker" e use o socket Unix padrão
3. **Ambiente local**: O Portainer já está configurado para gerenciar o Docker local

## ✨ Funcionalidades

- ✅ **Gerenciar Containers**: Iniciar, parar, reiniciar containers
- ✅ **Visualizar Logs**: Ver logs em tempo real
- ✅ **Gerenciar Images**: Pull, build e gerenciar imagens
- ✅ **Redes Docker**: Criar e gerenciar redes
- ✅ **Volumes**: Gerenciar volumes persistentes
- ✅ **Compose**: Deploy de aplicações via Docker Compose
- ✅ **Monitoramento**: Estatísticas de uso de recursos

## 🔒 Segurança

- **HTTPS**: Configurado automaticamente via Traefik + Let's Encrypt
- **Autenticação**: Sistema de usuários do Portainer
- **Acesso restrito**: Apenas administradores podem gerenciar

## 📋 Comandos Úteis

```bash
# Ver logs do Portainer
docker-compose -f docker-compose-portainer.yml logs -f portainer

# Reiniciar Portainer
docker-compose -f docker-compose-portainer.yml restart

# Parar Portainer
docker-compose -f docker-compose-portainer.yml down

# Subir Portainer
docker-compose -f docker-compose-portainer.yml up -d
```

## 🚨 Importante

- Configure uma senha forte na primeira execução
- O Portainer tem acesso total ao Docker, use com cuidado
- Backup dos dados: volume `portainer_data` contém todas as configurações
