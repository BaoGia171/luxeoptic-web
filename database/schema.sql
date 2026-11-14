-- LUXEOPTIC DATABASE SCHEMA

CREATE DATABASE IF NOT EXISTS luxeoptic
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE luxeoptic;


-- Bảng danh mục sản phẩm
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Bảng sản phẩm
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,

    category_id INT NOT NULL,

    sku VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(150) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,

    price INT NOT NULL,
    old_price INT DEFAULT NULL,

    stock INT DEFAULT 0,

    color VARCHAR(100),
    material VARCHAR(100),

    description TEXT,

    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE CASCADE
);