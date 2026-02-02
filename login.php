<?php
/**
 * Console login – email + password against `admins` table.
 * Redirects to index (or ?redirect=) after success. Does not use console header.
 */
$page_title = 'Login';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';
$error = '';

// Already logged in → redirect
if (auth_user()) {
    $goto = isset($_GET['redirect']) ? $_GET['redirect'] : ($base . '/index.php');
    if (!preg_match('#^[a-z0-9/_\-\.\?=&]+$#i', $goto)) {
        $goto = $base . '/index.php';
    }
    header('Location: ' . $goto);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (auth_login($db, $email, $password)) {
        $goto = isset($_POST['redirect']) ? trim($_POST['redirect']) : ($base . '/index.php');
        // Only allow same-origin path (no protocol or host)
        if ($goto === '' || strpos($goto, '://') !== false || !preg_match('#^[a-z0-9/_\-\.\?=&]+$#i', $goto)) {
            $goto = $base . '/index.php';
        }
        header('Location: ' . $goto);
        exit;
    }
    $error = 'Invalid email or password.';
}

$redirect_value = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – Financial Console</title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/console.css">
  <style>
    .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: var(--surface); }
    .login-card { width: 100%; max-width: 400px; background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 32px; }
    .login-card h1 { margin: 0 0 8px; font-size: 22px; font-weight: 500; color: var(--text-primary); }
    .login-card h1 span { color: var(--primary); }
    .login-card .sub { margin-bottom: 24px; font-size: 14px; color: var(--text-secondary); }
    .login-page .form-group { margin-bottom: 20px; }
    .login-page .form-group label { display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 6px; }
    .login-page .form-group input { width: 100%; padding: 10px 12px; font-size: 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); }
    .login-page .form-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2); }
    .login-page .btn-block { width: 100%; justify-content: center; margin-top: 8px; }
    .login-page .form-message-error { margin-bottom: 16px; }
  </style>
</head>
<body>
  <div class="login-page">
    <div class="login-card">
      <h1>Calamus <span>Financial</span></h1>
      <p class="sub">Sign in to the console</p>
      <?php if ($error): ?>
      <div class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="post" action="">
        <input type="hidden" name="redirect" value="<?php echo $redirect_value; ?>">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
      </form>
    </div>
  </div>
</body>
</html>
