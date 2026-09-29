-- =========================================================
-- KINMEL E-COMMERCE MANAGEMENT SYSTEM
-- Database: kinmel_db
-- Import this file into phpMyAdmin (Import tab) to set up
-- everything you need: tables, sample admin, sample data.
-- =========================================================

CREATE DATABASE IF NOT EXISTS kinmel_db;
USE kinmel_db;

-- ---------------------------------------------------------
-- Table: users
-- Stores both customers and admin (role column separates them)
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) DEFAULT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    country VARCHAR(50) NOT NULL,
    address VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Table: categories
-- ---------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Table: products
-- ---------------------------------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT 'no-image.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Table: cart
-- One row per (user, product) combination
-- ---------------------------------------------------------
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- ---------------------------------------------------------
-- Table: orders
-- ---------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    shipping_address VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Table: order_items
-- ---------------------------------------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =========================================================
-- SAMPLE DATA
-- =========================================================

-- Sample admin account
-- Email: admin@kinmel.com   Password: admin123
-- (the hash below is password_hash('admin123', PASSWORD_DEFAULT))
INSERT INTO users (first_name, middle_name, last_name, gender, email, phone, password, country, address, role)
VALUES (
    'System', '', 'Admin', 'Other', 'admin@kinmel.com', '9800000000',
    '$2y$10$72B1EgSqRjH7JCKYDRMJ0e6NAQRApql2KEp7ikHIpGC898S1qaS2m',
    'Nepal', 'Kathmandu', 'admin'
);

-- Sample categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Phones, laptops, and gadgets'),
('Clothing', 'Men and women apparel'),
('Books', 'Fiction, non-fiction, and academic books'),
('Home & Kitchen', 'Household and kitchen items'),
('Sports', 'Sports gear and accessories');

-- Sample products
INSERT INTO products (category_id, name, description, price, stock, image) VALUES
(1, 'Wireless Mouse', 'Ergonomic wireless mouse with USB receiver', 799.00, 25, 'no-image.png'),
(1, 'Bluetooth Headphones', 'Over-ear Bluetooth headphones with noise cancellation', 3499.00, 15, 'no-image.png'),
(2, 'Cotton T-Shirt', 'Comfortable 100% cotton round neck t-shirt', 599.00, 50, 'no-image.png'),
(3, 'PHP for Beginners', 'A complete guide to PHP and MySQL for beginners', 899.00, 20, 'no-image.png'),
(4, 'Non-Stick Frying Pan', '24cm non-stick frying pan', 1299.00, 10, 'no-image.png'),
(5, 'Football', 'Size 5 professional football', 1499.00, 30, 'no-image.png');

-- =========================================================
-- NOTE: If the admin password above does not work on your
-- machine due to hash differences, simply delete that row and
-- register a new user through register.php, then run:
-- UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
-- =========================================================
