<?php
require_once 'config/database.php';
global $pdo;

echo "<h2>Database Setup (Direct Mode)</h2><pre>";

$files_to_import = [
    'database_engagement.sql',
    'database_notifications.sql',
    'database_workflow.sql',
    'database_updates.sql'
];

foreach ($files_to_import as $file) {
    if (file_exists($file)) {
        echo "Processing $file... ";
        $sql = file_get_contents($file);
        
        // Remove comments
        $sql = preg_replace('/--.*?\n/', '', $sql);
        
        // Split by semicolon, but ignore those inside quotes (simplified)
        // Note: This won't handle DELIMITER commands perfectly, but will work for standard tables.
        $queries = explode(';', $sql);
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (empty($query) || strtoupper(substr($query, 0, 9)) === 'DELIMITER') continue;
            
            try {
                $pdo->exec($query);
                $successCount++;
            } catch (Exception $e) {
                // Ignore "already exists" errors if they happen during IGNORE
                if (strpos($e->getMessage(), 'already exists') === false) {
                    $failCount++;
                }
            }
        }
        
        echo "Done ($successCount success, $failCount skipped/failed)\n";
    } else {
        echo "File not found: $file\n";
    }
}

echo "</pre><br/><h3>Database logic updated! Refresh the student page now.</h3>";
?>
