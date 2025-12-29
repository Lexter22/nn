<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../includes/db.php';
$pdo = getDB();
$role = strtolower($_SESSION['role'] ?? 'nurse');

$errMsg = null;

// Detect visits table
$hasVisits = function_exists('tableExists') ? tableExists($pdo, 'visits') : true;

$q = trim($_GET['q'] ?? '');
$params = [];
if ($q !== '') { $params[] = "%$q%"; }

try {
    if ($hasVisits) {
        // Latest visit per patient (essential fields only)
        $sql = "
            SELECT 
                p.id,
                p.name,
                p.student_number,
                lv.visit_date   AS last_visit_date,
                lv.symptom      AS last_symptom,
                lv.treatment    AS last_treatment,
                lv.disposition  AS last_disposition
            FROM patients p
            LEFT JOIN (
                SELECT v.patient_id, v.visit_date, v.symptom, v.treatment, v.disposition
                FROM visits v
                INNER JOIN (
                    SELECT patient_id, MAX(visit_date) AS max_visit
                    FROM visits
                    GROUP BY patient_id
                ) mv ON mv.patient_id = v.patient_id AND v.visit_date = mv.max_visit
            ) lv ON lv.patient_id = p.id
            " . ($q !== '' ? "WHERE p.name LIKE ?" : "") . "
            ORDER BY p.name
        ";
    } else {
        // Fallback when visits table is missing
        $sql = "
            SELECT p.id, p.name, p.student_number
            FROM patients p
            " . ($q !== '' ? "WHERE p.name LIKE ?" : "") . "
            ORDER BY p.name
        ";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $patients = $stmt->fetchAll();

    if (!$hasVisits) {
        foreach ($patients as &$p) {
            $p['last_visit_date'] = null;
            $p['last_symptom'] = null;
            $p['last_treatment'] = null;
            $p['last_disposition'] = null;
        }
        unset($p);
    }
} catch (Throwable $e) {
    // Graceful fallback on schema mismatch
    $errMsg = 'Unable to load patient records. Please verify table and column names.';
    $patients = [];
    try {
        $fallback = "
            SELECT p.id, p.name, p.student_number
            FROM patients p
            " . ($q !== '' ? "WHERE p.name LIKE ?" : "") . "
            ORDER BY p.name
        ";
        $stmt = $pdo->prepare($fallback);
        $stmt->execute($params);
        $patients = $stmt->fetchAll();
        foreach ($patients as &$p) {
            $p['last_visit_date'] = null;
            $p['last_symptom'] = null;
            $p['last_treatment'] = null;
            $p['last_disposition'] = null;
        }
        unset($p);
    } catch (Throwable $e2) {
        // Keep list empty if even fallback fails
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Patient Records - Ayisha's Clinic</title>
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
                <h4 class="mb-0">Patient Records</h4>
                <a class="btn btn-primary" href="add_record.php">Add New Patient & Visit</a>
            </div>

            <?php if ($errMsg): ?>
                <div class="alert alert-warning"><?php echo htmlspecialchars($errMsg); ?></div>
            <?php endif; ?>

            <form class="row g-2 mb-3" method="get">
                <div class="col-9 col-md-4">
                    <input class="form-control" name="q" placeholder="Search patient by name" value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <div class="col-3 col-md-2">
                    <button class="btn btn-outline-secondary w-100">Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Student #</th>
                            <th>Last Visit</th>
                            <th>Complaint</th>
                            <th>Treatment</th>
                            <th>Disposition</th>
                            <th style="width:240px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['student_number'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($p['last_visit_date'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($p['last_symptom'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($p['last_treatment'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($p['last_disposition'] ?? ''); ?></td>
                            <td>
                                <button
                                    class="btn btn-sm btn-outline-primary me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#visitModal"
                                    data-patient-id="<?php echo (int)$p['id']; ?>"
                                    data-patient-name="<?php echo htmlspecialchars($p['name']); ?>"
                                >Add Visit</button>

                                <button
                                    class="btn btn-sm btn-outline-secondary me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editPatientModal"
                                    data-patient-id="<?php echo (int)$p['id']; ?>"
                                    data-patient-name="<?php echo htmlspecialchars($p['name']); ?>"
                                >Edit</button>

                                <?php if ($role === 'admin'): ?>
                                <form method="post" action="process.php?action=delete_patient" class="d-inline" onsubmit="return confirm('Delete this patient and all visits?');">
                                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Visit Modal -->
            <div class="modal fade" id="visitModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form method="post" class="modal-content" action="process.php?action=add_visit">
                  <div class="modal-header">
                    <h5 class="modal-title">Add Visit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="patient_id" id="visit-patient-id">
                    <div class="mb-2"><strong id="visit-patient-name"></strong></div>
                    <div class="mb-3">
                        <label class="form-label">Visit Date</label>
                        <input name="visit_date" type="date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Edit Patient Modal -->
            <div class="modal fade" id="editPatientModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form method="post" class="modal-content" action="process.php?action=update_patient">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id" id="edit-patient-id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input name="name" id="edit-patient-name" type="text" class="form-control" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                  </div>
                </form>
              </div>
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const visitModal = document.getElementById('visitModal');
visitModal.addEventListener('show.bs.modal', evt => {
  const btn = evt.relatedTarget;
  document.getElementById('visit-patient-id').value = btn.dataset.patientId;
  document.getElementById('visit-patient-name').textContent = btn.dataset.patientName;
});

const editModal = document.getElementById('editPatientModal');
editModal.addEventListener('show.bs.modal', evt => {
  const btn = evt.relatedTarget;
  document.getElementById('edit-patient-id').value = btn.dataset.patientId;
  document.getElementById('edit-patient-name').value = btn.dataset.patientName;
});
</script>
</body>
</html>
