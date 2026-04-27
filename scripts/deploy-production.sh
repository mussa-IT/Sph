#!/bin/bash

# Smart Project Hub - Production Deployment Script
# Usage: ./deploy-production.sh

set -e  # Exit on error

echo "🚀 Starting Smart Project Hub Production Deployment..."
echo "======================================================"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ .env file not found. Please create it first.${NC}"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -v | grep -oP 'PHP \K[0-9]+\.[0-9]+' | head -1)
REQUIRED_VERSION="8.2"

if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PHP_VERSION" | sort -V | head -n1)" != "$REQUIRED_VERSION" ]; then
    echo -e "${RED}❌ PHP version $PHP_VERSION is too old. Requires $REQUIRED_VERSION or higher.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ PHP version: $PHP_VERSION${NC}"

# Step 1: Maintenance Mode
echo -e "${YELLOW}🔧 Enabling maintenance mode...${NC}"
php artisan down --message="We're performing scheduled maintenance. We'll be back shortly!" --retry=60

# Step 2: Update Code (if git repo)
if [ -d .git ]; then
    echo -e "${YELLOW}📥 Pulling latest changes...${NC}"
    git pull origin main
fi

# Step 3: Install Dependencies
echo -e "${YELLOW}📦 Installing PHP dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${YELLOW}📦 Installing Node dependencies...${NC}"
npm ci

# Step 4: Build Assets
echo -e "${YELLOW}🔨 Building production assets...${NC}"
npm run build

# Step 5: Clear Caches
echo -e "${YELLOW}🧹 Clearing old caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
clear
php artisan cache:clear
php artisan event:clear

# Step 6: Database
echo -e "${YELLOW}🗄️ Running database migrations...${NC}"
php artisan migrate --force

# Step 7: Storage Link
echo -e "${YELLOW}🔗 Checking storage link...${NC}"
php artisan storage:link || true

# Step 8: Cache Configuration
echo -e "${YELLOW}⚡ Caching configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Step 9: Optimize
echo -e "${YELLOW}⚡ Optimizing...${NC}"
php artisan optimize

# Step 10: Permissions
echo -e "${YELLOW}🔒 Setting permissions...${NC}"
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Step 11: Restart Queue Workers
echo -e "${YELLOW}🔄 Restarting queue workers...${NC}"
php artisan queue:restart || true

# Step 12: Health Check
echo -e "${YELLOW}🏥 Running health checks...${NC}"
php artisan about

# Step 13: Disable Maintenance Mode
echo -e "${YELLOW}✅ Disabling maintenance mode...${NC}"
php artisan up

echo ""
echo -e "${GREEN}======================================================${NC}"
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${GREEN}======================================================${NC}"
echo ""
echo "Post-deployment checklist:"
echo "  1. Verify site is accessible"
echo "  2. Check error logs: tail -f storage/logs/laravel.log"
echo "  3. Test key features (login, projects, AI chat)"
echo "  4. Verify SSL certificate"
echo ""
