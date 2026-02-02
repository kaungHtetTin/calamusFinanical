<?php
/**
 * Remaining Balance – matches C:\xampp\htdocs\financial flow.
 * Funds are per staff_id. Type 0 = In (add to balance), Type 1 = Out (subtract).
 * Only staff_id 1, 2, 3 are managed.
 */
$page_title = 'Remaining Balance';
require_once __DIR__ . '/config.php';

// Only these staff IDs are managed for remaining balance
$ALLOWED_STAFF_IDS = [1, 2, 3];

$base = FINANCIAL_BASE;
$message = isset($_GET['msg']) ? trim($_GET['msg']) : '';
$error = '';

// POST: delete last transaction (and related costs + salary records by transfer_id)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $fund_id = (int)$_POST['id'];
    if ($fund_id > 0) {
        $fund = $db->read("SELECT id, staff_id, transfer_id FROM funds WHERE id = $fund_id");
        if ($fund) {
            $row = $fund[0];
            $staff_id = (int)$row['staff_id'];
            $transfer_id = (int)$row['transfer_id'];
            // Only allow delete if this is the last transaction for this staff (globally)
            $last = $db->read("SELECT id FROM funds WHERE staff_id = $staff_id ORDER BY id DESC LIMIT 1");
            if ($last && (int)$last[0]['id'] === $fund_id) {
                if ($transfer_id > 0) {
                    $db->save("DELETE FROM costs WHERE transfer_id = $transfer_id");
                    $db->save("DELETE FROM salaries WHERE transfer_id = $transfer_id");
                }
                if ($db->save("DELETE FROM funds WHERE id = $fund_id")) {
                    $message = 'Transaction and related costs and salary record(s) (if any) deleted.';
                } else {
                    $error = 'Failed to delete transaction.';
                }
            } else {
                $error = 'Only the last transaction can be deleted.';
            }
        } else {
            $error = 'Transaction not found.';
        }
    }
}

// Staff list: only staff_id 1, 2, 3 (names from DB or fallback)
$staffs_from_db = $db->read("SELECT id, name FROM staffs WHERE id IN (1, 2, 3) ORDER BY id");
$staffs_by_id = [];
if ($staffs_from_db) {
    foreach ($staffs_from_db as $s) {
        $staffs_by_id[(int)$s['id']] = $s['name'];
    }
}
$fallback_names = [1 => 'Staff 1', 2 => 'Staff 2', 3 => 'Staff 3'];
$staffs_list = [];
foreach ([1, 2, 3] as $sid) {
    $staffs_list[] = ['id' => $sid, 'name' => $staffs_by_id[$sid] ?? $fallback_names[$sid]];
}

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$date_from = sprintf('%04d-%02d-01', $year, $month);
$date_to = date('Y-m-t', strtotime($date_from));

$balances = [];
$transactions = [];
$last_fund_id_by_staff = []; // id of the latest transaction per staff (for delete button)
foreach ($staffs_list as $s) {
    $sid = (int)$s['id'];
    $last = $db->read("SELECT current_balance, id FROM funds WHERE staff_id = $sid ORDER BY id DESC LIMIT 1");
    $balances[$sid] = $last ? (int)$last[0]['current_balance'] : 0;
    $last_fund_id_by_staff[$sid] = $last ? (int)$last[0]['id'] : null;
    $list = $db->read("SELECT * FROM funds WHERE staff_id = $sid AND date >= '$date_from' AND date <= '$date_to' ORDER BY date DESC, id DESC");
    $transactions[$sid] = $list ? $list : [];
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Remaining Balance</h1>

<?php if ($message): ?>
<p class="form-message form-message-success" role="status"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($error): ?>
<p class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<!-- Actions: Add transaction / Balance transfer / Pay salary -->
<div class="page-actions">
  <a href="<?php echo $base; ?>/add-transaction.php" class="btn btn-primary">Add transaction</a>
  <a href="<?php echo $base; ?>/balance-transfer.php" class="btn btn-secondary">Balance transfer</a>
  <a href="<?php echo $base; ?>/pay-salary.php" class="btn btn-secondary">Pay salary</a>
</div>

<!-- Balance cards per staff (like reference remaining-balance.php) -->
<div class="dashboard-cards">
  <?php foreach ($staffs_list as $s): ?>
  <div class="card">
    <div class="card-title"><?php echo htmlspecialchars($s['name']); ?></div>
    <p class="card-value"><?php echo number_format($balances[(int)$s['id']] ?? 0); ?> MMK</p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters: month / year -->
<form method="get" action="" class="filters-bar">
  <div class="filter-group">
    <label>Month</label>
    <select name="month">
      <?php for ($m = 1; $m <= 12; $m++): ?>
      <option value="<?php echo $m; ?>" <?php echo $month === $m ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="filter-group">
    <label>Year</label>
    <select name="year">
      <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 5; $y--): ?>
      <option value="<?php echo $y; ?>" <?php echo $year === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
</form>

<!-- Transactions per staff -->
<?php foreach ($staffs_list as $s): ?>
<?php $sid = (int)$s['id']; $rows = $transactions[$sid] ?? []; ?>
<div class="content-card">
  <div class="card-header">
    <h2>Transactions (<?php echo htmlspecialchars($s['name']); ?>)</h2>
  </div>
  <?php if (empty($rows)): ?>
  <div class="empty-state">No transactions in this month.</div>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Type</th>
        <th class="num">Amount</th>
        <th class="num">Current balance</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row):
        $row_id = (int)$row['id'];
        $is_last = isset($last_fund_id_by_staff[$sid]) && $last_fund_id_by_staff[$sid] === $row_id;
      ?>
      <tr>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo $row['type'] == 0 ? 'In' : 'Out'; ?></td>
        <td class="num"><?php echo number_format($row['amount']); ?></td>
        <td class="num"><?php echo number_format($row['current_balance']); ?></td>
        <td><?php echo htmlspecialchars($row['date'] ?? '—'); ?></td>
        <td>
          <?php if ($is_last): ?>
          <form method="post" action="" class="form-inline" onsubmit="return confirm('Delete this transaction? Related costs and salary record(s) (same transfer) will also be deleted.');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo $row_id; ?>">
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
          </form>
          <?php else: ?>
          <span class="card-sub">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
