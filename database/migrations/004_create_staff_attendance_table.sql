-- Migration: Add staff attendance tracking
-- Description: Create staff_attendance table for tracking teacher attendance with geolocation
-- Date: 2025-01-15

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


CREATE TABLE IF NOT EXISTS homework_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_uid VARCHAR(100) NOT NULL UNIQUE,
    school_uid VARCHAR(255) NOT NULL,
    class_id VARCHAR(100) NOT NULL,
    subject_id VARCHAR(100) NOT NULL,
    teacher_id VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    due_time TIME,
    max_points INT DEFAULT 100,
    assignment_type ENUM('homework', 'project', 'essay', 'quiz') DEFAULT 'homework',
    status ENUM('active', 'closed', 'draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Homework submissions table
CREATE TABLE IF NOT EXISTS homework_submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_uid VARCHAR(100) NOT NULL,
    student_id VARCHAR(100) NOT NULL,
    school_uid VARCHAR(255) NOT NULL,
    submission_text TEXT,
    file_name VARCHAR(255),
    file_path VARCHAR(500),
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade INT,
    teacher_feedback TEXT,
    status ENUM('submitted', 'graded', 'late', 'missing') DEFAULT 'submitted',
    graded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_uid) REFERENCES homework_assignments(assignment_uid) ON DELETE CASCADE
);

-- Exam/Test schedules table  
CREATE TABLE IF NOT EXISTS exam_schedules (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_uid VARCHAR(100) NOT NULL UNIQUE,
    school_uid VARCHAR(255) NOT NULL,
    class_id VARCHAR(100) NOT NULL,
    subject_id VARCHAR(100) NOT NULL,
    teacher_id VARCHAR(100) NOT NULL,
    exam_title VARCHAR(255) NOT NULL,
    exam_description TEXT,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    exam_type ENUM('quiz', 'midterm', 'final', 'test') DEFAULT 'test',
    max_marks INT DEFAULT 100,
    status ENUM('scheduled', 'active', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Student exam results table
CREATE TABLE IF NOT EXISTS exam_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_uid VARCHAR(100) NOT NULL,
    student_id VARCHAR(100) NOT NULL,
    school_uid VARCHAR(255) NOT NULL,
    marks_obtained INT NOT NULL,
    grade VARCHAR(5),
    remarks TEXT,
    result_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    teacher_id VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_uid) REFERENCES exam_schedules(exam_uid) ON DELETE CASCADE
);

-- Enhanced attendance reporting table (extends existing student_attendance)
CREATE TABLE IF NOT EXISTS attendance_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    school_uid VARCHAR(255) NOT NULL,
    class_id VARCHAR(100) NOT NULL,
    student_id VARCHAR(100),
    report_type ENUM('daily', 'weekly', 'monthly', 'custom') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    present_days INT NOT NULL,
    absent_days INT NOT NULL,
    attendance_percentage DECIMAL(5,2) NOT NULL,
    generated_by VARCHAR(100) NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Parent-teacher communication messages
CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    message_uid VARCHAR(100) NOT NULL UNIQUE,
    school_uid VARCHAR(255) NOT NULL,
    sender_type ENUM('teacher', 'parent', 'admin') NOT NULL,
    sender_id VARCHAR(100) NOT NULL,
    recipient_type ENUM('teacher', 'parent', 'admin') NOT NULL,
    recipient_id VARCHAR(100) NOT NULL,
    student_id VARCHAR(100), -- For context
    subject VARCHAR(255) NOT NULL,
    message_body TEXT NOT NULL,
    message_type ENUM('general', 'attendance', 'academic', 'behavioral', 'announcement') DEFAULT 'general',
    is_read BOOLEAN DEFAULT FALSE,
    is_urgent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL
);

-- Event management table
CREATE TABLE IF NOT EXISTS school_events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_uid VARCHAR(100) NOT NULL UNIQUE,
    school_uid VARCHAR(255) NOT NULL,
    event_title VARCHAR(255) NOT NULL,
    event_description TEXT,
    event_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    event_type ENUM('academic', 'sports', 'cultural', 'meeting', 'announcement') DEFAULT 'academic',
    target_audience ENUM('all', 'students', 'teachers', 'parents', 'specific_class') DEFAULT 'all',
    class_id VARCHAR(100), -- For class-specific events
    location VARCHAR(255),
    max_participants INT,
    registration_required BOOLEAN DEFAULT FALSE,
    status ENUM('scheduled', 'active', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Event participant registrations
CREATE TABLE IF NOT EXISTS event_registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    event_uid VARCHAR(100) NOT NULL,
    participant_type ENUM('student', 'teacher', 'parent') NOT NULL,
    participant_id VARCHAR(100) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'confirmed', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (event_uid) REFERENCES school_events(event_uid) ON DELETE CASCADE
);

-- Rollback:
-- DROP TABLE IF EXISTS staff_attendance;
