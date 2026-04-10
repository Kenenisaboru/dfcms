<?php
// components/navbar.php
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-glass sticky-top py-3 px-4 border-bottom border-secondary border-opacity-10">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-accent d-flex align-items-center" href="<?php echo base_url('dashboard.php'); ?>">
            <i class="fas fa-university me-2"></i> DFCMS
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($current_role): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('dashboard.php'); ?>">Dashboard</a>
                    </li>
                    <?php if ($current_role === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('student/submit_complaint.php'); ?>">Submit Issue</a>
                        </li>
                    <?php endif; ?>
                    <?php if (in_array($current_role, ['cr', 'teacher', 'hod'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('representative/forward.php'); ?>">Action Hub</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($current_role === 'hod'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('admin/audit_monitor.php'); ?>">Audit Monitor</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <div class="ms-auto d-flex align-items-center">
                <?php if ($current_role): ?>
                    <div class="dropdown">
                        <a class="btn btn-outline-light btn-sm dropdown-toggle rounded-pill px-3" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg mt-2">
                            <li><h6 class="dropdown-header text-accent"><?php echo strtoupper($current_role); ?> Account</h6></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('auth/logout.php'); ?>"><i class="fas fa-sign-out-alt me-2 text-danger"></i> Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo base_url('auth/login.php'); ?>" class="btn btn-outline-accent btn-sm me-2">Login</a>
                    <a href="<?php echo base_url('auth/register.php'); ?>" class="btn btn-accent btn-sm">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
