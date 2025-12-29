# School Clinic Record System

## Project Structure

```
/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
├── config/
│   └── database.php
├── admin/
│   └── login.php
├── api/
│   └── (future AI chatbot)
└── index.php
```

## Tech Stack

- PHP 7.4+
- PDO for database
- Bootstrap 5
- Vanilla JavaScript
- AWS EC2 hosting

# Ayisha's Clinic (PHP + MySQL)

Overview:

- Admin panel for clinic records (patients, visits, medicines).
- Combined Dashboard & Reports at admin/reports.php; Dashboard redirects here.

Requirements:

- PHP 8+, MySQL/MariaDB, XAMPP (htdocs path: /Applications/XAMPP/xamppfiles/htdocs/nn).
- Database: school_clinic_database (falls back to clinic_database).

Config:

- Edit includes/db.php to set DB credentials.
- DB fallback: tries school_clinic_database, then clinic_database.
- Sessions: every page starts with session_start().

Schema (column names used):

- users: id, username, password (plain text), role ('admin'|'nurse')
- patients: id, name, student_number, course_section (optional), created_at
- medicines: id, name, category, stock_quantity, expiry_date, status ('available'|'low'|'out')
- visits: id, patient_id, visit_date (DATETIME), bp, temp, symptom, notes, treatment, disposition

Pages:

- admin/login.php: login card; posts action=login to process.php (plain password compare).
- admin/reports.php: combined Dashboard & Reports
  - Stats: Total Patients, Today’s Visits (DATE(visit_date)=CURDATE()), Low Stock count (status='low' or stock_quantity between 1–9).
  - Critical Inventory: medicines where status IN ('low','out'); shows name, stock_quantity, status badge.
  - Patient Visit Log (latest 50): DATE, Student Name, Section (optional), Complaint (symptom), Treatment, Disposition.
  - Print Report button triggers window.print().
- admin/patients_records.php: minimal columns (Name, Student #, Last Visit, Complaint, Treatment, Disposition). Latest visit via LEFT JOIN.
  - Actions: Add Visit modal (visit_date + notes), Edit Patient, Delete Patient (admin only).
- admin/medicines.php: inventory list with add/edit modal
  - Fields: name, category (dropdown + “Other”), stock_quantity, expiry_date.
  - Status badges: Available/Low/Out.
  - Delete button only for admin.
- admin/inventory.php: redirects to medicines.php.
- includes/admin_sidebar.php: tabs for Dashboard (reports.php), Patient Records, Medicine Inventory.

Controller (admin/process.php) actions (all use prepared statements):

- login: verifies users.username/password, sets $\_SESSION['user_id'], $\_SESSION['role'], $\_SESSION['username'], redirects to dashboard.php.
- add_medicine / update_medicine: computes status by stock_quantity (<=0 'out', <10 'low', else 'available').
- delete_medicine: admin only.
- add_patient_visit: upsert patient by name (update student_number, course_section) and insert visit (DATETIME + vitals/notes).
- add_visit: inserts visit for existing patient (normalizes date to DATETIME).
- update_patient, delete_patient (admin only).

RBAC:

- Nurse: cannot delete medicines/patients (server-side checks, UI hides delete button).
- Admin: full access.

Troubleshooting:

- HTTP 500: verify DB name/case, tables exist, and columns match schema.
- If stock column mismatch, reports/dashboard adapt to stock_quantity or stock when present.
- course_section is optional (UI omits it in Patient Records; Reports shows blank if absent).
- Ensure visits.visit_date is DATETIME; forms send date and backend appends current time.

Printing:

- Use the Print button on the combined Dashboard & Reports page to export to PDF (browser print dialog).
