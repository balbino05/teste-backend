# Teste Back-end

Plataforma de pagamentos simplificada que permite depositar e realizar transferências de dinheiro entre usuários comuns e lojistas.

## 🏗️ Arquitetura

O projeto foi desenvolvido seguindo os princípios SOLID e Design Patterns, com uma arquitetura limpa e desacoplada:

- **Domain Layer**: Entidades de negócio (User, Transaction)
- **Repository Layer**: Abstração de acesso a dados
- **Service Layer**: Lógica de negócio
- **Controller Layer**: Endpoints REST
- **Infrastructure Layer**: Configurações e integrações externas

### Design Patterns Aplicados

- **Repository Pattern**: Abstração de acesso a dados
- **Service Pattern**: Encapsulamento da lógica de negócio
- **Factory Pattern**: Criação de conexões de banco de dados
- **Dependency Injection**: Injeção de dependências via construtor

## 🚀 Tecnologias

- **PHP 8.1+**
- **Laravel 10**: Framework PHP moderno e robusto
- **Eloquent ORM**: ORM elegante para banco de dados
- **Guzzle HTTP**: Cliente HTTP para serviços externos
- **PHPUnit**: Testes unitários e de integração
- **Docker**: Containerização

## 📋 Requisitos

- Docker e Docker Compose
- PHP 8.1+ (para desenvolvimento local)
- Composer (para desenvolvimento local)

## 🔧 Instalação

1. Clone o repositório:
```bash
git clone <seu-repositorio>
cd teste
```

2. Copie o arquivo de ambiente:
```bash
cp .env.example .env
```

3. Inicie os containers:
```bash
docker-compose up -d
```

4. Instale as dependências e configure:
```bash
docker-compose exec php-cli composer install
docker-compose exec php-cli php artisan key:generate
docker-compose exec php-cli php artisan migrate
docker-compose exec php-cli php artisan db:seed
```

5. A aplicação estará disponível em: `http://localhost:8000`

## 📝 Endpoints

### POST /api/transfer

Realiza uma transferência entre dois usuários.

**Request:**
```json
{
  "value": 100.0,
  "payer": 4,
  "payee": 15
}
```

**Response (Sucesso - 201):**
```json
{
  "transaction_id": 1,
  "status": "completed",
  "message": "Transfer completed successfully"
}
```

**Response (Erro - 400):**
```json
{
  "error": "Insufficient balance"
}
```

### GET /health

Verifica o status da aplicação.

**Response:**
```json
{
  "status": "ok"
}
```

## 🧪 Testes

Execute os testes com:

```bash
docker-compose exec php-cli composer test
```

Ou diretamente:

```bash
docker-compose exec php-cli vendor/bin/phpunit
```

## 📊 Estrutura do Banco de Dados

### Tabela `users`
- `id`: ID único do usuário
- `name`: Nome completo
- `cpf`: CPF (único)
- `email`: E-mail (único)
- `password`: Senha (hash)
- `user_type`: Tipo de usuário (`common` ou `merchant`)
- `balance`: Saldo da carteira
- `created_at`: Data de criação
- `updated_at`: Data de atualização

### Tabela `transactions`
- `id`: ID único da transação
- `payer_id`: ID do pagador
- `payee_id`: ID do recebedor
- `value`: Valor da transferência
- `status`: Status (`pending`, `completed`, `failed`, `reversed`)
- `authorization_code`: Código de autorização
- `error_message`: Mensagem de erro (se houver)
- `created_at`: Data de criação
- `updated_at`: Data de atualização

## 🔒 Regras de Negócio

1. **Usuários comuns** podem enviar dinheiro para outros usuários e lojistas
2. **Lojistas** só podem receber dinheiro, não podem enviar
3. Validação de saldo antes da transferência
4. Consulta ao serviço autorizador externo antes de finalizar
5. Transferências são transacionais (rollback em caso de erro)
6. Notificação enviada após recebimento (não bloqueia se falhar)

## 🔌 Serviços Externos

### Serviço de Autorização
- **URL**: `https://util.devi.tools/api/v2/authorize`
- **Método**: GET
- **Retorno**: `{ "message": "Autorizado" }` quando autoriza

### Serviço de Notificação
- **URL**: `https://util.devi.tools/api/v1/notify`
- **Método**: POST
- **Body**: `{ "user_id": 1, "message": "..." }`

## 🎯 Melhorias Implementadas

- ✅ Arquitetura limpa com separação de responsabilidades
- ✅ Aplicação de SOLID principles
- ✅ Design Patterns (Repository, Service, Factory)
- ✅ Tratamento robusto de erros
- ✅ Transações de banco de dados
- ✅ Logging estruturado
- ✅ Testes unitários
- ✅ Docker e Docker Compose
- ✅ Validações de negócio
- ✅ Integração resiliente com serviços externos

## 📈 Melhorias Futuras

- [ ] Implementar cache (Redis) para consultas frequentes
- [ ] Adicionar fila de mensageria para notificações assíncronas
- [ ] Implementar CQRS para separar leitura e escrita
- [ ] Adicionar observabilidade (Prometheus, Grafana)
- [ ] Implementar rate limiting
- [ ] Adicionar autenticação e autorização (JWT)
- [ ] Implementar testes de integração end-to-end
- [ ] Adicionar CI/CD pipeline
- [ ] Implementar retry policy para serviços externos
- [ ] Adicionar documentação OpenAPI/Swagger

## 📄 Licença

Este projeto foi desenvolvido como parte de um desafio técnico.

