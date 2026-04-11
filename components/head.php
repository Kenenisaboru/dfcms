<?php
// components/head.php - Premium Head Component
$page_title = isset($page_title) ? $page_title : 'DFCMS';
$base_path = isset($base_path) ? $base_path : '';
$extra_css = isset($extra_css) ? $extra_css : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DFCMS - Ultra-Premium Digital Feedback & Complaint Management System for Universities">
    <meta name="theme-color" content="#0c0d0e">
    <title><?php echo htmlspecialchars($page_title); ?> | DFCMS</title>
    
    <!-- Premium Font Stack -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Master Design System -->
    <link href="<?php echo $base_path; ?>assets/css/dfcms-modern.css" rel="stylesheet">
    <?php echo $extra_css; ?>
</head>
