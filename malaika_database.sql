-- Malaika Beauty Parlor & Boutique Database
-- Corrected Schema with Admin, Staff, and Client entities

CREATE DATABASE IF NOT EXISTS malaika_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE malaika_db;

-- ============================================================
-- 1. USERS (Central Authentication Table)
-- ============================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('Client','Staff','Admin') NOT NULL DEFAULT 'Client',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. ADMIN (Dedicated Admin Entity)
-- ============================================================
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    admin_level ENUM('Super','Manager') DEFAULT 'Manager',
    last_login DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3. STAFF (Dedicated Staff Entity)
-- ============================================================
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    position VARCHAR(50) DEFAULT 'Beautician',
    hire_date DATE,
    is_available TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. CLIENTS (Dedicated Client Entity)
-- ============================================================
CREATE TABLE clients (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. SERVICES (Beauty Services)
-- ============================================================
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL, -- Nails, Eyelashes, Massage
    duration_mins INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 6. PRODUCTS (Clothing Boutique)
-- ============================================================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('Women','Men','Kids') NOT NULL,
    size VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    stock_status ENUM('Available','Sold Out') DEFAULT 'Available',
    image_url VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 7. BOOKINGS (Appointments)
-- ============================================================
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    staff_id INT,
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    time_slot TIME NOT NULL,
    status ENUM('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
    notes TEXT,
    reminder_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. PAYMENTS
-- ============================================================
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Credit Card','Cash','Mobile Money','EFT') DEFAULT 'Cash',
    status ENUM('Pending','Paid','Refunded') DEFAULT 'Pending',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 9. WISHLIST
-- ============================================================
CREATE TABLE wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    product_id INT NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (client_id, product_id)
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin: Ms. Mogapi
INSERT INTO users (full_name, email, password_hash, phone, role) VALUES
('LMJ Mogapi', 'mogapi@malaika.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0734567890', 'Admin');

INSERT INTO admin (user_id, admin_level) VALUES
(1, 'Super');

-- Staff: Boitumelo
INSERT INTO users (full_name, email, password_hash, phone, role) VALUES
('Boitumelo Modise', 'boitumelo@malaika.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0723456789', 'Staff');

INSERT INTO staff (user_id, position, hire_date) VALUES
(2, 'Beautician', '2024-01-15');

-- Demo Client
INSERT INTO users (full_name, email, password_hash, phone, role) VALUES
('Lindiwe Nkosi', 'lindiwe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0712345678', 'Client');

INSERT INTO clients (user_id) VALUES (3);

-- Services
INSERT INTO services (service_name, category, duration_mins, price, description) VALUES
('Gel Nails', 'Nails', 45, 350.00, 'Gel manicure with polish'),
('Classic Eyelashes', 'Eyelashes', 60, 550.00, 'Classic lash extensions'),
('Full Body Massage', 'Massage', 90, 750.00, 'Swedish relaxation massage'),
('Acrylic Nails', 'Nails', 60, 450.00, 'Acrylic nail set with design'),
('Hybrid Eyelashes', 'Eyelashes', 75, 650.00, 'Hybrid volume lash extensions'),
('Back & Neck Massage', 'Massage', 45, 400.00, 'Targeted tension relief massage');

-- Products (Women)
INSERT INTO products (name, category, size, price, stock_status, description) VALUES
('Summer Floral Dress', 'Women', 'S, M, L', 350.00, 'Available', 'Light floral summer dress'),
('Casual Cotton Blouse', 'Women', 'XS, S, M', 280.00, 'Available', 'Comfortable everyday blouse'),
('Elegant Evening Gown', 'Women', 'M, L, XL', 650.00, 'Sold Out', 'Formal evening wear'),
('Denim Skinny Jeans', 'Women', '28, 30, 32', 420.00, 'Available', 'Classic skinny fit denim');

-- Products (Men)
INSERT INTO products (name, category, size, price, stock_status, description) VALUES
('Classic Men's Shirt', 'Men', 'M, L, XL', 320.00, 'Available', 'Formal button-up shirt'),
('Slim Fit Jeans', 'Men', '30, 32, 34', 650.00, 'Sold Out', 'Modern slim fit denim'),
('Polo T-Shirt', 'Men', 'S, M, L, XL', 250.00, 'Available', 'Casual polo neck tee');

-- Products (Kids)
INSERT INTO products (name, category, size, price, stock_status, description) VALUES
('Kids' Party Dress', 'Kids', '4Y, 6Y, 8Y', 250.00, 'Available', 'Colorful party dress for girls'),
('Cartoon T-Shirt', 'Kids', '2Y, 4Y, 6Y', 180.00, 'Available', 'Fun cartoon print tee'),
('Boys Shorts Set', 'Kids', '3Y, 5Y, 7Y', 220.00, 'Available', 'Summer shorts and top set');

-- Demo Booking
INSERT INTO bookings (client_id, staff_id, service_id, booking_date, time_slot, status, notes) VALUES
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 'Confirmed', 'Bring nail color ideas');

INSERT INTO payments (booking_id, amount, payment_method, status) VALUES
(1, 350.00, 'Cash', 'Paid');


-- E-commerce checkout tables
CREATE TABLE orders (
 order_id INT AUTO_INCREMENT PRIMARY KEY,
 client_id INT NOT NULL,
 total_amount DECIMAL(10,2) NOT NULL,
 status ENUM('Pending','Paid','Cancelled','Refunded') DEFAULT 'Pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
 order_item_id INT AUTO_INCREMENT PRIMARY KEY,
 order_id INT NOT NULL,
 product_id INT NOT NULL,
 quantity INT NOT NULL DEFAULT 1,
 unit_price DECIMAL(10,2) NOT NULL,
 FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
 FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB;
