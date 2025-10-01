# 🎓 ekiliSense - Modern School Management SaaS Platform

![ekiliSense](https://img.shields.io/badge/version-2.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql)
![License](https://img.shields.io/badge/license-MIT-green)

**ekiliSense** is a comprehensive, production-ready SaaS school management platform designed for schools across Africa and beyond. Built with modern security practices, scalable architecture, and an intuitive user interface.

## ✨ Features

### 🔐 Security First
- **Modern password hashing** using bcrypt/Argon2 (replacing legacy MD5)
- **CSRF protection** on all forms
- **Rate limiting** to prevent brute force attacks
- **SQL injection prevention** with prepared statements
- **Secure session management** with HTTPOnly and Secure flags
- **Login attempt tracking** and security audit logs
- **2FA ready** infrastructure for multi-factor authentication

### 💳 Subscription Management
- **4-tier subscription system**: Free, Basic, Professional, Enterprise
- **Usage limits** per plan (students, teachers, classes)
- **Feature access control** based on subscription
- **Trial period support**
- **Payment integration** with Pesapal (African markets)
- **Billing dashboard** and transaction tracking

### 🏫 School Management
- **Multi-tenant architecture** with school isolation
- **Role-based access control** (Admin, Teacher, Class Teacher, Parent)
- **Student enrollment** and management
- **Teacher management** with bulk import
- **Class and subject** assignment
- **Attendance tracking**
- **Homework and exam** management
- **Performance analytics**

### 🎨 Modern UI/UX
- **Clean, professional design** with modern aesthetics
- **Responsive layout** for mobile and desktop
- **Dark mode support**
- **Toast notifications** for better user feedback
- **Loading states** and error handling
- **Intuitive navigation** and workflows

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer (for dependencies)
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/tacheraSasi/ekiliSense.git
   cd ekiliSense
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Create database**
   ```sql
   CREATE DATABASE ekiliSense_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. **Run migrations**
   ```bash
   # Import the base schema
   mysql -u your_user -p ekiliSense_db < database_updates.sql
   
   # Run new migrations
   mysql -u your_user -p ekiliSense_db < database/migrations/001_subscription_system.sql
   mysql -u your_user -p ekiliSense_db < database/migrations/002_security_improvements.sql
   ```

6. **Configure web server**
   - Point document root to the project directory
   - Enable mod_rewrite (Apache) or configure appropriate rules (Nginx)
   - Ensure .htaccess is being read (Apache)

7. **Set permissions**
   ```bash
   chmod -R 755 .
   chmod -R 777 logs/  # Create logs directory if it doesn't exist
   ```

8. **Access the application**
   - Visit `http://your-domain.com`
   - Register a new school account
   - Login and start managing your school!

## 📁 Project Structure

```
ekiliSense/
├── assets/                 # Static assets (CSS, JS, images)
│   ├── css/               # Stylesheets including modern design system
│   ├── js/                # JavaScript files
│   └── vendor/            # Third-party libraries
├── auth/                   # Authentication system
│   ├── server/            # Login/logout handlers
│   └── index.php          # Login page
├── console/                # Main dashboard and management
│   ├── classes/           # Class management
│   ├── teachers/          # Teacher management
│   ├── students/          # Student management
│   └── index.php          # Dashboard
├── database/               # Database related files
│   └── migrations/        # Database migrations
├── docs/                   # Documentation
├── includes/               # Core PHP classes
│   ├── Security.php       # Security helper
│   ├── Database.php       # Database helper
│   ├── Subscription.php   # Subscription manager
│   └── init.php           # Initialization
├── middlwares/            # Authentication middleware
├── onboarding/            # School registration
├── pay/                   # Payment integration
├── vendor/                # Composer dependencies
├── config.php             # Database configuration
├── .env                   # Environment variables
└── index.php              # Landing page
```

## 🔒 Security Features

### Password Security
All passwords are hashed using PHP's `password_hash()` with the PASSWORD_DEFAULT algorithm (currently bcrypt, upgradable to Argon2).

**Legacy MD5 Migration**: The system automatically upgrades legacy MD5 hashes to modern bcrypt on successful login.

### Rate Limiting
- **5 failed attempts** allowed per 15 minutes
- Configurable per-school settings
- IP-based and email-based tracking

### CSRF Protection
All forms include CSRF tokens that are validated on the server side.

```php
// Generate token in form
$csrfToken = Security::generateCSRFToken();

// Verify on submission
if (!Security::verifyCSRFToken($_POST['csrf_token'])) {
    die('Invalid security token');
}
```

### SQL Injection Prevention
All database queries use prepared statements via the Database helper class:

```php
$db = new Database($conn);
$user = $db->selectOne(
    "SELECT * FROM schools WHERE School_email = ?",
    [$email],
    's'
);
```

## 💼 Subscription Plans

| Feature | Free | Basic | Professional | Enterprise |
|---------|------|-------|--------------|------------|
| **Price/month** | $0 | $150 | $400 | $800 |
| **Students** | 50 | 200 | 1,000 | Unlimited |
| **Teachers** | 5 | 20 | 100 | Unlimited |
| **Classes** | 5 | 20 | 50 | Unlimited |
| **Dashboard** | ✅ | ✅ | ✅ | ✅ |
| **Homework System** | ❌ | ✅ | ✅ | ✅ |
| **Analytics** | Basic | Basic | Advanced | Advanced |
| **Parent Portal** | ❌ | ❌ | ✅ | ✅ |
| **API Access** | ❌ | ❌ | ❌ | ✅ |
| **Support** | Email | Email | Priority | 24/7 Dedicated |

## 🔧 Configuration

### Environment Variables (.env)
```env
# Database
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=ekiliSense_db

# Application
APP_ENVIRONMENT=production  # development, staging, production
APP_URL=https://sense.ekilie.com

# Payment (Pesapal)
PESAPAL_CONSUMER_KEY=your_key
PESAPAL_CONSUMER_SECRET=your_secret
PESAPAL_ENVIRONMENT=live  # sandbox, live

# Email (Optional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email
SMTP_PASSWORD=your_password
```

## 📚 API Usage (Enterprise Plan)

### Authentication
```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "school@example.com",
  "password": "secure_password"
}
```

### Response
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "expires_in": 3600
}
```

## 🧪 Testing

### Manual Testing
1. Register a new school
2. Login with school credentials
3. Add teachers and students
4. Create classes and assignments
5. Test subscription limits

### Database Testing
```bash
# Test migrations
mysql -u root -p ekiliSense_db < database/migrations/001_subscription_system.sql
mysql -u root -p ekiliSense_db < database/migrations/002_security_improvements.sql
```

## 🐛 Troubleshooting

### Database Connection Issues
- Verify database credentials in `.env`
- Check MySQL service is running
- Ensure database exists and is accessible

### Login Issues
- Clear browser cookies and session
- Check if rate limiting is blocking (wait 15 minutes)
- Verify password is correct

### Permission Issues
```bash
# Fix file permissions
chmod -R 755 .
chown -R www-data:www-data .  # Linux
```

## 📈 Performance Optimization

### Database Indexes
All critical tables have indexes for optimal performance:
- School lookups by unique_id
- Student/teacher queries by school_uid
- Login logs with compound indexes

### Caching (Recommended)
- Implement Redis for session storage
- Use Memcached for database query caching
- Enable OPcache for PHP

## 🔄 Migration Guide (From Legacy System)

### Password Migration
The system automatically upgrades MD5 hashes on first login:

```php
// Old (MD5)
$hash = md5($password);

// New (Automatic upgrade)
if (md5($password) === $oldHash) {
    $newHash = Security::hashPassword($password);
    // Update database with new hash
}
```

### Database Migration
1. Backup existing database
2. Run new migrations
3. Test thoroughly
4. Deploy to production

## 🤝 Contributing

We welcome contributions! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

### Code Style
- Follow PSR-12 coding standards
- Use meaningful variable names
- Comment complex logic
- Write secure code

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Support

- **Email**: support@ekilie.com
- **Website**: https://sense.ekilie.com
- **Documentation**: https://docs.ekilie.com

## 🗺️ Roadmap

### Phase 1 (Q1 2025) ✅
- [x] Modern security implementation
- [x] Subscription system
- [x] Database migrations
- [x] Modern UI design system

### Phase 2 (Q2 2025)
- [ ] Parent portal
- [ ] Mobile app (React Native)
- [ ] Advanced analytics
- [ ] Real-time notifications

### Phase 3 (Q3 2025)
- [ ] AI-powered insights
- [ ] Facial recognition attendance
- [ ] Predictive analytics
- [ ] Multi-campus support

### Phase 4 (Q4 2025)
- [ ] API marketplace
- [ ] Third-party integrations
- [ ] White-label solution
- [ ] International expansion

## 🌍 Localization

Currently supported:
- English

Coming soon:
- Swahili
- French
- Portuguese
- Arabic

## 🙏 Acknowledgments

Built with ❤️ by the ekiliSense team for schools across Africa.

Special thanks to all contributors and early adopters who helped shape this platform.

---

**ekiliSense** - Transforming Education Management 🚀
