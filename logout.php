<?php
/**
 * Console logout – destroy session and redirect to login.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

auth_logout();

$base = FINANCIAL_BASE;
header('Location: ' . $base . '/login.php');
exit;
