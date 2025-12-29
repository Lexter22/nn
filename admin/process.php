<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
$pdo = getDB();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function redirect(string $path): void {
    header("Location: $path");
    exit;
}
function computeStatus(int $qty): string {
    if ($qty <= 0) return 'out';
    if ($qty < 10) return 'low';
    return 'available';
}

switch ($action) {
    case 'login': {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') {
            redirect('login.php?error=1');
        }
        $stmt = $pdo->prepare("SELECT id, username, role, password FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && hash_equals((string)$user['password'], (string)$password)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = strtolower($user['role']);
            $_SESSION['username'] = $user['username'];
            redirect('dashboard.php');
        }
        redirect('login.php?error=1');
    } break;

    case 'add_medicine': {
        if (empty($_SESSION['user_id'])) redirect('login.php');
        $name = trim($_POST['name'] ?? '');
        $categoryInput = trim($_POST['category'] ?? '');
        $category = ($categoryInput === 'other') ? trim($_POST['category_other'] ?? '') : $categoryInput;
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        $expiry_date = $_POST['expiry_date'] ?? null;
        if ($name === '' || $category === '' || !$expiry_date) {
            redirect('medicines.php?error=invalid');
        }
        $status = computeStatus($stock_quantity);
        $stmt = $pdo->prepare("INSERT INTO medicines (name, category, stock_quantity, expiry_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $stock_quantity, $expiry_date, $status]);
        redirect('medicines.php?ok=1');
    } break;

    case 'update_medicine': {
        if (empty($_SESSION['user_id'])) redirect('login.php');
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $categoryInput = trim($_POST['category'] ?? '');
        $category = ($categoryInput === 'other') ? trim($_POST['category_other'] ?? '') : $categoryInput;
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        $expiry_date = $_POST['expiry_date'] ?? null;
        if ($id <= 0 || $name === '' || $category === '' || !$expiry_date) {
            redirect('medicines.php?error=invalid');
        }
        $status = computeStatus($stock_quantity);
        $stmt = $pdo->prepare("UPDATE medicines SET name = ?, category = ?, stock_quantity = ?, expiry_date = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $category, $stock_quantity, $expiry_date, $status, $id]);
        redirect('medicines.php?ok=1');
    } break;

    case 'delete_medicine': {
        if (empty($_SESSION['user_id'])) redirect('login.php');
        if (($_SESSION['role'] ?? '') !== 'admin') { die('Access Denied'); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM medicines WHERE id = ?");
            $stmt->execute([$id]);
        }
        redirect('medicines.php?ok=1');
    } break;

    // FINALIZED SCHEMA: add patient + visit with full fields
    case 'add_patient_visit': {
        if (empty($_SESSION['user_id'])) redirect('login.php');

        $name = trim($_POST['name'] ?? '');
        $student_number = trim($_POST['student_number'] ?? '');
        $course_section = trim($_POST['course_section'] ?? '');
        $visit_date = $_POST['visit_date'] ?? '';
        $bp = trim($_POST['bp'] ?? '');
        $temp = isset($_POST['temp']) ? ($_POST['temp'] !== '' ? (float)$_POST['temp'] : null) : null;
        $symptom = trim($_POST['symptom'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $treatment = trim($_POST['treatment'] ?? '');
        $disposition = trim($_POST['disposition'] ?? '');

        if ($name === '' || $visit_date === '') {
            redirect('add_record.php?error=invalid');
        }

        // normalize visit datetime (combine date with current time if no time provided)
        $visit_datetime = $visit_date;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $visit_date)) {
            $visit_datetime = $visit_date . ' ' . date('H:i:s');
        }

        // Step 1: Check if patient exists by name
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        $existing = $stmt->fetch();
        if ($existing) {
            // Step 2: Update student_number and course_section
            $patient_id = (int)$existing['id'];
            $stmt = $pdo->prepare("UPDATE patients SET student_number = ?, course_section = ? WHERE id = ?");
            $stmt->execute([$student_number !== '' ? $student_number : null, $course_section !== '' ? $course_section : null, $patient_id]);
        } else {
            // Step 2 (NEW): Insert patient
            $stmt = $pdo->prepare("INSERT INTO patients (name, student_number, course_section) VALUES (?, ?, ?)");
            $stmt->execute([$name, $student_number !== '' ? $student_number : null, $course_section !== '' ? $course_section : null]);
            $patient_id = (int)$pdo->lastInsertId();
        }

        // Step 3: Insert visit (full fields)
        $stmt = $pdo->prepare("
            INSERT INTO visits (patient_id, visit_date, bp, temp, symptom, notes, treatment, disposition)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $patient_id,
            $visit_datetime,
            $bp !== '' ? $bp : null,
            $temp,
            $symptom !== '' ? $symptom : null,
            $notes !== '' ? $notes : null,
            $treatment !== '' ? $treatment : null,
            $disposition !== '' ? $disposition : null
        ]);

        redirect('patients_records.php?ok=1');
    } break;

    case 'add_visit': {
        if (empty($_SESSION['user_id'])) redirect('login.php');
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $visit_date = $_POST['visit_date'] ?? null;
        $notes = trim($_POST['notes'] ?? '');
        if ($patient_id <= 0 || !$visit_date) {
            redirect('patients_records.php?error=invalid');
        }
        // normalize to DATETIME (append current time if only date provided)
        $visit_datetime = $visit_date;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$visit_date)) {
            $visit_datetime = $visit_date . ' ' . date('H:i:s');
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO visits (patient_id, visit_date, notes) VALUES (?, ?, ?)");
            $stmt->execute([$patient_id, $visit_datetime, $notes !== '' ? $notes : null]);
            redirect('patients_records.php?ok=1');
        } catch (Throwable $e) {
            redirect('patients_records.php?error=server');
        }
    } break;

    case 'update_patient': {
        if (empty($_SESSION['user_id'])) redirect('login.php');
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || $name === '') {
            redirect('patients_records.php?error=invalid');
        }
        $stmt = $pdo->prepare("UPDATE patients SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        redirect('patients_records.php?ok=1');
    } break;

    case 'delete_patient': {
        if (empty($_SESSION['user_id'])) redirect('login.php');
        if (($_SESSION['role'] ?? '') !== 'admin') { die('Access Denied'); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM visits WHERE patient_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM patients WHERE id = ?")->execute([$id]);
        }
        redirect('patients_records.php?ok=1');
    } break;

    default:
        redirect('dashboard.php');
}
