#!/bin/bash

set -e

echo "🚀 Iniciando setup do projeto..."

if ! command -v docker &> /dev/null; then
    echo "❌ Docker não está instalado. Por favor, instale o Docker primeiro."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose não está instalado. Por favor, instale o Docker Compose primeiro."
    exit 1
fi

echo "📦 Construindo e iniciando containers..."
docker-compose up -d --build

echo "⏳ Aguardando serviços ficarem prontos..."
sleep 10

echo "📥 Instalando dependências do Composer..."
docker-compose exec -T php-cli composer install --no-interaction

if [ ! -f .env ]; then
    echo "📝 Criando arquivo .env..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "APP_NAME=Teste
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=teste_db
DB_USERNAME=teste_user
DB_PASSWORD=teste_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PORT=6379" > .env
    fi
fi

echo "🔑 Gerando chave da aplicação..."
docker-compose exec -T php-cli php artisan key:generate --no-interaction || true

echo "🗄️  Executando migrations..."
docker-compose exec -T php-cli php artisan migrate --force

echo "🌱 Populando banco de dados com dados de teste..."
docker-compose exec -T php-cli php artisan db:seed --force

echo "✅ Setup concluído com sucesso!"
echo ""
echo "📋 Informações importantes:"
echo "   - Aplicação: http://localhost:8001"
echo "   - MySQL: localhost:3307"
echo "   - Redis: localhost:6380"
echo ""
echo "🔧 Comandos úteis:"
echo "   - Ver logs: make logs"
echo "   - Executar testes: make test"
echo "   - Parar containers: make down"
echo ""

