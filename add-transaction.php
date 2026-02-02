<?php
/**
 * Add transaction – standalone page from Remaining Balance.
 * Out transactions create a cost record (optional project or split by projects). In transactions do not.
 */
$page_title = 'Add Transaction';
require_once __DIR__ . '/config.php';

$ALLOWED_STAFF_IDS = [1, 2, 3];
$base = FINANCIAL_BASE;
$message = '';
$error = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $type = isset($_POST['type']) ? (int)$_POST['type'] : 0;
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
    $staff_id = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : 0;
    $cost_category_id = (int)($_POST['cost_category_id'] ?? 0);
    $cost_mode = trim($_POST['cost_mode'] ?? 'single');
    $cost_project_single = trim($_POST['cost_project_single'] ?? '');
    if ($cost_project_single === '' || $cost_project_single === 'general') {
        $cost_project_single = 'general';
    }

    if ($title === '' || $amount <= 0 || $staff_id <= 0) {
        $error = 'Title, amount (positive), and owner are required.';
    } elseif (!in_array($staff_id, $ALLOWED_STAFF_IDS, true)) {
        $error = 'Invalid staff. Only staff 1, 2, 3 are allowed.';
    } elseif ($type === 1 && $cost_category_id <= 0) {
        $error = 'Cost category is required for Out transactions.';
    } else {
        $projects_for_cost = $db->read("SELECT id, keyword, project_name FROM course_categories ORDER BY id");
        $projects_for_cost = $projects_for_cost ?: [];

        if ($type === 1 && $cost_mode === 'split') {
            $total_cost = 0;
            foreach ($projects_for_cost as $proj) {
                $total_cost += (int)($_POST['cost_project'][$proj['keyword']] ?? 0);
            }
            if ($total_cost !== $amount) {
                $error = 'Sum of amounts per project (' . number_format($total_cost) . ') must equal transaction amount (' . number_format($amount) . ').';
            }
        }

        if ($error === '') {
            $prev = $db->read("SELECT current_balance FROM funds WHERE staff_id = $staff_id ORDER BY id DESC LIMIT 1");
            $prev_balance = $prev ? (int)$prev[0]['current_balance'] : 0;
            $new_balance = $type === 0 ? $prev_balance + $amount : $prev_balance - $amount;

            $title_esc = $conn->real_escape_string($title);
            $sql = "INSERT INTO funds (title, type, amount, current_balance, staff_id, transfer_id) VALUES ('$title_esc', $type, $amount, $new_balance, $staff_id, 0)";
            $fund_id = $db->save($sql);
            if ($fund_id) {
                $db->save("UPDATE funds SET transfer_id = $fund_id WHERE id = $fund_id");

                if ($type === 1) {
                    if ($cost_mode === 'split') {
                        foreach ($projects_for_cost as $proj) {
                            $key = $proj['keyword'];
                            $cost_amt = (int)($_POST['cost_project'][$key] ?? 0);
                            if ($cost_amt > 0) {
                                $major_esc = $conn->real_escape_string($key);
                                $db->save("INSERT INTO costs (cost_category_id, title, amount, major, transfer_id) VALUES ($cost_category_id, '$title_esc', $cost_amt, '$major_esc', $fund_id)");
                            }
                        }
                        $msg = 'Transaction and cost records (by project) added.';
                    } else {
                        $major_esc = $conn->real_escape_string($cost_project_single);
                        $db->save("INSERT INTO costs (cost_category_id, title, amount, major, transfer_id) VALUES ($cost_category_id, '$title_esc', $amount, '$major_esc', $fund_id)");
                        $msg = 'Transaction and cost record added.';
                    }
                } else {
                    $msg = 'Transaction added.';
                }
                header('Location: ' . $base . '/funds.php?msg=' . urlencode($msg));
                exit;
            } else {
                $error = 'Failed to add record.';
            }
        }
    }
}

// Staff list: only 1, 2, 3
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

$projects_list = $db->read("SELECT id, keyword, project_name FROM course_categories ORDER BY id");
if ($projects_list === false) $projects_list = [];
$cost_categories = $db->read("SELECT id, title FROM cost_categories ORDER BY title");
if ($cost_categories === false) $cost_categories = [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Add Transaction</h1>
<p class="period-hint">Record an In or Out transaction. Out transactions can be recorded as a cost (optional project or split by projects); In transactions do not create a cost record.</p>

<?php if ($error): ?>
<div class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="add-transaction-wrap">
  <form method="post" action="" class="add-transaction-form" id="addTransactionForm">
    <div class="content-card add-transaction-section">
      <div class="card-header">
        <h2 class="section-title">Transaction</h2>
        <a href="<?php echo $base; ?>/funds.php" class="btn btn-secondary btn-sm">← Back</a>
      </div>
      <div class="content-card-body">
        <div class="form-row form-row-2">
          <div class="form-group">
            <label for="amount">Amount (MMK)</label>
            <input type="number" id="amount" name="amount" required min="1" placeholder="0" value="<?php echo isset($_POST['amount']) ? (int)$_POST['amount'] : ''; ?>">
          </div>
          <div class="form-group">
            <label for="type">Type</label>
            <select id="type" name="type">
              <option value="0" <?php echo (isset($_POST['type']) && (int)$_POST['type'] === 0) ? 'selected' : ''; ?>>In</option>
              <option value="1" <?php echo (isset($_POST['type']) && (int)$_POST['type'] === 1) ? 'selected' : ''; ?>>Out</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" required maxlength="225" placeholder="e.g. Office supplies" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
        </div>
        <div class="form-group">
          <label for="staff_id">Owner (fund account)</label>
          <select id="staff_id" name="staff_id" required>
            <option value="">Select owner</option>
            <?php foreach ($staffs_list as $s): ?>
            <option value="<?php echo (int)$s['id']; ?>" <?php echo (isset($_POST['staff_id']) && (int)$_POST['staff_id'] === (int)$s['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="content-card add-transaction-section add-transaction-cost" id="costSection" style="display: none;">
      <div class="card-header">
        <h2 class="section-title">Cost record</h2>
        <span class="card-sub">Out transactions only — record as a cost</span>
      </div>
      <div class="content-card-body">
        <div class="form-group">
          <label for="cost_category_id">Cost category <span class="required">*</span></label>
          <select id="cost_category_id" name="cost_category_id">
            <option value="">Select category</option>
            <?php foreach ($cost_categories as $cc): ?>
            <option value="<?php echo (int)$cc['id']; ?>" <?php echo (isset($_POST['cost_category_id']) && (int)$_POST['cost_category_id'] === (int)$cc['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cc['title']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <fieldset class="cost-mode-fieldset">
          <legend class="cost-mode-legend">Assign cost to project</legend>
          <div class="cost-mode-options">
            <label class="cost-mode-option">
              <input type="radio" name="cost_mode" value="single" <?php echo (isset($_POST['cost_mode']) ? $_POST['cost_mode'] : 'single') === 'single' ? 'checked' : ''; ?>>
              <span>Single cost (no project or one project)</span>
            </label>
            <label class="cost-mode-option">
              <input type="radio" name="cost_mode" value="split" <?php echo (isset($_POST['cost_mode']) && $_POST['cost_mode'] === 'split') ? 'checked' : ''; ?>>
              <span>Split by projects</span>
            </label>
          </div>

          <div class="cost-mode-single" id="costModeSingle">
            <div class="form-group">
              <label for="cost_project_single">Project (optional)</label>
              <select id="cost_project_single" name="cost_project_single">
                <option value="general" <?php echo (isset($_POST['cost_project_single']) ? $_POST['cost_project_single'] : 'general') === 'general' ? 'selected' : ''; ?>>— No project (general) —</option>
                <?php foreach ($projects_list as $proj): ?>
                <option value="<?php echo htmlspecialchars($proj['keyword']); ?>" <?php echo (isset($_POST['cost_project_single']) && $_POST['cost_project_single'] === $proj['keyword']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($proj['project_name'] ?? $proj['keyword']); ?></option>
                <?php endforeach; ?>
              </select>
              <span class="form-hint">One cost record with the full amount. Leave as "No project" if not related to a project.</span>
            </div>
          </div>

          <div class="cost-mode-split" id="costModeSplit" style="display: none;">
            <p class="form-hint block">Enter amount per project. Sum must equal the transaction amount above.</p>
            <?php foreach ($projects_list as $proj): ?>
            <div class="form-group form-group-inline">
              <label for="cost_<?php echo htmlspecialchars($proj['keyword']); ?>"><?php echo htmlspecialchars($proj['project_name'] ?? $proj['keyword']); ?></label>
              <input type="number" id="cost_<?php echo htmlspecialchars($proj['keyword']); ?>" name="cost_project[<?php echo htmlspecialchars($proj['keyword']); ?>]" value="<?php echo isset($_POST['cost_project'][$proj['keyword']]) ? (int)$_POST['cost_project'][$proj['keyword']] : 0; ?>" min="0" step="1" class="cost-split-input">
            </div>
            <?php endforeach; ?>
            <p class="card-sub"><strong>Sum: <span id="costSplitSum">0</span> MMK</strong></p>
          </div>
        </fieldset>
      </div>
    </div>

    <div class="form-actions add-transaction-actions">
      <button type="submit" class="btn btn-primary">Add transaction & cost</button>
      <a href="<?php echo $base; ?>/funds.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<script>
(function() {
  var form = document.getElementById('addTransactionForm');
  if (!form) return;
  var typeSelect = document.getElementById('type');
  var costSection = document.getElementById('costSection');
  var costCategorySelect = document.getElementById('cost_category_id');
  var costModeSingle = document.getElementById('costModeSingle');
  var costModeSplit = document.getElementById('costModeSplit');
  var radios = form.querySelectorAll('input[name="cost_mode"]');
  var splitInputs = form.querySelectorAll('.cost-split-input');
  var sumEl = document.getElementById('costSplitSum');

  function toggleCostSection() {
    var isOut = typeSelect && typeSelect.value === '1';
    if (costSection) costSection.style.display = isOut ? 'block' : 'none';
    if (costCategorySelect) costCategorySelect.required = isOut;
  }
  function toggleCostMode() {
    var mode = form.querySelector('input[name="cost_mode"]:checked');
    if (!mode) return;
    if (costModeSingle) costModeSingle.style.display = mode.value === 'single' ? 'block' : 'none';
    if (costModeSplit) costModeSplit.style.display = mode.value === 'split' ? 'block' : 'none';
  }
  function updateSum() {
    var sum = 0;
    splitInputs.forEach(function(inp) { sum += parseInt(inp.value, 10) || 0; });
    if (sumEl) sumEl.textContent = sum.toLocaleString();
  }

  if (typeSelect) typeSelect.addEventListener('change', toggleCostSection);
  radios.forEach(function(r) { r.addEventListener('change', toggleCostMode); });
  splitInputs.forEach(function(inp) { inp.addEventListener('input', updateSum); });

  toggleCostSection();
  toggleCostMode();
  updateSum();
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
