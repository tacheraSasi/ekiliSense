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
