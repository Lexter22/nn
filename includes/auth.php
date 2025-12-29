<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function loginUser(PDO $pdo, string $username, string $password): bool {
    try {
        // Try username; fallback to email if present
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            try {
                $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$username]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e) { /* email may not exist; ignore */ }
        }
        if (!$row) return false;

        // Accept plain or hashed passwords (password or password_hash)
        $stored = (string)($row['password'] ?? ($row['password_hash'] ?? ''));
        $ok = ($stored !== '' && ($password === $stored || password_verify($password, $stored)));
        if (!$ok) return false;

        // Map role to admin/nurse; default admin for username 'admin', else nurse
        $rawRole = (string)($row['role'] ?? '');
        $role = strtolower($rawRole !== '' ? $rawRole : (($row['username'] ?? '') === 'admin' ? 'admin' : 'nurse'));
        if (!in_array($role, ['admin','nurse'], true)) $role = 'nurse';

        $_SESSION['user'] = [
            'id'       => (int)($row['id'] ?? 0),
            'username' => (string)($row['username'] ?? $username),
            'role'     => $role,
        ];
        // legacy keys for compatibility
        $_SESSION['user_id'] = (int)($_SESSION['user']['id']);
        $_SESSION['username'] = (string)($_SESSION['user']['username']);
        session_regenerate_id(true);

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function logoutUser(): void {
    $_SESSION = [];
    if (session_id()) session_destroy();
}

function requireUser(array $roles = []): void {
    $me = currentUser();
    if (!$me) {
        $redirect = $_SERVER['REQUEST_URI'] ?? 'admin/medicines.php';
        header('Location: admin/login.php?redirect=' . urlencode($redirect));
        exit;
    }
    if ($roles && !in_array($me['role'], $roles, true)) {
        header('Location: admin/medicines.php?error=unauthorized');
        exit;
    }
}
