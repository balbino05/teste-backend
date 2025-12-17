.PHONY: help up down build install seed test logs clean

help: ## Mostra esta mensagem de ajuda
	@echo "Comandos disponíveis:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

up: ## Inicia os containers Docker
	docker-compose up -d

down: ## Para os containers Docker
	docker-compose down

build: ## Constrói as imagens Docker
	docker-compose build

install: ## Instala as dependências do Composer
	docker-compose exec php-cli composer install

seed: ## Popula o banco de dados com dados de teste
	docker-compose exec php-cli php scripts/seed.php

test: ## Executa os testes
	docker-compose exec php-cli composer test

test-unit: ## Executa apenas testes unitários
	docker-compose exec php-cli vendor/bin/phpunit tests/Unit

logs: ## Mostra os logs da aplicação
	docker-compose logs -f php-cli

logs-app: ## Mostra os logs do arquivo app.log
	tail -f logs/app.log

phpstan: ## Executa análise estática com PHPStan
	docker-compose exec php-cli composer phpstan

cs-check: ## Verifica o código com PHP CodeSniffer
	docker-compose exec php-cli composer cs-check

cs-fix: ## Corrige automaticamente problemas de código
	docker-compose exec php-cli composer cs-fix

clean: ## Remove containers e volumes
	docker-compose down -v
	rm -rf vendor/
	rm -rf .phpunit.cache/

restart: down up install seed ## Reinicia tudo (para, inicia, instala e popula banco)

