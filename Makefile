.PHONY: help up down build test lint analyse migrate seed

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

build: ## Build and start containers
	docker compose up -d --build

shell: ## Enter app container
	docker compose exec app bash

composer-install: ## Install PHP dependencies
	docker compose exec app composer install

test: ## Run all tests
	docker compose exec app composer test

test-unit: ## Run unit tests only
	docker compose exec app vendor/bin/phpunit --testsuite Unit

test-coverage: ## Run tests with coverage report
	docker compose exec app composer test:coverage

lint: ## Run code style check
	docker compose exec app composer lint

analyse: ## Run static analysis
	docker compose exec app composer analyse

migrate: ## Run pending migrations
	docker compose exec app composer migrations:run

migrate-rollback: ## Rollback last migration
	docker compose exec app composer migrations:rollback

migrate-status: ## Show migrations status
	docker compose exec app composer migrations:status

migrate-create: ## Create new migration
	docker compose exec app composer migrations:create

logs: ## Show app logs
	docker compose logs -f app

logs-nginx: ## Show nginx logs
	docker compose logs -f nginx

db-cli: ## Open PostgreSQL CLI
	docker compose exec db psql -U weale -d weale_store

restart: ## Restart all containers
	docker compose restart

clean: ## Remove containers, volumes and images
	docker compose down -v --rmi local

seed: ## Run database seeders
	docker compose exec app php database/seeders/DatabaseSeeder.php

test-feature: ## Run feature tests only
	docker compose exec app vendor/bin/phpunit --testsuite Feature

test-integration: ## Run integration tests (requires DB)
	docker compose exec app vendor/bin/phpunit --testsuite Integration

test-unit: ## Run unit tests only
	docker compose exec app vendor/bin/phpunit --testsuite Unit
