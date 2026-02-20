<?php
require_once '../app/config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard/');
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $user = new User();
        $result = $user->login($username, $password);
        
        if ($result['success']) {
            // Redirect based on role
            if ($result['user']['role'] === 'admin') {
                redirect(APP_URL . '/dashboard/');
            } else {
                redirect(APP_URL . '/dashboard/employee-dashboard.php');
            }
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-users-cog" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h1><?php echo APP_NAME; ?></h1>
                <p class="text-muted">Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                
                <div class="form-group">
                    <label class="form-label" for="username">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Enter username or email" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter password" required>
                </div>
                
                <div class="form-group d-flex justify-content-between align-items-center">
                    <label class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="remember"> 
                        <span class="text-sm">Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="auth-divider">or</div>
            
            <p class="text-center">
                Don't have an account? 
                <a href="register.php" style="color: var(--primary-color); font-weight: 500;">Create Account</a>
            </p>
            
            <div class="text-center mt-4 text-muted text-sm">
                <p>Default Login:</p>
                <p>Admin: admin / admin123</p>
                <p>Employee: john.doe / admin123</p>
            </div>
        </div>
    </div>
    
    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
</body>
</html>
