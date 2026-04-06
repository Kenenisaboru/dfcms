<?php
// examples/email_examples.php
// This file demonstrates how to send emails in DFCMS

require_once '../config/database.php';
require_once '../lib/EmailService.php';

// Initialize Email Service
$emailService = new EmailService();

// Example 1: Send complaint notification to user
function sendComplaintNotification($complaintId, $userId) {
    global $emailService;
    
    // Get complaint details
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name, u.email 
        FROM complaints c 
        JOIN users u ON c.student_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute(array($complaintId));
    $complaint = $stmt->fetch();
    
    if (!$complaint) return false;
    
    // Template data
    $templateData = array(
        'complaint_id' => $complaintId,
        'student_name' => $complaint['full_name'],
        'student_email' => $complaint['email'],
        'category' => $complaint['category'],
        'priority' => $complaint['priority'],
        'message' => $complaint['message'],
        'submission_date' => date('F j, Y, g:i a', strtotime($complaint['created_at'])),
        'dashboard_link' => 'http://localhost/dfcms/dashboard.php',
        'recipient_name' => $complaint['full_name']
    );
    
    // Get template
    $template = EmailConfig::getTemplate('complaint_submitted');
    
    // Read template files
    $htmlBody = file_get_contents('../templates/complaint_submitted.html');
    $textBody = file_get_contents('../templates/complaint_submitted.txt');
    
    // Send email
    return $emailService->sendEmail(
        $complaint['email'],
        $template['subject'],
        $htmlBody,
        $textBody,
        $templateData
    );
}

// Example 2: Send password reset email
function sendPasswordReset($email, $resetToken) {
    global $emailService;
    
    $templateData = array(
        'reset_link' => "http://localhost/dfcms/auth/reset_password.php?token=" . $resetToken,
        'recipient_name' => 'User'
    );
    
    $template = EmailConfig::getTemplate('password_reset');
    
    // Create simple password reset templates
    $htmlBody = '
        <h2>Password Reset Request</h2>
        <p>Click the link below to reset your password:</p>
        <p><a href="{reset_link}">Reset Password</a></p>
        <p>This link expires in 1 hour.</p>
    ';
    
    $textBody = '
        Password Reset Request
        Click here to reset your password: {reset_link}
        This link expires in 1 hour.
    ';
    
    return $emailService->sendEmail(
        $email,
        $template['subject'],
        $htmlBody,
        $textBody,
        $templateData
    );
}

// Example 3: Send complaint status update
function sendStatusUpdate($complaintId, $newStatus, $updatedBy) {
    global $emailService;
    global $pdo;
    
    // Get complaint and user details
    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name, u.email 
        FROM complaints c 
        JOIN users u ON c.student_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute(array($complaintId));
    $complaint = $stmt->fetch();
    
    if (!$complaint) return false;
    
    $templateData = array(
        'complaint_id' => $complaintId,
        'status' => $newStatus,
        'updated_by' => $updatedBy,
        'comments' => 'Your complaint status has been updated',
        'recipient_name' => $complaint['full_name'],
        'dashboard_link' => 'http://localhost/dfcms/dashboard.php'
    );
    
    $template = EmailConfig::getTemplate('complaint_updated');
    
    // Create status update template
    $htmlBody = '
        <h2>Complaint Status Update</h2>
        <p>Your complaint #{complaint_id} status has been updated to: <strong>{status}</strong></p>
        <p><strong>Updated by:</strong> {updated_by}</p>
        <p><strong>Comments:</strong> {comments}</p>
        <p><a href="{dashboard_link}">View in Dashboard</a></p>
    ';
    
    $textBody = '
        Complaint #{complaint_id} status updated to: {status}
        Updated by: {updated_by}
        Comments: {comments}
        Dashboard: {dashboard_link}
    ';
    
    return $emailService->sendEmail(
        $complaint['email'],
        $template['subject'],
        $htmlBody,
        $textBody,
        $templateData
    );
}

// Example 4: Queue multiple emails (bulk processing)
function queueBulkNotifications($complaintId, $recipients) {
    global $emailService;
    
    foreach ($recipients as $userId) {
        $emailService->queueEmail(
            'user@example.com', // This would be fetched from database
            'New Complaint Assigned - #' . $complaintId,
            '<h3>New Complaint Assigned</h3><p>Complaint #' . $complaintId . ' has been assigned to you.</p>',
            'New Complaint #' . $complaintId . ' assigned to you.',
            array('complaint_id' => $complaintId),
            'high'
        );
    }
    
    return true;
}

// Example 5: Process email queue (run via cron job)
function processEmailQueue() {
    global $emailService;
    return $emailService->processQueue(20); // Process 20 emails at a time
}

// Example 6: Send notification based on user preferences
function sendUserNotification($userId, $templateType, $data) {
    global $emailService;
    return $emailService->sendNotification($userId, $templateType, $data);
}

// Usage Examples:
echo "<h1>DFCMS Email Examples</h1>";

// Example 1: Send complaint notification
if (isset($_GET['send_complaint'])) {
    $result = sendComplaintNotification(1, 1);
    echo "<p>Complaint notification sent: " . ($result ? "Success" : "Failed") . "</p>";
}

// Example 2: Send password reset
if (isset($_GET['send_reset'])) {
    $result = sendPasswordReset('user@example.com', 'reset_token_123');
    echo "<p>Password reset sent: " . ($result ? "Success" : "Failed") . "</p>";
}

// Example 3: Send status update
if (isset($_GET['send_update'])) {
    $result = sendStatusUpdate(1, 'In-Progress', 'John Doe');
    echo "<p>Status update sent: " . ($result ? "Success" : "Failed") . "</p>";
}

// Example 4: Process queue
if (isset($_GET['process_queue'])) {
    $processed = processEmailQueue();
    echo "<p>Processed $processed emails from queue</p>";
}

// Display email statistics
$stats = $emailService->getEmailStats();
echo "<h2>Email Statistics</h2>";
echo "<p>Total: {$stats['total']}, Sent: {$stats['sent']}, Failed: {$stats['failed']}</p>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>DFCMS Email Examples</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .btn { padding: 10px 20px; margin: 5px; background: #10b981; color: white; text-decoration: none; border-radius: 5px; }
        .stats { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>DFCMS Email System Examples</h1>
    
    <div class="actions">
        <a href="?send_complaint=1" class="btn">Send Complaint Notification</a>
        <a href="?send_reset=1" class="btn">Send Password Reset</a>
        <a href="?send_update=1" class="btn">Send Status Update</a>
        <a href="?process_queue=1" class="btn">Process Email Queue</a>
    </div>
    
    <div class="stats">
        <h3>Quick Integration Guide</h3>
        <h4>1. Basic Email Send:</h4>
        <pre>
$emailService = new EmailService();
$result = $emailService->sendEmail(
    'recipient@example.com',
    'Subject',
    'HTML Body',
    'Text Body',
    array('template_var' => 'value')
);
        </pre>
        
        <h4>2. Queue Email:</h4>
        <pre>
$emailService->queueEmail(
    'recipient@example.com',
    'Subject',
    'HTML Body',
    'Text Body',
    array('template_var' => 'value'),
    'high' // priority
);
        </pre>
        
        <h4>3. Send Notification:</h4>
        <pre>
$emailService->sendNotification(
    $userId, 
    'complaint_submitted', 
    array('complaint_id' => 123)
);
        </pre>
    </div>
</body>
</html>
