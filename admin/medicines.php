<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../includes/db.php';
$pdo = getDB();
$role = strtolower($_SESSION['role'] ?? 'nurse');

$filter = $_GET['filter'] ?? 'all';
$query = "SELECT id, name, category, stock_quantity, expiry_date, status FROM medicines";
if ($filter === 'low') {
    $query .= " WHERE status = 'low'";
}
$query .= " ORDER BY name";
$stmt = $pdo->prepare($query);
$stmt->execute();
$meds = $stmt->fetchAll();

function statusBadge(array $m) {
    $status = $m['status'];
    if ($status === 'low') return '<span class="badge bg-danger">Low</span>';
    if ($status === 'out') return '<span class="badge bg-secondary">Out</span>';
    return '<span class="badge bg-success">Available</span>';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Medicine Inventory - Ayisha's Clinic</title>
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
                <div class="d-flex align-items-center">
                    <h4 class="mb-0 me-3">Medicine Inventory</h4>
                    <a class="btn btn-outline-secondary btn-sm me-2" href="?filter=all">All</a>
                    <a class="btn btn-outline-warning btn-sm me-2" href="?filter=low">Low/Out</a>
                </div>
                <div>
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#medicineModal">Add Medicine</button>
                    <a class="btn btn-outline-secondary" href="reports.php?mode=low&export=1" target="_blank">Export Low Stock PDF</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($meds as $m): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m['name']); ?></td>
                            <td><?php echo htmlspecialchars($m['category']); ?></td>
                            <td><?php echo (int)$m['stock_quantity']; ?></td>
                            <td><?php echo htmlspecialchars($m['expiry_date']); ?></td>
                            <td><?php echo statusBadge($m); ?></td>
                            <td>
                                <button
                                    class="btn btn-sm btn-outline-primary me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#medicineModal"
                                    data-id="<?php echo (int)$m['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($m['name']); ?>"
                                    data-category="<?php echo htmlspecialchars($m['category']); ?>"
                                    data-stock="<?php echo (int)$m['stock_quantity']; ?>"
                                    data-expiry="<?php echo htmlspecialchars($m['expiry_date']); ?>"
                                >Edit</button>
                                <?php if ($role === 'admin'): ?>
                                    <form method="post" action="process.php?action=delete_medicine" class="d-inline" onsubmit="return confirm('Delete this medicine?');">
                                        <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add/Edit Modal -->
            <div class="modal fade" id="medicineModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form method="post" class="modal-content" id="medicineForm" action="process.php?action=add_medicine">
                  <div class="modal-header">
                    <h5 class="modal-title">Medicine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                        <input type="hidden" name="id" id="med-id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input name="name" id="med-name" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" id="med-category" class="form-select" required>
                                <option value="" disabled selected>Select category</option>
                                <option value="Analgesic">Analgesic</option>
                                <option value="Antibiotic">Antibiotic</option>
                                <option value="Antihistamine">Antihistamine</option>
                                <option value="Antipyretic">Antipyretic</option>
                                <option value="Antiseptic">Antiseptic</option>
                                <option value="Vitamin">Vitamin</option>
                                <option value="Supplement">Supplement</option>
                                <option value="Antacid">Antacid</option>
                                <option value="Cough Suppressant">Cough Suppressant</option>
                                <option value="Decongestant">Decongestant</option>
                                <option value="other">Other</option>
                            </select>
                            <input name="category_other" id="med-category-other" type="text" class="form-control mt-2 d-none" placeholder="Enter category" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input name="stock_quantity" id="med-stock" type="number" min="0" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input name="expiry_date" id="med-expiry" type="date" class="form-control" required>
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
const modalEl = document.getElementById('medicineModal');
const catSelect = document.getElementById('med-category');
const catOther = document.getElementById('med-category-other');

function toggleOtherCategory() {
  if (catSelect.value === 'other') {
    catOther.classList.remove('d-none');
    catOther.required = true;
  } else {
    catOther.classList.add('d-none');
    catOther.required = false;
    catOther.value = '';
  }
}
catSelect.addEventListener('change', toggleOtherCategory);

modalEl.addEventListener('show.bs.modal', event => {
  const btn = event.relatedTarget;
  const form = document.getElementById('medicineForm');
  if (btn && btn.dataset.id) {
    // Edit mode
    form.action = 'process.php?action=update_medicine';
    document.getElementById('med-id').value = btn.dataset.id;
    document.getElementById('med-name').value = btn.dataset.name;
    // Try to select matching category; if not found, use 'Other' and fill custom
    const catValue = btn.dataset.category || '';
    const options = Array.from(catSelect.options).map(o => o.value);
    if (options.includes(catValue)) {
      catSelect.value = catValue;
      catOther.value = '';
    } else {
      catSelect.value = 'other';
      catOther.value = catValue;
    }
    toggleOtherCategory();
    document.getElementById('med-stock').value = btn.dataset.stock;
    document.getElementById('med-expiry').value = btn.dataset.expiry;
  } else {
    // Add mode
    form.action = 'process.php?action=add_medicine';
    document.getElementById('med-id').value = '';
    document.getElementById('med-name').value = '';
    catSelect.value = '';
    catOther.value = '';
    toggleOtherCategory();
    document.getElementById('med-stock').value = '';
    document.getElementById('med-expiry').value = '';
  }
});
</script>
</body>
</html>
