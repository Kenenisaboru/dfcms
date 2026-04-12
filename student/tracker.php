<?php
// student/tracker.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Get all complaints for the student
$stmt = $pdo->prepare("SELECT c.*, u.full_name as handler_name 
                      FROM complaints c 
                      LEFT JOIN users u ON c.assigned_to = u.id 
                      WHERE c.student_id = ? 
                      ORDER BY c.created_at DESC");
$stmt->execute([$userId]);
$complaints = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Complaints - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0c0d0e;
            --card-bg: rgba(18, 18, 18, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-light: #f8fafc;
            --text-dim: #94a3b8;
        }

        body { 
            background-color: var(--bg-dark); 
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(16, 185, 129, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
            color: var(--text-light); 
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .navbar-custom { 
            background: rgba(18, 18, 18, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border); 
            padding: 1rem 2rem;
        }

        .card-custom { 
            background: var(--card-bg); 
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); 
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-light);
            --bs-table-border-color: var(--glass-border);
            margin-bottom: 0;
        }

        .table-custom thead th {
            border-top: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: var(--text-dim);
            padding: 1.5rem 1rem;
        }

        .table-custom tbody td {
            vertical-align: middle;
            padding: 1.25rem 1rem;
            border-bottom-color: var(--glass-border);
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-resolved { background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-rejected { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
        .badge-progress { background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }

        .btn-view {
            background: rgba(16, 185, 129, 0.1);
            color: var(--primary);
            border: 1px solid rgba(16, 185, 129, 0.2);
            transition: all 0.2s ease;
        }
        .btn-view:hover {
            background: var(--primary);
            color: #000;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
        <a class="navbar-brand text-success fw-bold" href="../dashboard.php">DFCMS</a>
        <div class="ms-auto">
            <a href="../dashboard.php" class="btn btn-outline-light btn-sm me-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="fas fa-search me-2 text-success"></i> Track Your Status</h3>
            <span class="text-dim small">Total Complaints: <?php echo count($complaints); ?></span>
        </div>
        
        <div class="card card-custom">
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Category</th>
                            <th>Current Handler</th>
                            <th>Status</th>
                            <th>Last Update</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($complaints) > 0): ?>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td class="fw-bold text-accent">#<?php echo $c['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-folder-open me-2 text-dim"></i>
                                            <?php echo htmlspecialchars($c['category']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle me-2 text-dim"></i>
                                            <?php echo $c['handler_name'] ? htmlspecialchars($c['handler_name']) : '<span class="text-dim small italic">Unassigned</span>'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php 
                                            echo match($c['status']) {
                                                'Pending' => 'badge-pending',
                                                'In-Progress' => 'badge-progress',
                                                'Resolved' => 'badge-resolved',
                                                'Rejected' => 'badge-rejected',
                                                default => 'bg-secondary'
                                            };
                                        ?>"><?php echo $c['status']; ?></span>
                                    </td>
                                    <td>
                                        <div class="text-dim small">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo date('M d, Y', strtotime($c['updated_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <?php if ($c['assigned_to']): ?>
                                                <a href="messages.php?receiver_id=<?php echo $c['assigned_to']; ?>" class="btn btn-sm btn-view rounded-pill px-3">
                                                    <i class="fas fa-comment-dots me-1"></i> Message
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-view rounded-pill px-3" onclick="viewHistory(<?php echo $c['id']; ?>)">
                                                <i class="fas fa-history me-1"></i> View Log
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-dim py-5">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                                    <p>You haven't submitted any complaints yet.</p>
                                    <a href="submit_complaint.php" class="btn btn-success btn-sm mt-2 rounded-pill px-4">Submit First Complaint</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-glass border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom border-secondary border-opacity-10 py-3 px-4">
                    <h5 class="modal-title fw-bold text-white">Complaint #<span id="modalCompId"></span> History</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="historyTimeline" class="history-timeline">
                        <!-- History items injected here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .history-timeline { position: relative; padding-left: 30px; }
        .history-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: rgba(16, 185, 129, 0.2); }
        .history-item { position: relative; margin-bottom: 25px; }
        .history-item::before { content: ''; position: absolute; left: -25px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #10b981; border: 3px solid #000; z-index: 1; }
        .history-actor { font-weight: bold; color: #10b981; font-size: 0.9rem; }
        .history-date { font-size: 0.75rem; color: #888; margin-left: 10px; }
        .history-comment { margin-top: 5px; color: #ddd; font-size: 0.9rem; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('historyModal'));
        
        async function viewHistory(id) {
            document.getElementById('modalCompId').innerText = id;
            document.getElementById('historyTimeline').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-success"></i></div>';
            modal.show();

            try {
                const response = await fetch(`../api/get_complaint_history.php?id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    let html = '';
                    if (data.history.length === 0) {
                        html = '<p class="text-center text-muted py-4">No logged history found for this item.</p>';
                    } else {
                        data.history.forEach(item => {
                            html += `
                                <div class="history-item">
                                    <div class="d-flex align-items-center">
                                        <span class="history-actor">${item.actor_name} (${item.actor_role.toUpperCase()})</span>
                                        <span class="history-date">${new Date(item.action_date).toLocaleString()}</span>
                                    </div>
                                    <div class="fw-bold text-light small mt-1">${item.action}</div>
                                    <div class="history-comment">${item.comments || 'No specific comments provided.'}</div>
                                </div>
                            `;
                        });
                    }
                    document.getElementById('historyTimeline').innerHTML = html;
                } else {
                    document.getElementById('historyTimeline').innerHTML = `<p class="text-danger">${data.message}</p>`;
                }
            } catch (err) {
                document.getElementById('historyTimeline').innerHTML = `<p class="text-danger">Failed to load history.</p>`;
            }
        }
    </script>
</body>
</html>
