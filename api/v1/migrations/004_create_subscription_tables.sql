-- Create subscription plans table
CREATE TABLE IF NOT EXISTS subscription_plans (
    plan_id VARCHAR(100) PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'TZS',
    billing_cycle ENUM('monthly', 'yearly') DEFAULT 'monthly',
    features JSON, -- Array of feature names
    max_students INT DEFAULT 100,
    max_teachers INT DEFAULT 10,
    max_storage_gb INT DEFAULT 5,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
);

-- Create subscriptions table
CREATE TABLE IF NOT EXISTS subscriptions (
    subscription_id VARCHAR(100) PRIMARY KEY,
    school_uid VARCHAR(255) NOT NULL,
    plan_id VARCHAR(100) NOT NULL,
    status ENUM('active', 'cancelled', 'expired', 'pending_payment') DEFAULT 'pending_payment',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_uid) REFERENCES schools(unique_id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(plan_id),
    INDEX idx_school_uid (school_uid),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date)
);

-- Create invoices table
CREATE TABLE IF NOT EXISTS invoices (
    invoice_id VARCHAR(100) PRIMARY KEY,
    school_uid VARCHAR(255) NOT NULL,
    subscription_id VARCHAR(100),
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'TZS',
    status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_uid) REFERENCES schools(unique_id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id),
    INDEX idx_school_uid (school_uid),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Insert default subscription plans
INSERT INTO subscription_plans (plan_id, plan_name, description, price, currency, billing_cycle, features, max_students, max_teachers, max_storage_gb) 
VALUES 
    ('plan_free', 'Free', 'Perfect for small schools getting started', 0.00, 'TZS', 'monthly', 
     '["Basic student management", "Teacher accounts", "Class management", "Basic reporting"]', 
     50, 5, 1),
    ('plan_basic', 'Basic', 'Great for growing schools', 10000.00, 'TZS', 'monthly', 
     '["All Free features", "Parent portal", "Assignment management", "Attendance tracking", "Email support"]', 
     200, 20, 5),
    ('plan_premium', 'Premium', 'Advanced features for larger schools', 50000.00, 'TZS', 'monthly', 
     '["All Basic features", "Advanced analytics", "API access", "Webhooks", "Priority support", "Custom reports"]', 
     1000, 100, 20),
    ('plan_enterprise', 'Enterprise', 'Unlimited scale with dedicated support', 150000.00, 'TZS', 'monthly', 
     '["All Premium features", "Unlimited students", "Unlimited teachers", "Dedicated support", "Custom integrations", "White-labeling"]', 
     999999, 999999, 100)
ON DUPLICATE KEY UPDATE plan_name = plan_name;
