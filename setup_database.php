<?php
$host = '127.0.0.1';
$port = '3309';
$user = 'root';
$pass = ''; // No password for XAMPP default
$db = 'dfcms';

echo "<h2>Database Setup</h2><pre>";

$mysql_path = 'c:\xampp\mysql\bin\mysql.exe';
if (!file_exists($mysql_path)) {
    echo "MySQL executable not found at $mysql_path. Make sure XAMPP is installed there.\n";
    exit;
}

$files_to_import = [
    'database_engagement.sql',
    'database_notifications.sql',
    'database_workflow.sql',
    'database_updates.sql'
];

foreach ($files_to_import as $file) {
    $full_path = realpath($file);
    if ($full_path && file_exists($full_path)) {
        echo "Importing $file... ";
        
        // Execute mysql command to import the file
        $command = "\"$mysql_path\" -h $host -P $port -u $user $db < \"$full_path\" 2>&1";
        $output = [];
        $return_var = 0;
        
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            echo "SUCCESS\n";
        } else {
            echo "FAILED (Error Code: $return_var)\n";
            echo "Output:\n" . implode("\n", $output) . "\n";
        }
    } else {
        echo "File not found: $file\n";
    }
}

echo "</pre><br/><h3>Done! You can now resume using the platform.</h3>";
?>
