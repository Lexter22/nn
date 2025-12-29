CREATE DATABASE school_clinic_database;
USE school_clinic_database;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0; -- Disable foreign key checks to allow drops

-- 1. Reset Tables (Clean Slate)
DROP TABLE IF EXISTS visits;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS medicines;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1; -- Re-enable checks

-- --------------------------------------------------------

-- 2. Users Table (Staff/Admins)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, -- Supports both plain text (for now) or Hash
  role ENUM('admin', 'nurse') NOT NULL DEFAULT 'nurse',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 3. Medicines Inventory
CREATE TABLE medicines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  category VARCHAR(80) NOT NULL, -- e.g., 'Analgesic', 'Antibiotic'
  stock_quantity INT NOT NULL DEFAULT 0,
  expiry_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL, -- 'available', 'low', 'out', 'expired'
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 4. Patients Master List (Demographics)
-- This separates the STUDENT from the VISIT.
CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  student_number VARCHAR(50) UNIQUE NULL, -- e.g., '2023-001-BN'
  course_section VARCHAR(50) NULL,        -- e.g., 'BSIT 3-1'
  allergies TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_name (name) -- Makes searching faster
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 5. Visits (The Medical Records)
-- This links to 'patients' and contains the SOAP data.
CREATE TABLE visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  
  -- VITAL SIGNS (Objective)
  visit_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  bp VARCHAR(20) NULL,      -- e.g., '120/80'
  temp DECIMAL(4,1) NULL,   -- e.g., 36.5

  -- SOAP NOTES
  symptom TEXT NOT NULL,    -- Chief Complaint (Subjective)
  notes TEXT NULL,          -- Nurse's Assessment
  treatment TEXT NULL,      -- Meds given / Intervention (Plan)
  
  -- OUTCOME
  disposition VARCHAR(50) DEFAULT 'Back to Class', -- 'Sent Home', 'Hospital'

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;