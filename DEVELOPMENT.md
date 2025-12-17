# Guia de Desenvolvimento

## Estrutura do Projeto

```
teste/
├── docker/              # Configurações Docker
│   ├── mysql/          # Scripts SQL
│   └── nginx/          # Configuração Nginx
├── public/             # Ponto de entrada da aplicação
│   └── index.php
├── scripts/            # Scripts auxiliares
│   └── seed.php        # Popula banco com dados de teste
├── src/                # Código fonte
│   ├── Controller/     # Controllers REST
│   ├── Domain/         # Entidades de domínio
│   ├── Infrastructure/ # Infraestrutura (DB, etc)
│   ├── Repository/     # Repositórios (acesso a dados)
│   └── Service/        # Serviços de negócio
│       └── External/   # Integrações externas
├── tests/              # Testes
│   └── Unit/           # Testes unitários
├── logs/               # Logs da aplicação
├── composer.json       # Dependências PHP
├── docker-compose.yml  # Orquestração de containers
├── Dockerfile          # Imagem da aplicação
└── README.md           # Documentação principal
```

## Princípios Aplicados

### SOLID

1. **Single Responsibility**: Cada classe tem uma única responsabilidade
   - `User`: Representa um usuário
   - `TransferService`: Orquestra transferências
   - `UserRepository`: Acesso a dados de usuários

2. **Open/Closed**: Extensível via interfaces
   - `UserRepositoryInterface` permite diferentes implementações
   - `AuthorizationServiceInterface` permite mock em testes

3. **Liskov Substitution**: Interfaces bem definidas
   - Qualquer implementação de `UserRepositoryInterface` funciona

4. **Interface Segregation**: Interfaces específicas
   - `AuthorizationServiceInterface` e `NotificationServiceInterface` separadas

5. **Dependency Inversion**: Dependências via interfaces
   - `TransferService` depende de interfaces, não implementações

### Design Patterns

- **Repository Pattern**: Abstração de acesso a dados
- **Service Pattern**: Encapsulamento de lógica de negócio
- **Factory Pattern**: `ConnectionFactory` para criar conexões
- **Strategy Pattern**: Diferentes tipos de usuário (Common/Merchant)

## Executando o Projeto

### 1. Iniciar containers

```bash
docker-compose up -d
```

### 2. Instalar dependências

```bash
docker-compose exec php-cli composer install
```

### 3. Popular banco de dados

```bash
docker-compose exec php-cli php scripts/seed.php
```

### 4. Acessar aplicação

- API: http://localhost:8000
- MySQL: localhost:3306

## Testes

### Executar todos os testes

```bash
docker-compose exec php-cli composer test
```

### Executar testes específicos

```bash
docker-compose exec php-cli vendor/bin/phpunit tests/Unit/TransferServiceTest.php
```

## Análise de Código

### PHPStan (análise estática)

```bash
docker-compose exec php-cli composer phpstan
```

### Code Sniffer (PSR-12)

```bash
docker-compose exec php-cli composer cs-check
```

### Corrigir automaticamente

```bash
docker-compose exec php-cli composer cs-fix
```

## Logs

Os logs são salvos em `logs/app.log`. Para visualizar em tempo real:

```bash
tail -f logs/app.log
```

## Melhorias Implementadas

1. ✅ Arquitetura limpa com separação de camadas
2. ✅ Aplicação de SOLID principles
3. ✅ Design Patterns apropriados
4. ✅ Tratamento robusto de erros
5. ✅ Transações de banco de dados
6. ✅ Logging estruturado
7. ✅ Testes unitários
8. ✅ Docker e Docker Compose
9. ✅ Validações de negócio
10. ✅ Integração resiliente com serviços externos

## Próximos Passos Sugeridos

1. Adicionar testes de integração end-to-end
2. Implementar cache (Redis) para consultas frequentes
3. Adicionar fila de mensageria para notificações
4. Implementar CQRS para separar leitura e escrita
5. Adicionar observabilidade (métricas, traces)
6. Implementar rate limiting
7. Adicionar autenticação JWT
8. Criar CI/CD pipeline
9. Adicionar documentação OpenAPI/Swagger
10. Implementar retry policy para serviços externos

