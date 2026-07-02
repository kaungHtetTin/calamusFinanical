<?php
/**
 * Balance transfer - standalone page from Remaining Balance.
 * Transfer between staff_id 1, 2, 3 only.
 */
$page_title = 'Balance Transfer';
require_once __DIR__ . '/config.php';

$ALLOWED_STAFF_IDS = [1, 2, 3];
$base = FINANCIAL_BASE;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_id = (int)($_POST['from_staff_id'] ?? 0);
    $to_id = (int)($_POST['to_staff_id'] ?? 0);
    $amount = (int)($_POST['amount'] ?? 0);

    if ($from_id <= 0 || $to_id <= 0 || $from_id === $to_id || $amount <= 0) {
        $error = 'Select different From/To staff and enter a valid amount.';
    } elseif (!in_array($from_id, $ALLOWED_STAFF_IDS, true) || !in_array($to_id, $ALLOWED_STAFF_IDS, true)) {
        $error = 'Invalid staff. Only staff 1, 2, 3 are allowed.';
    } else {
        $staffs_list = $db->read("SELECT id, name FROM staffs WHERE id IN ($from_id, $to_id)");
        $names = [];
        if ($staffs_list) {
            foreach ($staffs_list as $s) {
                $names[(int)$s['id']] = $conn->real_escape_string($s['name']);
            }
        }
        $fallback = [1 => 'Staff 1', 2 => 'Staff 2', 3 => 'Staff 3'];
        $title_out = "Transfer to " . ($names[$to_id] ?? $fallback[$to_id] ?? 'Staff');
        $title_in = "Received from " . ($names[$from_id] ?? $fallback[$from_id] ?? 'Staff');

        $prev_from = $db->read("SELECT current_balance FROM funds WHERE staff_id = $from_id ORDER BY id DESC LIMIT 1");
        $prev_to = $db->read("SELECT current_balance FROM funds WHERE staff_id = $to_id ORDER BY id DESC LIMIT 1");
        $bal_from = $prev_from ? (int)$prev_from[0]['current_balance'] : 0;
        $bal_to = $prev_to ? (int)$prev_to[0]['current_balance'] : 0;
        $new_from = $bal_from - $amount;
        $new_to = $bal_to + $amount;

        $title_out_esc = $conn->real_escape_string($title_out);
        $title_in_esc = $conn->real_escape_string($title_in);
        $id1 = $db->save("INSERT INTO funds (title, type, amount, current_balance, staff_id, transfer_id) VALUES ('$title_out_esc', 1, $amount, $new_from, $from_id, 0)");
        if ($id1) $db->save("UPDATE funds SET transfer_id = $id1 WHERE id = $id1");
        $id2 = $db->save("INSERT INTO funds (title, type, amount, current_balance, staff_id, transfer_id) VALUES ('$title_in_esc', 0, $amount, $new_to, $to_id, 0)");
        if ($id2) $db->save("UPDATE funds SET transfer_id = $id2 WHERE id = $id2");
        header('Location: ' . $base . '/funds.php?msg=' . urlencode('Balance transfer completed.'));
        exit;
    }
}

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
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="form-page">
  <div class="admin-page-heading">
    <div>
      <p class="eyebrow">Remaining Balance</p>
      <h1>Balance Transfer</h1>
    </div>
    <a href="<?php echo $base; ?>/funds.php" class="btn secondary">Back to Remaining Balance</a>
  </div>

  <?php if ($error): ?>
  <div class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <section class="panel glass form-panel narrow">
    <div class="panel-heading">
      <div>
        <p class="eyebrow">Transfer</p>
        <h2>Move balance between staff</h2>
      </div>
    </div>
    <form method="post" action="" class="crud-grid">
      <label class="form-field">
        <span>From</span>
        <select name="from_staff_id" required>
          <option value="">Select staff</option>
          <?php foreach ($staffs_list as $s): ?>
          <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="form-field">
        <span>To</span>
        <select name="to_staff_id" required>
          <option value="">Select staff</option>
          <?php foreach ($staffs_list as $s): ?>
          <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="form-field span-2">
        <span>Amount</span>
        <input type="number" name="amount" required min="1">
      </label>
      <div class="form-actions span-2">
        <button type="submit" class="btn primary">Transfer</button>
        <a href="<?php echo $base; ?>/funds.php" class="btn secondary">Cancel</a>
      </div>
    </form>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
