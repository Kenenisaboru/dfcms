<?php
global $pdo;
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'knowledge_base'");
    $exists = (bool) $stmt->fetch();
    if ($exists) {
        echo "Table knowledge_base EXISTS\n";
    } else {
        echo "Table knowledge_base MISSING\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
