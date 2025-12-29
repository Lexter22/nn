<?php
function getDB(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = 'localhost';
    $primaryDb = 'school_clinic_database';
    $fallbackDb = 'clinic_database';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Try primary DB
    $dsn = "mysql:host=$host;dbname=$primaryDb;charset=$charset";
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Fallback to legacy DB if primary missing
        try {
            $dsn2 = "mysql:host=$host;dbname=$fallbackDb;charset=$charset";
            $pdo = new PDO($dsn2, $username, $password, $options);
            return $pdo;
        } catch (PDOException $e2) {
            http_response_code(500);
            exit('Database connection error.');
        }
    }
}

/**
 * Check if a table exists in the current database.
 */
function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

/**
 * Check if a column exists in a table.
 */
function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetchColumn();
}
