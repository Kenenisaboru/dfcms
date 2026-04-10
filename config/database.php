<?php
// config/database.php
// config/database.php
// Support for environment variables (if available) - Fallback to defaults
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'dfcms';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Better for debugging
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    if (getenv('APP_DEBUG') === 'true') {
        die("Connection Failed: " . $e->getMessage());
    }
    $pdo = null;
    error_log("Database Connection Error: " . $e->getMessage());
}
?>