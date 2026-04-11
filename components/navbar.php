<?php
// components/navbar.php - Premium Navigation Component
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$current_page = basename($_SERVER['PHP_SELF']);
$is_landing = isset($nav_transparent) && $nav_transparent === true;
?>
<!-- Premium Navigation -->
<nav class="nav-modern<?php echo $is_landing ? ' nav-transparent' : ''; ?>">
    <div class="nav-container">
        <a class="nav-brand" href="<?php echo $current_role ? base_url('dashboard.php') : base_url('index.php'); ?>">
            <div class="nav-brand-icon">
                <i class="fas fa-university"></i>
            </div>
            DFCMS
        </a>
        
        <div class="nav-links">
            <?php if ($is_landing && !$current_role): ?>
                <a class="nav-link" href="#platform">Platform</a>
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
                
                <div class="dropdown">
                    <button class="btn btn-secondary" type="button" onclick="this.nextElementSibling.classList.toggle('show')">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <div class="dropdown-header">
                            <p class="dropdown-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                            <p class="dropdown-role"><?php echo ucfirst($current_role); ?></p>
                        </div>
                        <a href="<?php echo base_url('auth/logout.php'); ?>" class="dropdown-item dropdown-item-danger">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo base_url('auth/login.php'); ?>" class="btn btn-ghost">Sign In</a>
                <a href="<?php echo base_url('auth/register.php'); ?>" class="btn btn-primary">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    .dropdown-menu.show {
        display: block !important;
        animation: fadeInDown 0.2s ease-out;
    }
    
    .dropdown-header {
        padding: var(--space-3) var(--space-4);
        border-bottom: 1px solid var(--glass-border);
    }
    
    .dropdown-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    
    .dropdown-role {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin: var(--space-1) 0 0;
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-4);
        color: var(--text-secondary);
        font-size: 0.875rem;
        text-decoration: none;
        transition: all var(--transition-fast);
    }
    
    .dropdown-item:hover {
        background: var(--glass-highlight);
        color: var(--text-primary);
    }
    
    .dropdown-item-danger {
        color: var(--danger);
    }
    
    .dropdown-item-danger:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }
</style>
