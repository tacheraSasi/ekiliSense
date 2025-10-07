# ekiliSense - School Management System

A comprehensive school management system built with PHP.

## Database Migrations

This project uses a migration system to manage database schema changes. Migrations are SQL files located in `database/migrations/` directory.

### Running Migrations

To run all pending migrations:

```bash
php database/migrate.php up
```

### Checking Migration Status

To see which migrations have been executed and which are pending:

```bash
php database/migrate.php status
```

### Rolling Back Migrations

To rollback the last batch of migrations:

```bash
php database/migrate.php down
```

### Available Migrations

1. **001_subscription_system.sql** - Adds subscription and payment tables for the SaaS model
2. **002_security_improvements.sql** - Adds security-related tables (login_logs, active_sessions)
3. **003_quiz_questions.sql** - Adds quiz and questions tables
4. **004_create_staff_attendance_table.sql** - Adds staff attendance tracking with geolocation

### Important Notes

- Migrations are executed in order based on their filename prefix (001, 002, 003, etc.)
- Each migration runs only once and is tracked in the `migrations` table
- Always backup your database before running migrations in production
- For web access (non-CLI), ensure `APP_ENVIRONMENT` is set to development in `.env`

## Setup

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your database credentials
3. Run migrations: `php database/migrate.php up`
4. Start PHP development server: `php -S localhost:8000`

For more detailed contribution guidelines, see [CONTRIBUTING.md](CONTRIBUTING.md).