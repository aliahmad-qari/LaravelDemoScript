# Quick Setup Instructions

## Prerequisites
- PHP 8.2+
- Composer
- SQLite or MySQL database
- Node.js (optional, for frontend assets)

## Quick Start

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Setup
```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Database
Edit `.env` and set your database connection:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Or for MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Configure Mail (Important for Security Alerts)
Edit `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

For testing, use [Mailtrap](https://mailtrap.io/) or [MailHog](https://github.com/mailhog/MailHog).

### 6. Start Queue Worker (Required for Emails)
```bash
php artisan queue:work
```

Keep this running in a separate terminal.

### 7. Start Development Server
```bash
php artisan serve
```

Visit: http://localhost:8000

### 8. Create Admin User
Register a new account at: http://localhost:8000/register

### 9. Access Admin Panel
After logging in, visit:
- http://localhost:8000/admin/security/dashboard
- http://localhost:8000/admin/logs
- http://localhost:8000/admin/products

### 10. Seed Disposable Email Domains (Optional)
Visit: http://localhost:8000/admin/security/disposable-emails
Click "Seed Common Domains"

### 11. Set Up Abandoned Cart Reminders (Optional)
Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('cart:send-reminders')->hourly();
}
```

Then run:
```bash
php artisan schedule:work
```

## Testing the Features

### Test Security Features
1. **Country Restrictions**: Visit `/admin/security/country-restrictions`
2. **Disposable Emails**: Try registering with `test@tempmail.com` after adding domain
3. **Brute Force**: Try 5 wrong passwords to trigger account lock
4. **IP Blocking**: Make 5 failed attempts to trigger IP block
5. **Security Alerts**: Check `/admin/security/alerts` after login

### Test Marketplace Features
1. **Products**: Create products at `/admin/products`
2. **Members-Only**: Toggle "Members Only" and test as guest
3. **Shopping Cart**: Add items at `/products` and view at `/cart`
4. **File Size**: Add file size when creating products

## Common Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Send abandoned cart reminders manually
php artisan cart:send-reminders

# Check queue jobs
php artisan queue:work --once

# View routes
php artisan route:list
```

## Troubleshooting

### "Class not found" errors
```bash
composer dump-autoload
```

### Database errors
```bash
php artisan migrate:fresh
```

### Queue not processing
Make sure `php artisan queue:work` is running.

### Emails not sending
1. Check `.env` mail configuration
2. Verify queue worker is running
3. Check `failed_jobs` table

## Production Deployment

### 1. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### 2. Set Up Supervisor (Queue Worker)
Create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

### 3. Set Up Cron (Scheduler)
Add to crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Environment Variables
Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`

## Support

For issues or questions, refer to:
- `IMPLEMENTATION_GUIDE.md` - Detailed feature documentation
- `README.md` - Project overview
- Laravel Documentation: https://laravel.com/docs

## Security Checklist

- [ ] Change `APP_KEY` in production
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configure proper mail service
- [ ] Set up SSL/HTTPS
- [ ] Configure firewall rules
- [ ] Set up regular backups
- [ ] Monitor security alerts
- [ ] Review blocked IPs regularly
- [ ] Update disposable email list
- [ ] Test all security features

## Next Steps

1. Create test user accounts
2. Configure country restrictions
3. Add disposable email domains
4. Create sample products
5. Test all security features
6. Set up monitoring
7. Configure backup strategy

Your Laravel Security & Marketplace System is now ready! 🚀
