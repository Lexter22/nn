<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (($_POST['action'] ?? '') === 'login') {
    $pdo = getDB();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username !== '' && $password !== '') {
        $stmt = $pdo->prepare("SELECT id, username, role, password FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        $ok = false;
        if ($user) {
            // Compare against plain text password column only
            $ok = hash_equals((string)$user['password'], (string)$password);
        }
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            // normalize role for consistent RBAC checks
            $_SESSION['role'] = strtolower($user['role']);
            header('Location: dashboard.php');
            exit;
        }
        header('Location: login.php?error=1');
        exit;
    } else {
        header('Location: login.php?error=1');
        exit;
    }
}

if (($_GET['action'] ?? '') === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: login.php');
    exit;
}

$err = isset($_GET['error']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title text-center mb-3">Admin Login</h5>
                        <?php if ($err): ?>
                            <div class="alert alert-danger py-2">Invalid credentials.</div>
                        <?php endif; ?>
                        <form method="post" action="login.php" autocomplete="off">
                            <input type="hidden" name="action" value="login" />
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input name="username" type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input name="password" type="password" class="form-control" required>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Login</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small class="text-muted">Ayisha's Clinic</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
