<?php
require_once '../app/config/config.php';
require_once '../app/models/User.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Department.php';

// Check if user is admin
requireAdmin();

$pageTitle = 'Employees';

$employeeModel = new Employee();
$departmentModel = new Department();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setMessage('danger', 'Invalid security token.');
    } else {
        switch ($_POST['action']) {
            case 'add':
                // Create user first
                $userModel = new User();
                $userData = [
                    'username' => sanitize($_POST['username']),
                    'email' => sanitize($_POST['email']),
                    'password' => $_POST['password'],
                    'first_name' => sanitize($_POST['first_name']),
                    'last_name' => sanitize($_POST['last_name']),
                    'phone' => sanitize($_POST['phone'] ?? ''),
                    'role' => 'employee'
                ];
                
                $userResult = $userModel->register($userData);
                
                if ($userResult['success']) {
                    // Create employee record
                    $empData = [
                        'user_id' => $userResult['id'],
                        'department_id' => $_POST['department_id'],
                        'designation' => sanitize($_POST['designation']),
                        'salary' => $_POST['salary'],
                        'join_date' => $_POST['join_date']
                    ];
                    
                    $empResult = $employeeModel->create($empData);
                    
                    if ($empResult['success']) {
                        setMessage('success', 'Employee added successfully!');
                    } else {
                        setMessage('danger', 'Failed to create employee record.');
                    }
                } else {
                    setMessage('danger', $userResult['error']);
                }
                break;
                
            case 'delete':
                if ($employeeModel->delete($_POST['employee_id'])) {
                    setMessage('success', 'Employee deleted successfully!');
                } else {
                    setMessage('danger', 'Failed to delete employee.');
                }
                break;
        }
    }
    
    redirect(APP_URL . '/employees/');
}

// Get employees list
$search = $_GET['search'] ?? '';
$department_id = $_GET['department_id'] ?? null;
$employees = $employeeModel->getAll($search, $department_id);
$departments = $departmentModel->getAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Employees</h2>
    <button class="btn btn-primary" data-modal="addEmployeeModal">
        <i class="fas fa-user-plus"></i> Add Employee
    </button>
</div>

<!-- Filters -->
<div class="card mb-4">
    <form method="GET" action="" class="d-flex gap-3 align-items-end">
        <div class="form-group mb-0" style="flex: 1;">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Search by name, email or employee ID..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-control">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>" <?php echo $department_id == $dept['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="<?php echo APP_URL; ?>/employees/" class="btn btn-outline">Reset</a>
    </form>
</div>

<!-- Employees Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Join Date</th>
                    <th>Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No employees found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar" style="width: 36px; height: 36px; font-size: 0.875rem;">
                                        <?php echo strtoupper(substr($emp['first_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-medium"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></div>
                                        <div class="text-sm text-muted"><?php echo htmlspecialchars($emp['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($emp['employee_id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($emp['designation'] ?? 'N/A'); ?></td>
                            <td><?php echo $emp['join_date'] ? formatDate($emp['join_date']) : 'N/A'; ?></td>
                            <td><?php echo $emp['salary'] ? formatCurrency($emp['salary']) : 'N/A'; ?></td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-sm btn-outline" onclick="viewEmployee(<?php echo $emp['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteEmployee(<?php echo $emp['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal-overlay" id="addEmployeeModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Add New Employee</h3>
            <button class="modal-close" onclick="closeModal('addEmployeeModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="action" value="add">
            
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Department *</label>
                    <select name="department_id" class="form-control" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Designation *</label>
                        <input type="text" name="designation" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Salary</label>
                        <input type="number" name="salary" class="form-control" step="0.01">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Join Date *</label>
                    <input type="date" name="join_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addEmployeeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add Employee
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
