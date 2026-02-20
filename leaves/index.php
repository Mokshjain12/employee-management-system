<?php
require_once '../app/config/config.php';
require_once '../app/models/User.php';
require_once '../app/models/Leave.php';

// Check if user is logged in
requireLogin();

$pageTitle = 'Leave Requests';

$user_id = getUserId();
$isAdmin = getUserRole() === 'admin';

$leaveModel = new Leave();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'submit':
            $result = $leaveModel->create([
                'user_id' => $user_id,
                'leave_type' => $_POST['leave_type'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'reason' => $_POST['reason'] ?? ''
            ]);
            echo json_encode($result);
            exit;
            
        case 'approve':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                exit;
            }
            $result = $leaveModel->approve($_POST['leave_id'], $user_id);
            echo json_encode($result);
            exit;
            
        case 'reject':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                exit;
            }
            $result = $leaveModel->reject($_POST['leave_id'], $user_id);
            echo json_encode($result);
            exit;
            
        case 'cancel':
            $result = $leaveModel->cancel($_POST['leave_id'], $user_id);
            echo json_encode(['success' => $result]);
            exit;
    }
}

// Get leave records
$status = $_GET['status'] ?? null;

if ($isAdmin) {
    $leaveRecords = $leaveModel->getAll($status);
    $pendingCount = $leaveModel->getPendingCount();
} else {
    $leaveRecords = $leaveModel->getByUser($user_id);
    $leaveStats = $leaveModel->getStats($user_id);
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isAdmin ? 'Leave Management' : 'My Leave Requests'; ?></h2>
    <?php if (!$isAdmin): ?>
        <button class="btn btn-primary" data-modal="requestLeaveModal">
            <i class="fas fa-plus"></i> Request Leave
        </button>
    <?php endif; ?>
</div>

<!-- Admin Stats -->
<?php if ($isAdmin && $pendingCount > 0): ?>
    <div class="card mb-4" style="border-left: 4px solid var(--warning-color);">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">Pending Leave Requests</h3>
                <p class="text-muted mb-0"><?php echo $pendingCount; ?> request(s) waiting for review</p>
            </div>
            <a href="?status=pending" class="btn btn-warning">Review Now</a>
        </div>
    </div>
<?php endif; ?>

<!-- Employee Leave Stats -->
<?php if (!$isAdmin && !empty($leaveStats)): ?>
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Requests</div>
                <div class="stat-value"><?php echo $leaveStats['total']; ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?php echo $leaveStats['pending']; ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Approved</div>
                <div class="stat-value"><?php echo $leaveStats['approved']; ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-times"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Rejected</div>
                <div class="stat-value"><?php echo $leaveStats['rejected']; ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Filters (Admin Only) -->
<?php if ($isAdmin): ?>
    <div class="card mb-4">
        <form method="GET" action="" class="d-flex gap-3 align-items-end">
            <div class="form-group mb-0">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="<?php echo APP_URL; ?>/leaves/" class="btn btn-outline">Reset</a>
        </form>
    </div>
<?php endif; ?>

<!-- Leave Requests Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <?php if ($isAdmin): ?>
                        <th>Employee</th>
                    <?php endif; ?>
                    <th>Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaveRecords)): ?>
                    <tr>
                        <td colspan="<?php echo $isAdmin ? 8 : 7; ?>" class="text-center text-muted">No leave requests found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leaveRecords as $record): ?>
                        <tr>
                            <?php if ($isAdmin): ?>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            <?php echo strtoupper(substr($record['first_name'], 0, 1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <td><?php echo ucfirst($record['leave_type']); ?></td>
                            <td><?php echo formatDate($record['start_date']); ?></td>
                            <td><?php echo formatDate($record['end_date']); ?></td>
                            <td><?php echo $record['days']; ?></td>
                            <td><?php echo htmlspecialchars($record['reason'] ?? '-'); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $record['status'] === 'approved' ? 'success' : 
                                        ($record['status'] === 'rejected' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($isAdmin && $record['status'] === 'pending'): ?>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-success" onclick="approveLeave(<?php echo $record['id']; ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectLeave(<?php echo $record['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php elseif (!$isAdmin && $record['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-danger" onclick="cancelLeave(<?php echo $record['id']; ?>)">
                                        Cancel
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Request Leave Modal (Employee) -->
<?php if (!$isAdmin): ?>
    <div class="modal-overlay" id="requestLeaveModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Request Leave</h3>
                <button class="modal-close" onclick="closeModal('requestLeaveModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="requestLeaveForm">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="action" value="submit">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Leave Type *</label>
                        <select name="leave_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="sick">Sick Leave</option>
                            <option value="casual">Casual Leave</option>
                            <option value="annual">Annual Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for leave..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('requestLeaveModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('requestLeaveForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Leave request submitted successfully!', 'success');
                    closeModal('requestLeaveModal');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.error || 'Failed to submit leave request', 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred', 'danger');
            });
        });
    </script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
