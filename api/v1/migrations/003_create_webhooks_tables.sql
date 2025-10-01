-- Create webhooks table for third-party integrations
CREATE TABLE IF NOT EXISTS webhooks (
    webhook_id VARCHAR(100) PRIMARY KEY,
    school_uid VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    events JSON NOT NULL, -- Array of event types to trigger on
    secret VARCHAR(255) NOT NULL, -- Secret for HMAC signature
    is_active BOOLEAN DEFAULT TRUE,
    last_triggered TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_uid) REFERENCES schools(unique_id) ON DELETE CASCADE,
    INDEX idx_school_uid (school_uid),
    INDEX idx_is_active (is_active)
);

-- Create webhook logs table
CREATE TABLE IF NOT EXISTS webhook_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    webhook_id VARCHAR(100) NOT NULL,
    event VARCHAR(100) NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    http_code INT,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(webhook_id) ON DELETE CASCADE,
    INDEX idx_webhook_id (webhook_id),
    INDEX idx_created_at (created_at)
);
