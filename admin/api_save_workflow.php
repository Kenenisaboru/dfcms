<?php
// admin/api_save_workflow.php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], array('hod', 'admin'))) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

CSRF::validateRequest(true);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['steps']) || !is_array($data['steps'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Clear existing workflow
    $pdo->exec("DELETE FROM workflow_steps");

    // Insert new workflow steps
    $stmt = $pdo->prepare("INSERT INTO workflow_steps (role_key, step_order, created_at) VALUES (?, ?, NOW())");
    
    foreach ($data['steps'] as $index => $step) {
        $stmt->execute([$step['role_key'], $step['step_order']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Workflow save failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save workflow.']);
}
?>
