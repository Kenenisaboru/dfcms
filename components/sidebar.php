<?php
// components/sidebar.php - Premium Sidebar Component
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Modern Sidebar -->
<aside class="sidebar-modern" id="sidebar">
    <div class="sidebar-header">
        <div class="nav-brand">
            <div class="nav-brand-icon">
                <i class="fas fa-university"></i>
            </div>
            <span>DFCMS</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($current_role): ?>
            <a href="<?php echo base_url('dashboard.php'); ?>" 
               class="sidebar-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <?php if ($current_role === 'student'): ?>
                <a href="<?php echo base_url('student/submit_complaint.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'submit') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Submit Complaint</span>
                </a>

                <a href="<?php echo base_url('student/my_complaints.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'my_complaints') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i>
                    <span>My Complaints</span>
                </a>

                <a href="<?php echo base_url('student/messages.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'messages') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>

                <a href="<?php echo base_url('student/knowledge_base.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'knowledge_base') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i>
                    <span>Knowledge Base</span>
                </a>

                <a href="<?php echo base_url('student/badges.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'badges') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-award"></i>
                    <span>Badges</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($current_role, ['cr', 'teacher', 'hod'])): ?>
                <a href="<?php echo base_url('representative/forward.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'forward') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i>
                    <span>Inbox</span>
                </a>

                <a href="<?php echo base_url('representative/forwarded.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'forwarded') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-paper-plane"></i>
                    <span>Forwarded</span>
                </a>

                <a href="<?php echo base_url('representative/resolved.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'resolved') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i>
                    <span>Resolved</span>
                </a>
            <?php endif; ?>

            <?php if ($current_role === 'hod'): ?>
                <a href="<?php echo base_url('admin/analytics.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'analytics') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>

                <a href="<?php echo base_url('admin/settings.php'); ?>" 
                   class="sidebar-link <?php echo strpos($current_page, 'settings') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo base_url('auth/logout.php'); ?>" class="sidebar-link sidebar-link-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle Sidebar">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
    // Sidebar Toggle Logic
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('show');
    }

    sidebarToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);
</script>
