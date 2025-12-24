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

- **PHP 8.3+**
- **Laravel 11**: Framework PHP moderno e robusto
- **Eloquent ORM**: ORM elegante para banco de dados
- **Guzzle HTTP**: Cliente HTTP para serviços externos
- **PHPUnit**: Testes unitários e de integração
- **Docker**: Containerização

## 📋 Requisitos

- Docker e Docker Compose
- PHP 8.3+ (para desenvolvimento local)
- Composer (para desenvolvimento local)

## 🔧 Instalação

### Setup Automático (Recomendado)

Execute o script de setup que automatiza todo o processo:

```bash
./scripts/setup.sh
```

Ou usando Make:

```bash
make setup
```

### Setup Manual

1. Clone o repositório:

```bash
git clone <seu-repositorio>
cd teste
```

2. Inicie os containers:

```bash
docker-compose up -d
```

3. Instale as dependências e configure:

```bash
docker-compose exec php-cli composer install
docker-compose exec php-cli php artisan key:generate
docker-compose exec php-cli php artisan migrate
docker-compose exec php-cli php artisan db:seed
```

4. A aplicação estará disponível em: `http://localhost:8001`

## 📝 Endpoints

### POST /transfer

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

Execute todos os testes:

```bash
make test
```

Ou diretamente:

```bash
docker-compose exec php-cli composer test
```

### Estrutura de Testes

- **Testes Unitários** (`tests/Unit/`): Testam lógica isolada sem dependências do framework
- **Testes de Integração** (`tests/Feature/`): Testam integração com banco de dados e serviços externos

Execute apenas testes unitários:

```bash
make test-unit
```

### Cobertura de Código

Para gerar relatório de cobertura:

```bash
docker-compose exec php-cli vendor/bin/phpunit --coverage-html coverage
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

### Arquitetura e Código
- ✅ Arquitetura limpa com separação de responsabilidades
- ✅ Aplicação de SOLID principles
- ✅ Design Patterns (Repository, Service, Factory)
- ✅ Uso consistente do Repository Pattern (sem chamadas diretas ao Model)
- ✅ Métodos pequenos e focados (refatoração do TransferService)
- ✅ Código limpo sem comentários excessivos

### Qualidade e Testes
- ✅ Testes unitários verdadeiros (sem dependência do framework)
- ✅ Separação clara entre testes unitários e de integração
- ✅ Cobertura de código configurada
- ✅ Análise estática (PHPStan) e formatação (Laravel Pint)

### Infraestrutura
- ✅ Versões atualizadas: PHP 8.3 e Laravel 11
- ✅ Setup automatizado com script único
- ✅ Docker e Docker Compose configurados
- ✅ Cache Redis para consultas frequentes
- ✅ Fila de mensageria para notificações assíncronas (Redis Queue)
- ✅ CI/CD pipeline configurado (GitHub Actions)

### Segurança e Performance
- ✅ Lock pessimista para prevenir race conditions
- ✅ Cache Redis com invalidação correta
- ✅ Transações de banco de dados
- ✅ Tratamento robusto de erros
- ✅ Validações de negócio
- ✅ Integração resiliente com serviços externos
- ✅ Rate limiting implementado
- ✅ Retry policy para serviços externos (Jobs com backoff)

### Outros
- ✅ Logging estruturado
- ✅ Enums para type safety
- ✅ FormRequest para validação robusta

## 📄 Licença

Este projeto foi desenvolvido como parte de um desafio técnico.
