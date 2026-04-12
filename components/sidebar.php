<?php
// components/sidebar.php - Premium Sidebar Component v4.0
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Premium Navy Sidebar -->
<div class="sidebar-container" id="sidebar-container">
    <div class="sidebar-sticky">
        <!-- Logo -->
        <a href="<?php echo base_url('dashboard.php'); ?>" class="sidebar-logo">
            <i class="bi bi-shield-check"></i>
            <span>DFCMS</span>
        </a>

        <div class="sidebar-divider"></div>

        <!-- Navigation -->
        <div class="sidebar">
            <h6 class="nav-heading">Main Menu</h6>
            <nav class="nav flex-column">
                <?php 
                $dashboard_url = base_url('dashboard.php');
                if ($current_role === 'admin') {
                    $dashboard_url = base_url('admin/dashboard.php');
                }
                ?>
                <a href="<?php echo $dashboard_url; ?>" 
                   class="nav-link <?php echo (strpos($current_page, 'dashboard') !== false) ? 'active' : ''; ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>

                <?php if ($current_role === 'student'): ?>
                    <a href="<?php echo base_url('student/submit_complaint.php'); ?>" 
                       class="nav-link <?php echo strpos($current_page, 'submit') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Submit Complaint</span>
                    </a>

                    <a href="<?php echo base_url('student/tracker.php'); ?>" 
                       class="nav-link <?php echo strpos($current_page, 'tracker') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-list-task"></i>
                        <span>My Complaints</span>
                    </a>

                    <a href="<?php echo base_url('student/messages.php'); ?>" 
                       class="nav-link <?php echo strpos($current_page, 'messages') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span>Messages</span>
                    </a>
                <?php endif; ?>

                <?php if ($current_role === 'admin'): ?>
                    <div class="sidebar-divider"></div>
                    <h6 class="nav-heading">Administration</h6>
                    <a href="<?php echo base_url('admin/dashboard.php'); ?>" 
                       class="nav-link <?php echo $current_page === 'dashboard.php' && strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>Admin Hub</span>
                    </a>
                    <a href="<?php echo base_url('admin/audit_monitor.php'); ?>" 
                       class="nav-link <?php echo strpos($current_page, 'audit') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-activity"></i>
                        <span>Audit Log</span>
                    </a>
                    <a href="<?php echo base_url('admin/workflow_builder.php'); ?>" 
                       class="nav-link <?php echo strpos($current_page, 'workflow') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-diagram-3-fill"></i>
                        <span>Workflow</span>
                    </a>
                <?php endif; ?>

                <?php if (in_array($current_role, ['cr', 'teacher', 'hod'])): ?>
                    <a href="<?php echo base_url('representative/forward.php'); ?>" 
                       class="nav-link <?php echo strpos($current_page, 'forward') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-inbox-fill"></i>
                        <span>Inbox</span>
                    </a>

                    <a href="<?php echo base_url('representative/forwarded.php'); ?>" 
                       class="nav-link <?php echo strpos($current_page, 'forwarded') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-send-fill"></i>
                        <span>Forwarded</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-divider"></div>

            <h6 class="nav-heading">Resources</h6>
            <nav class="nav flex-column">
                <a href="<?php echo base_url('student/knowledge_base.php'); ?>" 
                   class="nav-link <?php echo strpos($current_page, 'knowledge_base') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-book-fill"></i>
                    <span>Knowledge Base</span>
                </a>

                <a href="<?php echo base_url('student/badges.php'); ?>" 
                   class="nav-link <?php echo strpos($current_page, 'badges') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-award-fill"></i>
                    <span>Achievements</span>
                </a>
            </nav>

            <!-- Sign Out - pushed to bottom -->
            <div style="margin-top: auto; padding-top: 2rem;">
                <div class="sidebar-divider"></div>
                <nav class="nav flex-column" style="margin-top: 0.5rem;">
                    <a href="<?php echo base_url('auth/logout.php'); ?>" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-left"></i>
                        <span>Sign Out</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
    // Sidebar Toggle Logic for Mobile
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar-container');
        const overlay = document.getElementById('sidebar-overlay');
        const toggleBtn = document.getElementById('mobile-sidebar-toggle');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
                document.body.classList.toggle('overflow-hidden');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.classList.remove('overflow-hidden');
            });
        }

        // Close sidebar on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.classList.remove('overflow-hidden');
            }
        });
    });
</script>
