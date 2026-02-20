<?php
require_once '../app/config/config.php';
require_once '../app/models/User.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Attendance.php';

// Check if user is logged in
requireLogin();

$pageTitle = 'Attendance';

$user_id = getUserId();
$isAdmin = getUserRole() === 'admin';

$attendanceModel = new Attendance();
$userModel = new User();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'check_in':
            $result = $attendanceModel->checkIn($user_id);
            echo json_encode($result);
            exit;
            
        case 'check_out':
            $result = $attendanceModel->checkOut($user_id);
            echo json_encode($result);
            exit;
            
        case 'mark_attendance':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                exit;
            }
            
            $result = $attendanceModel->markAttendance([
                'user_id' => $_POST['user_id'],
                'date' => $_POST['date'],
                'check_in' => $_POST['check_in'],
                'check_out' => $_POST['check_out'],
                'status' => $_POST['status'],
                'notes' => $_POST['notes'] ?? ''
            ]);
            echo json_encode(['success' => $result]);
            exit;
    }
}

// Get attendance records
$date = $_GET['date'] ?? date('Y-m-d');
$status = $_GET['status'] ?? null;
$selected_user = $_GET['user_id'] ?? null;

if ($isAdmin) {
    $attendanceRecords = $attendanceModel->getAll($date, $selected_user, $status);
    $employees = $userModel->getAllEmployees();
} else {
    $attendanceRecords = $attendanceModel->getByUser($user_id);
    $todayAttendance = $attendanceModel->getTodayByUser($user_id);
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $isAdmin ? 'Attendance Management' : 'My Attendance'; ?></h2>
    <?php if (!$isAdmin && (!$todayAttendance || !$todayAttendance['check_out'])): ?>
        <div>
            <?php if (!$todayAttendance): ?>
                <button class="btn btn-success" onclick="checkIn()">
                    <i class="fas fa-sign-in-alt"></i> Check In
                </button>
            <?php elseif (!$todayAttendance['check_out']): ?>
                <button class="btn btn-danger" onclick="checkOut()">
                    <i class="fas fa-sign-out-alt"></i> Check Out
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Filters (Admin Only) -->
<?php if ($isAdmin): ?>
    <div class="card mb-4">
        <form method="GET" action="" class="d-flex gap-3 align-items-end">
            <div class="form-group mb-0">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="<?php echo $date; ?>">
            </div>
            <div class="form-group mb-0">
                <label class="form-label">Employee</label>
                <select name="user_id" class="form-control">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo $emp['user_id']; ?>" <?php echo $selected_user == $emp['user_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="present" <?php echo $status === 'present' ? 'selected' : ''; ?>>Present</option>
                    <option value="absent" <?php echo $status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                    <option value="late" <?php echo $status === 'late' ? 'selected' : ''; ?>>Late</option>
                    <option value="half_day" <?php echo $status === 'half_day' ? 'selected' : ''; ?>>Half Day</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
            <button type="button" class="btn btn-primary" data-modal="markAttendanceModal">
                <i class="fas fa-plus"></i> Mark Attendance
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- Today's Status (Employee View) -->
<?php if (!$isAdmin && $todayAttendance): ?>
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Today's Status</h3>
                <p class="text-muted mb-0"><?php echo date('l, F d, Y'); ?></p>
            </div>
            <div class="text-right">
                <span class="badge badge-<?php 
                    echo $todayAttendance['status'] === 'present' ? 'success' : 
                        ($todayAttendance['status'] === 'absent' ? 'danger' : 
                        ($todayAttendance['status'] === 'late' ? 'warning' : 'info')); 
                ?> badge-lg">
                    <?php echo ucfirst($todayAttendance['status']); ?>
                </span>
                <?php if ($todayAttendance['check_in']): ?>
                    <p class="text-sm text-muted mt-2">
                        Checked in at: <?php echo date('h:i A', strtotime($todayAttendance['check_in'])); ?>
                        <?php if ($todayAttendance['check_out']): ?>
                            | Checked out at: <?php echo date('h:i A', strtotime($todayAttendance['check_out'])); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Attendance Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <?php if ($isAdmin): ?>
                        <th>Employee</th>
                    <?php endif; ?>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendanceRecords)): ?>
                    <tr>
                        <td colspan="<?php echo $isAdmin ? 6 : 5; ?>" class="text-center text-muted">No attendance records found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($attendanceRecords as $record): ?>
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
                            <td><?php echo formatDate($record['date']); ?></td>
                            <td><?php echo $record['check_in'] ? date('h:i A', strtotime($record['check_in'])) : '-'; ?></td>
                            <td><?php echo $record['check_out'] ? date('h:i A', strtotime($record['check_out'])) : '-'; ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $record['status'] === 'present' ? 'success' : 
                                        ($record['status'] === 'absent' ? 'danger' : 
                                        ($record['status'] === 'late' ? 'warning' : 'info')); 
                                ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($record['notes'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mark Attendance Modal (Admin Only) -->
<?php if ($isAdmin): ?>
    <div class="modal-overlay" id="markAttendanceModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Mark Attendance</h3>
                <button class="modal-close" onclick="closeModal('markAttendanceModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="markAttendanceForm">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="action" value="mark_attendance">
                
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
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Check In Time</label>
                            <input type="time" name="check_in" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Check Out Time</label>
                            <input type="time" name="check_out" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('markAttendanceModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark Attendance</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('markAttendanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Attendance marked successfully!', 'success');
                    closeModal('markAttendanceModal');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.error || 'Failed to mark attendance', 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred', 'danger');
            });
        });
    </script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
