<?php
require_once __DIR__ . '/includes/auth.php';
logout_user();
session_start();
flash('auth', 'You have been logged out.', 'info');
redirect('login.php');
