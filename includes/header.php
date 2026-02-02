<?php
if (!isset($db)) {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/../auth.php';
require_login();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';

// Pending payments count for notification badge
$header_pending_count = 0;
if (isset($db)) {
    $pending_row = $db->read("SELECT COUNT(*) AS cnt FROM payments WHERE approve = 0");
    $header_pending_count = $pending_row ? (int)$pending_row[0]['cnt'] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Financial Console</title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/console.css">
</head>
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>
  <header class="console-header">
    <div class="header-left">
      <button type="button" class="menu-toggle" aria-label="Toggle menu" aria-expanded="false" id="menuToggle">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <h1 class="logo"><a href="<?php echo $base; ?>/index.php" class="logo-link">Calamus <span>Financial</span></a></h1>
    </div>
    <div class="header-right">
      <nav class="header-quick-links" aria-label="Quick links">
        <a href="<?php echo $base; ?>/index.php" class="header-quick-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">
          <span class="header-quick-icon" aria-hidden="true">📊</span> Dashboard
        </a>
        <a href="<?php echo $base; ?>/add-transaction.php" class="header-quick-link <?php echo $current_page === 'add-transaction' ? 'active' : ''; ?>">
          <span class="header-quick-icon" aria-hidden="true">➕</span> Add transaction
        </a>
        <a href="<?php echo $base; ?>/funds.php" class="header-quick-link <?php echo $current_page === 'funds' ? 'active' : ''; ?>">
          <span class="header-quick-icon" aria-hidden="true">💰</span> Balance
        </a>
        <a href="<?php echo $base; ?>/payments.php" class="header-quick-link <?php echo $current_page === 'payments' ? 'active' : ''; ?>">
          <span class="header-quick-icon" aria-hidden="true">💳</span> Approve payment
        </a>
      </nav>
      <div class="header-notification-wrap">
        <button type="button" class="header-notification-btn" id="notificationToggle" aria-label="Notifications" aria-expanded="false" aria-haspopup="true">
          <svg class="header-notification-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($header_pending_count > 0): ?>
          <span class="header-notification-badge" aria-hidden="true"><?php echo $header_pending_count > 99 ? '99+' : $header_pending_count; ?></span>
          <?php endif; ?>
        </button>
        <div class="header-notification-dropdown" id="notificationDropdown" role="menu" aria-label="Notification panel" hidden>
          <div class="header-notification-title">Notifications</div>
          <?php if ($header_pending_count > 0): ?>
          <a href="<?php echo $base; ?>/payments.php?status=pending" class="header-notification-item" role="menuitem">
            <span class="header-notification-item-icon">💳</span>
            <span><strong><?php echo $header_pending_count; ?></strong> payment<?php echo $header_pending_count === 1 ? '' : 's'; ?> pending approval</span>
          </a>
          <?php else: ?>
          <div class="header-notification-item header-notification-empty">No new notifications</div>
          <?php endif; ?>
        </div>
      </div>
      <a href="<?php echo $base; ?>/logout.php" class="btn btn-secondary btn-sm header-logout">Log out</a>
    </div>
  </header>
  <script>
  (function(){
    var btn = document.getElementById('notificationToggle');
    var panel = document.getElementById('notificationDropdown');
    if (btn && panel) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var open = panel.hidden === false;
        panel.hidden = open;
        btn.setAttribute('aria-expanded', !open);
      });
      document.addEventListener('click', function() {
        panel.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
      });
      panel.addEventListener('click', function(e) { e.stopPropagation(); });
    }
  })();
  </script>
  <aside class="console-sidebar" id="sidebar">
    <nav>
      <div class="nav-section">
        <div class="nav-section-title">Overview</div>
        <a href="<?php echo $base; ?>/index.php" class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">
          <span class="nav-icon">📊</span> Dashboard
        </a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Money</div>
        <a href="<?php echo $base; ?>/funds.php" class="<?php echo $current_page === 'funds' ? 'active' : ''; ?>">
          <span class="nav-icon">💰</span> Remaining Balance
        </a>
        <a href="<?php echo $base; ?>/costs.php" class="<?php echo $current_page === 'costs' ? 'active' : ''; ?>">
          <span class="nav-icon">📤</span> Costs
        </a>
        <a href="<?php echo $base; ?>/cost_categories.php" class="<?php echo $current_page === 'cost_categories' ? 'active' : ''; ?>">
          <span class="nav-icon">📁</span> Cost Categories
        </a>
        <a href="<?php echo $base; ?>/staffs.php" class="<?php echo $current_page === 'staffs' ? 'active' : ''; ?>">
          <span class="nav-icon">👤</span> Staff
        </a>
        <a href="<?php echo $base; ?>/salaries.php" class="<?php echo $current_page === 'salaries' ? 'active' : ''; ?>">
          <span class="nav-icon">👥</span> Salaries
        </a>
        <a href="<?php echo $base; ?>/payments.php" class="<?php echo $current_page === 'payments' ? 'active' : ''; ?>">
          <span class="nav-icon">💳</span> Approve Payment
        </a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Projects</div>
        <?php
        $nav_projects = isset($db) ? $db->read("SELECT project_name, keyword FROM course_categories ORDER BY id") : [];
        if ($nav_projects === false) $nav_projects = [];
        foreach ($nav_projects as $np):
          $np_major = htmlspecialchars($np['keyword']);
          $np_path = htmlspecialchars($np['project_name'] ?? $np['keyword']);
        ?>
        <a href="<?php echo $base; ?>/earning.php?major=<?php echo urlencode($np['keyword']); ?>&path=<?php echo urlencode($np['project_name'] ?? $np['keyword']); ?>" class="<?php echo ($current_page === 'earning' && isset($_GET['major']) && $_GET['major'] === $np['keyword']) ? 'active' : ''; ?>">
          <span class="nav-icon">📊</span> <?php echo $np_path; ?>
        </a>
        <?php endforeach; ?>
      </div>
    </nav>
  </aside>
  <div class="console-sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>
  <script>
  (function(){
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggle = document.getElementById('menuToggle');
    if (!sidebar || !overlay || !toggle) return;
    function open() { sidebar.classList.add('open'); overlay.classList.add('show'); overlay.setAttribute('aria-hidden', 'false'); toggle.setAttribute('aria-expanded', 'true'); }
    function close() { sidebar.classList.remove('open'); overlay.classList.remove('show'); overlay.setAttribute('aria-hidden', 'true'); toggle.setAttribute('aria-expanded', 'false'); }
    toggle.addEventListener('click', function(){ sidebar.classList.contains('open') ? close() : open(); });
    overlay.addEventListener('click', close);
  })();
  </script>
  <main class="console-main" id="main-content" tabindex="-1">
