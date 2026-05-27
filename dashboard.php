<?php
// dashboard.php - Premium Dashboard v4.0
require_once 'config/config.php';
require_once 'lib/NotificationService.php';

check_login();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

// Fetch stats based on role
$totalComplaints = 0;
$pendingComplaints = 0;
$resolvedComplaints = 0;
$unreadNotifications = 0;

if ($role == 'student') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ?");
    $stmt->execute([$userId]);
    $totalComplaints = $stmt->fetchColumn();

    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status IN ('Pending','In-Progress','Forwarded')");
    $stmt2->execute([$userId]);
    $pendingComplaints = $stmt2->fetchColumn();

    $stmt3 = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status = 'Resolved'");
    $stmt3->execute([$userId]);
    $resolvedComplaints = $stmt3->fetchColumn();

    // Chart data: complaints by status
    $stmtChart = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM complaints WHERE student_id = ? GROUP BY status");
    $stmtChart->execute([$userId]);
    $chartStatusRows = $stmtChart->fetchAll();

    // Chart data: complaints by category (top 5)
    $stmtCat = $pdo->prepare("SELECT category, COUNT(*) as cnt FROM complaints WHERE student_id = ? GROUP BY category ORDER BY cnt DESC LIMIT 5");
    $stmtCat->execute([$userId]);
    $chartCatRows = $stmtCat->fetchAll();

    // Chart data: complaints over last 7 days
    $stmtTrend = $pdo->prepare("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM complaints WHERE student_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY day ASC");
    $stmtTrend->execute([$userId]);
    $chartTrendRows = $stmtTrend->fetchAll();

    $stmtActivity = $pdo->prepare("SELECT id, category, priority, status, created_at FROM complaints WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmtActivity->execute([$userId]);
    $activities = $stmtActivity->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE current_handler_role = ? OR assigned_to = ?");
    $stmt->execute([$role, $userId]);
    $totalComplaints = $stmt->fetchColumn();

    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE (current_handler_role = ? OR assigned_to = ?) AND status IN ('Pending','In-Progress','Forwarded')");
    $stmt2->execute([$role, $userId]);
    $pendingComplaints = $stmt2->fetchColumn();

    $stmt3 = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE (current_handler_role = ? OR assigned_to = ?) AND status = 'Resolved'");
    $stmt3->execute([$role, $userId]);
    $resolvedComplaints = $stmt3->fetchColumn();

    // Chart data: complaints by status
    $stmtChart = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM complaints WHERE current_handler_role = ? OR assigned_to = ? GROUP BY status");
    $stmtChart->execute([$role, $userId]);
    $chartStatusRows = $stmtChart->fetchAll();

    // Chart data: complaints by category (top 5)
    $stmtCat = $pdo->prepare("SELECT category, COUNT(*) as cnt FROM complaints WHERE (current_handler_role = ? OR assigned_to = ?) GROUP BY category ORDER BY cnt DESC LIMIT 5");
    $stmtCat->execute([$role, $userId]);
    $chartCatRows = $stmtCat->fetchAll();

    // Chart data: complaints over last 7 days
    $stmtTrend = $pdo->prepare("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM complaints WHERE (current_handler_role = ? OR assigned_to = ?) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY day ASC");
    $stmtTrend->execute([$role, $userId]);
    $chartTrendRows = $stmtTrend->fetchAll();

    $stmtActivity = $pdo->prepare("SELECT id, category, priority, status, created_at FROM complaints WHERE current_handler_role = ? OR assigned_to = ? ORDER BY created_at DESC LIMIT 5");
    $stmtActivity->execute([$role, $userId]);
    $activities = $stmtActivity->fetchAll();
}

// Build chart data arrays for JS
$statusLabels = []; $statusData = [];
foreach ($chartStatusRows as $r) { $statusLabels[] = $r['status']; $statusData[] = (int)$r['cnt']; }

$catLabels = []; $catData = [];
foreach ($chartCatRows as $r) { $catLabels[] = $r['category']; $catData[] = (int)$r['cnt']; }

// Fill last 7 days trend (fill missing days with 0)
$trendLabels = []; $trendData = [];
$trendMap = [];
foreach ($chartTrendRows as $r) { $trendMap[$r['day']] = (int)$r['cnt']; }
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $trendLabels[] = date('M j', strtotime($day));
    $trendData[] = $trendMap[$day] ?? 0;
}

$notificationService = new NotificationService();
$unreadNotifications = $notificationService->getUnreadCount($userId);
$resolutionRate = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100) : 0;

$page_title = "Dashboard";
include 'components/head.php';
?>

<body>
<div class="admin-layout">
    <?php include 'components/sidebar.php'; ?>
    <div class="main-container">
        <?php $current_role = $role; include 'components/navbar.php'; ?>
        <main class="p-4 p-lg-5" style="max-width: 1600px;">

            <!-- Welcome Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-5 page-header">
                <div>
                    <p class="text-secondary-color mb-1 fw-600" style="font-size: 0.875rem;">Good <?php echo date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening'); ?>,</p>
                    <h1 class="fw-800 mb-1" style="color: var(--premium-text-heading);"><?php echo explode(' ', $userName)[0]; ?> 👋</h1>
                    <p class="text-muted-color mb-0" style="font-size: 0.9375rem;">Here's what's happening with your complaints today.</p>
                </div>
                <div class="mt-4 mt-md-0 d-flex gap-3">
                    <?php if($role === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn btn-primary px-4 py-2 rounded-pill fw-600"><i class="bi bi-shield-lock-fill me-2"></i>Admin Hub</a>
                    <?php endif; ?>
                    <?php if($role === 'student'): ?>
                        <a href="student/submit_complaint.php" class="btn btn-primary px-4 py-2 rounded-pill fw-600"><i class="bi bi-plus-circle-fill me-2"></i>File Complaint</a>
                    <?php endif; ?>
                    <button class="btn btn-white px-4 py-2 rounded-pill fw-600" id="btn-export-data"><i class="bi bi-cloud-arrow-down me-2"></i>Export</button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-5">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 stat-card border-0">
                        <div class="card-body p-4">
                            <div class="stat-icon-badge bg-primary-soft"><i class="bi bi-journal-text"></i></div>
                            <div class="stat-label mb-2 mt-3">Total Submissions</div>
                            <div class="stat-value counter" data-target="<?php echo $totalComplaints; ?>">0</div>
                            <div class="d-flex align-items-center gap-1 mt-3" style="font-size:0.8125rem;">
                                <span class="badge-soft badge-soft-success"><i class="bi bi-graph-up-arrow"></i> Active</span>
                                <span class="text-muted-color ms-1">All time</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 stat-card border-0">
                        <div class="card-body p-4">
                            <div class="stat-icon-badge" style="background:var(--premium-info-soft);color:var(--premium-info);"><i class="bi bi-bell-fill"></i></div>
                            <div class="stat-label mb-2 mt-3">Unread Alerts</div>
                            <div class="stat-value counter" data-target="<?php echo $unreadNotifications; ?>">0</div>
                            <div class="d-flex align-items-center gap-1 mt-3" style="font-size:0.8125rem;">
                                <span class="badge-soft badge-soft-info"><i class="bi bi-envelope"></i> New</span>
                                <span class="text-muted-color ms-1">Check alerts</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 stat-card border-0">
                        <div class="card-body p-4">
                            <div class="stat-icon-badge bg-amber-soft"><i class="bi bi-hourglass-split"></i></div>
                            <div class="stat-label mb-2 mt-3">Pending</div>
                            <div class="stat-value counter" data-target="<?php echo $pendingComplaints; ?>">0</div>
                            <div class="d-flex align-items-center gap-1 mt-3" style="font-size:0.8125rem;">
                                <span class="badge-soft badge-soft-warning"><i class="bi bi-clock-history"></i> Waiting</span>
                                <span class="text-muted-color ms-1">In review</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 stat-card border-0">
                        <div class="card-body p-4">
                            <div class="stat-icon-badge bg-teal-soft"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="stat-label mb-2 mt-3">Resolution Rate</div>
                            <div class="stat-value"><span class="counter" data-target="<?php echo $resolutionRate; ?>">0</span><span style="font-size:1rem;font-weight:600;">%</span></div>
                            <div class="progress mt-3" style="height:6px;">
                                <div class="progress-bar rounded-pill" id="resolution-bar" role="progressbar"
                                     style="width:0%;background:linear-gradient(90deg,var(--premium-teal),#00e68a);"
                                     data-width="<?php echo $resolutionRate; ?>" aria-valuenow="<?php echo $resolutionRate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-5">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-700 mb-0" style="color:var(--premium-text-heading);">Complaints This Week</h5>
                                <p class="text-muted-color mb-0 x-small mt-1">Daily submission trend — last 7 days</p>
                            </div>
                            <span class="badge-soft badge-soft-primary">7-day view</span>
                        </div>
                        <div class="card-body p-4"><canvas id="trendChart" height="110"></canvas></div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card border-0 h-100">
                        <div class="card-header">
                            <h5 class="fw-700 mb-0" style="color:var(--premium-text-heading);">Status Breakdown</h5>
                            <p class="text-muted-color mb-0 x-small mt-1">Complaints by current status</p>
                        </div>
                        <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                            <?php if (empty($statusData)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-pie-chart fs-1 text-muted-color"></i>
                                    <p class="text-muted-color small mt-2">No data yet</p>
                                </div>
                            <?php else: ?>
                                <canvas id="statusChart" height="200"></canvas>
                                <div class="d-flex flex-wrap gap-2 justify-content-center mt-3" id="status-legend"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($catData)): ?>
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <div class="card border-0">
                        <div class="card-header">
                            <h5 class="fw-700 mb-0" style="color:var(--premium-text-heading);">Top Complaint Categories</h5>
                            <p class="text-muted-color mb-0 x-small mt-1">Most frequent complaint types</p>
                        </div>
                        <div class="card-body p-4"><canvas id="categoryChart" height="80"></canvas></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="card border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-700 mb-0" style="color:var(--premium-text-heading);">Recent Complaints</h5>
                                <p class="text-muted-color mb-0 x-small mt-1">Latest submissions and their status</p>
                            </div>
                            <a href="<?php echo $role==='student'?'student/tracker.php':($role==='hod'?'hod/tracker.php':'representative/forward.php'); ?>"
                               class="btn btn-light btn-sm rounded-pill px-3 fw-600">See All <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($activities)): ?>
                                <div class="text-center py-5 px-4">
                                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-xl" style="width:80px;height:80px;background:var(--premium-bg);">
                                        <i class="bi bi-journal-x fs-1" style="color:var(--premium-text-muted);"></i>
                                    </div>
                                    <p class="fw-600 mb-1" style="color:var(--premium-text-heading);">No complaints yet</p>
                                    <p class="text-muted-color small mb-0">Your complaint history will appear here.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead><tr><th class="ps-4">Reference</th><th>Category</th><th>Priority</th><th>Status</th><th class="text-end pe-4">Date</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($activities as $act): ?>
                                                <tr>
                                                    <td class="ps-4"><span class="fw-700" style="color:var(--premium-primary);">#<?php echo $act['id']; ?></span></td>
                                                    <td class="fw-500"><?php echo htmlspecialchars($act['category']); ?></td>
                                                    <td><?php $pC=strtolower($act['priority'])=='high'?'badge-soft-danger':(strtolower($act['priority'])=='medium'?'badge-soft-warning':'badge-soft-info'); ?><span class="badge-soft <?php echo $pC; ?>"><?php echo $act['priority']; ?></span></td>
                                                    <td><?php $sC=strtolower($act['status'])=='resolved'?'badge-soft-success':(strtolower($act['status'])=='pending'?'badge-soft-warning':'badge-soft-info'); ?><span class="badge-soft <?php echo $sC; ?>"><?php echo $act['status']; ?></span></td>
                                                    <td class="text-end pe-4 text-muted-color fw-500"><?php echo date('M j, Y', strtotime($act['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card border-0 mb-4">
                        <div class="card-header"><h5 class="fw-700 mb-0" style="color:var(--premium-text-heading);">Recent Badges</h5></div>
                        <div class="card-body"><div class="d-flex gap-4 overflow-auto pb-2">
                            <div class="text-center" style="min-width:80px;"><div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" style="width:64px;height:64px;background:var(--premium-primary-soft);"><i class="bi bi-star-fill fs-3" style="color:var(--premium-primary);"></i></div><span class="x-small fw-700" style="color:var(--premium-text-heading);">Early Bird</span></div>
                            <div class="text-center" style="min-width:80px;opacity:0.5;"><div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" style="width:64px;height:64px;background:var(--premium-bg);"><i class="bi bi-shield-check fs-3" style="color:var(--premium-text-muted);"></i></div><span class="x-small fw-700" style="color:var(--premium-text-muted);">Verified</span></div>
                            <div class="text-center" style="min-width:80px;opacity:0.5;"><div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" style="width:64px;height:64px;background:var(--premium-bg);"><i class="bi bi-trophy fs-3" style="color:var(--premium-text-muted);"></i></div><span class="x-small fw-700" style="color:var(--premium-text-muted);">Winner</span></div>
                        </div></div>
                    </div>
                    <div class="card border-0 mb-4">
                        <div class="card-header"><h5 class="fw-700 mb-0" style="color:var(--premium-text-heading);">Quick Actions</h5></div>
                        <div class="card-body"><div class="d-flex flex-column gap-2">
                            <?php if ($role==='student'): ?>
                            <a href="student/submit_complaint.php" class="quick-action-link d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none"><div class="stat-icon-badge bg-primary-soft" style="width:42px;height:42px;margin:0;flex-shrink:0;"><i class="bi bi-plus-lg"></i></div><div><div class="fw-600" style="color:var(--premium-text-heading);font-size:0.875rem;">New Complaint</div><div class="x-small text-muted-color">Submit a new feedback</div></div><i class="bi bi-chevron-right ms-auto" style="color:var(--premium-text-muted);"></i></a>
                            <?php elseif ($role==='hod'): ?>
                            <a href="hod/tracker.php" class="quick-action-link d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none"><div class="stat-icon-badge bg-primary-soft" style="width:42px;height:42px;margin:0;flex-shrink:0;"><i class="bi bi-diagram-3-fill"></i></div><div><div class="fw-600" style="color:var(--premium-text-heading);font-size:0.875rem;">Distribution Hub</div><div class="x-small text-muted-color">Assign & manage complaints</div></div><i class="bi bi-chevron-right ms-auto" style="color:var(--premium-text-muted);"></i></a>
                            <?php else: ?>
                            <a href="representative/forward.php" class="quick-action-link d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none"><div class="stat-icon-badge bg-primary-soft" style="width:42px;height:42px;margin:0;flex-shrink:0;"><i class="bi bi-inbox-fill"></i></div><div><div class="fw-600" style="color:var(--premium-text-heading);font-size:0.875rem;">Inbox</div><div class="x-small text-muted-color">View pending complaints</div></div><i class="bi bi-chevron-right ms-auto" style="color:var(--premium-text-muted);"></i></a>
                            <?php endif; ?>
                            <a href="student/knowledge_base.php" class="quick-action-link d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none"><div class="stat-icon-badge bg-teal-soft" style="width:42px;height:42px;margin:0;flex-shrink:0;"><i class="bi bi-book"></i></div><div><div class="fw-600" style="color:var(--premium-text-heading);font-size:0.875rem;">Knowledge Base</div><div class="x-small text-muted-color">Browse guides & FAQ</div></div><i class="bi bi-chevron-right ms-auto" style="color:var(--premium-text-muted);"></i></a>
                        </div></div>
                    </div>
                    <div class="card border-0 overflow-hidden position-relative" style="background:linear-gradient(135deg,var(--premium-primary) 0%,#7551ff 100%);border:none!important;">
                        <div class="card-body p-4 position-relative" style="z-index:1;">
                            <h4 class="fw-800 text-white mb-2">Need Help?</h4>
                            <p class="small text-white mb-4" style="opacity:0.85;">Our comprehensive guides are here to help you navigate through any issues.</p>
                            <a href="student/knowledge_base.php" class="btn btn-white rounded-pill px-4 py-2 fw-600 small">Explore FAQ <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                        <i class="bi bi-question-diamond position-absolute text-white" style="font-size:10rem;right:-1.5rem;bottom:-2.5rem;opacity:0.12;pointer-events:none;"></i>
                        <div class="position-absolute" style="width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,0.06);top:-30px;left:-30px;pointer-events:none;"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    // Animated counters
    document.querySelectorAll('.counter').forEach(function(el){
        var target=parseInt(el.dataset.target,10)||0, current=0;
        var t=setInterval(function(){ current+=Math.ceil(target/60)||1; if(current>=target){current=target;clearInterval(t);} el.textContent=current.toLocaleString(); },16);
    });
    // Progress bar
    var bar=document.getElementById('resolution-bar');
    if(bar){setTimeout(function(){bar.style.transition='width 1.2s ease';bar.style.width=bar.dataset.width+'%';},300);}

    Chart.defaults.font.family="'Inter',sans-serif"; Chart.defaults.color='#707eae';

    // Trend line
    var tCtx=document.getElementById('trendChart');
    if(tCtx){new Chart(tCtx,{type:'line',data:{labels:<?php echo json_encode($trendLabels);?>,datasets:[{label:'Complaints',data:<?php echo json_encode($trendData);?>,borderColor:'#4318ff',backgroundColor:'rgba(67,24,255,0.08)',borderWidth:2.5,pointBackgroundColor:'#4318ff',pointRadius:4,pointHoverRadius:6,fill:true,tension:0.4}]},options:{responsive:true,plugins:{legend:{display:false},tooltip:{mode:'index',intersect:false}},scales:{x:{grid:{display:false},border:{display:false}},y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'rgba(0,0,0,0.04)'},border:{display:false}}}}});}

    // Status doughnut
    var sCtx=document.getElementById('statusChart');
    if(sCtx){var cMap={'Pending':'#ffb547','In-Progress':'#3965ff','Forwarded':'#4318ff','Assigned':'#01b574','Resolved':'#01b574','Rejected':'#ee5d50'};var lbs=<?php echo json_encode($statusLabels);?>;var cols=lbs.map(function(l){return cMap[l]||'#a3aed0';});new Chart(sCtx,{type:'doughnut',data:{labels:lbs,datasets:[{data:<?php echo json_encode($statusData);?>,backgroundColor:cols,borderWidth:0,hoverOffset:6}]},options:{responsive:true,cutout:'72%',plugins:{legend:{display:false}}}});var leg=document.getElementById('status-legend');if(leg){lbs.forEach(function(l,i){leg.innerHTML+='<span class="d-flex align-items-center gap-1 small fw-600" style="color:var(--premium-text-body);"><span style="width:10px;height:10px;border-radius:50%;background:'+cols[i]+';display:inline-block;"></span>'+l+'</span>';});}}

    // Category bar
    var cCtx=document.getElementById('categoryChart');
    if(cCtx){new Chart(cCtx,{type:'bar',data:{labels:<?php echo json_encode($catLabels);?>,datasets:[{label:'Complaints',data:<?php echo json_encode($catData);?>,backgroundColor:['#4318ff','#01b574','#ffb547','#3965ff','#ee5d50'],borderRadius:8,borderSkipped:false}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},border:{display:false}},y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'rgba(0,0,0,0.04)'},border:{display:false}}}}});}

    // Quick action hover
    document.querySelectorAll('.quick-action-link').forEach(function(el){
        el.style.cssText+='background:var(--premium-bg);transition:all 0.25s;border:1px solid transparent;border-radius:12px;';
        el.addEventListener('mouseenter',function(){el.style.borderColor='var(--premium-primary)';el.style.transform='translateX(4px)';});
        el.addEventListener('mouseleave',function(){el.style.borderColor='transparent';el.style.transform='none';});
    });
})();
</script>
<?php include 'components/footer.php'; ?>
</body>
</html>
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Recent Complaints</h5>
                                <p class="text-muted-color mb-0 x-small mt-1">Latest submissions and their status</p>
                            </div>
                            <a href="<?php echo $role === 'student' ? 'student/tracker.php' : ($role === 'hod' ? 'hod/tracker.php' : 'representative/forward.php'); ?>" 
                               class="btn btn-light btn-sm rounded-pill px-3 fw-600" id="btn-see-all-complaints">
                                See All <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($activities)): ?>
                                <div class="text-center py-5 px-4">
                                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-xl" 
                                         style="width: 80px; height: 80px; background: var(--premium-bg);">
                                        <i class="bi bi-journal-x fs-1" style="color: var(--premium-text-muted);"></i>
                                    </div>
                                    <p class="fw-600 mb-1" style="color: var(--premium-text-heading);">No complaints yet</p>
                                    <p class="text-muted-color small mb-0">Your complaint history will appear here.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Reference</th>
                                                <th>Category</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th class="text-end pe-4">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($activities as $act): ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <span class="fw-700" style="color: var(--premium-primary);">#<?php echo $act['id']; ?></span>
                                                    </td>
                                                    <td class="fw-500"><?php echo htmlspecialchars($act['category']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $pClass = 'badge-soft-info';
                                                        if (strtolower($act['priority']) == 'high') $pClass = 'badge-soft-danger';
                                                        if (strtolower($act['priority']) == 'medium') $pClass = 'badge-soft-warning';
                                                        ?>
                                                        <span class="badge-soft <?php echo $pClass; ?>"><?php echo $act['priority']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $sClass = 'badge-soft-info';
                                                        if (strtolower($act['status']) == 'resolved') $sClass = 'badge-soft-success';
                                                        if (strtolower($act['status']) == 'pending') $sClass = 'badge-soft-warning';
                                                        ?>
                                                        <span class="badge-soft <?php echo $sClass; ?>"><?php echo $act['status']; ?></span>
                                                    </td>
                                                    <td class="text-end pe-4 text-muted-color fw-500">
                                                        <?php echo date('M j, Y', strtotime($act['created_at'])); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-12 col-xl-4">
                    <!-- Achievements Card -->
                    <div class="card border-0 mb-4" id="card-achievements">
                        <div class="card-header">
                            <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Recent Badges</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-4 overflow-auto pb-2">
                                <div class="text-center" style="min-width: 80px;">
                                    <div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" 
                                         style="width: 64px; height: 64px; background: var(--premium-primary-soft);">
                                        <i class="bi bi-star-fill fs-3" style="color: var(--premium-primary);"></i>
                                    </div>
                                    <span class="x-small fw-700" style="color: var(--premium-text-heading);">Early Bird</span>
                                </div>
                                <div class="text-center" style="min-width: 80px; opacity: 0.5;">
                                    <div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" 
                                         style="width: 64px; height: 64px; background: var(--premium-bg);">
                                        <i class="bi bi-shield-check fs-3" style="color: var(--premium-text-muted);"></i>
                                    </div>
                                    <span class="x-small fw-700" style="color: var(--premium-text-muted);">Verified</span>
                                </div>
                                <div class="text-center" style="min-width: 80px; opacity: 0.5;">
                                    <div class="d-flex align-items-center justify-content-center mb-2 mx-auto rounded-xl" 
                                         style="width: 64px; height: 64px; background: var(--premium-bg);">
                                        <i class="bi bi-trophy fs-3" style="color: var(--premium-text-muted);"></i>
                                    </div>
                                    <span class="x-small fw-700" style="color: var(--premium-text-muted);">Winner</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="card border-0 mb-4" id="card-quick-actions">
                        <div class="card-header">
                            <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-2">
                                <a href="student/submit_complaint.php" class="d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none" 
                                   style="background: var(--premium-bg); transition: all 0.25s; border: 1px solid transparent;"
                                   onmouseover="this.style.borderColor='var(--premium-primary)'; this.style.transform='translateX(4px)'"
                                   onmouseout="this.style.borderColor='transparent'; this.style.transform='none'">
                                    <div class="stat-icon-badge bg-primary-soft" style="width: 42px; height: 42px; margin: 0; flex-shrink: 0;">
                                        <i class="bi bi-plus-lg" style="font-size: 1rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-600" style="color: var(--premium-text-heading); font-size: 0.875rem;">New Complaint</div>
                                        <div class="x-small text-muted-color">Submit a new feedback</div>
                                    </div>
                                    <i class="bi bi-chevron-right ms-auto" style="color: var(--premium-text-muted);"></i>
                                </a>
                                <a href="student/knowledge_base.php" class="d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none" 
                                   style="background: var(--premium-bg); transition: all 0.25s; border: 1px solid transparent;"
                                   onmouseover="this.style.borderColor='var(--premium-teal)'; this.style.transform='translateX(4px)'"
                                   onmouseout="this.style.borderColor='transparent'; this.style.transform='none'">
                                    <div class="stat-icon-badge bg-teal-soft" style="width: 42px; height: 42px; margin: 0; flex-shrink: 0;">
                                        <i class="bi bi-book" style="font-size: 1rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-600" style="color: var(--premium-text-heading); font-size: 0.875rem;">Knowledge Base</div>
                                        <div class="x-small text-muted-color">Browse guides & FAQ</div>
                                    </div>
                                    <i class="bi bi-chevron-right ms-auto" style="color: var(--premium-text-muted);"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Help CTA Card -->
                    <div class="card border-0 overflow-hidden position-relative" id="card-help-cta" 
                         style="background: linear-gradient(135deg, var(--premium-primary) 0%, #7551ff 100%); border: none !important;">
                        <div class="card-body p-4 position-relative" style="z-index: 1;">
                            <h4 class="fw-800 text-white mb-2">Need Help?</h4>
                            <p class="small text-white mb-4" style="opacity: 0.85;">Our comprehensive guides are here to help you navigate through any issues you might face.</p>
                            <a href="student/knowledge_base.php" class="btn btn-white rounded-pill px-4 py-2 fw-600 small" id="btn-explore-faq">
                                Explore FAQ <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <!-- Decorative Icon -->
                        <i class="bi bi-question-diamond position-absolute text-white" 
                           style="font-size: 10rem; right: -1.5rem; bottom: -2.5rem; opacity: 0.12; pointer-events: none;"></i>
                        <!-- Decorative circles -->
                        <div class="position-absolute" style="width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.06); top: -30px; left: -30px; pointer-events: none;"></div>
                        <div class="position-absolute" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.04); bottom: 20px; left: 40%; pointer-events: none;"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'components/footer.php'; ?>
</body>
</html>
