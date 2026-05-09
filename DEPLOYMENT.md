# StyleBlend POS - Production Deployment Guide

## Prerequisites

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Node.js & npm
- Web server (Apache/Nginx)

## Production Setup

### 1. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Update the following variables in `.env`:

```env
APP_NAME="StyleBlend"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=styleblend_pos
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install and build frontend assets
npm install
npm run build
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Database Setup

Create the MySQL database:

```sql
CREATE DATABASE styleblend_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run migrations and seed initial data:

```bash
php artisan migrate --force
php artisan db:seed --force
```

**Note**: The system will start with empty product catalog. Categories (Women, Men, Kids) and user accounts will be created, but no test products. Add your products through the admin interface.

### 5. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

### 6. Set File Permissions

```bash
# Storage and cache directories
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Make sure the web server can write to these directories
```

### 7. Web Server Configuration

#### Apache (.htaccess)

The application includes a `.htaccess` file in the `public` directory. Ensure mod_rewrite is enabled.

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/styleblend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Security Considerations

1. **Environment File**: Never commit `.env` to version control
2. **Database**: Use strong passwords and restrict database access
3. **HTTPS**: Always use SSL certificates in production
4. **File Permissions**: Ensure proper file permissions are set
5. **Updates**: Keep dependencies updated regularly

## Default Login Credentials

After seeding, you can login with any of these admin accounts:

| Name | Email | Password | Role |
|------|-------|----------|------|
| Admin | admin@styleblend.com | password | Admin |
| Shean Louise Margallo | sheanlouisemargallo@gmail.com | password | Admin |
| Jayannet | jayannet4@gmail.com | password | Admin |
| Cashier | cashier@styleblend.com | password | Cashier |

**⚠️ IMPORTANT**: Change all default passwords immediately after first login!

## Maintenance

### Backup Database
```bash
mysqldump -u username -p styleblend_pos > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Update Application
```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Monitoring

- Monitor application logs in `storage/logs/`
- Set up database monitoring
- Configure error reporting for production issues
- Monitor disk space for file uploads and logs

## Support

For technical support or issues, check the application logs and ensure all requirements are met.