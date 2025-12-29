<?php
require_once 'auth_check.php';
$page_title = isset($page_title) ? $page_title : 'Admin - Ayisha\'s Clinic';
require_once '../includes/header.php';
?>

<style>
.admin-layout {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 250px;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
}

.main-content {
    margin-left: 250px;
    flex: 1;
    background-color: #f8f9fa;
    min-height: 100vh;
}

.nav-link {
    color: #6c757d;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background-color: #e9ecef;
    color: #495057;
}

.nav-link.active {
    background-color: #667eea;
    color: white;
}

.content-header {
    background: white;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
    }
}
</style>

<div class="admin-layout">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container-fluid p-4">