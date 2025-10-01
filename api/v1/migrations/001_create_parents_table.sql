-- Create parents table for parent portal feature
CREATE TABLE IF NOT EXISTS parents (
    parent_id VARCHAR(100) PRIMARY KEY,
    parent_fullname VARCHAR(255) NOT NULL,
    parent_email VARCHAR(255) NOT NULL UNIQUE,
    parent_password VARCHAR(255) NOT NULL,
    parent_phone VARCHAR(50),
    school_uid VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_uid) REFERENCES schools(unique_id) ON DELETE CASCADE,
    INDEX idx_school_uid (school_uid),
    INDEX idx_email (parent_email)
);

-- Create parent-student relationship table
CREATE TABLE IF NOT EXISTS parent_student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id VARCHAR(100) NOT NULL,
    student_id VARCHAR(100) NOT NULL,
    school_uid VARCHAR(255) NOT NULL,
    relationship VARCHAR(50) DEFAULT 'parent', -- parent, guardian, etc.
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES parents(parent_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    UNIQUE KEY unique_parent_student (parent_id, student_id)
);

-- Add parent_id column to students table if it doesn't exist
ALTER TABLE students ADD COLUMN IF NOT EXISTS parent_id VARCHAR(100) DEFAULT NULL;
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_parent_id (parent_id);
