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
$port = db_env('DB_PORT', '3306');
$db = db_env('DB_NAME', 'dfcms');
$user = db_env('DB_USER', '');
$pass = db_env('DB_PASS', '');

$appEnv = strtolower((string) db_env('APP_ENV', 'development'));
$isProduction = $appEnv === 'production';
$isDebug = strtolower((string) db_env('APP_DEBUG', 'false')) === 'true';

// #region agent log
if (!function_exists('dfcms_db_debug_log')) {
    function dfcms_db_debug_log($runId, $hypothesisId, $location, $message, $data = array()) {
        $payload = array(
            'sessionId' => '78993d',
            'runId' => (string) $runId,
            'hypothesisId' => (string) $hypothesisId,
            'location' => (string) $location,
            'message' => (string) $message,
            'data' => $data,
            'timestamp' => round(microtime(true) * 1000)
        );
        @file_put_contents(dirname(__DIR__) . '/debug-78993d.log', json_encode($payload, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
    }
}
dfcms_db_debug_log('pre-fix', 'H2', 'config/database.php', 'db_config_loaded', array('env' => $appEnv, 'userConfigured' => $user !== '' ? 1 : 0));
// #endregion

if ($user === '') {
    if ($isProduction) {
        http_response_code(500);
        error_log('Database configuration error: DB_USER is required in production.');
        exit('Service temporarily unavailable.');
    }
    $user = 'root';
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
);

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // #region agent log
    dfcms_db_debug_log('pre-fix', 'H2', 'config/database.php', 'db_connect_success', array('pdoReady' => $pdo ? 1 : 0));
    // #endregion
} catch (PDOException $e) {
    $pdo = null;
    error_log('Database Connection Error: ' . $e->getMessage());
    // #region agent log
    dfcms_db_debug_log('pre-fix', 'H2', 'config/database.php', 'db_connect_failure', array('errorClass' => get_class($e)));
    // #endregion
    if ($isDebug && !$isProduction) {
        exit('Database connection failed.');
    }
}
?>