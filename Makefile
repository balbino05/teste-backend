.PHONY: help up down build install seed test logs clean setup

help: ## Mostra esta mensagem de ajuda
	@echo "Comandos disponíveis:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

setup: ## Setup completo do projeto (build, install, migrate, seed)
	@./scripts/setup.sh

up: ## Inicia os containers Docker
	docker-compose up -d

down: ## Para os containers Docker
	docker-compose down

build: ## Constrói as imagens Docker
	docker-compose build

install: ## Instala as dependências do Composer
	docker-compose exec php-cli composer install

seed: ## Popula o banco de dados com dados de teste
	docker-compose exec php-cli php artisan db:seed

test: ## Executa os testes
	docker-compose exec php-cli composer test

test-unit: ## Executa apenas testes unitários
	docker-compose exec php-cli vendor/bin/phpunit tests/Unit

logs: ## Mostra os logs da aplicação
	docker-compose logs -f php-cli

logs-app: ## Mostra os logs do Laravel
	docker-compose exec php-cli tail -f storage/logs/laravel.log

migrate: ## Executa as migrations
	docker-compose exec php-cli php artisan migrate

migrate-fresh: ## Recria o banco de dados e executa migrations
	docker-compose exec php-cli php artisan migrate:fresh --seed

pint: ## Formata o código com Laravel Pint
	docker-compose exec php-cli composer pint

phpstan: ## Executa análise estática com PHPStan
	docker-compose exec php-cli composer phpstan

analyse: ## Executa todas as análises de código
	docker-compose exec php-cli composer analyse

queue-work: ## Inicia o worker de filas manualmente
	docker-compose exec queue-worker php artisan queue:work redis --verbose

queue-restart: ## Reinicia o worker de filas
	docker-compose restart queue-worker

clean: ## Remove containers e volumes
	docker-compose down -v
	rm -rf vendor/
	rm -rf .phpunit.cache/

restart: down up install migrate seed ## Reinicia tudo (para, inicia, instala, migra e popula banco)

