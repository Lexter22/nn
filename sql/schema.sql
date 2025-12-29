-- Charset/engine defaults for XAMPP MySQL/MariaDB
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS clinic_database;
-- Users: login accounts (supports plain or hashed passwords)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  email VARCHAR(120) UNIQUE NULL,
  -- Either 'password' (plain for demo) or 'password_hash' (bcrypt) may be used
  password VARCHAR(255) NULL,
  password_hash VARCHAR(255) NULL,
  role ENUM('admin','nurse') NOT NULL DEFAULT 'nurse',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Medicines inventory
CREATE TABLE IF NOT EXISTS medicines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  category VARCHAR(80) NOT NULL,
  stock_quantity INT NOT NULL DEFAULT 0,
  expiry_date DATE NOT NULL,
  status VARCHAR(16) NOT NULL, -- values used: 'available','low','out','expired'
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_meds_name (name),
  KEY idx_meds_category (category),
  KEY idx_meds_expiry (expiry_date),
  KEY idx_meds_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Patients master (minimal fields; extend as needed)
CREATE TABLE IF NOT EXISTS patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_patients_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Visits (used for today's visits count)
CREATE TABLE IF NOT EXISTS visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  visit_date DATE NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_visits_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  KEY idx_visits_date (visit_date),
  KEY idx_visits_created (created_at),
  KEY idx_visits_patient (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Patient records (fallback for counts and reporting)
CREATE TABLE IF NOT EXISTS patient_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  visit_date DATE NULL,        -- optional; some code uses created_at as visit date
  remarks TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_records_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  KEY idx_records_created (created_at),
  KEY idx_records_visit (visit_date),
  KEY idx_records_patient (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: Super Admin and Nurse (plain passwords for simplicity)
INSERT INTO users (username, email, password, role)
VALUES
  ('admin', NULL, 'Admin@123', 'admin'),
  ('nurse', NULL, 'Nurse@123', 'nurse')
ON DUPLICATE KEY UPDATE
  email = VALUES(email),
  password = VALUES(password),
  role = VALUES(role);
