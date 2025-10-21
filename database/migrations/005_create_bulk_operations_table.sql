-- Migration: Create bulk_operations table for import/export history
-- Date: 2025-10-21

CREATE TABLE IF NOT EXISTS bulk_operations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_uid VARCHAR(100) NOT NULL,
    operation_type ENUM('import', 'export') NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
