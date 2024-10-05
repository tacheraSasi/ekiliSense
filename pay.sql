-- ekiliSense Database Schema
-- Instructions for setting up the schema

-- 1. Create a new database for ekiliSense (if not already created)
--    Use the following command in your SQL client:
--    CREATE DATABASE ekiliSense;

-- 2. Use the database
USE ekiliSense;

-- 3. Create Users Table
CREATE TABLE Users (
    id SERIAL PRIMARY KEY,
    unique_id VARCHAR(255) UNIQUE NOT NULL, -- e.g., GUID or UUID
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Store hashed passwords
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Create Subscriptions Table
CREATE TABLE Subscriptions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL, -- Monthly or yearly price
    billing_cycle ENUM('monthly', 'yearly') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 5. Create User_Subscriptions Table
CREATE TABLE User_Subscriptions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_id INT NOT NULL,
    start_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    next_billing_date TIMESTAMP NOT NULL,
    status ENUM('active', 'paused', 'canceled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES Subscriptions(id) ON DELETE CASCADE
);

-- 6. Create Payments Table
CREATE TABLE Payments (
    id SERIAL PRIMARY KEY,
    user_subscription_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('successful', 'failed', 'pending') NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(255) UNIQUE, -- External transaction ID from payment gateway
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_subscription_id) REFERENCES User_Subscriptions(id) ON DELETE CASCADE
);

-- 7. Create IPN_Notifications Table
CREATE TABLE IPN_Notifications (
    id SERIAL PRIMARY KEY,
    user_subscription_id INT NOT NULL,
    notification_type ENUM('payment', 'refund', 'cancellation') NOT NULL,
    notification_data JSON NOT NULL, -- Store the notification payload as JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_subscription_id) REFERENCES User_Subscriptions(id) ON DELETE CASCADE
);

-- 8. (Optional) Create Audit_Log Table
CREATE TABLE Audit_Log (
    id SERIAL PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_data JSON,
    new_data JSON,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE SET NULL
);

-- Instructions for setting up the schema
-- 1. Save this file as ekiliSense_schema.sql
-- 2. Open your SQL client and connect to your database server.
-- 3. Run the commands in this file to create the tables in your database.
-- 4. Ensure that you have the necessary privileges to create tables and manage the database.
-- 5. After setting up, consider adding indexes on frequently queried fields for performance.

-- End of ekiliSense Database Schema
