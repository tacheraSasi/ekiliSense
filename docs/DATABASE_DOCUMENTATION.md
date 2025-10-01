# Database Documentation & Inconsistencies

## 📊 Database Schema Overview

ekiliSense uses a multi-tenant MySQL database architecture where schools are isolated by a `school_uid` field.

## 🔍 Identified Inconsistencies

### 1. **Naming Convention Inconsistencies**

#### Column Name Casing
The database uses inconsistent casing for column names:

- **PascalCase**: `Class_id`, `Class_name`, `School_unique_id`, `Admin_email`
- **snake_case**: `school_uid`, `student_id`, `teacher_email`, `created_at`
- **Mixed**: `School_unique_id` (PascalCase with underscore)

**Recommendation**: Standardize to `snake_case` (industry standard):
```sql
-- Before: Class_id, Class_name
-- After: class_id, class_name

-- Before: School_unique_id
-- After: school_unique_id
```

#### Table Name Consistency
Some tables use plural, others use singular:
- Plural: `classes`, `teachers`, `students`, `schools`
- Singular: `class_teacher`, `student_attendance`, `stuff_attendance`

**Current State**: Mixed (acceptable, but document convention)

### 2. **Missing Foreign Key Constraints**

Several tables reference other tables but lack foreign key constraints:

```sql
-- class_teacher table references:
- teacher_id → teachers.teacher_id (NO CONSTRAINT)
- Class_id → classes.Class_id (NO CONSTRAINT)

-- students table references:
- class_id → classes.Class_id (NO CONSTRAINT)
- school_uid → schools.unique_id (NO CONSTRAINT)

-- subjects table references:
- class_id → classes.Class_id (NO CONSTRAINT)
- teacher_id → teachers.teacher_id (NO CONSTRAINT)
```

**Impact**: 
- No referential integrity enforcement
- Orphaned records possible
- Cascading deletes not automatic

**Recommendation**: Add foreign key constraints in future migration:

```sql
-- Example migration
ALTER TABLE class_teacher
ADD CONSTRAINT fk_ct_teacher 
FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
ADD CONSTRAINT fk_ct_class 
FOREIGN KEY (Class_id) REFERENCES classes(Class_id) ON DELETE CASCADE;

ALTER TABLE students
ADD CONSTRAINT fk_student_class 
FOREIGN KEY (class_id) REFERENCES classes(Class_id) ON DELETE SET NULL,
ADD CONSTRAINT fk_student_school 
FOREIGN KEY (school_uid) REFERENCES schools(unique_id) ON DELETE CASCADE;
```

### 3. **Inconsistent Primary Key Types**

Different tables use different approaches for primary keys:

- **Auto-increment IDs**: Most tables (classes, schools, students)
- **Random integers**: teacher_id, student_id (not sequential)
- **String UIDs**: school_uid, school_unique_id

```sql
-- students table
student_id int(11) NOT NULL  -- Random, not auto-increment

-- teachers table
teacher_id int(11) NOT NULL  -- Random, not auto-increment

-- classes table
Class_id int(11) NOT NULL AUTO_INCREMENT  -- Sequential
```

**Recommendation**: Keep current system but document strategy:
- Sequential IDs for internal tables (classes, payments)
- Random IDs for public-facing identifiers (students, teachers)
- String UIDs for tenant isolation (schools)

### 4. **School UID Field Naming**

The `school_uid` field has inconsistent naming across tables:

- `schools` table: `unique_id`
- `students` table: `school_uid`
- `classes` table: `school_unique_id`
- `teachers` table: `School_unique_id`
- `class_teacher` table: `school_unique_id`

**Impact**: Confusing queries, join complexity

**Recommendation**: Standardize to `school_uid`:

```sql
-- Migration to standardize
ALTER TABLE schools CHANGE unique_id school_uid VARCHAR(255);
ALTER TABLE classes CHANGE school_unique_id school_uid VARCHAR(100);
ALTER TABLE teachers CHANGE School_unique_id school_uid VARCHAR(255);
ALTER TABLE class_teacher CHANGE school_unique_id school_uid VARCHAR(100);
```

### 5. **Missing Indexes**

Several frequently queried columns lack indexes:

```sql
-- Frequent queries without indexes:
SELECT * FROM students WHERE school_uid = ? AND class_id = ?;
SELECT * FROM teachers WHERE School_unique_id = ? AND teacher_email = ?;
SELECT * FROM class_teacher WHERE school_unique_id = ? AND Class_id = ?;
```

**Recommendation**: Add compound indexes:

```sql
CREATE INDEX idx_students_school_class ON students(school_uid, class_id);
CREATE INDEX idx_teachers_school_email ON teachers(School_unique_id, teacher_email);
CREATE INDEX idx_ct_school_class ON class_teacher(school_unique_id, Class_id);
CREATE INDEX idx_subjects_school_class ON subjects(School_unique_id, class_id);
```

### 6. **Unused/Incomplete Tables**

Some tables appear incomplete or unused:

```sql
-- school_admin table: Empty structure, no data
CREATE TABLE `school_admin` (
  `School_unique_id` varchar(255) DEFAULT NULL,
  `Admin_name` varchar(255) DEFAULT NULL,
  `Admin_email` varchar(255) DEFAULT NULL,
  `Admin_password` varchar(255) DEFAULT NULL,
  ...
) -- NO PRIMARY KEY, NO INDEXES

-- payments table: Schema exists but likely unused
-- results table: Schema exists but no implementation
```

**Recommendation**: 
- Remove unused tables or document their purpose
- Add proper constraints if tables are needed
- Merge school admin into schools table

### 7. **Character Set Inconsistencies**

Tables use different character sets:

- Most tables: `latin1` with `latin1_swedish_ci`
- New tables: `utf8mb4` with `utf8mb4_unicode_ci` (correct)

**Impact**: 
- Potential emoji/international character issues
- Inconsistent collation in joins

**Recommendation**: Migrate all tables to utf8mb4:

```sql
-- Convert table character set
ALTER TABLE classes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE teachers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE students CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Repeat for all tables
```

### 8. **Nullable Fields Without Defaults**

Many fields are nullable without clear defaults:

```sql
-- classes table
Class_name varchar(255) DEFAULT NULL  -- Should empty string be allowed?

-- students table
parent_phone varchar(255) DEFAULT NULL  -- OK
parent_email varchar(255) DEFAULT NULL  -- OK

-- teachers table
teacher_home_address varchar(255) DEFAULT NULL  -- OK
verified tinyint(1) NOT NULL DEFAULT 0  -- Good!
```

**Recommendation**: Review nullable fields and add sensible defaults

### 9. **Timestamp Handling**

Inconsistent use of timestamp fields:

```sql
-- Some tables have both created_at and updated_at
-- Others only have created_at
-- Some use TIMESTAMP, others use DATETIME

-- Good example:
created_at timestamp NOT NULL DEFAULT current_timestamp(),
updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()

-- Inconsistent:
attendance_date datetime NOT NULL DEFAULT current_timestamp()  -- Should use DATE?
```

**Recommendation**: Standardize on TIMESTAMP with:
- `created_at` for all tables
- `updated_at` for tables that need it
- Use DATE for date-only fields (attendance_date)

### 10. **Redundant/Duplicate Data**

Some potential data redundancy:

```sql
-- students table has both id and student_id
id int(11) NOT NULL AUTO_INCREMENT,
student_id int(11) NOT NULL,

-- Why two IDs? student_id appears to be the actual identifier
-- id might be internal, student_id for display
```

**Status**: Document the purpose of each ID field

## 📋 Recommended Migration Plan

### Phase 1: Non-Breaking Changes (Can be done immediately)
1. Add missing indexes
2. Add foreign key constraints (with ON DELETE behavior)
3. Convert character sets to utf8mb4
4. Add created_at/updated_at where missing

### Phase 2: Breaking Changes (Requires testing)
1. Standardize column name casing
2. Rename school_uid fields consistently
3. Normalize data structures
4. Remove unused tables

### Phase 3: Optimization (After stabilization)
1. Optimize data types
2. Add partitioning for large tables
3. Implement archiving strategy
4. Add full-text indexes where needed

## 📝 Current Table Structure

### Core Tables

#### `schools`
- **Purpose**: Multi-tenant school records
- **Primary Key**: `id` (auto-increment)
- **Unique Key**: `unique_id` (varchar, tenant identifier)
- **Issues**: Inconsistent UID naming, MD5 password field

#### `teachers`
- **Purpose**: Teacher accounts per school
- **Primary Key**: `teacher_id` (unique composite with email)
- **Issues**: No auto-increment, School_unique_id naming, MD5 auth

#### `students`
- **Purpose**: Student records per school
- **Primary Key**: `id` (auto-increment)
- **Unique**: `student_id` (displayed ID)
- **Issues**: Dual ID system, missing FK constraints

#### `classes`
- **Purpose**: Class/grade definitions per school
- **Primary Key**: `Class_id` (auto-increment)
- **Issues**: PascalCase naming, missing FK to school

#### `class_teacher`
- **Purpose**: Assignment of teachers to classes
- **Primary Key**: `Class_teacher_id`
- **Issues**: Missing FK constraints, could be junction table

#### `subjects`
- **Purpose**: Subject definitions per class
- **Primary Key**: `subject_id`
- **Issues**: Missing FK constraints

### Supporting Tables

#### `student_attendance`
- **Purpose**: Daily attendance tracking
- **Issues**: No proper composite key, inefficient for large data

#### `plans`
- **Purpose**: School planning/task system
- **Issues**: Unclear relationship to core system

#### `otps`
- **Purpose**: One-time passwords for teacher verification
- **Issues**: No cleanup mechanism, should use password_reset_tokens table

### New Tables (v2.0)

#### `subscription_plans`
- **Purpose**: Define available subscription tiers
- **Structure**: Clean, with proper constraints

#### `school_subscriptions`
- **Purpose**: Track school subscriptions
- **Structure**: Clean, with FK to schools and plans

#### `payment_transactions`
- **Purpose**: Payment history
- **Structure**: Clean, with FK to subscriptions

#### `login_logs`
- **Purpose**: Security audit trail
- **Structure**: Clean, indexed for queries

#### `active_sessions`
- **Purpose**: Session tracking
- **Structure**: Clean, with cleanup indexes

## 🔧 Maintenance Queries

### Check for Orphaned Records

```sql
-- Find teachers without schools
SELECT t.* FROM teachers t
LEFT JOIN schools s ON t.School_unique_id = s.unique_id
WHERE s.unique_id IS NULL;

-- Find students without classes
SELECT st.* FROM students st
LEFT JOIN classes c ON st.class_id = c.Class_id
WHERE st.class_id IS NOT NULL AND c.Class_id IS NULL;

-- Find class_teacher without teachers
SELECT ct.* FROM class_teacher ct
LEFT JOIN teachers t ON ct.teacher_id = t.teacher_id
WHERE t.teacher_id IS NULL;
```

### Cleanup Old Data

```sql
-- Remove expired password reset tokens
DELETE FROM password_reset_tokens 
WHERE expires_at < NOW() OR used = 1;

-- Remove old login logs (keep 90 days)
DELETE FROM login_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Remove inactive sessions
DELETE FROM active_sessions 
WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR);
```

## 📊 Database Statistics

Run these queries to understand your data:

```sql
-- Count records per table
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'your_database_name'
ORDER BY TABLE_ROWS DESC;

-- Find tables without indexes
SELECT DISTINCT t.TABLE_NAME
FROM information_schema.TABLES t
LEFT JOIN information_schema.STATISTICS s 
    ON t.TABLE_NAME = s.TABLE_NAME AND t.TABLE_SCHEMA = s.TABLE_SCHEMA
WHERE t.TABLE_SCHEMA = 'your_database_name' 
    AND s.INDEX_NAME IS NULL
    AND t.TABLE_TYPE = 'BASE TABLE';
```

## 🎯 Best Practices Moving Forward

1. **Always use prepared statements** (Database helper class)
2. **Add indexes** for frequently queried columns
3. **Use foreign key constraints** for referential integrity
4. **Standardize naming conventions** in new tables
5. **Use utf8mb4** for all new tables
6. **Add created_at and updated_at** to all tables
7. **Document table purposes** and relationships
8. **Regular maintenance** (cleanup, optimization)
9. **Monitor slow queries** and optimize
10. **Backup before migrations**

## 📚 Resources

- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Database Design Best Practices](https://www.vertabelo.com/blog/database-design-best-practices/)
- [MySQL Performance Tuning](https://www.percona.com/blog/)

---

**Note**: This documentation reflects the database state as of version 2.0. Always refer to actual schema for current structure.
