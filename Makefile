.PHONY: setup dev test seed lint deploy shell-app shell-db install typecheck

setup:
	cp -n .env.example .env || true
	composer install
	cd frontend && npm install
	php artisan key:generate --ansi
	touch database/database.sqlite || true
	php artisan migrate --seed

install:
	composer install
	cd frontend && npm install

dev:
	docker compose up -d

dev-build:
	docker compose up -d --build

down:
	docker compose down

test:
	php artisan test

test-frontend:
	cd frontend && npm run test -- --run

test-all: test test-frontend

lint:
	./vendor/bin/pint --test

typecheck:
	cd frontend && npm run type-check

lint-fix:
	./vendor/bin/pint

seed:
	php artisan migrate:fresh --seed

migrate:
	php artisan migrate

migrate-fresh:
	php artisan migrate:fresh --seed

shell-app:
	docker compose exec app bash

shell-db:
	docker compose exec mysql mysql -u $${DB_USERNAME:-erp_user} -p$${DB_PASSWORD:-secret_password} $${DB_DATABASE:-erp}

logs:
	docker compose logs -f

deploy:
	git pull
	composer install --no-dev --optimize-autoloader
	cd frontend && npm ci && npm run build
	php artisan migrate --force
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	php artisan event:cache

tinker:
	php artisan tinker

horizon:
	php artisan horizon

assets:
	cd frontend && npm run build
