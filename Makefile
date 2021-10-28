CONTAINERS=docker-compose
LARAVEL=$(CONTAINERS) exec app

help: ## Print this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: ## Rebuild development environment
	$(CONTAINERS) up -d --build

start: ## Start development environment
	$(CONTAINERS) up -d

stop: ## Stop development environment
	$(CONTAINERS) down

purge: ## Purge development environment and all data
	$(CONTAINERS) down -v

shell: ## Start a shell session inside the shopware container
	$(LARAVEL) bash

cache: ## Clear shopware cache
	$(LARAVEL) php artisan cache:clear

logs: ## Display latest and any following log entries
	$(LARAVEL) tail -f -n $${N:=250} storage/logs/laravel.log

test: ## Run unit tests
	php artisan test

docs: ## Build docs
	@mkdir -p public/docs
	@vendor/bin/openapi config/openapi.php app | tee public/docs/openapi.yml
