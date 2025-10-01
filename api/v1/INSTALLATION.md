# ekiliSense API Installation Guide

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependency management)
- mod_rewrite enabled (for Apache)

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/tacheraSasi/ekiliSense.git
cd ekiliSense
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Create a `.env` file from the example:

```bash
cp .env.example .env
```

Edit `.env` and update the configuration:

```env
# Database Configuration
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
DB_NAME=ekiliSense_db

# JWT Configuration
# Generate a secure key: openssl rand -base64 64
JWT_SECRET=your_secure_random_key_here

# API Configuration
API_RATE_LIMIT=100
API_RATE_WINDOW=60

# Environment
APP_ENV=production
```

### 4. Database Setup

#### Create Database

```sql
CREATE DATABASE ekiliSense_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Run Existing Schema

Import the main database schema (if you have the SQL file):

```bash
mysql -u your_username -p ekiliSense_db < path/to/your/schema.sql
```

#### Run API Migrations

Run the new migrations for API features:

```bash
# Navigate to migrations directory
cd api/v1/migrations

# Run each migration
mysql -u your_username -p ekiliSense_db < 001_create_parents_table.sql
mysql -u your_username -p ekiliSense_db < 002_create_notifications_table.sql
mysql -u your_username -p ekiliSense_db < 003_create_webhooks_tables.sql
mysql -u your_username -p ekiliSense_db < 004_create_subscription_tables.sql
```

Or run all at once:

```bash
cat api/v1/migrations/*.sql | mysql -u your_username -p ekiliSense_db
```

### 5. Web Server Configuration

#### Apache Configuration

Create/update `.htaccess` in the root directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect API requests to API gateway
    RewriteRule ^api/v1/(.*)$ api/v1/index.php [QSA,L]
</IfModule>
```

Ensure mod_rewrite is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx Configuration

Add to your nginx site configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/ekiliSense;
    index index.php index.html;

    # API routing
    location /api/v1/ {
        try_files $uri $uri/ /api/v1/index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # CORS headers
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
    add_header Access-Control-Allow-Headers "Content-Type, Authorization";
}
```

### 6. File Permissions

Set appropriate permissions:

```bash
# Make uploads directory writable
chmod 755 uploads/
chown www-data:www-data uploads/

# Make cache directory writable (if exists)
chmod 755 cache/
chown www-data:www-data cache/

# Secure sensitive files
chmod 600 .env
```

### 7. Generate JWT Secret (Production)

For production, generate a strong JWT secret:

```bash
# Generate random key
openssl rand -base64 64

# Add to .env file
JWT_SECRET=generated_key_here
```

### 8. Test Installation

#### Test API Endpoint

```bash
curl http://localhost/ekiliSense/api/v1/auth/login
```

Should return a JSON error (since no credentials provided):

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": "The email field is required",
    "password": "The password field is required"
  }
}
```

#### Test Authentication

Register a test school:

```bash
curl -X POST http://localhost/ekiliSense/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "school_name": "Test School",
    "email": "admin@testschool.com",
    "password": "TestPass123",
    "phone": "+255712345678"
  }'
```

Login:

```bash
curl -X POST http://localhost/ekiliSense/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@testschool.com",
    "password": "TestPass123"
  }'
```

### 9. Import Postman Collection

Import the Postman collection for easy testing:

1. Open Postman
2. Click "Import"
3. Select `api/v1/ekiliSense_API.postman_collection.json`
4. Update the `base_url` variable to your domain
5. Start testing!

## Upgrading Existing Installation

If you already have ekiliSense installed:

### 1. Backup Database

```bash
mysqldump -u your_username -p ekiliSense_db > backup_$(date +%Y%m%d).sql
```

### 2. Run New Migrations

```bash
cd api/v1/migrations
cat *.sql | mysql -u your_username -p ekiliSense_db
```

### 3. Update Password Hashes

The API automatically upgrades MD5 passwords to bcrypt on first login. No manual action needed.

### 4. Test API

Verify the API works with existing data:

```bash
# Login with existing credentials
curl -X POST http://localhost/ekiliSense/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your_existing_email@school.com",
    "password": "your_existing_password"
  }'
```

## Production Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Generate strong JWT secret
- [ ] Configure HTTPS/SSL
- [ ] Set up automated backups
- [ ] Configure firewall rules
- [ ] Set appropriate file permissions
- [ ] Enable error logging
- [ ] Set up monitoring (optional)
- [ ] Configure rate limiting (if using load balancer)
- [ ] Set up CDN for static assets (optional)
- [ ] Configure email for notifications
- [ ] Set up cron jobs for scheduled tasks

## Troubleshooting

### API Returns 404

**Problem:** All API endpoints return 404

**Solution:**
- Ensure mod_rewrite is enabled (Apache)
- Check .htaccess file exists in api/v1/
- Verify nginx configuration (if using nginx)

### Database Connection Error

**Problem:** "Connection failed" error

**Solution:**
- Verify database credentials in .env
- Ensure database exists
- Check MySQL is running: `systemctl status mysql`

### JWT Token Invalid

**Problem:** "Invalid or expired token" error

**Solution:**
- Verify JWT_SECRET is set in .env
- Check token hasn't expired (default: 1 hour)
- Use refresh token to get new access token

### Rate Limit Errors

**Problem:** 429 Too Many Requests

**Solution:**
- Wait for rate limit to reset (shown in error response)
- Increase rate limit in .env (development only)
- Implement exponential backoff in your client

### Permission Denied

**Problem:** Can't write to uploads/cache directory

**Solution:**
```bash
chmod 755 uploads/ cache/
chown www-data:www-data uploads/ cache/
```

### CORS Errors

**Problem:** CORS policy blocking requests from frontend

**Solution:**
- Verify .htaccess CORS headers (Apache)
- Check nginx CORS configuration
- Ensure preflight OPTIONS requests are handled

## Performance Optimization

### Enable PHP OpCache

Edit `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Database Indexes

Ensure indexes exist on frequently queried columns:

```sql
-- Add indexes if not exists
ALTER TABLE students ADD INDEX idx_school_uid (school_uid);
ALTER TABLE teachers ADD INDEX idx_school_uid (School_unique_id);
ALTER TABLE classes ADD INDEX idx_school_uid (school_unique_id);
```

### Enable Compression

Apache:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE text/html
</IfModule>
```

Nginx:

```nginx
gzip on;
gzip_types application/json text/html;
```

### Redis for Rate Limiting (Optional)

For high-traffic sites, upgrade rate limiting to use Redis:

1. Install Redis: `apt install redis-server`
2. Update RateLimitMiddleware.php to use Redis instead of file storage

## Monitoring and Logging

### Enable Error Logging

Create logs directory:

```bash
mkdir logs
chmod 755 logs
chown www-data:www-data logs
```

Update PHP error logging:

```php
// Add to config.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/ekiliSense/logs/php_errors.log');
```

### Monitor API Usage

Track API usage with custom logging:

```sql
CREATE TABLE api_usage_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_uid VARCHAR(255),
    endpoint VARCHAR(255),
    method VARCHAR(10),
    response_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_school_uid (school_uid),
    INDEX idx_created_at (created_at)
);
```

## Support

For installation support:
- Email: support@ekilie.com
- GitHub Issues: https://github.com/tacheraSasi/ekiliSense/issues
- Documentation: https://docs.ekilie.com

## License

ekiliSense is proprietary software owned by ekilie.
