# Changelog

All notable changes to ekiliSense will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-01-01

### 🎉 Major Release - Production-Ready SaaS Platform

This is a major overhaul transforming ekiliSense from a basic school management system into a production-ready, secure, and scalable SaaS platform.

### Added

#### Security Enhancements
- **Modern password hashing** using bcrypt/Argon2 (replacing insecure MD5)
- **CSRF protection** for all forms and state-changing operations
- **Rate limiting** system to prevent brute force attacks (5 attempts per 15 minutes)
- **SQL injection prevention** through prepared statements
- **Secure session management** with HTTPOnly, Secure, and SameSite flags
- **Login attempt logging** for security audits
- **Session tracking** system for active user monitoring
- **2FA infrastructure** ready for future implementation
- **Password reset token** system
- **Security settings** per school with configurable policies

#### Subscription Management System
- **4-tier subscription plans**: Free, Basic, Professional, Enterprise
- **Usage limits** enforcement (students, teachers, classes per plan)
- **Feature access control** based on subscription level
- **Subscription status** tracking and validation
- **Payment transaction** logging and management
- **Trial period** support infrastructure
- **Billing management** framework
- **Automatic subscription** expiry handling

#### Core Classes & Infrastructure
- **Security helper class** (`includes/Security.php`)
  - Password hashing and verification
  - CSRF token generation and validation
  - Input sanitization and validation
  - Rate limiting management
  - Secure session configuration
- **Database helper class** (`includes/Database.php`)
  - Prepared statement wrapper
  - Transaction support
  - Query builder helpers
  - Connection management
- **Subscription manager class** (`includes/Subscription.php`)
  - Plan management
  - Limit checking
  - Feature access validation
  - Usage tracking
- **Initialization file** (`includes/init.php`)
  - Centralized bootstrapping
  - Helper functions
  - Environment configuration
  - Error handling setup

#### Database Improvements
- **Migration system** structure (`database/migrations/`)
- **Subscription tables** with proper foreign keys
- **Security tables** for auditing and tracking
- **Proper indexes** for performance optimization
- **Referential integrity** with foreign key constraints
- **UTF-8mb4 character set** for international support

#### Documentation
- **Comprehensive README** with quick start guide
- **Deployment guide** for production setup
- **Environment variables** documentation and examples
- **API usage** examples for Enterprise customers
- **Security best practices** guide
- **Troubleshooting** section

#### UI/UX Improvements
- **Modern design system** (`assets/css/ekilisense-modern.css`)
- **Consistent color palette** and typography
- **Responsive components** (cards, buttons, forms, tables)
- **Toast notifications** system
- **Loading states** and spinners
- **Badge components** for status indicators
- **Alert components** with contextual styling
- **Dark mode** CSS variables support
- **Modern button styles** with hover effects
- **Card components** with shadows and transitions

### Changed

#### Security Updates
- **Replaced MD5 password hashing** with bcrypt/Argon2
- **Automatic password upgrade** for legacy MD5 hashes on login
- **Enhanced session security** with regeneration and timeout
- **Improved authentication flow** with rate limiting
- **Better error messages** that don't leak sensitive information

#### Code Organization
- **Modular class structure** for better maintainability
- **Separation of concerns** (database, security, business logic)
- **Consistent coding standards** following PSR-12
- **Better error handling** and logging
- **Environment-based configuration** using .env

#### Database Schema
- **Normalized subscription data** structure
- **Added indexes** for frequently queried columns
- **Foreign key constraints** for data integrity
- **Proper column types** and defaults
- **Audit trail fields** (created_at, updated_at)

### Fixed

#### Security Vulnerabilities
- **SQL injection** risks eliminated through prepared statements
- **XSS vulnerabilities** addressed with proper output escaping
- **CSRF attacks** prevented with token validation
- **Brute force attacks** mitigated with rate limiting
- **Session fixation** prevented with ID regeneration

#### Performance Issues
- **Added database indexes** for faster queries
- **Optimized session handling** with secure configuration
- **Reduced query count** through better data access patterns
- **Improved connection handling** with proper resource management

### Deprecated

- **MD5 password hashing** - Will be removed in v3.0 (use automatic upgrade)
- **Direct mysqli queries** without prepared statements
- **Unprotected forms** without CSRF tokens

### Security

This release addresses multiple security vulnerabilities:

1. **Critical**: Replaced insecure MD5 password hashing
2. **High**: Implemented SQL injection prevention
3. **High**: Added CSRF protection
4. **Medium**: Implemented rate limiting
5. **Medium**: Enhanced session security
6. **Low**: Added security headers

All schools are encouraged to upgrade immediately.

## [1.0.0] - 2024-10-01

### Initial Release

#### Features
- Basic school registration and management
- Teacher and student management
- Class creation and assignment
- Homework system
- Attendance tracking
- Basic dashboard with statistics
- Pesapal payment integration
- Google OAuth for teachers
- Multi-tenant architecture
- Role-based access control

---

## Migration Guide

### From v1.0 to v2.0

#### Database Migration

```bash
# 1. Backup your database
mysqldump -u user -p database > backup_v1.sql

# 2. Run new migrations
mysql -u user -p database < database/migrations/001_subscription_system.sql
mysql -u user -p database < database/migrations/002_security_improvements.sql

# 3. Test the system
# 4. Monitor error logs for issues
```

#### Code Updates

1. **Replace config.php includes**:
   ```php
   // Old
   include_once "../config.php";
   
   // New
   require_once "../includes/init.php";
   ```

2. **Update password verification**:
   ```php
   // Old (will still work but deprecated)
   if (md5($password) === $stored_hash) {
       // login
   }
   
   // New (recommended)
   if (Security::verifyPassword($password, $stored_hash)) {
       // login
   }
   ```

3. **Add CSRF protection to forms**:
   ```php
   <?php $csrfToken = Security::generateCSRFToken(); ?>
   <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
   ```

4. **Update database queries**:
   ```php
   // Old (vulnerable to SQL injection)
   $result = mysqli_query($conn, "SELECT * FROM schools WHERE email = '$email'");
   
   // New (secure)
   $result = $db->selectOne("SELECT * FROM schools WHERE School_email = ?", [$email], 's');
   ```

#### Configuration Updates

1. Create `.env` file from `.env.example`
2. Update database credentials
3. Configure security settings
4. Set up payment gateway credentials
5. Configure email/SMS services

### Breaking Changes

- Password hashes are automatically upgraded on first login
- Direct mysqli queries should be replaced with Database helper
- All forms require CSRF tokens
- Session configuration has changed (more secure defaults)

### Backward Compatibility

Version 2.0 maintains backward compatibility with:
- Existing database structure
- User authentication (with automatic upgrade)
- Multi-tenant architecture
- Role-based permissions

---

For more information, see:
- [README.md](README.md) - Installation and usage
- [docs/DEPLOYMENT_GUIDE.md](docs/DEPLOYMENT_GUIDE.md) - Production deployment
- [docs/SYSTEM_ANALYSIS_AND_ROADMAP.md](docs/SYSTEM_ANALYSIS_AND_ROADMAP.md) - Future plans
=======
## [2.0.0] - 2024-01-15 - SaaS Transformation

### 🎉 Major Release: SaaS Platform with RESTful API

This release transforms ekiliSense from a traditional PHP application into a modern SaaS platform with comprehensive API support.

### Added

#### API Infrastructure
- **RESTful API v1** (`/api/v1/`)
  - Complete API gateway with routing
  - Clean URL support with .htaccess
  - Standardized JSON responses
  - Error handling and validation
  - CORS support for cross-origin requests

#### Authentication & Security
- **JWT Authentication**
  - Token-based authentication
  - Access tokens (1 hour expiry)
  - Refresh tokens (7 days expiry)
  - Bearer token support
- **Password Security**
  - Upgraded from MD5 to bcrypt hashing
  - Automatic migration of old passwords on login
  - Password strength validation
- **Rate Limiting**
  - 100 requests per minute per user/IP
  - Automatic rate limit headers
  - 429 status code on limit exceeded
- **Input Sanitization**
  - XSS prevention
  - SQL injection protection
  - Validation middleware

#### Core API Endpoints
- **Authentication** (`/auth/*`)
  - `POST /auth/login` - User login
  - `POST /auth/register` - School registration
  - `POST /auth/refresh` - Token refresh
  - `POST /auth/logout` - User logout
  
- **School Management** (`/schools/*`)
  - `GET /schools/profile` - Get school profile
  - `PUT /schools/profile` - Update profile
  - `GET /schools/stats` - Get statistics
  
- **Student Management** (`/students/*`)
  - `GET /students` - List with pagination
  - `POST /students` - Create student
  - `GET /students/{id}` - Get details
  - `PUT /students/{id}` - Update student
  - `DELETE /students/{id}` - Delete student
  
- **Teacher Management** (`/teachers/*`)
  - `GET /teachers` - List teachers
  - `POST /teachers` - Create teacher
  - `GET /teachers/{id}` - Get details
  - `PUT /teachers/{id}` - Update teacher
  
- **Class Management** (`/classes/*`)
  - `GET /classes` - List classes
  - `POST /classes` - Create class
  - `GET /classes/{id}` - Get class with students
  
- **Assignment Management** (`/assignments/*`)
  - `GET /assignments` - List assignments
  - `POST /assignments` - Create assignment
  - `GET /assignments/{id}` - Get details

#### Parent Portal (NEW)
- **Parent Endpoints** (`/parent/*`)
  - `GET /parent/children` - List parent's children
  - `GET /parent/children/{id}/grades` - View child's grades
  - `GET /parent/children/{id}/attendance` - View attendance
  - `GET /parent/notifications` - Get notifications
- **Database Tables**
  - `parents` - Parent accounts
  - `parent_student` - Parent-student relationships
  - `notifications` - Notification system
  - `notification_preferences` - Notification settings

#### Analytics & Reporting (NEW)
- **Analytics Endpoints** (`/analytics/*`)
  - `GET /analytics/dashboard` - Real-time dashboard metrics
  - `GET /analytics/student-performance` - Student reports
  - `GET /analytics/teacher-performance` - Teacher effectiveness
  - `GET /analytics/class-comparison` - Class comparison reports
- **Features**
  - Real-time statistics (students, teachers, classes)
  - Performance trends (6-month analysis)
  - Attendance analytics
  - Grade distribution
  - Class-level comparisons

#### Webhook System (NEW)
- **Webhook Endpoints** (`/webhooks/*`)
  - `GET /webhooks` - List registered webhooks
  - `POST /webhooks` - Register new webhook
  - `DELETE /webhooks/{id}` - Delete webhook
- **Features**
  - Event-driven architecture
  - HMAC SHA-256 signature verification
  - Webhook delivery logging
  - Retry mechanism
  - Multiple event subscriptions
- **Supported Events**
  - `student.created`, `student.updated`, `student.deleted`
  - `assignment.created`, `assignment.submitted`
  - `grade.updated`, `attendance.recorded`
  - `*` (wildcard for all events)
- **Database Tables**
  - `webhooks` - Webhook configurations
  - `webhook_logs` - Delivery tracking

#### Subscription Management (NEW)
- **Subscription Endpoints** (`/subscription/*`)
  - `GET /subscription/current` - Current subscription
  - `GET /subscription/plans` - Available plans
  - `POST /subscription/subscribe` - Subscribe to plan
  - `POST /subscription/cancel` - Cancel subscription
  - `GET /subscription/billing-history` - Billing history
- **Pricing Tiers**
  - **Free**: 50 students, 5 teachers, 1GB storage
  - **Basic** (10,000 TZS/month): 200 students, parent portal
  - **Premium** (50,000 TZS/month): 1000 students, API access, webhooks
  - **Enterprise** (150,000 TZS/month): Unlimited, white-labeling
- **Features**
  - Usage tracking and limits
  - Auto-renewal support
  - Billing history
  - Invoice generation
- **Database Tables**
  - `subscription_plans` - Available plans
  - `subscriptions` - School subscriptions
  - `invoices` - Billing records

#### Developer Tools
- **Documentation**
  - Comprehensive API documentation (README.md)
  - Installation guide (INSTALLATION.md)
  - Code examples in multiple languages (EXAMPLES.md)
  - System architecture documentation
- **Testing Tools**
  - Postman collection (JSON import ready)
  - cURL examples for all endpoints
  - JavaScript/React examples
  - Python examples
  - React Native examples
- **Integration Examples**
  - Webhook verification code
  - Error handling patterns
  - Rate limit handling
  - Token refresh strategies

### Changed
- **Password Storage**: Migrated from MD5 to bcrypt (automatic on login)
- **API Response Format**: Standardized JSON structure across all endpoints
- **Authentication**: Added JWT support alongside session-based auth
- **Database**: Added indexes for better query performance

### Security Improvements
- Bcrypt password hashing (10 rounds)
- JWT token-based authentication
- HMAC webhook signatures
- Rate limiting protection
- Input sanitization and validation
- SQL injection prevention
- XSS protection
- CORS policy enforcement

### Performance Enhancements
- Database query optimization with indexes
- Pagination on all list endpoints
- Rate limiting to prevent abuse
- Efficient webhook delivery
- Caching strategy recommendations

### Database Migrations
- `001_create_parents_table.sql` - Parent portal tables
- `002_create_notifications_table.sql` - Notification system
- `003_create_webhooks_tables.sql` - Webhook infrastructure
- `004_create_subscription_tables.sql` - Subscription management

### Configuration
- **Environment Variables** (`.env`)
  - `DB_USERNAME`, `DB_PASSWORD`, `DB_NAME` - Database config
  - `JWT_SECRET` - JWT signing key
  - `API_RATE_LIMIT` - Rate limit configuration
  - `API_RATE_WINDOW` - Rate limit window
  - `APP_ENV` - Environment (development/production)

### Documentation
- API Documentation (api/v1/README.md)
- Installation Guide (api/v1/INSTALLATION.md)
- Usage Examples (api/v1/EXAMPLES.md)
- Postman Collection (api/v1/ekiliSense_API.postman_collection.json)
- System Analysis (docs/SYSTEM_ANALYSIS_AND_ROADMAP.md)
- Authentication Flow (docs/AUTHENTICATION_FLOW.md)
- Technical Architecture (docs/TECHNICAL_ARCHITECTURE.md)

## [1.0.0] - Initial Release

### Features
- Multi-tenant school management
- User management (Admins, Teachers, Students)
- Class and subject management
- Homework assignment system
- Exam scheduling
- Attendance tracking
- Basic reporting
- Pesapal payment integration
- Session-based authentication
- Teacher Google integration

---

## Migration Guide (1.0.0 → 2.0.0)

### For Existing Users

1. **Backup your database** before upgrading
2. **Run new migrations** in api/v1/migrations/
3. **Passwords are auto-upgraded** on first login (MD5 → bcrypt)
4. **Existing web interface** continues to work unchanged
5. **API is additive** - no breaking changes to current system

### For Developers

1. **Update dependencies**: Run `composer install`
2. **Configure environment**: Copy `.env.example` to `.env`
3. **Set JWT secret**: Generate with `openssl rand -base64 64`
4. **Import Postman collection**: For API testing
5. **Read API docs**: See api/v1/README.md

### Breaking Changes
None. This release is fully backward compatible with version 1.0.0.

## Roadmap

### Version 2.1.0 (Planned)
- WebSocket support for real-time notifications
- Mobile push notifications
- Multi-currency support
- GraphQL API endpoint
- Advanced RBAC (Role-Based Access Control)

### Version 2.2.0 (Planned)
- React Native mobile app
- Progressive Web App (PWA)
- Offline-first architecture
- Biometric authentication

### Version 3.0.0 (Future)
- AI-powered analytics
- Predictive performance insights
- Automated report generation
- White-labeling support
- Multi-language support

## Support

For questions or issues:
- Email: support@ekilie.com
- GitHub: https://github.com/tacheraSasi/ekiliSense/issues
- Documentation: https://docs.ekilie.com

## License

Proprietary software owned by ekilie.
