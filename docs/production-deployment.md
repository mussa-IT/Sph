# Smart Project Hub - Production Deployment Guide

This guide covers deploying Smart Project Hub to production environments with optimal performance, security, and reliability.

## Table of Contents
1. [Environment Configuration](#environment-configuration)
2. [Server Requirements](#server-requirements)
3. [Deployment Methods](#deployment-methods)
4. [Optimization Commands](#optimization-commands)
5. [Security Setup](#security-setup)
6. [Monitoring & Maintenance](#monitoring--maintenance)

## Environment Configuration

### Production Environment Variables

```bash
# Application
APP_NAME="Smart Project Hub"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_project_hub
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=public
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-s3-bucket
AWS_USE_PATH_STYLE_ENDPOINT=false

# AI Services (if using external AI)
OPENAI_API_KEY=your-openai-key
OPENAI_ORG_ID=your-openai-org

# Analytics & Monitoring
ANALYTICS_ENABLED=true
SENTRY_LARAVEL_DSN=your-sentry-dsn

# Security
BCRYPT_ROUNDS=12
SANCTUM_STATEFUL_DOMAINS=yourdomain.com

# Web3 (if using blockchain features)
WEB3_ENABLED=true
ETHEREUM_RPC_URL=https://mainnet.infura.io/v3/your-infura-key
```

## Server Requirements

### Minimum Requirements
- **PHP**: 8.2+
- **MySQL/MariaDB**: 8.0+ / 10.3+
- **Redis**: 6.0+
- **Memory**: 2GB RAM
- **Storage**: 20GB SSD
- **CPU**: 2 cores

### Recommended Requirements
- **PHP**: 8.3
- **MySQL**: 8.0+
- **Redis**: 7.0+
- **Memory**: 4GB RAM
- **Storage**: 50GB SSD
- **CPU**: 4 cores

### Required PHP Extensions
```bash
php-fpm
php-cli
php-mysql
php-redis
php-json
php-bcmath
php-curl
php-dom
php-fileinfo
php-gd
php-mbstring
php-openssl
php-pdo
php-tokenizer
php-xml
php-zip
php-intl
php-exif
```

## Deployment Methods

### 1. Shared Hosting (Hostinger, cPanel)

#### Step-by-Step Instructions:

1. **Upload Files**
   ```bash
   # Upload all files to public_html directory
   # Exclude node_modules, .git, and local storage
   ```

2. **Set Permissions**
   ```bash
   chmod 755 storage bootstrap/cache
   chmod -R 644 storage/app storage/framework
   chmod -R 644 bootstrap/cache
   ```

3. **Configure Environment**
   - Copy `.env.example` to `.env`
   - Set production values as shown above
   - Generate app key: `php artisan key:generate`

4. **Install Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```

5. **Run Deployment Commands**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan migrate --force
   php artisan storage:link
   ```

6. **Setup Cron Job**
   ```bash
   # Add to cPanel Cron Jobs
   * * * * * cd /home/user/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```

### 2. VPS Deployment (Ubuntu 22.04)

#### Server Setup Script:

```bash
#!/bin/bash
# setup-server.sh

# Update system
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Install PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.3 php8.3-fpm php8.3-mysql php8.3-redis php8.3-json php8.3-bcmath php8.3-curl php8.3-dom php8.3-fileinfo php8.3-gd php8.3-mbstring php8.3-openssl php8.3-pdo php8.3-tokenizer php8.3-xml php8.3-zip php8.3-intl php8.3-exif -y

# Install Redis
sudo apt install redis-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Configure Nginx
sudo tee /etc/nginx/sites-available/smartprojecthub << 'EOF'
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/smartprojecthub/public;
    index index.php index.html;

    client_max_body_size 100M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss;
}
EOF

# Enable site
sudo ln -s /etc/nginx/sites-available/smartprojecthub /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Setup SSL with Let's Encrypt
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com

echo "Server setup complete! Now deploy your application."
```

#### Application Deployment:

```bash
#!/bin/bash
# deploy.sh

# Set variables
APP_DIR="/var/www/smartprojecthub"
BACKUP_DIR="/var/www/backups"

# Create backup
sudo mkdir -p $BACKUP_DIR
sudo tar -czf "$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz" $APP_DIR

# Pull latest code
cd $APP_DIR
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Clear and warm caches
php artisan cache:clear
php artisan queue:restart

# Set permissions
sudo chown -R www-data:www-data $APP_DIR/storage $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

echo "Deployment complete!"
```

### 3. Laravel Forge Deployment

#### Forge Configuration:

1. **Server Setup**
   - Choose Ubuntu 22.04 with PHP 8.3
   - Enable Redis and PostgreSQL/MySQL
   - Configure automatic backups

2. **Site Configuration**
   - Add your domain
   - Set web root to `/public`
   - Enable SSL certificate
   - Configure Nginx rules

3. **Environment Variables**
   - Add all production variables
   - Set `APP_ENV=production`
   - Configure database and cache drivers

4. **Deployment Script**
   ```bash
   cd /home/forge/yourdomain.com
   
   git pull origin main
   
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan migrate --force
   php artisan storage:link
   
   php artisan cache:clear
   php artisan queue:restart
   ```

5. **Queue Worker**
   - Configure supervisor for queue workers
   - Set up auto-restart on failure

### 4. Plesk Deployment

#### Plesk Steps:

1. **Create Subscription**
   - Add domain subscription
   - Enable PHP 8.3 with required extensions
   - Setup MySQL database

2. **Upload Application**
   - Upload files to `httpdocs`
   - Set correct permissions
   - Configure `.env` file

3. **Configure Web Server**
   - Set document root to `public`
   - Enable HTTPS with Let's Encrypt
   - Add rewrite rules

4. **Setup Scheduled Tasks**
   - Add Laravel scheduler cron job
   - Configure log rotation

## Optimization Commands

### Pre-Deployment Optimization
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Build assets
npm ci && npm run build
```

### Database Optimization
```bash
# Run migrations
php artisan migrate --force

# Seed production data if needed
php artisan db:seed --class=ProductionSeeder --force

# Optimize database
php artisan db:optimize
```

### Cache Warming
```bash
# Warm application cache
php artisan cache:warm

# Warm route cache
php artisan route:list

# Precompile views
php artisan view:clear && php artisan view:cache
```

## Security Setup

### 1. File Permissions
```bash
# Secure file permissions
chmod 755 storage bootstrap/cache
chmod -R 644 storage/app storage/framework
chmod -R 644 bootstrap/cache

# Set proper ownership
chown -R www-data:www-data storage bootstrap/cache
```

### 2. Firewall Configuration
```bash
# UFW setup
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 3. SSL Configuration
```bash
# Install SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 4. Security Headers
```nginx
# Add to Nginx configuration
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
```

## Monitoring & Maintenance

### 1. Health Monitoring
```bash
# Create health check endpoint
# routes/web.php
Route::get('/health', function () {
    return [
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version'),
        'environment' => config('app.env'),
    ];
});
```

### 2. Log Management
```bash
# Setup log rotation
sudo nano /etc/logrotate.d/laravel

/var/www/smartprojecthub/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        php /var/www/smartprojecthub/artisan log:clear
    endscript
}
```

### 3. Performance Monitoring
```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y

# Monitor Redis
redis-cli info memory

# Monitor queue
php artisan queue:monitor
```

### 4. Backup Strategy
```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d-%H%M%S)
BACKUP_DIR="/var/www/backups"
APP_DIR="/var/www/smartprojecthub"

# Database backup
mysqldump -u backup_user -p smart_project_hub > "$BACKUP_DIR/db-$DATE.sql"

# Files backup
tar -czf "$BACKUP_DIR/files-$DATE.tar.gz" $APP_DIR/storage/app

# Upload to cloud storage (optional)
aws s3 cp "$BACKUP_DIR/db-$DATE.sql" s3://your-backup-bucket/
aws s3 cp "$BACKUP_DIR/files-$DATE.tar.gz" s3://your-backup-bucket/

# Clean old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

### 5. Scheduled Tasks
```bash
# Add to crontab
# Laravel Scheduler
* * * * * cd /var/www/smartprojecthub && php artisan schedule:run >> /dev/null 2>&1

# Queue Worker
* * * * * cd /var/www/smartprojecthub && php artisan queue:work --sleep=3 --tries=3 --max-time=3600 >> /dev/null 2>&1

# Backup
0 2 * * * /var/www/scripts/backup.sh >> /var/www/logs/backup.log 2>&1

# Log cleanup
0 3 * * 0 find /var/www/smartprojecthub/storage/logs -name "*.log" -mtime +7 -delete
```

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check file permissions
   - Verify `.env` configuration
   - Check Laravel logs: `storage/logs/laravel.log`

2. **Database Connection Failed**
   - Verify database credentials
   - Check if database server is running
   - Test connection manually

3. **Queue Not Processing**
   - Restart queue worker: `php artisan queue:restart`
   - Check Redis connection
   - Verify queue configuration

4. **SSL Certificate Issues**
   - Check certificate expiration: `certbot certificates`
   - Renew manually: `certbot renew`
   - Verify Nginx configuration

### Performance Optimization

1. **Enable OPcache**
   ```ini
   ; /etc/php/8.3/fpm/php.ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=4000
   opcache.revalidate_freq=60
   ```

2. **Redis Configuration**
   ```ini
   ; /etc/redis/redis.conf
   maxmemory 256mb
   maxmemory-policy allkeys-lru
   save 900 1
   save 300 10
   save 60 10000
   ```

3. **Nginx Optimization**
   ```nginx
   worker_processes auto;
   worker_connections 1024;
   
   gzip on;
   gzip_vary on;
   gzip_min_length 1024;
   gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
   ```

This deployment guide ensures Smart Project Hub runs efficiently, securely, and reliably in production environments across various hosting platforms.
