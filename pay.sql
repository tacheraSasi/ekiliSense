CREATE TABLE Subscriptions (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL, -- Monthly or yearly price
    billing_cycle ENUM('monthly', 'yearly') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE School_Subscriptions (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    school_uid VARCHAR(255) NOT NULL, -- Unique ID for each school
    subscription_id INT NOT NULL, -- Matches type to Subscriptions(id)
    start_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    next_billing_date TIMESTAMP NOT NULL,
    status ENUM('active', 'paused', 'canceled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES Subscriptions(id) ON DELETE CASCADE
);
CREATE TABLE Payments (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    school_subscription_id INT NOT NULL, -- Links to School_Subscriptions table
    amount DECIMAL(10, 2) NOT NULL,
    transaction_id VARCHAR(255) NOT NULL,
    payment_status ENUM('pending', 'successful', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_subscription_id) REFERENCES School_Subscriptions(id) ON DELETE CASCADE
);
CREATE TABLE IPN_Notifications (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    school_subscription_id INT NOT NULL, -- Links to School_Subscriptions table
    notification_type VARCHAR(50) NOT NULL, -- Type of notification (e.g., payment)
    notification_data JSON NOT NULL, -- Store the entire notification data as JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_subscription_id) REFERENCES School_Subscriptions(id) ON DELETE CASCADE
);
