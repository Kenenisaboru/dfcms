<?php
// admin/workflow_builder.php
require_once '../config/config.php';

// Only allow HOD and admin access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], array('hod', 'admin'))) {
    die("Access Denied: Only administrators can access the workflow builder.");
}

// Fetch existing workflow steps
$stmt = $pdo->query("SELECT * FROM workflow_steps ORDER BY step_order ASC");
$steps = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drag-and-Drop Workflow Builder - DFCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/next-gen-ui.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        .workflow-canvas {
            background: rgba(255, 255, 255, 0.02);
            border: 2px dashed var(--border-dark);
            border-radius: 1.5rem;
            min-height: 500px;
            padding: 2rem;
        }
        .step-card {
            background: var(--card-bg-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: grab;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .step-card:active {
            cursor: grabbing;
        }
        .step-card:hover {
            border-color: var(--primary);
            box-shadow: 0 0 15px var(--primary-glow);
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        .step-handle {
            color: #64748b;
            cursor: grab;
        }
        .ghost-step {
            opacity: 0.4;
            background: var(--primary-glow);
        }
    </style>
</head>
<body class="dark-mode">
    <nav class="main-header py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="../dashboard.php" class="text-decoration-none text-white h4 mb-0">
                <i class="fas fa-project-diagram text-success me-2"></i>Workflow Builder
            </a>
            <div class="d-flex gap-3">
                <button class="btn btn-success" onclick="saveWorkflow()">
                    <i class="fas fa-save me-2"></i>Save Workflow
                </button>
                <a href="../dashboard.php" class="btn btn-outline-light">Exit</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="row">
            <div class="col-lg-4">
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Available Roles</h5>
                    <p class="text-secondary small mb-4">Drag roles to build the escalation path.</p>
                    <div id="available-roles" class="d-flex flex-column gap-2">
                        <div class="step-card py-2 px-3" data-role="student">
                            <i class="fas fa-user-graduate text-info"></i> Student
                        </div>
                        <div class="step-card py-2 px-3" data-role="cr">
                            <i class="fas fa-users text-warning"></i> Class Rep
                        </div>
                        <div class="step-card py-2 px-3" data-role="teacher">
                            <i class="fas fa-chalkboard-teacher text-primary"></i> Teacher
                        </div>
                        <div class="step-card py-2 px-3" data-role="lab_assistant">
                            <i class="fas fa-flask text-danger"></i> Lab Assistant
                        </div>
                        <div class="step-card py-2 px-3" data-role="hod">
                            <i class="fas fa-user-tie text-success"></i> HOD
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="workflow-canvas" id="workflow-steps">
                    <?php if (empty($steps)): ?>
                        <div class="text-center py-5 empty-msg">
                            <i class="fas fa-plus-circle fa-3x text-secondary mb-3"></i>
                            <p class="text-secondary">Drag roles here to define the complaint lifecycle.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($steps as $index => $step): ?>
                            <div class="step-card" data-role="<?php echo $step['role_key']; ?>">
                                <div class="step-handle"><i class="fas fa-grip-vertical"></i></div>
                                <div class="step-number"><?php echo $index + 1; ?></div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo ucwords(str_replace('_', ' ', $step['role_key'])); ?></h6>
                                    <small class="text-secondary">Assigned for processing</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <button class="theme-toggle" aria-label="Toggle dark/light mode">
        <i class="fas fa-sun"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/next-gen-ui.js"></script>
    <script>
        const workflowEl = document.getElementById('workflow-steps');
        const rolesEl = document.getElementById('available-roles');

        // Initialize Sortable for workflow steps
        new Sortable(workflowEl, {
            group: 'workflow',
            animation: 150,
            ghostClass: 'ghost-step',
            handle: '.step-handle',
            onAdd: function (evt) {
                const item = evt.item;
                const role = item.getAttribute('data-role');
                const emptyMsg = workflowEl.querySelector('.empty-msg');
                if (emptyMsg) emptyMsg.remove();
                
                // Transform the dragged item into a proper step card
                item.innerHTML = `
                    <div class="step-handle"><i class="fas fa-grip-vertical"></i></div>
                    <div class="step-number">0</div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">${role.charAt(0).toUpperCase() + role.slice(1).replace('_', ' ')}</h6>
                        <small class="text-secondary">Assigned for processing</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                item.className = 'step-card';
                updateStepNumbers();
            },
            onEnd: updateStepNumbers
        });

        // Initialize Sortable for available roles (clone mode)
        new Sortable(rolesEl, {
            group: {
                name: 'workflow',
                pull: 'clone',
                put: false
            },
            animation: 150,
            sort: false
        });

        function removeStep(btn) {
            btn.closest('.step-card').remove();
            updateStepNumbers();
            if (workflowEl.children.length === 0) {
                workflowEl.innerHTML = `
                    <div class="text-center py-5 empty-msg">
                        <i class="fas fa-plus-circle fa-3x text-secondary mb-3"></i>
                        <p class="text-secondary">Drag roles here to define the complaint lifecycle.</p>
                    </div>
                `;
            }
        }

        function updateStepNumbers() {
            const steps = workflowEl.querySelectorAll('.step-card');
            steps.forEach((step, index) => {
                const num = step.querySelector('.step-number');
                if (num) num.textContent = index + 1;
            });
        }

        async function saveWorkflow() {
            const steps = Array.from(workflowEl.querySelectorAll('.step-card')).map((step, index) => ({
                role_key: step.getAttribute('data-role'),
                step_order: index + 1
            }));

            if (steps.length === 0) {
                alert('Please add at least one step to the workflow.');
                return;
            }

            try {
                const response = await fetch('api_save_workflow.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo CSRF::generate(); ?>'
                    },
                    body: JSON.stringify({ steps })
                });
                const data = await response.json();
                if (data.success) {
                    alert('Workflow saved successfully!');
                } else {
                    alert('Error saving workflow: ' + data.message);
                }
            } catch (e) {
                console.error(e);
                alert('An unexpected error occurred.');
            }
        }
    </script>
</body>
</html>
