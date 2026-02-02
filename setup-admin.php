<?php
/**
 * One-time setup: create or update an admin for console login.
 * Run from browser once, then delete or restrict access.
 *
 * Usage: /financial/setup-admin.php
 * Or with params: ?email=admin@example.com&password=yourpassword&name=Admin
 * Passwords are stored with password_hash (bcrypt).
 */
require_once __DIR__ . '/config.php';

$base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['email'])) {
    $email = trim($_POST['email'] ?? $_GET['email'] ?? '');
    $password = $_POST['password'] ?? $_GET['password'] ?? '';
    $name = trim($_POST['name'] ?? $_GET['name'] ?? 'Admin');

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $conn = $db->connect();
        $email_esc = $conn->real_escape_string($email);
        $existing = $db->read("SELECT id, password FROM admins WHERE email = '$email_esc' LIMIT 1");
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($existing && count($existing) > 0) {
            $id = (int)$existing[0]['id'];
            $name_esc = $conn->real_escape_string($name);
            if ($db->save("UPDATE admins SET name = '$name_esc', password = '" . $conn->real_escape_string($hash) . "' WHERE id = $id")) {
                $message = 'Admin updated. You can now log in at the console.';
            } else {
                $error = 'Update failed.';
            }
        } else {
            $name_esc = $conn->real_escape_string($name);
            $hash_esc = $conn->real_escape_string($hash);
            if ($db->save("INSERT INTO admins (name, email, password) VALUES ('$name_esc', '$email_esc', '$hash_esc')")) {
                $message = 'Admin created. You can now log in at the console.';
            } else {
                $error = 'Insert failed. Check that the admins table exists.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup admin – Financial Console</title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/console.css">
  <style>
    .setup-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: var(--surface); }
    .setup-card { width: 100%; max-width: 420px; background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 32px; }
    .setup-card h1 { margin: 0 0 8px; font-size: 20px; font-weight: 500; color: var(--text-primary); }
    .setup-card .sub { margin-bottom: 24px; font-size: 13px; color: var(--text-secondary); }
    .setup-page .form-group { margin-bottom: 20px; }
    .setup-page .form-group label { display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 6px; }
    .setup-page .form-group input { width: 100%; padding: 10px 12px; font-size: 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); }
    .setup-page .btn-block { width: 100%; justify-content: center; margin-top: 8px; }
    .setup-page .form-message-error, .setup-page .form-message-success { margin-bottom: 16px; }
  </style>
</head>
<body>
  <div class="setup-page">
    <div class="setup-card">
      <h1>Setup console admin</h1>
      <p class="sub">Create or update an admin user for the financial console (uses <code>admins</code> table).</p>
      <?php if ($message): ?>
      <div class="form-message form-message-success" role="status"><?php echo htmlspecialchars($message); ?></div>
      <p><a href="<?php echo $base; ?>/login.php" class="btn btn-primary">Go to login</a></p>
      <?php elseif ($error): ?>
      <div class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if (!$message): ?>
      <form method="post" action="">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? $_GET['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
          <label for="name">Display name</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $_GET['name'] ?? 'Admin'); ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create or update admin</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
