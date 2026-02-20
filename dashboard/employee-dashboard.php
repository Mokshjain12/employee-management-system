<?php
require_once '../app/config/config.php';
require_once '../app/models/User.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Attendance.php';
require_once '../app/models/Leave.php';
require_once '../app/models/Salary.php';

// Check if user is logged in
requireLogin();

$pageTitle = 'My Dashboard';

$user_id = getUserId();
$userModel = new User();
$employeeModel = new Employee();
$attendanceModel = new Attendance();
$leaveModel = new Leave();
$salaryModel = new Salary();

$user = $userModel->getWithEmployee($user_id);
$employee = $employeeModel->getByUserId($user_id);
$todayAttendance = $attendanceModel->getTodayByUser($user_id);
$attendanceStats = $attendanceModel->getStats($user_id, date('Y-m'));
$leaveStats = $leaveModel->getStats($user_id);
$recentAttendance = $attendanceModel->getByUser($user_id, 7, 0);
$salaryHistory = $salaryModel->getHistory($user_id, 3);

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
    <div>
        <span class="text-muted"><?php echo date('l, F d, Y'); ?></span>
    </div>
</div>

<!-- Profile Card -->
<div class="card mb-4">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
        </div>
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <p><?php echo htmlspecialchars($employee['designation'] ?? 'Employee'); ?> - <?php echo htmlspecialchars($employee['employee_id'] ?? ''); ?></p>
            <p class="text-muted">
                <i class="fas fa-building"></i> <?php echo htmlspecialchars($employee['department_name'] ?? 'No Department'); ?>
                &nbsp;|&nbsp;
                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon <?php echo $todayAttendance ? 'success' : 'warning'; ?>">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Today's Status</div>
            <div class="stat-value">
                <?php if ($todayAttendance): ?>
                    <?php if ($todayAttendance['check_out']): ?>
                        Checked Out
                    <?php else: ?>
                        Checked In
                    <?php endif; ?>
                <?php else: ?>
                    Not Checked In
                <?php endif; ?>
            </div>
            <?php if ($todayAttendance && $todayAttendance['check_in']): ?>
                <div class="text-sm text-muted">At: <?php echo date('h:i A', strtotime($todayAttendance['check_in'])); ?></div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Present Days</div>
            <div class="stat-value"><?php echo $attendanceStats['present'] ?? 0; ?></div>
            <div class="text-sm text-muted">This month</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-calendar-minus"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Leave Balance</div>
            <div class="stat-value"><?php echo $leaveStats['approved'] ?? 0; ?></div>
            <div class="text-sm text-muted">Days approved</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Latest Salary</div>
            <div class="stat-value">
                <?php if (!empty($salaryHistory)): ?>
                    <?php echo formatCurrency($salaryHistory[0]['net_salary']); ?>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </div>
            <div class="text-sm text-muted">
                <?php if (!empty($salaryHistory)): ?>
                    <?php echo date('M Y', strtotime($salaryHistory[0]['month'] . '-01')); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Check In/Out Buttons -->
<?php if (!$todayAttendance || !$todayAttendance['check_out']): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Attendance</h3>
        </div>
        <div class="d-flex gap-3">
            <?php if (!$todayAttendance): ?>
                <button class="btn btn-success btn-lg" onclick="checkIn()">
                    <i class="fas fa-sign-in-alt"></i> Check In
                </button>
            <?php elseif (!$todayAttendance['check_out']): ?>
                <button class="btn btn-danger btn-lg" onclick="checkOut()">
                    <i class="fas fa-sign-out-alt"></i> Check Out
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Attendance</h3>
                <a href="<?php echo APP_URL; ?>/attendance/" class="btn btn-sm btn-secondary">View All</a>
            </div>
            
            <?php if (empty($recentAttendance)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <p>No attendance records</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAttendance as $att): ?>
                                <tr>
                                    <td><?php echo formatDate($att['date']); ?></td>
                                    <td><?php echo $att['check_in'] ? date('h:i A', strtotime($att['check_in'])) : '-'; ?></td>
                                    <td><?php echo $att['check_out'] ? date('h:i A', strtotime($att['check_out'])) : '-'; ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $att['status'] === 'present' ? 'success' : 
                                                ($att['status'] === 'absent' ? 'danger' : 
                                                ($att['status'] === 'late' ? 'warning' : 'info')); 
                                        ?>">
                                            <?php echo ucfirst($att['status']); ?>
                                        </span>
                                    </td>
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
                <h3 class="card-title">Salary History</h3>
                <a href="<?php echo APP_URL; ?>/salaries/" class="btn btn-sm btn-secondary">View All</a>
            </div>
            
            <?php if (empty($salaryHistory)): ?>
                <div class="empty-state">
                    <i class="fas fa-dollar-sign"></i>
                    <p>No salary records</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Basic Salary</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salaryHistory as $salary): ?>
                                <tr>
                                    <td><?php echo date('M Y', strtotime($salary['month'] . '-01')); ?></td>
                                    <td><?php echo formatCurrency($salary['basic_salary']); ?></td>
                                    <td><?php echo formatCurrency($salary['net_salary']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $salary['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($salary['payment_status']); ?>
                                        </span>
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

<?php include '../includes/footer.php'; ?>
