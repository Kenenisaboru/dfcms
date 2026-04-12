<?php
// api/get_complaint_history.php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$complaintId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($complaintId === 0) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

try {
    // SECURITY CHECK: Student can only see their own complaint history. 
    // Others (CR, Teacher, HOD, Lab) can see any? Maybe limit them too.
    if ($role === 'student') {
        $stmtCheck = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
        $stmtCheck->execute([$complaintId]);
        if ($stmtCheck->fetchColumn() != $userId) {
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            exit;
        }
    }

    $stmt = $pdo->prepare("SELECT h.*, u.full_name as actor_name, u.role as actor_role 
                          FROM complaint_history h 
                          JOIN users u ON h.action_by = u.id 
                          WHERE h.complaint_id = ? 
                          ORDER BY h.created_at DESC");
    $stmt->execute([$complaintId]);
    $history = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
