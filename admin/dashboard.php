<?php
// admin/dashboard.php - Admin Premium Layout
$base_path = '../'; 
require_once $base_path . 'config/config.php';
require_once $base_path . 'lib/NotificationService.php';

check_login('admin'); 

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

$page_title = "Admin Dashboard";
include $base_path . 'components/head.php';
?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Specific styles for admin dashboard */
    .admin-stat-card {
        border-radius: var(--radius-xl);
        position: relative;
        overflow: hidden;
        transition: transform 0.3s var(--ease-smooth), box-shadow 0.3s var(--ease-smooth);
        border: none;
    }
    
    .admin-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--premium-shadow-lg);
    }
    
    .admin-stat-icon-bg {
        position: absolute;
        right: -10%;
        top: -10%;
        font-size: 8rem;
        opacity: 0.1;
        transform: rotate(-15deg);
        pointer-events: none;
    }

    /* Gradients */
    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--premium-primary) 0%, #7551ff 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, var(--premium-amber) 0%, #ff8c00 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, var(--premium-teal) 0%, #00d2ff 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, var(--premium-info) 0%, #009fff 100%);
    }
    
    .chart-container-card {
        border-radius: var(--radius-xl);
        background: var(--premium-white);
        box-shadow: var(--premium-shadow-sm);
        padding: var(--space-6);
        border: 1px solid var(--premium-border-light);
    }
    
    .table-container-card {
        border-radius: var(--radius-xl);
        background: var(--premium-white);
        box-shadow: var(--premium-shadow-sm);
        padding: var(--space-6);
        border: 1px solid var(--premium-border-light);
    }
    
    .modal-content {
        border-radius: var(--radius-xl);
        border: none;
        box-shadow: var(--premium-shadow-lg);
    }
</style>

<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <?php include '../components/sidebar.php'; ?>

    <div class="main-container">
        <!-- Top Navbar -->
        <?php 
        $current_role = $role;
        include '../components/navbar.php'; 
        ?>

        <!-- Page Content -->
        <main class="p-4 p-lg-5" style="max-width: 1600px;">
            <!-- Welcome Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-5 page-header pt-3">
                <div>
                    <h1 class="fw-800 mb-1 d-flex align-items-center gap-2" style="color: var(--premium-text-heading);">
                        Overview <i class="bi bi-shield-check text-primary fs-3"></i>
                    </h1>
                    <p class="text-muted-color mb-0" style="font-size: 0.9375rem;">Platform-wide analytics and management</p>
                </div>
            </div>

            <!-- Four Summary Stat Cards -->
            <div class="row g-4 mb-5">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card admin-stat-card bg-gradient-primary text-white h-100 p-4">
                        <i class="bi bi-list-task admin-stat-icon-bg text-white"></i>
                        <h6 class="fw-600 mb-3 text-white opacity-75 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.8rem">Total Complaints</h6>
                        <h2 class="fw-800 mb-0 display-5 text-white">1,284</h2>
                        <div class="mt-3 fs-7 opacity-75"><i class="bi bi-arrow-up-short"></i> 12% from last month</div>
                    </div>
                </div>
                
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card admin-stat-card bg-gradient-warning text-white h-100 p-4">
                        <i class="bi bi-hourglass-split admin-stat-icon-bg text-white"></i>
                        <h6 class="fw-600 mb-3 text-white opacity-75 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.8rem">Pending</h6>
                        <h2 class="fw-800 mb-0 display-5 text-white">342</h2>
                        <div class="mt-3 fs-7 opacity-75"><i class="bi bi-arrow-up-short"></i> 5% from last month</div>
                    </div>
                </div>
                
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card admin-stat-card bg-gradient-success text-white h-100 p-4">
                        <i class="bi bi-check-circle-fill admin-stat-icon-bg text-white"></i>
                        <h6 class="fw-600 mb-3 text-white opacity-75 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.8rem">Resolved</h6>
                        <h2 class="fw-800 mb-0 display-5 text-white">890</h2>
                        <div class="mt-3 fs-7 opacity-75"><i class="bi bi-arrow-up-short"></i> 24% from last month</div>
                    </div>
                </div>
                
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card admin-stat-card bg-gradient-info text-white h-100 p-4">
                        <i class="bi bi-star-fill admin-stat-icon-bg text-white"></i>
                        <h6 class="fw-600 mb-3 text-white opacity-75 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.8rem">User Feedback</h6>
                        <h2 class="fw-800 mb-0 display-5 text-white">4.8<span class="fs-4 opacity-50">/5</span></h2>
                        <div class="mt-3 fs-7 opacity-75"><i class="bi bi-dash"></i> Same as last month</div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-5">
                <div class="col-12 col-lg-8">
                    <div class="chart-container-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Complaints by Category</h5>
                        </div>
                        <div style="position: relative; height: 300px; width: 100%;">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Doughnut Chart -->
                <div class="col-12 col-lg-4">
                    <div class="chart-container-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Resolution Status</h5>
                        </div>
                        <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center;">
                            <canvas id="doughnutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Complaints Data Table -->
            <div class="table-container-card mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-700 mb-0" style="color: var(--premium-text-heading);">Recent Complaints</h5>
                    <a href="<?php echo base_url('admin/monitoring_dashboard.php'); ?>" class="btn btn-sm btn-light rounded-pill px-3 fw-600">View All</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-secondary-color fw-600 py-3 ps-3 rounded-start" style="font-size: 0.85rem">ID</th>
                                <th class="text-secondary-color fw-600 py-3" style="font-size: 0.85rem">Subject</th>
                                <th class="text-secondary-color fw-600 py-3" style="font-size: 0.85rem">Category</th>
                                <th class="text-secondary-color fw-600 py-3" style="font-size: 0.85rem">Date</th>
                                <th class="text-secondary-color fw-600 py-3" style="font-size: 0.85rem">Status</th>
                                <th class="text-secondary-color fw-600 py-3 pe-3 rounded-end text-end" style="font-size: 0.85rem">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-bottom" style="border-color: var(--premium-border-light) !important;">
                                <td class="ps-3"><span class="fw-700 text-primary">#1024</span></td>
                                <td><span class="fw-600" style="color: var(--premium-text-heading);">Library AC Malfunction</span></td>
                                <td><span class="badge bg-light text-dark rounded-pill px-3">Facilities</span></td>
                                <td><span class="text-muted-color small">Today, 10:45 AM</span></td>
                                <td><span class="badge badge-soft-warning px-3 rounded-pill">Pending</span></td>
                                <td class="pe-3 text-end">
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 fw-600" data-bs-toggle="modal" data-bs-target="#actionModal">
                                        Take Action
                                    </button>
                                </td>
                            </tr>
                            <tr class="border-bottom" style="border-color: var(--premium-border-light) !important;">
                                <td class="ps-3"><span class="fw-700 text-primary">#1023</span></td>
                                <td><span class="fw-600" style="color: var(--premium-text-heading);">Incorrect Grade Entry</span></td>
                                <td><span class="badge bg-light text-dark rounded-pill px-3">Academic</span></td>
                                <td><span class="text-muted-color small">Yesterday, 14:30 PM</span></td>
                                <td><span class="badge badge-soft-info px-3 rounded-pill">In Progress</span></td>
                                <td class="pe-3 text-end">
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 fw-600" data-bs-toggle="modal" data-bs-target="#actionModal">
                                        Take Action
                                    </button>
                                </td>
                            </tr>
                            <tr class="border-bottom" style="border-color: var(--premium-border-light) !important;">
                                <td class="ps-3"><span class="fw-700 text-primary">#1022</span></td>
                                <td><span class="fw-600" style="color: var(--premium-text-heading);">Fee Portal Error</span></td>
                                <td><span class="badge bg-light text-dark rounded-pill px-3">Administration</span></td>
                                <td><span class="text-muted-color small">Oct 24, 09:15 AM</span></td>
                                <td><span class="badge badge-soft-success px-3 rounded-pill">Resolved</span></td>
                                <td class="pe-3 text-end">
                                    <button class="btn btn-sm btn-light rounded-pill px-3 fw-600" data-bs-toggle="modal" data-bs-target="#actionModal">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3"><span class="fw-700 text-primary">#1021</span></td>
                                <td><span class="fw-600" style="color: var(--premium-text-heading);">WiFi Outage in Block B</span></td>
                                <td><span class="badge bg-light text-dark rounded-pill px-3">Facilities</span></td>
                                <td><span class="text-muted-color small">Oct 23, 16:00 PM</span></td>
                                <td><span class="badge badge-soft-danger px-3 rounded-pill">Rejected</span></td>
                                <td class="pe-3 text-end">
                                    <button class="btn btn-sm btn-light rounded-pill px-3 fw-600" data-bs-toggle="modal" data-bs-target="#actionModal">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Take Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom border-light px-4 py-3">
                <h5 class="modal-title fw-700" style="color: var(--premium-text-heading);">Take Action on Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <form id="actionForm">
                    <div class="mb-4">
                        <label class="form-label fw-600 text-secondary-color small">Change Status</label>
                        <select class="form-select border-light shadow-none" style="border-radius: var(--radius-md);">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600 text-secondary-color small">Admin Reply / Note</label>
                        <textarea class="form-control border-light shadow-none" rows="4" placeholder="Write your reply or internal note here..." style="border-radius: var(--radius-md); resize: none;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top border-light px-4 py-3 bg-light rounded-bottom" style="border-radius: 0 0 var(--radius-xl) var(--radius-xl);">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-600" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-600">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Init -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared Chart Options
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = "#8f9bba";
    Chart.defaults.plugins.tooltip.backgroundColor = "rgba(11, 20, 55, 0.9)";
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    
    // Bar Chart - Complaints by Category
    const barCtx = document.getElementById('barChart').getContext('2d');
    
    // Create gradient for bars
    let gradientPrimary = barCtx.createLinearGradient(0, 0, 0, 400);
    gradientPrimary.addColorStop(0, '#4318ff');   
    gradientPrimary.addColorStop(1, 'rgba(67, 24, 255, 0.2)');

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Academic', 'Facilities', 'Administration', 'Hostel', 'Other'],
            datasets: [{
                label: 'Complaints',
                data: [350, 420, 210, 250, 54],
                backgroundColor: gradientPrimary,
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(143, 155, 186, 0.1)',
                        drawBorder: false,
                    },
                    border: { display: false }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                    border: { display: false }
                }
            }
        }
    });

    // Doughnut Chart - Resolution Status
    const dCtx = document.getElementById('doughnutChart').getContext('2d');
    new Chart(dCtx, {
        type: 'doughnut',
        data: {
            labels: ['Resolved', 'In Progress', 'Pending', 'Rejected'],
            datasets: [{
                data: [890, 150, 200, 44],
                backgroundColor: [
                    '#01b574', // success
                    '#009fff', // info
                    '#ffb547', // warning
                    '#ee5d50'  // danger
                ],
                hoverOffset: 4,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 13,
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include '../components/footer.php'; ?>
</body>
</html>
