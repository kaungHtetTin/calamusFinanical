<?php
/**
 * Console login - email + password against `admins` table.
 * Redirects to index (or ?redirect=) after success. Does not use console header.
 */
$page_title = 'Login';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';
$error = '';

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
    $remember = !empty($_POST['remember']);
    if (auth_login($db, $email, $password, $remember)) {
        $goto = isset($_POST['redirect']) ? trim($_POST['redirect']) : ($base . '/index.php');
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
  <title>Login - Financial Console</title>
  <script>
  (function() {
    var theme = localStorage.getItem('financial.theme') || 'light';
    var brand = localStorage.getItem('financial.brand') || '#545760';
    document.documentElement.dataset.theme = theme;
    document.documentElement.style.setProperty('--color-primary', brand);
  })();
  </script>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/console.css">
</head>
<body class="app-root auth-page" data-theme="light" style="--color-primary: #545760">
  <main class="auth-card glass">
    <p class="eyebrow">Office Console</p>
    <h1>Calamus Financial</h1>
    <p class="sub">Sign in to the console</p>

    <?php if ($error): ?>
    <div class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <input type="hidden" name="redirect" value="<?php echo $redirect_value; ?>">
      <label class="form-field" for="email">
        <span>Email</span>
        <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
      </label>
      <label class="form-field" for="password">
        <span>Password</span>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </label>
      <label class="checkbox-label">
        <input type="checkbox" name="remember" value="1" <?php echo !empty($_POST['remember']) ? 'checked' : ''; ?>>
        <span>Remember me</span>
      </label>
      <button type="submit" class="btn primary btn-block">Sign in</button>
    </form>
  </main>
</body>
</html>
