# Contributing to ekiliSense

Thank you for your interest in contributing to ekiliSense! This document provides guidelines and instructions for contributing.

## 🤝 How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:
- **Clear title** describing the problem
- **Steps to reproduce** the issue
- **Expected behavior** vs actual behavior
- **Environment details** (PHP version, MySQL version, browser)
- **Screenshots** if applicable

### Suggesting Features

Feature requests are welcome! Please:
- Check if the feature already exists or is planned
- Describe the feature and its use case
- Explain why it would be beneficial
- Provide examples if possible

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes**
4. **Test thoroughly**
5. **Commit your changes** (`git commit -m 'Add amazing feature'`)
6. **Push to your branch** (`git push origin feature/amazing-feature`)
7. **Open a Pull Request**

## 📋 Development Guidelines

### Code Style

We follow **PSR-12** coding standards for PHP:

```php
<?php
// Good
class UserController
{
    public function index()
    {
        $users = $this->db->selectAll("SELECT * FROM users");
        return $users;
    }
}

// Bad
class usercontroller {
  function index() {
    $users=$this->db->selectAll("SELECT * FROM users");
    return $users;
  }
}
```

### Security Requirements

**All contributions must follow these security practices:**

1. **Use prepared statements** for all database queries
   ```php
   // Good
   $user = $db->selectOne("SELECT * FROM users WHERE email = ?", [$email], 's');
   
   // Bad - NEVER DO THIS
   $user = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
   ```

2. **Validate and sanitize all input**
   ```php
   $email = Security::sanitizeInput($_POST['email']);
   if (!Security::validateEmail($email)) {
       throw new Exception('Invalid email');
   }
   ```

3. **Use CSRF tokens** for state-changing operations
   ```php
   // In form
   <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
   
   // In handler
   if (!Security::verifyCSRFToken($_POST['csrf_token'])) {
       die('Invalid token');
   }
   ```

4. **Never store sensitive data in plain text**
   ```php
   // Good
   $hash = Security::hashPassword($password);
   
   // Bad
   $password = $_POST['password']; // Store directly
   ```

### Database Changes

When adding database changes:

1. **Create a migration file** in `database/migrations/`
2. **Use proper naming**: `003_descriptive_name.sql`
3. **Include rollback instructions** in comments
4. **Test the migration** before committing

Example migration:
```sql
-- Migration: Add email verification
-- Description: Add email verification fields to users table
-- Date: 2025-01-15

ALTER TABLE users 
ADD COLUMN email_verified tinyint(1) DEFAULT 0,
ADD COLUMN verification_token varchar(64) DEFAULT NULL,
ADD COLUMN verified_at timestamp NULL;

-- Rollback:
-- ALTER TABLE users 
-- DROP COLUMN email_verified,
-- DROP COLUMN verification_token,
-- DROP COLUMN verified_at;
```

### Testing

Before submitting a PR:

1. **Test manually** in your local environment
2. **Check for PHP errors** (enable error reporting)
3. **Test database operations** (create, read, update, delete)
4. **Verify security measures** (try to bypass auth, inject SQL, etc.)
5. **Check responsive design** on mobile devices
6. **Test different browsers** if UI changes

### Git Commit Messages

Write clear, descriptive commit messages:

```
Good:
- "Add CSRF protection to login form"
- "Fix SQL injection vulnerability in user search"
- "Update README with deployment instructions"

Bad:
- "fix bug"
- "update"
- "changes"
```

Use this format:
```
<type>: <subject>

<body>

<footer>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

Example:
```
feat: Add two-factor authentication

Implement TOTP-based 2FA using Google Authenticator.
Includes backup codes and recovery options.

Closes #123
```

## 🏗️ Project Structure

```
ekiliSense/
├── assets/               # Frontend assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── vendor/          # Third-party libraries
├── auth/                # Authentication system
├── console/             # Main dashboard
├── database/            # Database related files
│   └── migrations/      # Database migrations
├── docs/                # Documentation
├── includes/            # Core PHP classes
│   ├── Security.php     # Security utilities
│   ├── Database.php     # Database wrapper
│   ├── Subscription.php # Subscription manager
│   └── init.php         # Initialization
├── middlwares/          # Authentication middleware
└── vendor/              # Composer dependencies
```

## 🔐 Security Vulnerabilities

**Do not** create public issues for security vulnerabilities.

Instead, email security@ekilie.com with:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

We will respond within 48 hours and work with you on a fix.

## 📝 Documentation

When adding features:

1. **Update README.md** if needed
2. **Add code comments** for complex logic
3. **Update API documentation** if adding endpoints
4. **Create/update relevant docs** in `docs/` directory

Documentation style:
```php
/**
 * Brief description of what the method does
 * 
 * @param string $email User email address
 * @param string $password Plain text password
 * @return array|null User data or null if not found
 * @throws Exception If database error occurs
 */
public function authenticate($email, $password)
{
    // Implementation
}
```

## 🎨 UI/UX Guidelines

When working on UI:

1. **Use the modern design system** (`assets/css/ekilisense-modern.css`)
2. **Follow existing patterns** and components
3. **Ensure responsive design** (mobile-first)
4. **Test on different screen sizes**
5. **Maintain consistent spacing** using CSS variables
6. **Use semantic HTML** elements
7. **Ensure accessibility** (ARIA labels, keyboard navigation)

Example:
```html
<!-- Good -->
<button class="btn-modern btn-primary">
    Save Changes
</button>

<!-- Bad -->
<div onclick="save()" style="padding: 10px; background: blue; color: white;">
    Save Changes
</div>
```

## 🚀 Performance Considerations

1. **Optimize database queries**
   - Use indexes on frequently queried columns
   - Avoid N+1 queries
   - Limit result sets appropriately

2. **Minimize HTTP requests**
   - Combine CSS/JS files when possible
   - Use CSS sprites for icons
   - Implement caching headers

3. **Optimize images**
   - Compress images before uploading
   - Use appropriate formats (WebP, SVG)
   - Implement lazy loading

## 📊 Code Review Process

All pull requests go through code review:

1. **Automated checks** run (if configured)
2. **Manual review** by maintainers
3. **Feedback provided** with requested changes
4. **Approval** once requirements are met
5. **Merge** into main branch

Be patient and responsive to feedback!

## 🌍 Internationalization

When adding user-facing text:

1. **Use translation keys** (future implementation)
2. **Avoid hardcoded strings** in critical areas
3. **Keep text clear and concise**
4. **Consider cultural differences**

## 📦 Dependencies

### Adding New Dependencies

Before adding a new dependency:

1. **Check if it's necessary** - can we use existing code?
2. **Verify it's maintained** - recent updates, active community
3. **Check license** - compatible with MIT
4. **Consider size** - impact on load time
5. **Security scan** - no known vulnerabilities

Add via Composer:
```bash
composer require vendor/package
```

Update composer.json and commit the lock file.

## 🧪 Testing Checklist

Before submitting:

- [ ] Code follows PSR-12 standards
- [ ] All database queries use prepared statements
- [ ] CSRF tokens implemented for forms
- [ ] Input validation and sanitization in place
- [ ] No PHP errors or warnings
- [ ] Works in Chrome, Firefox, Safari
- [ ] Responsive on mobile devices
- [ ] Database migrations tested
- [ ] Documentation updated
- [ ] Commit messages are clear
- [ ] No debug code or console.logs left
- [ ] Sensitive data not exposed

## 📞 Getting Help

Need help contributing?

- **Email**: dev@ekilie.com
- **Documentation**: Check `docs/` directory
- **Issues**: Search existing issues for similar problems
- **Discussions**: Start a discussion for questions

## 🏆 Recognition

Contributors are recognized in:
- Commit history
- Release notes
- Contributors section (coming soon)
- Special thanks in documentation

## 📜 License

By contributing, you agree that your contributions will be licensed under the MIT License.

## 🎯 Priority Areas

We especially welcome contributions in these areas:

### High Priority
- [ ] Two-factor authentication implementation
- [ ] Parent portal pages
- [ ] Mobile app API endpoints
- [ ] Real-time notifications system
- [ ] Advanced analytics dashboard

### Medium Priority
- [ ] Email template system
- [ ] SMS integration
- [ ] Report generation
- [ ] Bulk operations
- [ ] Import/export improvements

### Low Priority
- [ ] UI refinements
- [ ] Additional themes
- [ ] Translations
- [ ] Documentation improvements
- [ ] Test coverage

## 📋 Development Setup

### Prerequisites
```bash
# PHP 8.0+
php -v

# Composer
composer --version

# MySQL 8.0+
mysql --version
```

### Setup Steps
```bash
# Clone repository
git clone https://github.com/tacheraSasi/ekiliSense.git
cd ekiliSense

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Edit .env with your database credentials
nano .env

# Run migrations
php database/migrate.php up

# Start PHP development server
php -S localhost:8000
```

### Development Environment

Recommended tools:
- **IDE**: VS Code, PHPStorm
- **Database**: MySQL Workbench, phpMyAdmin
- **API Testing**: Postman, Insomnia
- **Browser DevTools**: Chrome DevTools

Useful VS Code extensions:
- PHP Intelephense
- PHP Debug
- ESLint
- Prettier

## ✨ Thank You!

Your contributions help make ekiliSense better for schools across Africa and beyond. Thank you for taking the time to contribute! 🙏

---

**Happy Coding!** 🚀
