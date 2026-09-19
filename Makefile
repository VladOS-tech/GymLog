SHELL = /bin/bash

.PHONY: configure
configure:
	cp .env.dist .env

.PHONY: up
up:
	docker compose up --detach --build --remove-orphans --timeout 0

.PHONY: down
down:
	docker compose down -v --remove-orphans

.PHONY: logs
logs:
	docker compose logs -f composer

.PHONY: bash
bash:
	docker compose exec composer bash

.PHONY: psql
psql:
	docker compose exec postgres psql -U gymlog -d gymlog

.PHONY: migrate
migrate:
	docker compose exec composer ./vendor/bin/doctrine-migrations migrations:migrate --no-interaction

.PHONY: migrate-down
migrate-down:
	docker compose exec composer ./vendor/bin/doctrine-migrations migrations:migrate first --no-interaction

# Сверка маппинга со схемой в БД: если расхождений нет, команда откажется
# создавать миграцию — это и есть «схема совпадает с маппингом».
.PHONY: migrate-diff
migrate-diff:
	docker compose exec composer ./vendor/bin/doctrine-migrations migrations:diff

.PHONY: migrate-generate
migrate-generate:
	docker compose exec composer ./vendor/bin/doctrine-migrations migrations:generate

.PHONY: migrate-status
migrate-status:
	docker compose exec composer ./vendor/bin/doctrine-migrations migrations:status


.PHONY: test
test:
	docker compose exec composer ./vendor/bin/phpunit --configuration phpunit.xml.dist
