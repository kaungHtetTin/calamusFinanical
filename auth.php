<?php
/**
 * Console authentication – session-based login using `admins` table.
 * Use require_login() in header so all pages that include header are protected.
 * Supports "Remember me" via secure cookie + database tokens.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('AUTH_REMEMBER_DAYS', 30);
define('AUTH_REMEMBER_COOKIE', 'financial_remember');

/**
 * Ensure remember_tokens table exists.
 */
function auth_ensure_remember_table($db) {
    $conn = $db->connect();
    $conn->query("CREATE TABLE IF NOT EXISTS remember_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        token_hash VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_token (token_hash),
        INDEX idx_admin (admin_id),
        INDEX idx_expires (expires_at)
    )");
}

/**
 * Redirect to login if not authenticated. Call this from header.php.
 * Tries remember-me cookie before redirecting.
 * Use $redirect to send user back after login (e.g. ?redirect=...).
 *
 * @param Database|null $db Database instance (required for remember-me; uses global if omitted)
 */
function require_login($db = null) {
    if (!empty($_SESSION['financial_admin_id'])) {
        return;
    }
    if ($db === null && isset($GLOBALS['db'])) {
        $db = $GLOBALS['db'];
    }
    if ($db !== null) {
        auth_try_remember($db);
        if (!empty($_SESSION['financial_admin_id'])) {
            return;
        }
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
 * @param bool $remember Set remember-me cookie when true
 * @return bool
 */
function auth_login($db, $email, $password, $remember = false) {
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
    if ($remember) {
        auth_set_remember_token($db, (int)$row['id']);
    }
    return true;
}

/**
 * Set remember-me cookie and store token in database.
 */
function auth_set_remember_token($db, $admin_id) {
    auth_ensure_remember_table($db);
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', strtotime('+' . AUTH_REMEMBER_DAYS . ' days'));
    $conn = $db->connect();
    $admin_id_esc = (int)$admin_id;
    $hash_esc = $conn->real_escape_string($token_hash);
    $exp_esc = $conn->real_escape_string($expires);
    $db->save("INSERT INTO remember_tokens (admin_id, token_hash, expires_at) VALUES ($admin_id_esc, '$hash_esc', '$exp_esc')");
    $base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';
    $path = $base ?: '/';
    setcookie(AUTH_REMEMBER_COOKIE, $token, strtotime($expires), $path, '', isset($_SERVER['HTTPS']), true);
}

/**
 * Try to restore session from remember-me cookie.
 */
function auth_try_remember($db) {
    $cookie = $_COOKIE[AUTH_REMEMBER_COOKIE] ?? null;
    if (!$cookie || strlen($cookie) !== 64 || !ctype_xdigit($cookie)) {
        return false;
    }
    auth_ensure_remember_table($db);
    $token_hash = hash('sha256', $cookie);
    $conn = $db->connect();
    $hash_esc = $conn->real_escape_string($token_hash);
    $now = date('Y-m-d H:i:s');
    $rows = $db->read("SELECT rt.admin_id, a.name, a.email FROM remember_tokens rt
        INNER JOIN admins a ON a.id = rt.admin_id
        WHERE rt.token_hash = '$hash_esc' AND rt.expires_at > '$now' LIMIT 1");
    if (!$rows || count($rows) === 0) {
        auth_clear_remember_cookie();
        return false;
    }
    $row = $rows[0];
    $_SESSION['financial_admin_id'] = (int)$row['admin_id'];
    $_SESSION['financial_admin_name'] = $row['name'];
    $_SESSION['financial_admin_email'] = $row['email'];
    $conn->query("DELETE FROM remember_tokens WHERE token_hash = '$hash_esc'");
    auth_set_remember_token($db, (int)$row['admin_id']);
    return true;
}

/**
 * Clear remember-me cookie and optionally delete token from DB.
 */
function auth_clear_remember_cookie() {
    $base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';
    $path = $base ?: '/';
    setcookie(AUTH_REMEMBER_COOKIE, '', time() - 3600, $path, '', isset($_SERVER['HTTPS']), true);
}

/**
 * Log out and clear session and remember-me.
 */
function auth_logout($db = null) {
    if ($db === null && isset($GLOBALS['db'])) {
        $db = $GLOBALS['db'];
    }
    if ($db !== null) {
        $cookie = $_COOKIE[AUTH_REMEMBER_COOKIE] ?? null;
        if ($cookie && strlen($cookie) === 64 && ctype_xdigit($cookie)) {
            auth_ensure_remember_table($db);
            $token_hash = hash('sha256', $cookie);
            $conn = $db->connect();
            $hash_esc = $conn->real_escape_string($token_hash);
            $conn->query("DELETE FROM remember_tokens WHERE token_hash = '$hash_esc'");
        }
    }
    auth_clear_remember_cookie();
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
