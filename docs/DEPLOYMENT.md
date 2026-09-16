# Deployment Checklist

Panduan deployment untuk Project Reservasi Fasilitas Kampus.

## Pre-Deployment

### 1. Code Quality Check
- [ ] Semua tests passing (`php artisan test`)
- [ ] Code formatted (`./vendor/bin/pint`)
- [ ] No debug code atau `dd()`, `dump()`, `console.log()`
- [ ] No hardcoded credentials
- [ ] Environment variables configured

### 2. Security Check
- [ ] Update `.env` dengan production values
- [ ] Generate new `APP_KEY` untuk production
- [ ] Database credentials secure
- [ ] CSRF protection enabled
- [ ] Input validation complete
- [ ] File upload validation (size, type)
- [ ] SQL injection prevention (use Eloquent/Query Builder)
- [ ] XSS prevention (use Blade `{{ }}` escaping)

### 3. Database
- [ ] Backup database
- [ ] Test migrations on staging
- [ ] Seeders ready (if needed for demo data)
- [ ] Indexes optimized

### 4. Frontend Assets
- [ ] Run `npm run build`
- [ ] Test built assets locally
- [ ] No console errors in browser

---

## Environment Setup

### Production `.env`
```env
APP_NAME="Reservasi Fasilitas"
APP_ENV=production
APP_KEY=base64:... # Generate new key!
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=production_db
DB_USERNAME=production_user
DB_PASSWORD=strong-password

SESSION_DRIVER=database
CACHE_STORE=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

---

## Deployment Steps

### Shared Hosting (cPanel)

1. **Upload Files**
   ```bash
   # Exclude these from upload:
   /node_modules
   /vendor (will reinstall)
   .env (create manually)
   /storage/logs/*
   /public/hot
   ```

2. **Install Dependencies**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env dengan production values
   php artisan key:generate
   ```

4. **Setup Database**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force  # If needed
   ```

5. **Optimize**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. **Setup Storage**
   ```bash
   php artisan storage:link
   chmod -R 775 storage bootstrap/cache
   ```

### VPS/Cloud Server

1. **Install Requirements**
   ```bash
   sudo apt update
   sudo apt install php8.5 php8.5-fpm php8.5-mysql nginx mysql-server
   ```

2. **Clone Repository**
   ```bash
   git clone https://github.com/your-team/ProjectReservasi.git
   cd ProjectReservasi
   ```

3. **Setup Application** (same as shared hosting steps 2-6)

4. **Configure Nginx**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       root /var/www/ProjectReservasi/public;

       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";

       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

5. **SSL Certificate**
   ```bash
   sudo apt install certbot python3-certbot-nginx
   sudo certbot --nginx -d yourdomain.com
   ```

---

## Post-Deployment

### 1. Verification
- [ ] Homepage loads correctly
- [ ] Login/Register works
- [ ] Database connection working
- [ ] File uploads working
- [ ] Email notifications working (if configured)
- [ ] All routes accessible with correct permissions

### 2. Monitoring
- [ ] Setup error logging
- [ ] Monitor database performance
- [ ] Check disk space (especially for uploads)
- [ ] Monitor server resources

### 3. Backup Strategy
```bash
# Database backup script
php artisan backup:run  # If using spatie/laravel-backup

# Manual backup
mysqldump -u user -p database_name > backup_$(date +%Y%m%d).sql
```

---

## Rollback Plan

If deployment fails:

1. **Restore Code**
   ```bash
   git reset --hard previous-commit
   composer install
   ```

2. **Restore Database**
   ```bash
   mysql -u user -p database_name < backup_file.sql
   ```

3. **Clear Caches**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

---

## Maintenance Mode

Enable maintenance mode during deployment:
```bash
php artisan down --secret="maintenance-secret-token"
# Access via: https://yourdomain.com/maintenance-secret-token

# After deployment
php artisan up
```

---

## Common Issues

### Permission Errors
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 500 Error
- Check `.env` configuration
- Check Laravel logs: `storage/logs/laravel.log`
- Check web server error logs

### Assets Not Loading
```bash
php artisan storage:link
npm run build
```

### Database Connection Failed
- Verify `.env` database credentials
- Check if MySQL is running
- Test connection: `php artisan tinker` then `DB::connection()->getPdo()`
