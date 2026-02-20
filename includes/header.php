<?php
// Check if user is logged in
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-users-cog" style="font-size: 2rem; color: var(--primary-color);"></i>
            <h1>EMS</h1>
        </div>
        
        <nav class="sidebar-nav">
            <?php if (getUserRole() === 'admin'): ?>
                <div class="nav-section-title">Main</div>
                <ul class="nav-item">
                    <li><a href="<?php echo APP_URL; ?>/dashboard/" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a></li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="nav-item">
                    <li><a href="<?php echo APP_URL; ?>/employees/" class="nav-link">
                        <i class="fas fa-users"></i> Employees
                    </a></li>
                    <li><a href="<?php echo APP_URL; ?>/departments/" class="nav-link">
                        <i class="fas fa-building"></i> Departments
                    </a></li>
                    <li><a href="<?php echo APP_URL; ?>/attendance/" class="nav-link">
                        <i class="fas fa-calendar-check"></i> Attendance
                    </a></li>
                    <li><a href="<?php echo APP_URL; ?>/leaves/" class="nav-link">
                        <i class="fas fa-calendar-minus"></i> Leave Requests
                    </a></li>
                    <li><a href="<?php echo APP_URL; ?>/salaries/" class="nav-link">
                        <i class="fas fa-dollar-sign"></i> Salaries
                    </a></li>
                </ul>
            <?php else: ?>
                <div class="nav-section-title">Main</div>
                <ul class="nav-item">
                    <li><a href="<?php echo APP_URL; ?>/dashboard/employee-dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i> Dashboard
                    </a></li>
                </ul>
                
                <div class="nav-section-title">My Records</div>
                <ul class="nav-item">
                    <li><a href="<?php echo APP_URL; ?>/attendance/" class="nav-link">
                        <i class="fas fa-calendar-check"></i> My Attendance
                    </a></li>
                    <li><a href="<?php echo APP_URL; ?>/leaves/" class="nav-link">
                        <i class="fas fa-calendar-minus"></i> Leave Requests
                    </a></li>
                    <li><a href="<?php echo APP_URL; ?>/salaries/" class="nav-link">
                        <i class="fas fa-dollar-sign"></i> My Salary
                    </a></li>
                </ul>
            <?php endif; ?>
            
            <div class="nav-section-title">Account</div>
            <ul class="nav-item">
                <li><a href="#" class="nav-link">
                    <i class="fas fa-user"></i> Profile
                </a></li>
                <li><a href="<?php echo APP_URL; ?>/auth/logout.php" id="logout-link" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a></li>
            </ul>
        </nav>
    </aside>
    
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="menu-toggle" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="global-search" placeholder="Search...">
            </div>
        </div>
        
        <div class="header-right">
            <button class="btn btn-icon" id="theme-toggle" data-tooltip="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
            
            <div class="user-menu" onclick="toggleDropdown()">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="user-info d-none d-md-block">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></div>
                    <div class="user-role"><?php echo ucfirst(getUserRole()); ?></div>
                </div>
                
                <div class="dropdown-menu" id="user-dropdown">
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo APP_URL; ?>/auth/logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="page-content">
        <?php
        // Display message if exists
        $message = getMessage();
        if ($message): 
        ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                <span><?php echo $message['text']; ?></span>
            </div>
        <?php endif; ?>
