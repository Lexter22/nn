<?php
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../includes/db.php';
$pdo = getDB();

$errMsg = null;
$criticalMeds = [];
$visitLogs = [];

// Dashboard stats (combined here)
$totalPatients = 0;
$todaysVisits = 0;
$lowStockCount = 0;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients");
    $stmt->execute();
    $totalPatients = (int)$stmt->fetchColumn();

    // dynamic stock column
    $stockCol = columnExists($pdo, 'medicines', 'stock_quantity') ? 'stock_quantity' : (columnExists($pdo, 'medicines', 'stock') ? 'stock' : null);
    if ($stockCol) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM medicines WHERE status = 'low' OR ($stockCol BETWEEN 1 AND 9)");
        $stmt->execute();
        $lowStockCount = (int)$stmt->fetchColumn();
    }

    if (function_exists('tableExists') && tableExists($pdo, 'visits')) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM visits WHERE DATE(visit_date) = CURDATE()");
        $stmt->execute();
        $todaysVisits = (int)$stmt->fetchColumn();
    }
} catch (Throwable $e) {
    $errMsg = ($errMsg ? $errMsg . ' ' : '') . 'Unable to load stats.';
}

try {
    $stockCol = columnExists($pdo, 'medicines', 'stock_quantity') ? 'stock_quantity' : (columnExists($pdo, 'medicines', 'stock') ? 'stock' : null);
    if ($stockCol) {
        $stmtLow = $pdo->prepare("SELECT name, $stockCol AS stock_quantity, status FROM medicines WHERE status IN ('low','out') ORDER BY $stockCol ASC");
        $stmtLow->execute();
        $criticalMeds = $stmtLow->fetchAll();
    } else {
        $errMsg = 'Stock column not found in medicines table.';
        $criticalMeds = [];
    }
} catch (Throwable $e) {
    $errMsg = 'Unable to load critical inventory.';
}

try {
    $hasCourse = columnExists($pdo, 'patients', 'course_section');
    $courseSelect = $hasCourse ? 'p.course_section' : "'' AS course_section";

    $stmtVisits = $pdo->prepare("
        SELECT v.visit_date, p.name AS student_name, $courseSelect, v.symptom, v.treatment, v.disposition
        FROM visits v
        JOIN patients p ON p.id = v.patient_id
        ORDER BY v.visit_date DESC
        LIMIT 50
    ");
    $stmtVisits->execute();
    $visitLogs = $stmtVisits->fetchAll();
} catch (Throwable $e) {
    $errMsg = ($errMsg ? $errMsg . ' ' : '') . 'Unable to load visit logs.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard & Reports - Ayisha's Clinic</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-3 col-xl-2">
            <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        </div>
        <div class="col-12 col-lg-9 col-xl-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Dashboard & Reports</h4>
                <button class="btn btn-outline-secondary" onclick="window.print()">Print Report</button>
            </div>

            <?php if ($errMsg): ?>
                <div class="alert alert-warning"><?php echo htmlspecialchars($errMsg); ?></div>
            <?php endif; ?>

            <!-- Stats row -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people display-6 text-primary me-3"></i>
                                <div>
                                    <div class="text-muted">Total Patients</div>
                                    <div class="h4 mb-0"><?php echo $totalPatients; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar-check display-6 text-success me-3"></i>
                                <div>
                                    <div class="text-muted">Today's Visits</div>
                                    <div class="h4 mb-0"><?php echo $todaysVisits; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-capsule display-6 text-danger me-3"></i>
                                <div>
                                    <div class="text-muted">Low Stock Medicines</div>
                                    <div class="h4 mb-0"><?php echo $lowStockCount; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 1: Critical Inventory -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">Critical Inventory (Low / Out)</div>
                <div class="card-body">
                    <?php if (empty($criticalMeds)): ?>
                        <p class="text-muted mb-0">No low or out-of-stock medicines.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead><tr><th>Medicine Name</th><th>Current Stock</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($criticalMeds as $m): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($m['name']); ?></td>
                                        <td><?php echo (int)$m['stock_quantity']; ?></td>
                                        <td><span class="badge bg-<?php echo ($m['status'] === 'out' ? 'secondary' : 'danger'); ?>"><?php echo htmlspecialchars(ucfirst($m['status'])); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 2: Patient Visit Log -->
            <div class="card shadow-sm">
                <div class="card-header">Patient Visit Log (Latest 50)</div>
                <div class="card-body">
                    <?php if (empty($visitLogs)): ?>
                        <p class="text-muted mb-0">No recent visits.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Student Name</th>
                                        <th>Section</th>
                                        <th>Complaint</th>
                                        <th>Treatment</th>
                                        <th>Disposition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($visitLogs as $v): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($v['visit_date']); ?></td>
                                            <td><?php echo htmlspecialchars($v['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($v['course_section'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($v['symptom'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($v['treatment'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($v['disposition'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
