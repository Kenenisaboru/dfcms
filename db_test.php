<?php
// db_test.php
$host = 'localhost';
$port = '3309'; // Updated port
$db   = 'dfcms';
$user = 'root';
$passwords = ['', '12345', '12345678', 'root', 'mysql', 'admin'];

foreach ($passwords as $p) {
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $p);
        echo "SUCCESS with password: [" . $p . "] on port $port\n";
        exit;
    } catch (PDOException $e) {
        echo "FAILED with password: [" . $p . "] - " . $e->getMessage() . "\n";
    }
}
?>

