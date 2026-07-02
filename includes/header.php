<?php
if (!isset($db)) {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/../auth.php';
require_login($db);

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';
$admin_user = function_exists('auth_user') ? auth_user() : null;
$admin_name = trim($admin_user['name'] ?? '') ?: 'Admin User';
$admin_email = trim($admin_user['email'] ?? '') ?: 'Office admin';
$admin_initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $admin_name), 0, 2));
if ($admin_initials === '') {
    $admin_initials = 'AD';
}

$header_pending_count = 0;
if (isset($db)) {
    $pending_row = $db->read("SELECT COUNT(*) AS cnt FROM payments WHERE approve = 0");
    $header_pending_count = $pending_row ? (int)$pending_row[0]['cnt'] : 0;
}

if (!function_exists('console_icon')) {
    function console_icon($name, $size = 17) {
        $icons = [
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
            'wallet' => '<path d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1"/><path d="M16 11h5v4h-5a2 2 0 0 1 0-4Z"/><path d="M16 13h.01"/>',
            'cost' => '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>',
            'folder' => '<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>',
            'user' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
            'users' => '<path d="M16 21a6 6 0 0 0-12 0"/><circle cx="10" cy="7" r="4"/><path d="M22 21a5 5 0 0 0-4-4.9"/><path d="M17 3.4a4 4 0 0 1 0 7.2"/>',
            'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M7 15h3"/>',
            'chart' => '<path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-7"/>',
            'plus' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'palette' => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 22a10 10 0 1 1 10-10 3.5 3.5 0 0 1-3.5 3.5H17a2 2 0 0 0-2 2c0 .5.2 1 .5 1.4.3.4.5.8.5 1.3 0 1-1.1 1.8-4 1.8Z"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'close' => '<path d="M18 6 6 18"/><path d="M6 6l12 12"/>',
            'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
            'trash' => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/>',
            'menu' => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
            'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        ];
        $paths = $icons[$name] ?? $icons['grid'];
        return '<svg class="icon" width="' . (int)$size . '" height="' . (int)$size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
    }
}

$nav_sections = [
    'Overview' => [
        ['index', 'grid', 'Dashboard', $base . '/index.php'],
    ],
    'Money' => [
        ['funds', 'wallet', 'Remaining Balance', $base . '/funds.php'],
        ['payments', 'card', 'Approve Payment', $base . '/payments.php'],
        ['costs', 'cost', 'Costs', $base . '/costs.php'],
    ],
    'Income' => [
        ['owner-income', 'users', 'Owner Income', $base . '/owner-income.php'],
    ],
    'Team' => [
        ['staffs', 'user', 'Staff', $base . '/staffs.php'],
        ['salaries', 'users', 'Salaries', $base . '/salaries.php'],
    ],
    'Settings' => [
        ['cost_categories', 'folder', 'Cost Categories', $base . '/cost_categories.php'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Financial Console</title>
  <script>
  (function() {
    var theme = localStorage.getItem('financial.theme') || 'light';
    var brand = localStorage.getItem('financial.brand') || '#545760';
    document.documentElement.dataset.theme = theme;
    document.documentElement.style.setProperty('--color-primary', brand);
  })();
  </script>
  <link rel="icon" type="image/png" href="<?php echo $base; ?>/assets/logo.png">
  <link rel="shortcut icon" type="image/png" href="<?php echo $base; ?>/assets/logo.png">
  <link rel="apple-touch-icon" href="<?php echo $base; ?>/assets/logo.png">
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/console.css">
</head>
<body class="app-root" data-theme="light" style="--color-primary: #545760">
  <a href="#main-content" class="skip-link">Skip to main content</a>
  <div class="admin-app">
    <aside class="console-sidebar admin-sidebar glass" id="sidebar">
      <div class="sidebar-brand">
        <a href="<?php echo $base; ?>/index.php" class="logo-link" aria-label="Calamus Financial dashboard">
          <img src="<?php echo $base; ?>/assets/logo.png" alt="Calamus Financial" class="logo-mark">
          <span class="logo-copy">Calamus <strong>Financial</strong></span>
        </a>
      </div>

      <nav aria-label="Admin navigation" id="sidebarNav">
        <?php foreach ($nav_sections as $section_label => $section_items): ?>
        <div class="nav-section">
          <div class="nav-section-title"><?php echo htmlspecialchars($section_label); ?></div>
          <?php foreach ($section_items as $item): ?>
          <a href="<?php echo $item[3]; ?>" class="<?php echo $current_page === $item[0] ? 'active' : ''; ?>" data-nav-label="<?php echo htmlspecialchars(strtolower($item[2])); ?>">
            <?php echo console_icon($item[1]); ?>
            <span><?php echo htmlspecialchars($item[2]); ?></span>
            <?php if ($item[0] === 'payments' && $header_pending_count > 0): ?>
            <small><?php echo $header_pending_count > 99 ? '99+' : $header_pending_count; ?></small>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <div class="nav-section">
          <div class="nav-section-title">Projects</div>
          <?php
          $nav_projects = isset($db) && function_exists('financial_project_rows') ? financial_project_rows($db) : [];
          foreach ($nav_projects as $np):
            $np_label = trim($np['project_name'] ?? '') ?: $np['keyword'];
            $np_active = $current_page === 'earning' && isset($_GET['major']) && $_GET['major'] === $np['keyword'];
          ?>
          <a href="<?php echo $base; ?>/earning.php?major=<?php echo urlencode($np['keyword']); ?>&path=<?php echo urlencode($np_label); ?>" class="<?php echo $np_active ? 'active' : ''; ?>" data-nav-label="<?php echo htmlspecialchars(strtolower($np_label)); ?>">
            <?php echo financial_project_icon_html($np, 'project-seal nav-project-seal', 'chart', 17); ?>
            <span><?php echo htmlspecialchars($np_label); ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </nav>

      <div class="admin-profile">
        <span><?php echo htmlspecialchars($admin_initials); ?></span>
        <div>
          <strong><?php echo htmlspecialchars($admin_name); ?></strong>
          <small><?php echo htmlspecialchars($admin_email); ?></small>
        </div>
      </div>
    </aside>
    <div class="console-sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <main class="console-main admin-main">
      <header class="console-header admin-topbar glass">
        <div class="header-left">
          <button type="button" class="icon-btn menu-toggle" aria-label="Toggle menu" aria-expanded="false" id="menuToggle">
            <?php echo console_icon('menu', 20); ?>
          </button>
          <nav class="mobile-appbar-actions" aria-label="Mobile quick actions">
            <a href="<?php echo $base; ?>/add-transaction.php" class="icon-btn <?php echo $current_page === 'add-transaction' ? 'active' : ''; ?>" aria-label="Add transaction" title="Add transaction">
              <?php echo console_icon('plus', 18); ?>
            </a>
            <a href="<?php echo $base; ?>/pay-salary.php" class="icon-btn <?php echo $current_page === 'pay-salary' ? 'active' : ''; ?>" aria-label="Pay salary" title="Pay salary">
              <?php echo console_icon('users', 18); ?>
            </a>
            <a href="<?php echo $base; ?>/funds.php" class="icon-btn <?php echo $current_page === 'funds' ? 'active' : ''; ?>" aria-label="Balance" title="Balance">
              <?php echo console_icon('wallet', 18); ?>
            </a>
            <a href="<?php echo $base; ?>/payments.php?status=pending" class="icon-btn <?php echo $current_page === 'payments' ? 'active' : ''; ?>" aria-label="Pending payments" title="Pending payments">
              <?php echo console_icon('card', 18); ?>
              <?php if ($header_pending_count > 0): ?>
              <span class="mobile-action-badge" aria-hidden="true"><?php echo $header_pending_count > 99 ? '99+' : $header_pending_count; ?></span>
              <?php endif; ?>
            </a>
          </nav>
        </div>

        <div class="header-right">
          <nav class="header-quick-links" aria-label="Quick links">
            <a href="<?php echo $base; ?>/index.php" class="header-quick-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?php echo $base; ?>/add-transaction.php" class="header-quick-link <?php echo $current_page === 'add-transaction' ? 'active' : ''; ?>">Add transaction</a>
            <a href="<?php echo $base; ?>/funds.php" class="header-quick-link <?php echo $current_page === 'funds' ? 'active' : ''; ?>">Balance</a>
          </nav>

          <div class="theme-control">
            <button type="button" class="icon-btn" id="themeToggle" aria-label="Theme settings" aria-expanded="false" aria-haspopup="true">
              <?php echo console_icon('palette', 18); ?>
            </button>
            <div class="theme-popover glass" id="themePopover" hidden>
              <p class="eyebrow">Theme mode</p>
              <div class="segmented-control" role="group" aria-label="Theme mode">
                <button type="button" data-theme-value="light">Light</button>
                <button type="button" data-theme-value="dark">Dark</button>
              </div>
              <p class="eyebrow">Brand color</p>
              <div class="brand-swatches">
                <button type="button" style="--swatch: #545760" data-brand-value="#545760" aria-label="Slate brand"></button>
                <button type="button" style="--swatch: #2874bc" data-brand-value="#2874bc" aria-label="Blue brand"></button>
                <button type="button" style="--swatch: #168255" data-brand-value="#168255" aria-label="Green brand"></button>
                <button type="button" style="--swatch: #9a5a12" data-brand-value="#9a5a12" aria-label="Amber brand"></button>
                <input type="color" id="brandPicker" value="#545760" aria-label="Custom brand color">
              </div>
            </div>
          </div>

          <div class="header-notification-wrap">
            <button type="button" class="icon-btn header-notification-btn" id="notificationToggle" aria-label="Notifications" aria-expanded="false" aria-haspopup="true">
              <?php echo console_icon('bell', 18); ?>
              <?php if ($header_pending_count > 0): ?>
              <span class="header-notification-badge" aria-hidden="true"><?php echo $header_pending_count > 99 ? '99+' : $header_pending_count; ?></span>
              <?php endif; ?>
            </button>
            <div class="header-notification-dropdown glass" id="notificationDropdown" role="menu" aria-label="Notification panel" hidden>
              <div class="header-notification-title">Notifications</div>
              <?php if ($header_pending_count > 0): ?>
              <a href="<?php echo $base; ?>/payments.php?status=pending" class="header-notification-item" role="menuitem">
                <span class="alert-icon info"><?php echo console_icon('card', 15); ?></span>
                <span><strong><?php echo $header_pending_count; ?></strong> payment<?php echo $header_pending_count === 1 ? '' : 's'; ?> pending approval</span>
              </a>
              <?php else: ?>
              <div class="header-notification-item header-notification-empty">No new notifications</div>
              <?php endif; ?>
            </div>
          </div>

          <a href="<?php echo $base; ?>/logout.php" class="icon-btn header-logout" aria-label="Log out" title="Log out">
            <?php echo console_icon('logout', 18); ?>
          </a>
        </div>
      </header>

      <div class="admin-content" id="main-content" tabindex="-1">
