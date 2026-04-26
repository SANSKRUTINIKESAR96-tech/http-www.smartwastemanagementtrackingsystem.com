-- =====================================================================
-- Waste Management Tracking System - Database Schema
-- =====================================================================
-- Usage:
--   1.  mysql -u root -p < database/schema.sql
--   2.  Then open  http://localhost/WastManagementSystem/install.php
--       (creates the sample users/complaints/pickups with hashed passwords)
-- =====================================================================

DROP DATABASE IF EXISTS waste_management;
CREATE DATABASE waste_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE waste_management;

-- ---------------------------------------------------------------------
-- 1. users
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    phone         VARCHAR(20)  DEFAULT NULL,
    address       VARCHAR(255) DEFAULT NULL,
    role          ENUM('user','collector','admin') NOT NULL DEFAULT 'user',
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. recycling_centers
-- ---------------------------------------------------------------------
CREATE TABLE recycling_centers (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    location      VARCHAR(255) NOT NULL,
    capacity_kg   INT DEFAULT 0,
    contact       VARCHAR(50)  DEFAULT NULL,
    accepted_types VARCHAR(255) DEFAULT 'dry,wet,recyclable',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. vehicles
-- ---------------------------------------------------------------------
CREATE TABLE vehicles (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_no    VARCHAR(50) NOT NULL UNIQUE,
    type          VARCHAR(50)  DEFAULT 'Truck',
    capacity_kg   INT DEFAULT 1000,
    driver_id     INT DEFAULT NULL,
    status        ENUM('available','on_duty','maintenance') NOT NULL DEFAULT 'available',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicle_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. waste_collection  (pickup requests / waste entries)
-- ---------------------------------------------------------------------
CREATE TABLE waste_collection (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    waste_type    ENUM('dry','wet','recyclable','hazardous','e-waste') NOT NULL,
    quantity      DECIMAL(10,2) NOT NULL DEFAULT 0,
    location      VARCHAR(255) NOT NULL,
    notes         TEXT DEFAULT NULL,
    pickup_date   DATE DEFAULT NULL,
    status        ENUM('Pending','Assigned','Collected','Cancelled') NOT NULL DEFAULT 'Pending',
    recycling_instructions TEXT DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. collection_assignment
-- ---------------------------------------------------------------------
CREATE TABLE collection_assignment (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL,
    collector_id    INT NOT NULL,
    vehicle_id      INT DEFAULT NULL,
    assigned_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at    TIMESTAMP NULL DEFAULT NULL,
    remarks         TEXT DEFAULT NULL,
    CONSTRAINT fk_ca_request    FOREIGN KEY (request_id)   REFERENCES waste_collection(id) ON DELETE CASCADE,
    CONSTRAINT fk_ca_collector  FOREIGN KEY (collector_id) REFERENCES users(id)            ON DELETE CASCADE,
    CONSTRAINT fk_ca_vehicle    FOREIGN KEY (vehicle_id)   REFERENCES vehicles(id)         ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. complaints
-- ---------------------------------------------------------------------
CREATE TABLE complaints (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    subject       VARCHAR(200) DEFAULT NULL,
    description   TEXT NOT NULL,
    location      VARCHAR(255) NOT NULL,
    status        ENUM('Open','In Progress','Resolved') NOT NULL DEFAULT 'Open',
    admin_reply   TEXT DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. waste_processing
-- ---------------------------------------------------------------------
CREATE TABLE waste_processing (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    collection_id   INT NOT NULL,
    center_id       INT DEFAULT NULL,
    processed_qty   DECIMAL(10,2) DEFAULT 0,
    method          ENUM('recycled','composted','landfilled','incinerated') DEFAULT 'recycled',
    processed_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wp_collection FOREIGN KEY (collection_id) REFERENCES waste_collection(id) ON DELETE CASCADE,
    CONSTRAINT fk_wp_center     FOREIGN KEY (center_id)     REFERENCES recycling_centers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. notifications
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    message       TEXT NOT NULL,
    is_read       TINYINT(1) DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- Static reference data (no password hashing needed here)
-- =====================================================================

INSERT INTO recycling_centers (name, location, capacity_kg, contact, accepted_types) VALUES
 ('GreenCycle Center',  'Sector 12, North Zone',  20000, '080-111-1111', 'dry,recyclable'),
 ('EcoHub Facility',    'Sector 5, South Zone',   15000, '080-222-2222', 'wet,recyclable'),
 ('SafeDispose Plant',  'Industrial Park',        30000, '080-333-3333', 'hazardous,e-waste');

INSERT INTO vehicles (vehicle_no, type, capacity_kg, status) VALUES
 ('WM-01-TRK-001', 'Truck',      2000, 'available'),
 ('WM-02-VAN-002', 'Van',        1000, 'available'),
 ('WM-03-TRK-003', 'Compactor',  3500, 'maintenance');
