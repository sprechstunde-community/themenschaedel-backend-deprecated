CONTAINERS=docker-compose
LARAVEL=$(CONTAINERS) exec app
APP_VERSION ?= $(shell git describe --always --abbrev=0)

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

shell: ## Start a shell session inside the container
	$(LARAVEL) bash

cache: ## Clear and rebuild all caches
	$(LARAVEL) php artisan cache:clear
	$(LARAVEL) php artisan cache:clear
	$(LARAVEL) php artisan config:cache
	$(LARAVEL) php artisan view:cache
	$(LARAVEL) php artisan route:cache

logs: ## Display latest and any following log entries
	$(LARAVEL) tail -f -n $${N:=250} storage/logs/laravel.log

test: ## Run unit tests
	php artisan test

docs: ## Build docs
	@mkdir -p storage/api-docs
	@vendor/bin/openapi config/openapi.php app | sed "s/SNAPSHOT/$(APP_VERSION)/g" > storage/api-docs/openapi.yaml

image: # Build docker image
	@echo "Build image for version: $(APP_VERSION)"
	docker build -f docker/Dockerfile -t sprechstunde-community/themenschaedel-backend:nightly \
	--build-arg=APP_VERSION="$(APP_VERSION)" .
