markdown
# Jeder Gewinnt! - Marketplace

## 🚀 Quick Start

### Prerequisites
- Docker Desktop
- PHP 8.3+
- Composer
- Node.js 18+

### Installation

1. Clone the repository:
```bash
git clone [your-repo-url]
cd jeder-gewinnt-marketplace
```

2. Start Docker containers:
```bash
docker-compose up -d
```

3. Install Laravel (first time only):
```bash
cd backend/api
composer create-project laravel/laravel .
```

4. Setup environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Run migrations:
```bash
php artisan migrate
```

### Access Points
- Frontend: http://localhost:8080
- PHPMyAdmin: http://localhost:8081
- API: http://localhost:8080/api

### Default Credentials
- MySQL Root: root / secret
- MySQL User: jg_user / jg_password
- PHPMyAdmin: root / secret

## Features
- ✅ 10€/hour spending limit
- ✅ Raffle system
- ✅ Multi-role (Buyer/Seller/Admin)
- ✅ Wallet system
- ✅ Live drawings

## Tech Stack
- Backend: Laravel 11, PHP 8.3
- Frontend: Vue.js 3
- Database: MySQL 8.0
- Cache: Redis
- Container: Docker