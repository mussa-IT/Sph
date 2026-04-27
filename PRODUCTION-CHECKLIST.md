# Smart Project Hub - Production Deployment Checklist

Quick checklist for deploying to production.

## Pre-Deployment

- [ ] **Environment Setup**
  ```bash
  cp .env.example .env
  # Edit .env with production values
  ```

- [ ] **Required Environment Variables**
  ```
  APP_NAME="Smart Project Hub"
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://yourdomain.com
  APP_KEY=  # Run: php artisan key:generate --force
  
  # Database
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_DATABASE=your_db
  DB_USERNAME=your_user
  DB_PASSWORD=your_pass
  
  # Mail (required for password reset, welcome emails)
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.mailgun.org
  MAIL_PORT=587
  MAIL_USERNAME=your_user
  MAIL_PASSWORD=your_pass
  MAIL_FROM_ADDRESS=noreply@yourdomain.com
  
  # Optional but recommended
  GOOGLE_CLIENT_ID=  # For social login
  GOOGLE_CLIENT_SECRET=
  OPENAI_API_KEY=  # For AI features
  ```

- [ ] **Install Dependencies**
  ```bash
  composer install --no-dev --optimize-autoloader --no-interaction
  npm ci
  npm run build
  ```

- [ ] **Prepare Assets**
  ```bash
  # Create placeholder images (or add your own)
  # public/images/og-default.jpg (1200x630)
  # public/apple-touch-icon.png (180x180)
  # public/favicon.ico
  ```

## Deployment Steps

- [ ] **Run Deployment Script**
  ```bash
  chmod +x scripts/deploy-production.sh
  ./scripts/deploy-production.sh
  ```

- [ ] **Or Run Commands Manually**
  ```bash
  php artisan key:generate --force
  php artisan migrate --force
  php artisan storage:link
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
  php artisan optimize
  ```

## Post-Deployment Verification

- [ ] **Site Accessibility**
  - Visit https://yourdomain.com
  - Check HTTPS is forced
  - Verify no mixed content warnings

- [ ] **Core Features Test**
  - [ ] Register new account
  - [ ] Login/logout
  - [ ] Create project
  - [ ] Create task
  - [ ] Use AI chat
  - [ ] Dark mode toggle
  - [ ] Command Palette (Cmd/Ctrl + K)
  - [ ] Keyboard shortcuts (?)

- [ ] **Email System**
  - [ ] Welcome email received
  - [ ] Password reset works
  - [ ] No email in spam

- [ ] **Security Checks**
  - [ ] `.env` not accessible (try: yourdomain.com/.env)
  - [ ] `APP_DEBUG=false` (no stack traces on errors)
  - [ ] File permissions correct (storage: 755, .env: 644)
  - [ ] Security headers present (check browser dev tools)

- [ ] **Performance**
  - [ ] OPcache enabled
  - [ ] Assets are cached
  - [ ] Database indexes working
  - [ ] Page load < 3 seconds

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 500 Error | Check `storage/logs/laravel.log` |
| CSS not loading | Run `npm run build` again |
| Database error | Verify DB credentials in `.env` |
| Email not sending | Check mail configuration |
| Queue not processing | Start queue worker: `php artisan queue:work` |

## Support Resources

- **Full Deployment Guide:** `docs/DEPLOYMENT.md`
- **Production Summary:** `docs/PRODUCTION-UPGRADE-SUMMARY.md`
- **Laravel Docs:** https://laravel.com/docs/deployment

---

**Estimated deployment time:** 15-30 minutes (depending on hosting)
