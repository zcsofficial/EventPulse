-- Create the database
CREATE DATABASE eventpulse_db;
USE eventpulse_db;

-- Users table (for registration and authentication)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Store hashed passwords (e.g., using password_hash in PHP)
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table (core of the platform)
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Tasks table (to-do list for each event)
CREATE TABLE tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    task_name VARCHAR(255) NOT NULL,
    completed TINYINT(1) DEFAULT 0, -- 0 = incomplete, 1 = complete
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- Budget table (track expenses per event)
CREATE TABLE budget (
    budget_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL, -- Supports currency (e.g., 150.75)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);
-- Add Orders table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);