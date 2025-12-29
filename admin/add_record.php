<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add Record - Ayisha's Clinic</title>
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
            <h4 class="mb-3">Add Patient & Visit Record</h4>
            <form method="post" action="process.php" class="row g-3">
                <input type="hidden" name="action" value="add_patient_visit">
                <div class="col-12 col-md-6">
                    <label class="form-label">Name</label>
                    <input name="name" type="text" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Visit Date</label>
                    <input name="visit_date" type="date" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Student Number</label>
                    <input name="student_number" type="text" class="form-control">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">BP</label>
                    <input name="bp" type="text" class="form-control" placeholder="e.g., 110/70">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Temp (°C)</label>
                    <input name="temp" type="number" step="0.1" class="form-control" placeholder="e.g., 37.2">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Disposition</label>
                    <input name="disposition" type="text" class="form-control" placeholder="e.g., Back to Class">
                </div>
                <div class="col-12">
                    <label class="form-label">Chief Complaint (Symptom)</label>
                    <textarea name="symptom" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Assessment/Notes</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Treatment/Plan</label>
                    <textarea name="treatment" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">Save</button>
                    <a href="patients_records.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
