# 🚀 ekiliSense Deployment Guide

## Production Deployment Checklist

### Pre-Deployment

- [ ] Backup existing database
- [ ] Test all migrations on staging
- [ ] Update environment variables
- [ ] Configure SSL/HTTPS
- [ ] Set up monitoring
- [ ] Configure email service
- [ ] Test payment gateway
- [ ] Review security settings

## Server Requirements

### Minimum Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 20GB minimum
- **SSL Certificate**: Required for production

### PHP Extensions Required
```bash
# Check installed extensions
php -m

# Required extensions:
- mysqli
- pdo_mysql
- mbstring
- json
- curl
- openssl
- zip
- gd (for image processing)
- fileinfo
```

## Deployment Methods

### Method 1: Traditional Hosting (cPanel/Plesk)

1. **Upload Files**
   ```bash
   # Via FTP or File Manager
   - Upload all files to public_html or www directory
   - Ensure vendor/ directory is included
   ```

2. **Configure Database**
   ```bash
   # Create database via cPanel
   # Update .env file with credentials
   DB_USERNAME=cpanel_user
   DB_PASSWORD=secure_password
   DB_NAME=cpanel_db_name
   ```

3. **Set Permissions**
   ```bash
   chmod -R 755 .
   chmod -R 777 logs/
   chmod 644 .env
   ```

4. **Configure Domain**
   - Point domain to installation directory
   - Enable SSL via Let's Encrypt
   - Force HTTPS redirect

### Method 2: VPS Deployment (Ubuntu/Debian)

#### 1. Initial Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP 8.0 and extensions
sudo apt install -y php8.0 php8.0-fpm php8.0-mysql php8.0-mbstring \
    php8.0-xml php8.0-curl php8.0-gd php8.0-zip php8.0-bcmath

# Install MySQL
sudo apt install -y mysql-server

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 2. Configure MySQL

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p

CREATE DATABASE ekiliSense_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ekilisense_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON ekiliSense_db.* TO 'ekilisense_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. Deploy Application

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/tacheraSasi/ekiliSense.git
cd ekiliSense

# Install dependencies
sudo composer install --no-dev --optimize-autoloader

# Set up environment
sudo cp .env.example .env
sudo nano .env  # Edit with your settings

# Set permissions
sudo chown -R www-data:www-data /var/www/ekiliSense
sudo chmod -R 755 /var/www/ekiliSense
sudo chmod -R 777 /var/www/ekiliSense/logs

# Run migrations
mysql -u ekilisense_user -p ekiliSense_db < database_updates.sql
mysql -u ekilisense_user -p ekiliSense_db < database/migrations/001_subscription_system.sql
mysql -u ekilisense_user -p ekiliSense_db < database/migrations/002_security_improvements.sql
```

#### 4. Configure Nginx

```bash
# Create site configuration
sudo nano /etc/nginx/sites-available/ekilisense

# Add configuration (see below)
# Enable site
sudo ln -s /etc/nginx/sites-available/ekilisense /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl restart php8.0-fpm
```

**Nginx Configuration:**

```nginx
server {
    listen 80;
    server_name sense.ekilie.com www.sense.ekilie.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name sense.ekilie.com www.sense.ekilie.com;
    root /var/www/ekiliSense;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/sense.ekilie.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sense.ekilie.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml;

    # PHP Processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\.env {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }

    # Try files or redirect to index
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Access and error logs
    access_log /var/log/nginx/ekilisense_access.log;
    error_log /var/log/nginx/ekilisense_error.log;
}
```

#### 5. Configure SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d sense.ekilie.com -d www.sense.ekilie.com

# Auto-renewal (cron job)
sudo crontab -e
# Add: 0 3 * * * /usr/bin/certbot renew --quiet
```

### Method 3: Docker Deployment

#### Docker Compose Configuration

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  web:
    image: php:8.0-apache
    container_name: ekilisense_web
    volumes:
      - ./:/var/www/html
    ports:
      - "80:80"
      - "443:443"
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_USERNAME=ekilisense
      - DB_PASSWORD=secure_password
      - DB_NAME=ekiliSense_db

  db:
    image: mysql:8.0
    container_name: ekilisense_db
    volumes:
      - db_data:/var/lib/mysql
    environment:
      - MYSQL_ROOT_PASSWORD=root_password
      - MYSQL_DATABASE=ekiliSense_db
      - MYSQL_USER=ekilisense
      - MYSQL_PASSWORD=secure_password
    ports:
      - "3306:3306"

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: ekilisense_phpmyadmin
    depends_on:
      - db
    environment:
      - PMA_HOST=db
      - PMA_USER=root
      - PMA_PASSWORD=root_password
    ports:
      - "8080:80"

volumes:
  db_data:
```

**Deploy with Docker:**

```bash
# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec db mysql -u ekilisense -p ekiliSense_db < database_updates.sql

# View logs
docker-compose logs -f web
```

## Post-Deployment Configuration

### 1. Performance Optimization

#### Enable OPcache

```bash
# Edit php.ini
sudo nano /etc/php/8.0/fpm/php.ini

# Add/update:
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.revalidate_freq=0

# Restart PHP-FPM
sudo systemctl restart php8.0-fpm
```

#### Configure MySQL

```sql
-- Add to my.cnf
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 64M
query_cache_limit = 2M
```

### 2. Set Up Monitoring

#### Install monitoring tools

```bash
# Install monitoring stack
sudo apt install -y prometheus node-exporter grafana

# Configure uptime monitoring
# Use tools like: UptimeRobot, Pingdom, or New Relic
```

### 3. Configure Backup System

```bash
# Create backup script
sudo nano /usr/local/bin/backup-ekilisense.sh
```

**Backup Script:**

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/ekilisense"
DB_NAME="ekiliSense_db"
DB_USER="ekilisense_user"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/ekiliSense --exclude='vendor' --exclude='node_modules'

# Remove backups older than 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-ekilisense.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-ekilisense.sh
```

### 4. Configure Logging

```bash
# Create log directory
sudo mkdir -p /var/www/ekiliSense/logs
sudo chown www-data:www-data /var/www/ekiliSense/logs

# Set up log rotation
sudo nano /etc/logrotate.d/ekilisense
```

**Log Rotation Config:**

```
/var/www/ekiliSense/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

## Security Hardening

### 1. Firewall Configuration

```bash
# Install UFW
sudo apt install -y ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

### 2. Fail2Ban Setup

```bash
# Install Fail2Ban
sudo apt install -y fail2ban

# Configure
sudo nano /etc/fail2ban/jail.local
```

**Fail2Ban Configuration:**

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true
```

### 3. Security Scanning

```bash
# Install security tools
sudo apt install -y lynis rkhunter

# Run security audit
sudo lynis audit system

# Check for rootkits
sudo rkhunter --check
```

## Monitoring & Maintenance

### Health Check Endpoints

Create `/health-check.php`:

```php
<?php
header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => time(),
    'checks' => []
];

// Database check
try {
    require_once 'config.php';
    $health['checks']['database'] = $conn->ping() ? 'ok' : 'error';
} catch (Exception $e) {
    $health['checks']['database'] = 'error';
    $health['status'] = 'unhealthy';
}

// Disk space check
$diskFree = disk_free_space('/');
$diskTotal = disk_total_space('/');
$diskPercent = ($diskFree / $diskTotal) * 100;
$health['checks']['disk_space'] = $diskPercent > 10 ? 'ok' : 'warning';

// Memory check
$memoryLimit = ini_get('memory_limit');
$health['checks']['memory_limit'] = $memoryLimit;

echo json_encode($health, JSON_PRETTY_PRINT);
```

### Monitoring Dashboard

Set up monitoring with:
- **Application**: New Relic, Datadog, or AppDynamics
- **Server**: Prometheus + Grafana
- **Uptime**: UptimeRobot or Pingdom
- **Logs**: ELK Stack or Papertrail

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check PHP error logs: `tail -f /var/log/nginx/ekilisense_error.log`
   - Verify file permissions
   - Check .htaccess or Nginx configuration

2. **Database Connection Failed**
   - Verify credentials in .env
   - Check MySQL is running: `sudo systemctl status mysql`
   - Test connection: `mysql -u user -p database`

3. **White Screen / No Output**
   - Enable error reporting temporarily
   - Check PHP error logs
   - Verify PHP-FPM is running

4. **Session Issues**
   - Check session directory permissions
   - Verify session configuration in php.ini
   - Clear session files

## Rollback Procedure

If deployment fails:

```bash
# 1. Restore database backup
mysql -u user -p database < backup_file.sql

# 2. Restore files
cd /var/www
sudo rm -rf ekiliSense
sudo tar -xzf files_backup.tar.gz

# 3. Restart services
sudo systemctl restart nginx php8.0-fpm mysql
```

## Support

For deployment assistance:
- Email: support@ekilie.com
- Documentation: https://docs.ekilie.com
- GitHub Issues: https://github.com/tacheraSasi/ekiliSense/issues

---

**Remember**: Always test deployments on staging before production!
