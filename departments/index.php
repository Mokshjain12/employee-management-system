<?php
require_once '../app/config/config.php';
require_once '../app/models/Department.php';
require_once '../app/models/Employee.php';

// Check if user is admin
requireAdmin();

$pageTitle = 'Departments';

$departmentModel = new Department();
$employeeModel = new Employee();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setMessage('danger', 'Invalid security token.');
    } else {
        switch ($_POST['action']) {
            case 'add':
                $result = $departmentModel->create([
                    'name' => sanitize($_POST['name']),
                    'description' => sanitize($_POST['description'] ?? '')
                ]);
                
                if ($result) {
                    setMessage('success', 'Department added successfully!');
                } else {
                    setMessage('danger', 'Failed to add department.');
                }
                break;
                
            case 'update':
                $result = $departmentModel->update($_POST['department_id'], [
                    'name' => sanitize($_POST['name']),
                    'description' => sanitize($_POST['description'] ?? '')
                ]);
                
                if ($result) {
                    setMessage('success', 'Department updated successfully!');
                } else {
                    setMessage('danger', 'Failed to update department.');
                }
                break;
                
            case 'delete':
                if ($departmentModel->delete($_POST['department_id'])) {
                    setMessage('success', 'Department deleted successfully!');
                } else {
                    setMessage('danger', 'Failed to delete department.');
                }
                break;
        }
    }
    
    redirect(APP_URL . '/departments/');
}

// Get departments list
$departments = $departmentModel->getWithCount();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Departments</h2>
    <button class="btn btn-primary" data-modal="addDepartmentModal">
        <i class="fas fa-plus"></i> Add Department
    </button>
</div>

<!-- Departments Grid -->
<div class="row">
    <?php if (empty($departments)): ?>
        <div class="col-12">
            <div class="card">
                <div class="empty-state">
                    <i class="fas fa-building"></i>
                    <h3>No Departments</h3>
                    <p>Add your first department to get started.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($departments as $dept): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3><?php echo htmlspecialchars($dept['name']); ?></h3>
                            <p class="text-muted text-sm mb-0">
                                <?php echo htmlspecialchars($dept['description'] ?? 'No description'); ?>
                            </p>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline" onclick="toggleDeptDropdown(<?php echo $dept['id']; ?>)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu" id="dept-dropdown-<?php echo $dept['id']; ?>" style="display: none; position: absolute; right: 0; top: 100%;">
                                <a href="#" class="dropdown-item" onclick="editDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['name']); ?>', '<?php echo htmlspecialchars($dept['description'] ?? ''); ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if ($dept['employee_count'] == 0): ?>
                                    <a href="#" class="dropdown-item text-danger" onclick="deleteDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['name']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-icon primary" style="width: 40px; height: 40px;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?php echo $dept['employee_count']; ?></div>
                                <div class="text-sm text-muted">Employees</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Department Modal -->
<div class="modal-overlay" id="addDepartmentModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add Department</h3>
            <button class="modal-close" onclick="closeModal('addDepartmentModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="action" value="add">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Department Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter department name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter department description"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDepartmentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal-overlay" id="editDepartmentModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Edit Department</h3>
            <button class="modal-close" onclick="closeModal('editDepartmentModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="department_id" id="edit_department_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Department Name *</label>
                    <input type="text" name="name" id="edit_department_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_department_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editDepartmentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Department Modal -->
<div class="modal-overlay" id="deleteDepartmentModal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">Delete Department</h3>
            <button class="modal-close" onclick="closeModal('deleteDepartmentModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="department_id" id="delete_department_id">
            
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="delete_department_name"></strong>?</p>
                <p class="text-muted text-sm">This action cannot be undone.</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteDepartmentModal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDeptDropdown(id) {
    const dropdown = document.getElementById('dept-dropdown-' + id);
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function editDepartment(id, name, description) {
    document.getElementById('edit_department_id').value = id;
    document.getElementById('edit_department_name').value = name;
    document.getElementById('edit_department_description').value = description;
    openModal('editDepartmentModal');
    
    // Hide all dropdowns
    document.querySelectorAll('[id^="dept-dropdown-"]').forEach(el => el.style.display = 'none');
}

function deleteDepartment(id, name) {
    document.getElementById('delete_department_id').value = id;
    document.getElementById('delete_department_name').textContent = name;
    openModal('deleteDepartmentModal');
    
    // Hide all dropdowns
    document.querySelectorAll('[id^="dept-dropdown-"]').forEach(el => el.style.display = 'none');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('[id^="dept-dropdown-"]').forEach(el => el.style.display = 'none');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
