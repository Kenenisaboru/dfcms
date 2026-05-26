<?php
require_once 'config/config.php';

echo "<h2>Current Users in Database</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";

$stmt = $pdo->query("SELECT id, full_name, email, role FROM users");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['full_name']}</td>";
    echo "<td>{$user['email']}</td>";
    echo "<td><strong>{$user['role']}</strong></td>";
    echo "</tr>";
}

echo "</table>";

if (isset($_GET['update_id']) && isset($_GET['new_role'])) {
    $id = (int)$_GET['update_id'];
    $newRole = $_GET['new_role'];
    
    $allowedRoles = ['student', 'cr', 'teacher', 'lab_assistant', 'hod'];
    if (in_array($newRole, $allowedRoles)) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $id]);
        echo "<p style='color: green; font-weight: bold;'>User ID $id updated to role: $newRole</p>";
        echo "<p><a href='fix_user_role.php'>Refresh to see changes</a></p>";
    } else {
        echo "<p style='color: red;'>Invalid role specified.</p>";
    }
} else {
    echo "<h3>Update a user's role:</h3>";
    echo "<p>Use this URL format to update a user: <code>fix_user_role.php?update_id=USER_ID&new_role=student</code></p>";
    echo "<p>Allowed roles: student, cr, teacher, lab_assistant, hod</p>";
    
    echo "<h3>Quick Update Links:</h3>";
    foreach ($users as $user) {
        echo "<p>";
        echo "<strong>{$user['full_name']}</strong> (Current: {$user['role']}) - ";
        echo "<a href='fix_user_role.php?update_id={$user['id']}&new_role=student'>Make Student</a> | ";
        echo "<a href='fix_user_role.php?update_id={$user['id']}&new_role=teacher'>Make Teacher</a> | ";
        echo "<a href='fix_user_role.php?update_id={$user['id']}&new_role=cr'>Make CR</a>";
        echo "</p>";
    }
}
?>
