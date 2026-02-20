<?php
require_once '../app/config/config.php';

$user = new User();
$user->logout();

redirect(APP_URL . '/auth/login.php');
