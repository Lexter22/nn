<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar bg-white shadow-sm" id="sidebar">
    <div class="sidebar-header p-3 border-bottom">
        <h5 class="mb-0 text-primary">
            <i class="bi bi-heart-pulse me-2"></i>
            Ayisha's Clinic
        </h5>
        <small class="text-muted">Admin Panel</small>
    </div>
    
    <nav class="sidebar-nav p-3">
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo ($current_page == 'reports.php' || $current_page == 'dashboard.php') ? 'active' : ''; ?>" href="reports.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo ($current_page == 'patients_records.php') ? 'active' : ''; ?>" href="patients_records.php">
                    <i class="bi bi-folder2-open me-2"></i>Patient Records
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link <?php echo ($current_page == 'medicines.php') ? 'active' : ''; ?>" href="medicines.php">
                    <i class="bi bi-capsule me-2"></i>Medicine Inventory
                </a>
            </li>
        </ul>
        
        <hr>
        
        <div class="user-info">
            <small class="text-muted d-block">Logged in as:</small>
            <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>
            <div class="mt-2">
                <a href="login.php?action=logout" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>
</div>