<?php
require_once '../app/config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard/');
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $data = [
            'username' => sanitize($_POST['username'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'role' => 'employee' // Default role
        ];
        
        // Validation
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $error = 'Please fill in all required fields.';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $error = 'Passwords do not match.';
        } elseif (strlen($data['password']) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $user = new User();
            
            // Check if username exists
            if ($user->usernameExists($data['username'])) {
                $error = 'Username already exists.';
            } elseif ($user->emailExists($data['email'])) {
                $error = 'Email already exists.';
            } else {
                $result = $user->register($data);
                
                if ($result['success']) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = $result['error'];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
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
                <p class="text-muted">Create your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
                <p class="text-center">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </p>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                   placeholder="First name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                   placeholder="Last name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Choose a username" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               placeholder="Phone number">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="password">Password *</label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Create password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                   placeholder="Confirm password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
                
                <div class="auth-divider">or</div>
                
                <p class="text-center">
                    Already have an account? 
                    <a href="login.php" style="color: var(--primary-color); font-weight: 500;">Sign In</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
</body>
</html>
