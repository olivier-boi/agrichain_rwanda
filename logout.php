<?php
require_once __DIR__ . '/includes/auth.php';
$_SESSION = [];
session_destroy();
session_start();
flash('You have been logged out.', 'success');
redirect('login.php');
