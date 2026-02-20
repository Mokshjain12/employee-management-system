<?php
require_once '../app/config/config.php';
require_once '../app/models/User.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Attendance.php';
require_once '../app/models/Leave.php';
require_once '../app/models/Salary.php';
require_once '../app/models/Department.php';

// Check if user is admin
requireAdmin();

$pageTitle = 'Dashboard';

// Get statistics
$userModel = new User();
$employeeModel = new Employee();
$attendanceModel = new Attendance();
$leaveModel = new Leave();
$salaryModel = new Salary();
$departmentModel = new Department();

$totalUsers = $userModel->getTotalCount();
$totalEmployees = $userModel->getTotalCount('employee');
$pendingLeaves = $leaveModel->getPendingCount();
$pendingSalaries = $salaryModel->getPendingCount();
$recentEmployees = $employeeModel->getRecent(5);
$departmentStats = $employeeModel->getDepartmentStats();
$attendanceStats = $attendanceModel->getStats(null, date('Y-m'));

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Admin Dashboard</h2>
    <div>
        <span class="text-muted"><?php echo date('l, F d, Y'); ?></span>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Employees</div>
            <div class="stat-value"><?php echo $totalEmployees; ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Departments</div>
            <div class="stat-value"><?php echo count($departmentStats); ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-calendar-minus"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Pending Leaves</div>
            <div class="stat-value"><?php echo $pendingLeaves; ?></div>
            <?php if ($pendingLeaves > 0): ?>
                <div class="stat-change negative">
                    <a href="<?php echo APP_URL; ?>/leaves/">Review now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Pending Salaries</div>
            <div class="stat-value"><?php echo $pendingSalaries; ?></div>
            <?php if ($pendingSalaries > 0): ?>
                <div class="stat-change negative">
                    <a href="<?php echo APP_URL; ?>/salaries/">Process now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Overview</h3>
            </div>
            <div class="chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Department Distribution</h3>
            </div>
            <div class="chart-container">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Employees and Pending Actions -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Employees</h3>
                <a href="<?php echo APP_URL; ?>/employees/" class="btn btn-sm btn-secondary">View All</a>
            </div>
            
            <?php if (empty($recentEmployees)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No employees found</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Join Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEmployees as $emp): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                <?php echo strtoupper(substr($emp['first_name'], 0, 1)); ?>
                                            </div>
                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatDate($emp['join_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            
            <div class="d-flex flex-column gap-2">
                <a href="<?php echo APP_URL; ?>/employees/?action=add" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New Employee
                </a>
                <a href="<?php echo APP_URL; ?>/attendance/" class="btn btn-secondary">
                    <i class="fas fa-calendar-check"></i> Manage Attendance
                </a>
                <a href="<?php echo APP_URL; ?>/leaves/" class="btn btn-secondary">
                    <i class="fas fa-calendar-minus"></i> Review Leave Requests
                    <?php if ($pendingLeaves > 0): ?>
                        <span class="badge badge-danger"><?php echo $pendingLeaves; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo APP_URL; ?>/salaries/" class="btn btn-secondary">
                    <i class="fas fa-dollar-sign"></i> Manage Salaries
                </a>
                <a href="<?php echo APP_URL; ?>/departments/" class="btn btn-secondary">
                    <i class="fas fa-building"></i> Manage Departments
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Department Chart
    const deptCtx = document.getElementById('departmentChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($departmentStats, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($departmentStats, 'employee_count')); ?>,
                backgroundColor: [
                    'rgba(79, 70, 229, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(6, 182, 212, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Attendance Chart (sample data)
    const attCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attCtx, {
        type: 'bar',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Half Day'],
            datasets: [{
                label: 'This Month',
                data: [
                    <?php echo $attendanceStats['present'] ?? 0; ?>,
                    <?php echo $attendanceStats['absent'] ?? 0; ?>,
                    <?php echo $attendanceStats['late'] ?? 0; ?>,
                    <?php echo $attendanceStats['half_day'] ?? 0; ?>
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(6, 182, 212, 0.8)'
                ],
                borderRadius: 6
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
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
