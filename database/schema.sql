-- Intentionally imperfect schema
-- Inconsistent naming, mixed casing, missing FKs

-- SellNow Schema (Hardened, Non-Breaking)
-- Purpose: Improve integrity without breaking legacy code

PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    Full_Name VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS products;
CREATE TABLE products (
    product_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255),
    file_path VARCHAR(255),
    is_active TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- Missing foreign key constraint strictly enforcing user existence

    -- Soft FK: enforced but non-cascading
    FOREIGN KEY (user_id) REFERENCES users(id)
);

DROP TABLE IF EXISTS Carts;  -- Mixed case table name
CREATE TABLE Carts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id VARCHAR(255) NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER DEFAULT 1 CHECK (quantity > 0),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_provider VARCHAR(50),
    payment_status VARCHAR(20),
    transaction_id VARCHAR(100),
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS payment_providers;
CREATE TABLE payment_providers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    provider_name VARCHAR(50) NOT NULL,
    api_key VARCHAR(255),
    api_secret VARCHAR(255),
    is_enabled TINYINT DEFAULT 1
);

-- Indexes (performance + intent)
CREATE INDEX idx_products_user ON products(user_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_carts_session ON Carts(session_id);
