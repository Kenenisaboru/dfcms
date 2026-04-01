<?php
// debug_env.php
echo "Current File: " . __FILE__ . "<br>";
echo "User: " . get_current_user() . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

$host = 'localhost';
$db   = 'dfcms';
$user = 'root';
$pass = '12345678';

echo "Attempting connection to $host with user $user and password provided...<br>";

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    // Force testing both with and without password explicitly in the constructor
    $pdo = new PDO($dsn, $user, $pass);
    echo "SUCCESS: Connection established via PDO.<br>";
    
    $stmt = $pdo->query("SELECT USER(), CURRENT_USER()");
    $row = $stmt->fetch();
    echo "Connected as: " . $row[0] . " (resolved as " . $row[1] . ")<br>";

} catch (PDOException $e) {
    echo "PDO ERROR: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}

// Check if mysqli works as a fallback test
echo "<br>Testing mysqli...<br>";
$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    echo "mysqli ERROR: " . $mysqli->connect_error . " (" . $mysqli->connect_errno . ")<br>";
} else {
    echo "SUCCESS: Connection established via mysqli.<br>";
}
?>
