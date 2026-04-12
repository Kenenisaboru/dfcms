<?php
// Temporary script to clear rate limits - DELETE AFTER USE
require_once 'config/database.php';

try {
    // Clear IP rate limits
    $pdo->exec("DELETE FROM rate_limits WHERE action = 'login'");
    
    // Reset account locks
    $pdo->exec("UPDATE users SET login_attempts = 0, locked_until = NULL");
    
    echo "<h2 style='font-family: sans-serif; color: green;'>✅ Rate limits cleared! Account locks reset!</h2>";
    echo "<p style='font-family: sans-serif;'>You can now <a href='auth/login.php'>log in</a> again.</p>";
    echo "<p style='font-family: sans-serif; color: red;'><strong>⚠️ Delete this file (clear_rate_limit.php) after use!</strong></p>";
} catch (Exception $e) {
    echo "<h2 style='font-family: sans-serif; color: red;'>Error: " . $e->getMessage() . "</h2>";
}
