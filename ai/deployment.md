# Deployment Guide — Petrotechnical Platform UC2

## Environment Requirements

| Component | Version |
|---|---|
| PHP | 8.2+ (Tested on 8.5) |
| Composer | 2.x |
| PostgreSQL | 14+ (Tested on 18) |
| Nginx | 1.20+ |
| Node.js | 18+ (build-time only) |
| OS | Ubuntu 22.04 LTS (recommended) |

---

## Required PHP Extensions

```bash
php-pgsql php-mbstring php-xml php-curl php-zip php-bcmath php-gd php-fpm
```

---

## Environment Variables (`.env`)

```dotenv
APP_NAME="Petrotechnical Platform"
APP_ENV=production
APP_KEY=base64:...           # Generate with: php artisan key:generate
APP_DEBUG=false
APP_URL=https://petrotech.internal.pertamina.com

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=petrotech
DB_USERNAME=petrotech_user
DB_PASSWORD=<strong_password>

SESSION_DRIVER=database      # Use database or redis for multi-node
SESSION_LIFETIME=480         # 8 hours

CACHE_DRIVER=file            # Or redis for production
QUEUE_CONNECTION=sync        # Or database/redis for background jobs

MAIL_MAILER=smtp
MAIL_HOST=smtp.internal.pertamina.com
MAIL_PORT=587
MAIL_FROM_ADDRESS=noreply@pertamina.com
MAIL_FROM_NAME="Petrotechnical Platform"
```

---

## PostgreSQL Setup

```sql
-- Create database and user
CREATE USER petrotech_user WITH PASSWORD 'strong_password';
CREATE DATABASE petrotech OWNER petrotech_user;
GRANT ALL PRIVILEGES ON DATABASE petrotech TO petrotech_user;

-- Connect and set schema permissions
\c petrotech
GRANT ALL ON SCHEMA public TO petrotech_user;
```

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name petrotech.internal.pertamina.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name petrotech.internal.pertamina.com;

    root /var/www/petrotech/public;
    index index.php;

    ssl_certificate     /etc/ssl/certs/petrotech.crt;
    ssl_certificate_key /etc/ssl/private/petrotech.key;
    ssl_protocols       TLSv1.2 TLSv1.3;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    client_max_body_size 50M;     # For ticket attachments
}
```

---

## PHP-FPM Configuration

`/etc/php/8.2/fpm/pool.d/petrotech.conf`:

```ini
[petrotech]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 8
pm.max_requests = 500

php_admin_value[error_log] = /var/log/php/petrotech-fpm.log
php_admin_flag[log_errors] = on
php_value[memory_limit] = 256M
php_value[upload_max_filesize] = 50M
php_value[post_max_size] = 50M
php_value[max_execution_time] = 120
```

---

## Deployment Steps

```bash
# 1. Clone / pull the repository
cd /var/www
git clone https://github.com/pertamina-uc2/petrotech.git
cd petrotech

# 2. Install PHP dependencies (no dev)
composer install --no-dev --optimize-autoloader

# 3. Set up environment
cp .env.example .env
# Edit .env with production values
php artisan key:generate

# 4. Run migrations + seed (first deploy only)
php artisan migrate --force
php artisan db:seed --force

# 5. Cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Create storage symlink
php artisan storage:link

# 7. Set file permissions
chown -R www-data:www-data /var/www/petrotech
chmod -R 755 /var/www/petrotech
chmod -R 775 /var/www/petrotech/storage
chmod -R 775 /var/www/petrotech/bootstrap/cache

# 8. Restart services
systemctl restart php8.2-fpm
systemctl reload nginx
```

---

## Zero-Downtime Deployment (Updates)

```bash
# 1. Pull latest code
git pull origin main

# 2. Install/update dependencies
composer install --no-dev --optimize-autoloader

# 3. Run any new migrations
php artisan migrate --force

# 4. Clear and re-cache
php artisan optimize:clear
php artisan optimize

# 5. Restart PHP-FPM (graceful)
systemctl reload php8.2-fpm
```

---

## Docker Deployment

`docker-compose.yml`:

```yaml
version: '3.9'

services:
  app:
    build:
      context: .
      dockerfile: docker/Dockerfile
    volumes:
      - ./:/var/www/html
      - ./docker/php.ini:/usr/local/etc/php/conf.d/custom.ini
    environment:
      - APP_ENV=production
    depends_on:
      - postgres
    networks:
      - petrotech

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
      - ./docker/ssl:/etc/ssl/certs
    depends_on:
      - app
    networks:
      - petrotech

  postgres:
    image: postgres:18-alpine
    environment:
      POSTGRES_DB: petrotech
      POSTGRES_USER: petrotech_user
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    networks:
      - petrotech

volumes:
  postgres_data:

networks:
  petrotech:
    driver: bridge
```

`docker/Dockerfile`:

```dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev libpng-dev libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd mbstring

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

RUN chown -R www-data:www-data storage bootstrap/cache
```

---

## Post-Deployment Checks

```bash
# Verify application is healthy
curl -s -o /dev/null -w "%{http_code}" https://petrotech.internal/

# Check routes are cached
php artisan route:list | head -20

# Verify DB connection
php artisan tinker --execute="echo DB::connection()->getPdo() ? 'DB OK' : 'DB FAIL';"

# Tail application logs
tail -f storage/logs/laravel.log
```
