<?php
// components/navbar.php - Modern Navigation Component
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$current_page = basename($_SERVER['PHP_SELF']);
$is_landing = isset($nav_transparent) && $nav_transparent === true;
?>
<!-- Modern Navigation -->
<nav class="nav-modern<?php echo $is_landing ? ' nav-transparent' : ''; ?>">
    <div class="nav-container">
        <a class="nav-brand" href="<?php echo $current_role ? base_url('dashboard.php') : base_url('index.php'); ?>">
            <div class="nav-brand-icon">
                <i class="fas fa-university"></i>
            </div>
            DFCMS<span class="dot">.</span>
        </a>
        
        <div class="nav-links">
            <?php if ($is_landing && !$current_role): ?>
                <a class="nav-link" href="#features">Features</a>
                <a class="nav-link" href="#about">About</a>
                <a class="nav-link" href="#contact">Contact</a>
            <?php elseif ($current_role): ?>
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo base_url('dashboard.php'); ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                
                <?php if ($current_role === 'student'): ?>
                    <a class="nav-link <?php echo strpos($current_page, 'submit') !== false ? 'active' : ''; ?>" href="<?php echo base_url('student/submit_complaint.php'); ?>">
                        <i class="fas fa-plus-circle"></i> Submit
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($current_role, ['cr', 'teacher', 'hod'])): ?>
                    <a class="nav-link <?php echo strpos($current_page, 'forward') !== false ? 'active' : ''; ?>" href="<?php echo base_url('representative/forward.php'); ?>">
                        <i class="fas fa-inbox"></i> Inbox
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="nav-actions">
            <?php if ($current_role): ?>
                <?php 
                $notify_path = file_exists('components/notifications.php') ? 'components/notifications.php' : '../components/notifications.php';
                if (file_exists($notify_path)) {
                    include $notify_path;
                }
                ?>
                
                <div class="dropdown" style="position: relative;">
                    <button class="btn btn-secondary" type="button" onclick="this.nextElementSibling.classList.toggle('show')" style="display: flex; align-items: center; gap: var(--space-2);">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                    </button>
                    <div class="dropdown-menu" style="position: absolute; top: 100%; right: 0; margin-top: var(--space-2); min-width: 200px; display: none;">
                        <div style="padding: var(--space-3) var(--space-4); border-bottom: 1px solid var(--glass-border);">
                            <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary); margin: 0;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                            <p style="font-size: 0.75rem; color: var(--text-tertiary); margin: var(--space-1) 0 0;"><?php echo ucfirst($current_role); ?></p>
                        </div>
                        <a href="<?php echo base_url('auth/logout.php'); ?>" class="dropdown-item" style="display: flex; align-items: center; gap: var(--space-2); padding: var(--space-3) var(--space-4); color: var(--danger); font-size: 0.875rem; text-decoration: none;">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo base_url('auth/login.php'); ?>" class="btn-login">Login</a>
                <a href="<?php echo base_url('auth/register.php'); ?>" class="link-signup">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    .dropdown-menu.show {
        display: block !important;
        animation: fadeInDown 0.2s ease-out;
    }
    
    .dropdown-item:hover {
        background: var(--glass-highlight);
    }
</style>
