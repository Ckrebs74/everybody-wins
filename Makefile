makefile
.PHONY: help start stop install setup

help:
	@echo "Verfügbare Befehle:"
	@echo "  make start    - Docker Container starten"
	@echo "  make stop     - Docker Container stoppen"
	@echo "  make install  - Laravel installieren"
	@echo "  make setup    - Komplettes Setup"

start:
	docker-compose up -d
	@echo "✅ Container gestartet!"
	@echo "PHPMyAdmin: http://localhost:8081"

stop:
	docker-compose down
	@echo "⏹ Container gestoppt!"

install:
	docker-compose up -d
	@echo "Warte auf MySQL..."
	@sleep 5
	cd backend/api && composer create-project laravel/laravel . --prefer-dist
	@echo "✅ Laravel installiert!"

setup: install
	cd backend/api && cp .env.example .env
	cd backend/api && php artisan key:generate
	@echo "✅ Setup komplett!"