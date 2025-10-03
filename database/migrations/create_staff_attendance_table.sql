-- Create staff_attendance table for tracking teacher attendance
CREATE TABLE IF NOT EXISTS staff_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_uid VARCHAR(255) NOT NULL,
    teacher_id VARCHAR(255) NOT NULL,
    attendance_date DATE NOT NULL,
    status TINYINT(1) DEFAULT 1,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (teacher_id, attendance_date),
    INDEX idx_school_uid (school_uid),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_attendance_date (attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
