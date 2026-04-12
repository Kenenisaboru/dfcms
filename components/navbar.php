<?php
// components/navbar.php - Premium Navigation Component v4.0
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$current_page = basename($_SERVER['PHP_SELF']);
$is_landing = isset($nav_transparent) && $nav_transparent === true;
?>
<!-- Premium Top Navbar -->
<nav class="navbar navbar-expand-lg top-navbar sticky-top <?php echo $is_landing ? 'navbar-transparent' : ''; ?>">
    <div class="container-fluid">
        <!-- Mobile Toggle -->
        <button class="btn btn-link link-dark d-lg-none p-0 me-3" id="mobile-sidebar-toggle" aria-label="Toggle sidebar">
            <i class="bi bi-list fs-2"></i>
        </button>

        <!-- Brand (visible on mobile when sidebar hidden) -->
        <a class="navbar-brand fw-bold" href="<?php echo base_url('index.php'); ?>">
            <span style="color: var(--premium-primary);">DF</span>CMS
        </a>

        <!-- Search Bar (Desktop) -->
        <div class="d-none d-md-block flex-grow-1">
            <div class="navbar-search">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Search complaints, students, or knowledge base..." id="global-search-input">
            </div>
        </div>

        <!-- Right Side Actions -->
        <div class="d-flex align-items-center gap-2 gap-lg-3 ms-auto">
            <?php if ($current_role): ?>
                <!-- Notifications -->
                <?php 
                $notify_path = file_exists('components/notifications.php') ? 'components/notifications.php' : '../components/notifications.php';
                if (file_exists($notify_path)) {
                    include $notify_path;
                }
                ?>

                <!-- User Dropdown (Logged In) -->
                <div class="dropdown">
                    <a href="#" class="user-dropdown dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="user-profile-dropdown">
                        <div class="user-profile-img">
                            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                        </div>
                        <div class="d-none d-lg-block">
                            <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                            <div class="user-role"><?php echo ucfirst($current_role); ?></div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="user-dropdown-menu">
                        <li><h6 class="dropdown-header">Account</h6></li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-shield-check"></i> Privacy
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo base_url('auth/logout.php'); ?>">
                                <i class="bi bi-box-arrow-right"></i> Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Guest Actions -->
                <div class="d-flex gap-2 align-items-center">
                    <a href="<?php echo base_url('auth/login.php'); ?>" class="btn btn-light rounded-pill px-4 py-2 fw-600 small">Sign In</a>
                    <a href="<?php echo base_url('auth/register.php'); ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-600 small">Get Started</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
