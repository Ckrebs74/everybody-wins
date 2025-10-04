batch
@echo off
echo ================================================
echo Starting Jeder Gewinnt! Development Environment
echo ================================================

echo Starting Docker containers...
docker-compose up -d

echo Waiting for MySQL to be ready...
:wait_mysql
docker exec jg_mysql mysql -uroot -psecret -e "SELECT 1" >nul 2>&1
if %errorlevel% neq 0 (
    timeout /t 1 /nobreak >nul
    goto wait_mysql
)
echo MySQL is ready!

if not exist "backend\api\artisan" (
    echo Installing Laravel...
    cd backend\api
    composer create-project laravel/laravel . --prefer-dist
    copy .env.example .env
    php artisan key:generate
    cd ..\..
)

echo.
echo ================================================
echo Everything is running!
echo ================================================
echo.
echo Access points:
echo   - Application:  http://localhost:8080
echo   - PHPMyAdmin:   http://localhost:8081
echo   - MySQL:        localhost:3306
echo   - Redis:        localhost:6379
echo.
echo MySQL Credentials:
echo   - Root:         root / secret
echo   - User:         jg_user / jg_password
echo.
echo Next steps:
echo   1. cd backend\api
echo   2. php artisan migrate
echo.
pause