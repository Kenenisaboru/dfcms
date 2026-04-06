<?php
// student/badges.php
session_start();
require_once '../config/database.php';
require_once '../lib/EngagementService.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$engagement = new EngagementService();
$userId = $_SESSION['user_id'];
$badges = $engagement->getUserBadges($userId);

// Calculate progress
$earnedCount = 0;
foreach ($badges as $badge) {
    if ($badge['earned']) $earnedCount++;
}
$totalCount = count($badges);
$progressPercent = ($earnedCount / $totalCount) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Badges - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/next-gen-ui.css" rel="stylesheet">
    <style>
        .badge-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .badge-card {
            background: var(--card-bg-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .badge-card.locked {
            opacity: 0.6;
            filter: grayscale(1);
        }
        .badge-card.earned {
            border-color: var(--primary);
            box-shadow: 0 0 20px var(--primary-glow);
        }
        .badge-icon-large {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        .progress-section {
            background: var(--card-bg-dark);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 3rem;
        }
        .lock-overlay {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.2rem;
            color: #64748b;
        }
    </style>
</head>
<body class="dark-mode">
    <nav class="main-header py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="../dashboard.php" class="text-decoration-none text-white h4 mb-0">
                <i class="fas fa-university text-success me-2"></i>DFCMS Badges
            </a>
            <div class="d-flex gap-3">
                <a href="../dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="progress-section text-center">
            <h2 class="mb-4">Your Achievement Progress</h2>
            <div class="d-flex justify-content-between mb-2">
                <span>Rank: <?php echo $earnedCount >= 3 ? 'Silver Contributor' : ($earnedCount >= 1 ? 'Active Member' : 'Newcomer'); ?></span>
                <span><?php echo $earnedCount; ?> / <?php echo $totalCount; ?> Badges Earned</span>
            </div>
            <div class="progress" style="height: 20px; border-radius: 10px; background: rgba(255,255,255,0.1);">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: <?php echo $progressPercent; ?>%" 
                     aria-valuenow="<?php echo $progressPercent; ?>" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>

        <div class="badge-grid">
            <?php foreach ($badges as $key => $badge): ?>
                <div class="badge-card <?php echo $badge['earned'] ? 'earned' : 'locked'; ?>">
                    <?php if (!$badge['earned']): ?>
                        <div class="lock-overlay"><i class="fas fa-lock"></i></div>
                    <?php else: ?>
                        <div class="lock-overlay text-success"><i class="fas fa-check-circle"></i></div>
                    <?php endif; ?>
                    
                    <i class="fas <?php echo $badge['icon']; ?> badge-icon-large" style="color: <?php echo $badge['color']; ?>"></i>
                    <h3 class="h5"><?php echo $badge['name']; ?></h3>
                    <p class="text-secondary small mb-0"><?php echo $badge['description']; ?></p>
                    
                    <?php if ($badge['earned']): ?>
                        <div class="mt-2 text-muted x-small">
                            Earned on <?php echo date('M d, Y', strtotime($badge['awarded_at'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button class="theme-toggle" aria-label="Toggle dark/light mode">
        <i class="fas fa-sun"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/next-gen-ui.js"></script>
</body>
</html>
