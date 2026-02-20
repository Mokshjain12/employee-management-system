<?php
require_once '../app/config/config.php';
require_once '../app/models/User.php';
require_once '../app/models/Salary.php';

// Check if user is admin
requireAdmin();

$pageTitle = 'Salary Management';

$salaryModel = new Salary();
$userModel = new User();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'add':
            $result = $salaryModel->create([
                'user_id' => $_POST['user_id'],
                'month' => $_POST['month'],
                'basic_salary' => $_POST['basic_salary'],
                'allowances' => $_POST['allowances'] ?? 0,
                'deductions' => $_POST['deductions'] ?? 0,
                'payment_status' => 'pending'
            ]);
            echo json_encode($result);
            exit;
            
        case 'mark_paid':
            $result = $salaryModel->markAsPaid($_POST['salary_id']);
            echo json_encode($result);
            exit;
            
        case 'delete':
            if ($salaryModel->delete($_POST['salary_id'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete']);
            }
            exit;
    }
}

// Get salary records
$month = $_GET['month'] ?? null;
$status = $_GET['status'] ?? null;

$salaryRecords = $salaryModel->getAll($month, $status);
$employees = $userModel->getAllEmployees();
$availableMonths = $salaryModel->getAvailableMonths();
$salaryStats = $salaryModel->getStats();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Salary Management</h2>
    <button class="btn btn-primary" data-modal="addSalaryModal">
        <i class="fas fa-plus"></i> Add Salary Record
    </button>
</div>

<!-- Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Records</div>
            <div class="stat-value"><?php echo $salaryStats['total_records'] ?? 0; ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Paid</div>
            <div class="stat-value"><?php echo formatCurrency($salaryStats['total_paid'] ?? 0); ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Pending</div>
            <div class="stat-value"><?php echo formatCurrency($salaryStats['total_pending'] ?? 0); ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Payroll</div>
            <div class="stat-value"><?php echo formatCurrency($salaryStats['total_net'] ?? 0); ?></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <form method="GET" action="" class="d-flex gap-3 align-items-end">
        <div class="form-group mb-0">
            <label class="form-label">Month</label>
            <select name="month" class="form-control">
                <option value="">All Months</option>
                <?php foreach ($availableMonths as $m): ?>
                    <option value="<?php echo $m; ?>" <?php echo $month === $m ? 'selected' : ''; ?>>
                        <?php echo date('F Y', strtotime($m . '-01')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="<?php echo APP_URL; ?>/salaries/" class="btn btn-outline">Reset</a>
    </form>
</div>

<!-- Salary Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Month</th>
                    <th>Basic Salary</th>
                    <th>Allowances</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th>Payment Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($salaryRecords)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">No salary records found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($salaryRecords as $record): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        <?php echo strtoupper(substr($record['first_name'], 0, 1)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>
                                </div>
                            </td>
                            <td><?php echo date('M Y', strtotime($record['month'] . '-01')); ?></td>
                            <td><?php echo formatCurrency($record['basic_salary']); ?></td>
                            <td><?php echo formatCurrency($record['allowances']); ?></td>
                            <td><?php echo formatCurrency($record['deductions']); ?></td>
                            <td class="font-bold"><?php echo formatCurrency($record['net_salary']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $record['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($record['payment_status']); ?>
                                </span>
                            </td>
                            <td><?php echo $record['payment_date'] ? formatDate($record['payment_date']) : '-'; ?></td>
                            <td>
                                <div class="table-actions">
                                    <?php if ($record['payment_status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-success" onclick="markSalaryPaid(<?php echo $record['id']; ?>)">
                                            <i class="fas fa-check"></i> Pay
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Salary Modal -->
<div class="modal-overlay" id="addSalaryModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add Salary Record</h3>
            <button class="modal-close" onclick="closeModal('addSalaryModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addSalaryForm">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="action" value="add">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Employee *</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['user_id']; ?>">
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Month *</label>
                    <input type="month" name="month" class="form-control" required value="<?php echo date('Y-m'); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Basic Salary *</label>
                    <input type="number" name="basic_salary" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Allowances</label>
                        <input type="number" name="allowances" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deductions</label>
                        <input type="number" name="deductions" class="form-control" step="0.01" value="0">
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addSalaryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Record</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('addSalaryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Salary record added successfully!', 'success');
                closeModal('addSalaryModal');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.error || 'Failed to add salary record', 'danger');
            }
        })
        .catch(error => {
            showAlert('An error occurred', 'danger');
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
