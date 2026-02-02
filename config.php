<?php
require_once __DIR__ . '/connect.php';
$db = new Database();
$conn = $db->connect();

// Base path for financial console (for links)
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if (!defined('FINANCIAL_BASE')) {
    define('FINANCIAL_BASE', $base_url);
}

