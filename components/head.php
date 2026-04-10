<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DFCMS - Digital Feedback & Complaint Management System for Universities">
    <title><?php echo isset($page_title) ? $page_title . " | DFCMS" : "DFCMS - Digital Feedback & Complaint Management"; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Modern Design System -->
    <link href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/css/dfcms-modern.css" rel="stylesheet">
    
    <!-- Page-specific CSS -->
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
