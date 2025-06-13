#!/bin/bash

# ================================================
# DISPETCH PRODUCTION DEPLOYMENT SCRIPT
# ================================================
# Автоматизированный скрипт для деплоя в продакшн
# Использование: ./deploy.sh

set -e  # Остановка при ошибке

echo "🚀 DISPETCH Production Deployment Starting..."
echo "================================================="

# Проверка .env файла
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    echo "💡 Copy env.production.example to .env and configure it"
    exit 1
fi

# 1. Git Pull
echo "📥 Pulling latest changes from Git..."
git pull origin main

# 2. Composer Install (Production)
echo "📦 Installing Composer dependencies (production)..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. NPM Install & Build
echo "🏗️ Building frontend assets..."
npm ci --production
npm run build

# 4. Artisan Commands
echo "⚙️ Running Laravel optimizations..."

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize application
php artisan optimize

# 5. Storage Link
echo "🔗 Creating storage symlink..."
php artisan storage:link

# 6. Queue Restart (if using queues)
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# 7. Set Permissions
echo "🔒 Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 8. Health Check
echo "🏥 Running health check..."
php artisan about

echo "================================================="
echo "✅ DISPETCH Deployment completed successfully!"
echo "🌐 Application URL: $(php artisan tinker --execute=\"echo config('app.url');\")"
echo "================================================="

# Optional: Run tests to verify deployment
read -p "🧪 Run tests to verify deployment? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🧪 Running tests..."
    php artisan test
fi

echo "🎉 Deployment completed! Your application is ready for production." 