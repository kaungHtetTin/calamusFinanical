<?php
/**
 * Console authentication – session-based login using `admins` table.
 * Use require_login() in header so all pages that include header are protected.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to login if not authenticated. Call this from header.php.
 * Use $redirect to send user back after login (e.g. ?redirect=...).
 */
function require_login() {
    if (!empty($_SESSION['financial_admin_id'])) {
        return;
    }
    $redirect = isset($_GET['redirect']) ? '&redirect=' . urlencode($_GET['redirect']) : '';
    if (isset($_SERVER['REQUEST_URI'])) {
        $here = urlencode($_SERVER['REQUEST_URI']);
        if ($redirect === '') {
            $redirect = '?redirect=' . $here;
        }
    }
    $base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';
    header('Location: ' . $base . '/login.php' . $redirect);
    exit;
}

/**
 * Attempt login with email and password. Returns true on success, false otherwise.
 * Uses `admins` table; password must be stored with password_hash (bcrypt).
 *
 * @param \Database $db
 * @param string $email
 * @param string $password
 * @return bool
 */
function auth_login($db, $email, $password) {
    $email = trim($email);
    if ($email === '' || $password === '') {
        return false;
    }
    $conn = $db->connect();
    $email_esc = $conn->real_escape_string($email);
    $rows = $db->read("SELECT id, name, email, password FROM admins WHERE email = '$email_esc' LIMIT 1");
    if (!$rows || count($rows) === 0) {
        return false;
    }
    $row = $rows[0];
    if (!password_verify($password, $row['password'])) {
        return false;
    }
    $_SESSION['financial_admin_id'] = (int)$row['id'];
    $_SESSION['financial_admin_name'] = $row['name'];
    $_SESSION['financial_admin_email'] = $row['email'];
    return true;
}

/**
 * Log out and clear session.
 */
function auth_logout() {
    $_SESSION['financial_admin_id'] = null;
    $_SESSION['financial_admin_name'] = null;
    $_SESSION['financial_admin_email'] = null;
    session_destroy();
}

/**
 * Get current logged-in admin (id, name, email) or null.
 *
 * @return array|null
 */
function auth_user() {
    if (empty($_SESSION['financial_admin_id'])) {
        return null;
    }
    return [
        'id'    => (int)$_SESSION['financial_admin_id'],
        'name'  => $_SESSION['financial_admin_name'] ?? '',
        'email' => $_SESSION['financial_admin_email'] ?? '',
    ];
}
