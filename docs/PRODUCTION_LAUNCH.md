# Production Launch Checklist

## 1) Environment
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set `APP_URL=https://your-domain.com`
- Set `APP_FORCE_HTTPS=true`
- Set `ADMIN_EMAILS=admin@your-domain.com`
- Set `SESSION_SECURE_COOKIE=true`
- Keep `SESSION_ENCRYPT=true`

## 2) Core Runtime
- Use MySQL/PostgreSQL in production (avoid sqlite for multi-user workloads).
- Use Redis for cache + queue + sessions where possible.
- Set queue worker process manager (Supervisor/systemd).
- Run scheduler: `php artisan schedule:work` (or cron + `schedule:run`).

## 3) Build and Cache
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4) Security
- Serve only over HTTPS.
- Verify security headers in responses.
- Rotate `APP_KEY` only with downtime + re-encryption strategy.
- Restrict admin route to trusted emails.
- Keep OAuth credentials and OpenAI keys in secure secret storage.

## 5) Observability
- Configure centralized logs.
- Add uptime checks for `/up` and `/sitemap.xml`.
- Add alerting for queue failures and HTTP 5xx spikes.

## 6) SEO
- Ensure `robots.txt` and `sitemap.xml` are publicly reachable.
- Set canonical `APP_URL` to production domain.
- Add social preview image and brand metadata before launch day.
