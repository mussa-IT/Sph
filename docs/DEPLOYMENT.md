# Smart Project Hub - Deployment Guide

This guide covers deploying Smart Project Hub to various hosting environments.

## Table of Contents
1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Environment Configuration](#environment-configuration)
3. [Hostinger Shared Hosting](#hostinger-shared-hosting)
4. [VPS Server (Ubuntu)](#vps-server-ubuntu)
5. [Laravel Forge](#laravel-forge)
6. [Plesk](#plesk)
7. [cPanel](#cpanel)
8. [Post-Deployment](#post-deployment)

---

## Pre-Deployment Checklist

Before deploying, ensure you have:
- [ ] `APP_ENV=production` set in `.env`
- [ ] `APP_DEBUG=false` set in `.env`
- [ ] Strong `APP_KEY` generated (`php artisan key:generate --force`)
- [ ] Database credentials configured
- [ ] Mail configuration set up
- [ ] Queue driver configured (database/redis recommended)
- [ ] SSL certificate ready
- [ ] Domain DNS configured
- [ ] Storage directory writable (755 permissions)
- [ ] `composer install --no-dev --optimize-autoloader` run
- [ ] `npm run build` completed

---

## Environment Configuration

### Production `.env` Template

```env
APP_NAME="Smart Project Hub"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_FORCE_HTTPS=true

# Admin Configuration
ADMIN_EMAILS=admin@yourdomain.com

# Database (MySQL recommended for production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_secure_password

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
CACHE_PREFIX=sph_cache

# Queue (use database for simple setups, redis for high traffic)
QUEUE_CONNECTION=database

# Mail (use a transactional email service)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@yourdomain.com
MAIL_PASSWORD=your-mailgun-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Google OAuth (optional)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

# OpenAI (for AI features)
OPENAI_API_KEY=your-openai-api-key
OPENAI_MODEL=gpt-4o-mini

# Security Headers
SECURITY_HSTS_MAX_AGE=31536000
SECURITY_REFERRER_POLICY=strict-origin-when-cross-origin
```

---

## Hostinger Shared Hosting

### Step 1: Upload Files

1. Compress your Laravel project (exclude `node_modules`, `vendor`, `.git`)
2. Upload via File Manager or FTP to `public_html/`
3. Extract the archive

### Step 2: Configure Directory Structure

```bash
# In Hostinger File Manager or SSH:
cd ~/public_html

# Move Laravel's public content to public_html root
mv public/* .
mv public/.htaccess .

# Edit index.php to update paths
# Change: require __DIR__.'/../vendor/autoload.php';
# To:     require __DIR__.'/vendor/autoload.php';
# And:   require __DIR__.'/../bootstrap/app.php';
# To:    require __DIR__.'/bootstrap/app.php';
```

### Step 3: Install Dependencies

```bash
# Via Hostinger's SSH (if available):
composer install --no-dev --optimize-autoloader --no-interaction
```

If no SSH, use Hostinger's "Composer" tool in the control panel.

### Step 4: Setup Environment

```bash
cp .env.example .env
php artisan key:generate --force
```

Edit `.env` with production values (use File Manager).

### Step 5: Database Setup

1. Create MySQL database in Hostinger control panel
2. Import database schema:
```bash
php artisan migrate --force
```

### Step 6: Storage & Cache

```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## VPS Server (Ubuntu)

### Prerequisites

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install dependencies
sudo apt install -y nginx php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-sqlite3 composer npm mysql-server redis-server

# Enable services
sudo systemctl enable nginx php8.3-fpm mysql redis-server
```

### Step 1: Create User & Directory

```bash
# Create deploy user
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG sudo deploy

# Create app directory
sudo mkdir -p /var/www/smartprojecthub
sudo chown deploy:deploy /var/www/smartprojecthub
```

### Step 2: Deploy Application

```bash
# As deploy user
cd /var/www/smartprojecthub
git clone https://github.com/yourusername/smart-project-hub.git .

# Or upload files via SCP/FTP

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

# Environment
cp .env.example .env
php artisan key:generate --force
nano .env  # Configure production values

# Database
php artisan migrate --force

# Storage
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Step 3: Configure Nginx

Create `/etc/nginx/sites-available/smartprojecthub`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/smartprojecthub/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/smartprojecthub /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 4: SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### Step 5: Supervisor (Queue Workers)

Create `/etc/supervisor/conf.d/sph-worker.conf`:

```ini
[program:sph-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/smartprojecthub/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/sph-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sph-worker:*
```

### Step 6: Cron Jobs

```bash
sudo crontab -e -u deploy
```

Add:
```
* * * * * cd /var/www/smartprojecthub && php artisan schedule:run >> /dev/null 2>&1
```

---

## Laravel Forge

### Step 1: Connect Server

1. Create server in Forge (DigitalOcean, AWS, Linode, etc.)
2. Add your SSH key
3. Wait for provisioning

### Step 2: Create Site

1. Click "New Site"
2. Enter domain: `yourdomain.com`
3. Project type: "General PHP / Laravel"
4. Web directory: `/public`

### Step 3: Install Repository

1. Select Git provider (GitHub/GitLab/Bitbucket)
2. Select repository: `yourusername/smart-project-hub`
3. Branch: `main`
4. Click "Install Repository"

### Step 4: Configure Environment

1. Go to Site → Environment
2. Paste production `.env` content
3. Click "Update Environment"

### Step 5: Run Migrations

```bash
# Via Forge's "Deployment Script" or SSH
cd /home/forge/yourdomain.com
php artisan migrate --force
```

### Step 6: Enable SSL

1. Go to Site → SSL
2. Click "Let's Encrypt"
3. Add domains and obtain certificate

### Step 7: Configure Queue

1. Go to Server → Daemons
2. Click "New Daemon"
   - Command: `php artisan queue:work --sleep=3 --tries=3`
   - User: `forge`
   - Directory: `/home/forge/yourdomain.com`

### Step 8: Scheduler

1. Go to Server → Scheduler
2. Click "New Job"
   - Command: `php artisan schedule:run`
   - User: `forge`
   - Frequency: Every Minute

---

## Plesk

### Step 1: Create Domain

1. Log in to Plesk
2. Click "Add Domain"
3. Enter domain name
4. Choose "Hosting type: Website"

### Step 2: Upload Files

1. Go to Files section
2. Navigate to `httpdocs/`
3. Upload Laravel project files
4. Or use Git deployment if available

### Step 3: Document Root

1. Go to "Hosting Settings"
2. Change "Document root" to `/httpdocs/public`
3. Enable SSL (if certificate available)

### Step 4: PHP Settings

1. Go to "PHP Settings"
2. Set PHP version: 8.3
3. Ensure extensions enabled: mbstring, xml, bcmath, curl, zip, gd, mysql

### Step 5: Composer & NPM

```bash
# Via Plesk's SSH or Terminal extension:
cd /var/www/vhosts/yourdomain.com/httpdocs
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### Step 6: Database

1. Go to "Databases" → "Add Database"
2. Create MySQL database and user
3. Run migrations:
```bash
php artisan migrate --force
```

### Step 7: Environment & Optimization

```bash
cp .env.example .env
php artisan key:generate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## cPanel

### Step 1: Upload Files

1. Log in to cPanel
2. Open "File Manager"
3. Navigate to `public_html/`
4. Upload project ZIP file
5. Extract it

### Step 2: Move Public Directory

In File Manager:
1. Go to `public_html/your-project/public/`
2. Select all files
3. Move to `public_html/`

### Step 3: Edit index.php

Edit `public_html/index.php`:
```php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
```

### Step 4: Setup Database

1. Go to "MySQL Database Wizard"
2. Create database and user
3. Note credentials

### Step 5: Configure PHP

1. Go to "Select PHP Version"
2. Choose 8.3
3. Enable extensions: mbstring, xml, bcmath, curl, zip, gd, mysql, sqlite3

### Step 6: Composer Installation

Option A - Terminal (if available):
```bash
cd ~/public_html
composer install --no-dev --optimize-autoloader
```

Option B - Manual upload:
Upload `vendor/` directory from local machine

### Step 7: Environment Setup

1. Create `.env` file via File Manager
2. Add production configuration
3. Set file permissions to 644

### Step 8: Run Setup

```bash
cd ~/public_html
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 9: Permissions

In File Manager:
- `storage/` → 755
- `bootstrap/cache/` → 755
- `.env` → 644

---

## Post-Deployment

### Essential Commands to Run

```bash
# 1. Clear and cache configuration
php artisan config:clear
php artisan config:cache

# 2. Clear and cache routes
php artisan route:clear
php artisan route:cache

# 3. Clear and cache views
php artisan view:clear
php artisan view:cache

# 4. Cache events (Laravel 10+)
php artisan event:clear
php artisan event:cache

# 5. Optimize class loading
composer dump-autoload --optimize

# 6. Storage link
php artisan storage:link

# 7. Run migrations
php artisan migrate --force

# 8. Clear application cache
php artisan cache:clear

# 9. Restart queue workers
php artisan queue:restart
```

### Security Checklist

- [ ] SSL certificate installed and forced
- [ ] `.env` file not accessible via web (test: `yourdomain.com/.env`)
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Database credentials are strong
- [ ] Admin account created and secured
- [ ] File permissions correct (storage: 755, files: 644)
- [ ] Hidden directories blocked (.git, .env, etc.)
- [ ] Security headers configured
- [ ] Rate limiting enabled

### Performance Optimization

1. **Enable OPcache** in PHP configuration
2. **Use Redis** for cache/sessions if available
3. **CDN** for static assets (CloudFlare, AWS CloudFront)
4. **Database indexes** verified
5. **Query caching** enabled where appropriate

### Monitoring Setup

- Configure error logging (Sentry, Bugsnag, or Laravel logs)
- Set up uptime monitoring (UptimeRobot, Pingdom)
- Configure log rotation
- Set up database backups (daily recommended)

---

## Troubleshooting

### 500 Errors
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs
3. Verify `.env` file exists and is readable
4. Check file permissions

### Database Connection Issues
1. Verify credentials in `.env`
2. Check database exists and user has permissions
3. Confirm MySQL/PostgreSQL is running
4. Check firewall rules

### Permission Denied
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data /var/www/smartprojecthub  # For nginx
chown -R apache:apache /var/www/smartprojecthub       # For Apache
```

### Queue Not Processing
1. Check worker is running: `php artisan queue:status`
2. Check supervisor configuration
3. Review worker logs
4. Try manual run: `php artisan queue:work --once -v`

---

## Support

For deployment issues:
- Check Laravel documentation: https://laravel.com/docs/deployment
- Review server logs
- Ensure all server requirements are met

For application issues:
- Review `storage/logs/laravel.log`
- Check browser console for JavaScript errors
- Verify environment configuration
