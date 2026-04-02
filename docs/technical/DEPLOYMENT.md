# Production Deployment Guide

This guide provides step-by-step instructions for deploying the Juara League platform to a production environment. We assume a standard Linux server (Ubuntu 22.04+), Nginx as the reverse proxy, and PostgreSQL as the database.

## 📋 Prerequisites

Before starting, ensure your server has the following installed:
- **PHP 8.3+** with extensions: `intl`, `mbstring`, `openssl`, `pdo_pgsql`, `redis`, `xml`, `zip`
- **Node.js 20+** and **NPM 10+**
- **PostgreSQL 15+**
- **Composer 2.x**
- **Nginx**
- **Supervisor** (for Laravel queues)
- **PM2** (for Nuxt background processes)

---

## 🛠 1. Backend Deployment (Laravel API)

Navigate to the directory where you want to host the project (e.g., `/var/www/juara-league`).

### 1.1 Clone and Install
```bash
git clone https://github.com/your-repo/juara-league.git .
cd juara-league-api

# Install production dependencies
composer install --no-dev --optimize-autoloader
```

### 1.2 Configuration
Copy the environment file and set the production values:
```bash
cp .env.example .env
nano .env
```

**Critical Production Settings:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.juaraleague.com
FRONTEND_URL=https://app.juaraleague.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=juara_league
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Sanctum Stateful Domains (Must match frontend domain)
SANCTUM_STATEFUL_DOMAINS=app.juaraleague.com
SESSION_DOMAIN=.juaraleague.com

# Social Login (Google)
GOOGLE_CLIENT_ID=your-google-id
GOOGLE_CLIENT_SECRET=your-google-secret
GOOGLE_REDIRECT_URL=https://api.juaraleague.com/api/v1/auth/google/callback
```

### 1.3 Secure and Initialize
```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
```

### 1.4 Production Optimizations
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.5 Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🌐 2. Frontend Deployment (Nuxt SPA)

Navigate to the frontend directory.

### 2.1 Install and Build
```bash
cd ../juara-league-web
npm install

# Build for production
# This creates the .output/ folder
npm run build
```

### 2.2 Process Management (PM2)
Nuxt 3 needs a Node.js process running in the background. We use PM2 to manage it.

Create a `ecosystem.config.js` in `juara-league-web/`:
```javascript
module.exports = {
  apps: [
    {
      name: 'juara-league-web',
      port: '3000',
      exec_mode: 'cluster',
      instances: 'max',
      script: './.output/server/index.mjs',
      env: {
        API_URL: 'https://api.juaraleague.com'
      }
    }
  ]
}
```

Start the application:
```bash
pm2 start ecosystem.config.js
pm2 save
```

---

## 🔒 3. Nginx Configuration

We use Nginx to serve the Nuxt app and proxy requests to the Laravel API.

Create a new Nginx config file (e.g., `/etc/nginx/sites-available/juara-league`):

```nginx
# Frontend (app.juaraleague.com)
server {
    listen 80;
    server_name app.juaraleague.com;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}

# Backend API (api.juaraleague.com)
server {
    listen 80;
    server_name api.juaraleague.com;
    root /var/www/juara-league/juara-league-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 3.1 Enable Config and SSL
```bash
sudo ln -s /etc/nginx/sites-available/juara-league /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Install SSL with Certbot
sudo certbot --nginx -d app.juaraleague.com -d api.juaraleague.com
```

---

## 🚀 4. Queue and Maintenance

### 4.1 Supervisor (Queue Worker)
Create a supervisor config to keep `php artisan queue:work` running:
```ini
[program:juara-league-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/juara-league/juara-league-api/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/juara-league/juara-league-api/storage/logs/worker.log
```

### 4.2 Updating the App
To deploy new changes safely:
```bash
cd /var/www/juara-league/juara-league-api
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan up

cd ../juara-league-web
npm install
npm run build
pm2 restart juara-league-web
```

---

> [!IMPORTANT]
> **Cross-Origin Security**  
> Ensure `SANCTUM_STATEFUL_DOMAINS` in Laravel `.env` exactly matches your frontend domain. Cookies and sessions will not work if there's a mismatch.
