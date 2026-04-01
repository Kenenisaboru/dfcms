<?php
// fix_db.php
$config_path = 'c:/xampp/phpMyAdmin/config.inc.php';
$target_path = 'c:/xampp/htdocs/dfcms/config/database.php';

if (!file_exists($config_path)) {
    echo "XAMPP config not found at $config_path\n";
    exit;
}

$config_content = file_get_contents($config_path);

// Simple regex to find password value
if (preg_match("/\['password'\]\s*=\s*'([^']*)'/", $config_content, $matches)) {
    $real_password = $matches[1];
    
    $database_php = file_get_contents($target_path);
    $database_php = preg_replace("/\\\$pass\s*=\s*'[^']*'/", "\$pass = '" . $real_password . "'", $database_php);
    
    file_put_contents($target_path, $database_php);
    echo "SUCCESS: Updated $target_path with password from config.inc.php\n";
} else {
    echo "Could not find password in $config_path\n";
}
?>
