<?php
// student/knowledge_base.php
session_start();
require_once '../config/database.php';
require_once '../lib/EngagementService.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$engagement = new EngagementService();
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

$articles = $engagement->getKBArticles($category, $search);
$categories = EngagementConfig::$knowledge_base_categories;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Base - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/next-gen-ui.css" rel="stylesheet">
    <style>
        .kb-search {
            background: var(--card-bg-dark);
            border: 1px solid var(--border-dark);
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        .article-card {
            transition: var(--transition);
            cursor: pointer;
            height: 100%;
        }
        .article-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }
        .category-badge {
            background: var(--primary-glow);
            color: var(--primary);
            padding: 0.2rem 0.8rem;
            border-radius: 1rem;
            font-size: 0.8rem;
        }
    </style>
</head>
<body class="dark-mode">
    <nav class="main-header py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="../dashboard.php" class="text-decoration-none text-white h4 mb-0">
                <i class="fas fa-university text-success me-2"></i>DFCMS KB
            </a>
            <div class="d-flex gap-3">
                <a href="../dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="kb-search text-center">
            <h1 class="mb-4">How can we help you today?</h1>
            <form action="" method="GET" class="d-flex gap-2 max-width-600 mx-auto">
                <input type="text" name="search" class="form-control form-control-lg bg-dark text-white border-secondary" 
                       placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-success btn-lg px-4">Search</button>
            </form>
            <div class="mt-3 d-flex justify-content-center gap-2 flex-wrap">
                <a href="knowledge_base.php" class="btn btn-sm <?php echo !$category ? 'btn-success' : 'btn-outline-secondary'; ?>">All</a>
                <?php foreach ($categories as $key => $name): ?>
                    <a href="?category=<?php echo $key; ?>" 
                       class="btn btn-sm <?php echo $category === $key ? 'btn-success' : 'btn-outline-secondary'; ?>">
                        <?php echo $name; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card p-4 mb-2">
                    <h3 class="h5 mb-3"><i class="fas fa-paper-plane text-success me-2"></i>How to Submit a Complaint</h3>
                    <ol class="mb-0 text-secondary">
                        <li>Log in to DFCMS with your student account.</li>
                        <li>Open <strong>Submit Complaint</strong> from the menu or dashboard.</li>
                        <li>Select complaint <strong>Category</strong> and <strong>Priority Level</strong>.</li>
                        <li>Choose <strong>Route Complaint To</strong> (Class Representative or Teacher).</li>
                        <li>Write a clear issue description: what happened, when, where, and key details.</li>
                        <li>Optionally attach evidence (JPG, PNG, PDF up to 5MB).</li>
                        <li>Click <strong>Initialize Workflow</strong> to submit.</li>
                        <li>Go to <strong>Track Complaints</strong> to monitor status and use <strong>Messages</strong> for follow-up.</li>
                    </ol>
                </div>
            </div>

            <div class="col-12">
                <div class="card p-4 mb-2">
                    <h3 class="h5 mb-3"><i class="fas fa-key text-warning me-2"></i>Password Reset Guide</h3>
                    <ol class="mb-0 text-secondary">
                        <li>Open the DFCMS login page.</li>
                        <li>Use <strong>Forgot Password</strong> if available.</li>
                        <li>Enter your registered email and follow the reset link.</li>
                        <li>Create a strong new password (at least 8 characters with upper, lower, number, and symbol).</li>
                        <li>Log in again with the new password.</li>
                        <li>If reset is not available, contact your DFCMS admin/department IT to reset your account.</li>
                    </ol>
                </div>
            </div>

            <?php if (empty($articles)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-secondary mb-3"></i>
                    <p class="text-secondary">No articles found matching your criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card article-card p-4" data-bs-toggle="modal" data-bs-target="#articleModal<?php echo $article['id']; ?>">
                            <div class="mb-2">
                                <span class="category-badge"><?php echo $categories[$article['category']] ?? $article['category']; ?></span>
                            </div>
                            <h4 class="h5 mb-3"><?php echo htmlspecialchars($article['title']); ?></h4>
                            <p class="text-secondary small mb-0">
                                <?php echo substr(strip_tags($article['content']), 0, 100); ?>...
                            </p>
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="far fa-eye me-1"></i> <?php echo $article['views']; ?> views</span>
                                <i class="fas fa-arrow-right text-success"></i>
                            </div>
                        </div>

                        <!-- Article Modal -->
                        <div class="modal fade" id="articleModal<?php echo $article['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0">
                                    <div class="modal-header border-bottom border-secondary">
                                        <h5 class="modal-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <span class="category-badge"><?php echo $categories[$article['category']] ?? $article['category']; ?></span>
                                            <span class="ms-2 text-muted small">Published on <?php echo date('M d, Y', strtotime($article['created_at'])); ?></span>
                                        </div>
                                        <div class="article-content">
                                            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top border-secondary">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="submit_complaint.php" class="btn btn-success">Still need help? Submit a Complaint</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <button class="theme-toggle" aria-label="Toggle dark/light mode">
        <i class="fas fa-sun"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/next-gen-ui.js"></script>
</body>
</html>
