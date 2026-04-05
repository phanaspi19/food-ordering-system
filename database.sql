-- Database: food_ordering_system

CREATE DATABASE IF NOT EXISTS food_ordering_system;
USE food_ordering_system;

-- Drop existing tables (for fresh install)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS foods;
DROP TABLE IF EXISTS users;

-- Table: users (with role and profile)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: foods (with category and image)
CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) DEFAULT 'Other',
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: orders (simpler - links to order_items)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: order_items (multiple items per order)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

-- Table: expenses
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123 - hashed)
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample foods with category
INSERT INTO foods (name, description, price, category) VALUES
('Burger', 'Delicious beef burger with cheese', 5.00, 'Fast Food'),
('Pizza', 'Italian pizza with tomato sauce', 8.00, 'Fast Food'),
('Fried Rice', 'Thai style fried rice', 4.00, 'Asian'),
('Noodle Soup', 'Hot noodle soup with chicken', 3.50, 'Asian'),
('Sandwich', 'Fresh sandwich with vegetables', 3.00, 'Fast Food'),
('Ice Cream', 'Vanilla ice cream', 2.00, 'Dessert'),
('Fried Chicken', 'Crispy fried chicken', 6.00, 'Fast Food'),
('Soft Drink', 'Cold Coca Cola', 1.00, 'Drink');