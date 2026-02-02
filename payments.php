<?php
$page_title = 'Payments';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = '';
$error = '';

// Use REQUEST so filter is preserved when forms POST with hidden filter fields
$status_filter = $_REQUEST['status'] ?? 'all';
$date_from = $_REQUEST['date_from'] ?? date('Y-m-01');
$date_to = $_REQUEST['date_to'] ?? date('Y-m-d');
$project_filter = trim($_REQUEST['project'] ?? '');

$date_from_esc = $conn->real_escape_string($date_from);
$date_to_esc = $conn->real_escape_string($date_to);
$project_esc = $project_filter !== '' ? $conn->real_escape_string($project_filter) : null;

// POST: approve, reject, delete, or approve_all
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve_all') {
        $wf = "approve = 0 AND date >= '$date_from_esc' AND date <= '$date_to_esc'";
        if ($project_esc !== null) $wf .= " AND major = '$project_esc'";
        if ($db->save("UPDATE payments SET approve = 1 WHERE $wf")) {
            $affected = $conn->affected_rows;
            $message = $affected > 0 ? "Approved $affected payment(s)." : 'No pending payments to approve.';
        } else {
            $error = 'Approve all failed.';
        }
    } elseif (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($id > 0) {
            if ($_POST['action'] === 'approve') {
                if ($db->save("UPDATE payments SET approve = 1 WHERE id = $id")) {
                    $message = 'Payment approved.';
                } else {
                    $error = 'Update failed.';
                }
            } elseif ($_POST['action'] === 'reject') {
                if ($db->save("UPDATE payments SET approve = 0 WHERE id = $id")) {
                    $message = 'Payment rejected.';
                } else {
                    $error = 'Update failed.';
                }
            } elseif ($_POST['action'] === 'delete') {
                if ($db->save("DELETE FROM payments WHERE id = $id")) {
                    $message = 'Payment deleted.';
                } else {
                    $error = 'Delete failed.';
                }
            }
        }
    }
}

$where = "p.date >= '$date_from_esc' AND p.date <= '$date_to_esc'";
if ($status_filter === 'pending') {
    $where .= " AND p.approve = 0";
} elseif ($status_filter === 'approved') {
    $where .= " AND p.approve = 1";
}
if ($project_esc !== null) {
    $where .= " AND p.major = '$project_esc'";
}

// Projects for filter dropdown
$projects_list = $db->read("SELECT id, project_name, keyword FROM course_categories ORDER BY project_name");
if ($projects_list === false) $projects_list = [];

// Name from learners: join on phone number (payments.user_id = learners.learner_phone)
$list = $db->read(
    "SELECT p.*, l.learner_name, l.learner_phone, cc.project_name " .
    "FROM payments p " .
    "LEFT JOIN learners l ON l.learner_phone = p.user_id " .
    "LEFT JOIN course_categories cc ON cc.keyword = p.major " .
    "WHERE $where ORDER BY p.date DESC, p.id DESC"
);
if ($list === false) $list = [];

$pending_count = 0;
foreach ($list as $row) { if (!$row['approve']) $pending_count++; }

// Base URL for payment screenshots (relative paths from project root)
$payment_image_base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
function payment_screenshot_src($screenshot, $base) {
    if (empty($screenshot)) return null;
    if (strpos($screenshot, 'http') === 0 || strpos($screenshot, '//') === 0) return $screenshot;
    return $base . ltrim($screenshot, '/');
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Approve Payment</h1>

<?php if ($message): ?>
<p style="color: var(--success); margin-bottom: 16px;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($error): ?>
<p style="color: var(--error); margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php
function payments_filter_hidden($status_filter, $date_from, $date_to, $project_filter) {
    echo '<input type="hidden" name="status" value="' . htmlspecialchars($status_filter) . '">';
    echo '<input type="hidden" name="date_from" value="' . htmlspecialchars($date_from) . '">';
    echo '<input type="hidden" name="date_to" value="' . htmlspecialchars($date_to) . '">';
    echo '<input type="hidden" name="project" value="' . htmlspecialchars($project_filter) . '">';
}
?>
<form method="get" action="" class="filters-bar">
  <div class="filter-group">
    <label>Project</label>
    <select name="project">
      <option value="">All projects</option>
      <?php foreach ($projects_list as $proj): ?>
      <option value="<?php echo htmlspecialchars($proj['keyword']); ?>" <?php echo $project_filter === $proj['keyword'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($proj['project_name'] ?? $proj['keyword']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filter-group">
    <label>Status</label>
    <select name="status">
      <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
      <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
      <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
    </select>
  </div>
  <div class="filter-group">
    <label>From</label>
    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
  </div>
  <div class="filter-group">
    <label>To</label>
    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
  </div>
  <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
</form>

<div class="content-card" style="margin-top: 24px;">
  <div class="card-header">
    <h2>Payment list</h2>
    <?php if ($pending_count > 0): ?>
    <form method="post" action="" style="display:inline;" onsubmit="return confirm('Approve all <?php echo $pending_count; ?> pending payment(s) in this list?');">
      <input type="hidden" name="action" value="approve_all">
      <?php payments_filter_hidden($status_filter, $date_from, $date_to, $project_filter); ?>
      <button type="submit" class="btn btn-primary btn-sm">Approve all (<?php echo $pending_count; ?>)</button>
    </form>
    <?php endif; ?>
  </div>
  <?php if (empty($list)): ?>
  <div class="empty-state">No payments in this range.</div>
  <?php else: ?>
  <div class="payment-list-wrap">
    <div class="table-wrapper">
      <table class="data-table payment-table">
        <thead>
          <tr>
            <th class="col-screenshot">Screenshot</th>
            <th>Project</th>
            <th>Name</th>
            <th>Phone</th>
            <th class="num">Amount</th>
            <th>Date</th>
            <th>Status</th>
            <th class="col-actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $row):
            $img_src = payment_screenshot_src($row['screenshot'] ?? '', $payment_image_base);
          ?>
          <tr>
            <td class="col-screenshot">
              <?php if ($img_src): ?>
              <a href="<?php echo htmlspecialchars($img_src); ?>" target="_blank" rel="noopener" class="payment-screenshot-link" title="View full size">
                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Payment screenshot" class="payment-screenshot-thumb" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span class="payment-screenshot-fallback" style="display:none;">View</span>
              </a>
              <?php else: ?>
              <span class="payment-screenshot-empty">—</span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['project_name'] ?? $row['major'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($row['learner_name'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($row['learner_phone'] ?? $row['user_id'] ?? '—'); ?></td>
            <td class="num"><?php echo number_format($row['amount']); ?></td>
            <td><?php echo htmlspecialchars($row['date']); ?></td>
            <td>
              <?php if ($row['approve']): ?>
              <span class="badge badge-success">Approved</span>
              <?php else: ?>
              <span class="badge badge-pending">Pending</span>
              <?php endif; ?>
            </td>
            <td class="actions-cell col-actions">
              <?php if (!$row['approve']): ?>
              <form method="post" action="" class="form-inline">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                <?php payments_filter_hidden($status_filter, $date_from, $date_to, $project_filter); ?>
                <button type="submit" class="btn btn-primary btn-sm">Approve</button>
              </form>
              <?php else: ?>
              <form method="post" action="" class="form-inline" onsubmit="return confirm('Reject this payment?');">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                <?php payments_filter_hidden($status_filter, $date_from, $date_to, $project_filter); ?>
                <button type="submit" class="btn btn-secondary btn-sm">Reject</button>
              </form>
              <?php endif; ?>
              <form method="post" action="" class="form-inline" onsubmit="return confirm('Delete this payment permanently?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                <?php payments_filter_hidden($status_filter, $date_from, $date_to, $project_filter); ?>
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Card layout for small screens (same data, better touch targets) -->
    <ul class="payment-cards" aria-hidden="true">
      <?php foreach ($list as $row):
        $img_src = payment_screenshot_src($row['screenshot'] ?? '', $payment_image_base);
      ?>
      <li class="payment-card">
        <div class="payment-card-media">
          <?php if ($img_src): ?>
          <a href="<?php echo htmlspecialchars($img_src); ?>" target="_blank" rel="noopener" class="payment-screenshot-link">
            <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Payment screenshot" class="payment-screenshot-thumb" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <span class="payment-screenshot-fallback" style="display:none;">View screenshot</span>
          </a>
          <?php else: ?>
          <span class="payment-screenshot-empty">No image</span>
          <?php endif; ?>
        </div>
        <div class="payment-card-body">
          <div class="payment-card-meta">
            <span class="payment-card-project"><?php echo htmlspecialchars($row['project_name'] ?? $row['major'] ?? '—'); ?></span>
            <span class="payment-card-status <?php echo $row['approve'] ? 'badge-success' : 'badge-pending'; ?>"><?php echo $row['approve'] ? 'Approved' : 'Pending'; ?></span>
          </div>
          <p class="payment-card-name"><?php echo htmlspecialchars($row['learner_name'] ?? '—'); ?></p>
          <p class="payment-card-phone"><?php echo htmlspecialchars($row['learner_phone'] ?? $row['user_id'] ?? '—'); ?></p>
          <p class="payment-card-amount"><?php echo number_format($row['amount']); ?> MMK</p>
          <p class="payment-card-date"><?php echo htmlspecialchars($row['date']); ?></p>
          <div class="payment-card-actions">
            <?php if (!$row['approve']): ?>
            <form method="post" action="" class="form-inline">
              <input type="hidden" name="action" value="approve">
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
              <?php payments_filter_hidden($status_filter, $date_from, $date_to, $project_filter); ?>
              <button type="submit" class="btn btn-primary btn-sm">Approve</button>
            </form>
            <?php else: ?>
            <form method="post" action="" class="form-inline" onsubmit="return confirm('Reject this payment?');">
              <input type="hidden" name="action" value="reject">
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
              <?php payments_filter_hidden($status_filter, $date_from, $date_to, $project_filter); ?>
              <button type="submit" class="btn btn-secondary btn-sm">Reject</button>
            </form>
            <?php endif; ?>
            <form method="post" action="" class="form-inline" onsubmit="return confirm('Delete this payment permanently?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
              <?php payments_filter_hidden($status_filter, $date_from, $date_to, $project_filter); ?>
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
