-- Furniture Management System
-- Import this file in phpMyAdmin
-- Database: furniture_management

CREATE DATABASE IF NOT EXISTS furniture_management
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE furniture_management;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE customers (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id INT NOT NULL AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
    id INT NOT NULL AUTO_INCREMENT,
    order_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    method VARCHAR(50) NOT NULL DEFAULT 'Cash',
    status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    PRIMARY KEY (id),
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default login used by index.php
INSERT INTO users (name, username, password, role)
VALUES ('Administrator', 'admin', 'admin123', 'admin');

-- Products already referenced by the project images
INSERT INTO products (name, category, description, price, stock, image) VALUES
('Dining Table', 'Dining', 'Stylish dining table for home use.', 65000.00, 5, 'dainig.jpg'),
('Coffee Table', 'Living Room', 'Modern coffee table for living room.', 28000.00, 5, 'coffy tbl.jpg'),
('Modern Wardrobe', 'Bedroom', 'Spacious modern wardrobe.', 85000.00, 3, 'Morden wardrobe.avif'),
('Office Chair', 'Office', 'Comfortable office chair.', 22000.00, 10, 'office chair.jpg'),
('Study Desk', 'Office', 'Simple study desk for home or office.', 35000.00, 7, 'study desk.jpg'),
('Bed', 'Bedroom', 'Comfortable wooden bed.', 75000.00, 5, 'bed.jpg');
