# Exemplos de Uso da API

## Pré-requisitos

1. Inicie os containers:
```bash
docker-compose up -d
```

2. Execute o seed para popular dados de teste:
```bash
docker-compose exec php-cli php scripts/seed.php
```

## Exemplos de Requisições

### 1. Transferência entre usuários comuns

```bash
curl -X POST http://localhost:8000/transfer \
  -H "Content-Type: application/json" \
  -d '{
    "value": 100.0,
    "payer": 1,
    "payee": 2
  }'
```

**Resposta de sucesso (201):**
```json
{
  "transaction_id": 1,
  "status": "completed",
  "message": "Transfer completed successfully"
}
```

### 2. Transferência de usuário comum para lojista

```bash
curl -X POST http://localhost:8000/transfer \
  -H "Content-Type: application/json" \
  -d '{
    "value": 50.0,
    "payer": 1,
    "payee": 3
  }'
```

### 3. Tentativa de transferência com saldo insuficiente

```bash
curl -X POST http://localhost:8000/transfer \
  -H "Content-Type: application/json" \
  -d '{
    "value": 2000.0,
    "payer": 1,
    "payee": 2
  }'
```

**Resposta de erro (400):**
```json
{
  "error": "Insufficient balance"
}
```

### 4. Tentativa de lojista enviar dinheiro (não permitido)

```bash
curl -X POST http://localhost:8000/transfer \
  -H "Content-Type: application/json" \
  -d '{
    "value": 100.0,
    "payer": 3,
    "payee": 1
  }'
```

**Resposta de erro (400):**
```json
{
  "error": "Merchants cannot send money"
}
```

### 5. Health Check

```bash
curl http://localhost:8000/health
```

**Resposta:**
```json
{
  "status": "ok"
}
```

## Validações Implementadas

- ✅ Lojistas não podem enviar dinheiro
- ✅ Validação de saldo suficiente
- ✅ Validação de valor maior que zero
- ✅ Validação de usuários existentes
- ✅ Validação de não transferir para si mesmo
- ✅ Autorização externa obrigatória
- ✅ Transações atômicas (rollback em caso de erro)
- ✅ Notificação assíncrona (não bloqueia a transação)

