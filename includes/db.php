<?php
function getDB(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Update these to your real database credentials
    $host = 'localhost';
    $dbname = 'clinic_database'; // ensure this matches your DB
    $username = 'root';    // your MySQL username
    $password = '';        // your MySQL password

    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    return $pdo;
}
