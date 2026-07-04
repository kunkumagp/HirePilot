backend-install:
	cd backend && composer install

backend-test:
	cd backend && php artisan test

frontend-install:
	cd frontend && npm install

frontend-build:
	cd frontend && npm run build

up-database:
	docker compose up -d mysql redis
