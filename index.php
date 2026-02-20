<?php
require_once 'app/config/config.php';

// Redirect based on login status
if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard/');
} else {
    redirect(APP_URL . '/auth/login.php');
}
