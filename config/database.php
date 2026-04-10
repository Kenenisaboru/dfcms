<?php
// config/database.php
if (!function_exists('db_env')) {
    function db_env($key, $default = null) {
        $value = getenv($key);
        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        return $value;
    }
}

$host = db_env('DB_HOST', '127.0.0.1');
$db = db_env('DB_NAME', 'dfcms');
$user = db_env('DB_USER', '');
$pass = db_env('DB_PASS', '');

$appEnv = strtolower((string) db_env('APP_ENV', 'development'));
$isProduction = $appEnv === 'production';
$isDebug = strtolower((string) db_env('APP_DEBUG', 'false')) === 'true';

if ($user === '') {
    if ($isProduction) {
        http_response_code(500);
        error_log('Database configuration error: DB_USER is required in production.');
        exit('Service temporarily unavailable.');
    }
    $user = 'root';
}

$dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
);

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    $pdo = null;
    error_log('Database Connection Error: ' . $e->getMessage());
    if ($isDebug && !$isProduction) {
        exit('Database connection failed.');
    }
}
?>