DOCKER = docker compose exec app

.PHONY: help setup dev shell artisan tinker test lint lint-check phpstan phpstan-baseline ci-check migrate fresh seed route-list config-show build npm-build

help:
	@echo "Usage: make <target>"
	@echo ""
	@echo "Available targets:"
	@sed -n 's/^## //p; s/^[a-z_-]*:.*/    &/' $(MAKEFILE_LIST)

## Setup — Full project bootstrap (composer install, migrate, npm)
setup:
	$(DOCKER) composer run setup

## Dev — Start dev server + queue + logs + Vite (concurrent)
dev:
	$(DOCKER) composer run dev

## Shell — Open bash in the app container
shell:
	$(DOCKER) bash

## Artisan — Run an artisan command (e.g. make artisan cmd="route:list")
artisan:
	$(DOCKER) php artisan $(cmd)

## Tinker — Open Laravel tinker REPL
tinker:
	$(DOCKER) php artisan tinker

## Test — Wipe test DB, run migrations + seeders, then run all tests
test:
	$(DOCKER) sh -c 'touch database/testing.sqlite && php artisan migrate:fresh --seed --quiet && php artisan test --compact'

## Test-only — Run tests without resetting the database
test-only:
	$(DOCKER) php artisan test --compact

## Test-f — Run a single test (e.g. make test-f filter=testName)
test-f:
	$(DOCKER) php artisan test --compact --filter=$(filter)

## Lint — Auto-format PHP with Pint
lint:
	$(DOCKER) vendor/bin/pint --format agent

## Lint-check — Check PHP formatting without changes
lint-check:
	$(DOCKER) vendor/bin/pint --test --format agent

## Phpstan — Run static analysis (level 6)
phpstan:
	$(DOCKER) composer run phpstan

## Phpstan-baseline — Generate PHPStan baseline
phpstan-baseline:
	$(DOCKER) composer run phpstan:baseline

## Ci-check — Full CI pipeline (lint:check → phpstan → test)
ci-check:
	$(DOCKER) composer run ci:check

## Migrate — Run database migrations
migrate:
	$(DOCKER) php artisan migrate

## Migrate-fresh — Drop all tables and re-run migrations
migrate-fresh:
	$(DOCKER) php artisan migrate:fresh

## Seed — Run database seeders
seed:
	$(DOCKER) php artisan db:seed

## Route-list — Show all registered routes
route-list:
	$(DOCKER) php artisan route:list

## Config-show — Show a config value (e.g. make config-show key=app.name)
config-show:
	$(DOCKER) php artisan config:show $(key)

## Build — Build Vite assets (host side)
build:
	npm run build
