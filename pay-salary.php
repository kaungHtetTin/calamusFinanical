<?php
/**
 * Pay salary – business owners (staff 1, 2) pay salary to employees.
 * One transaction (total), one salary record (total), multiple cost records (per-project split).
 * Validation: transaction amount = salary amount = sum of cost amounts.
 * All linked by transfer_id.
 */
$page_title = 'Pay Salary';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = '';
$error = '';

// Business owners (staff 1, 2) – who pays the salary
$payers_list = $db->read("SELECT id, name FROM staffs WHERE id IN (1, 2) ORDER BY id");
if (!$payers_list) $payers_list = [];
$payers_list = array_map(function ($r) {
    return ['id' => (int)$r['id'], 'name' => $r['name'] ?? 'Staff ' . $r['id']];
}, $payers_list);
if (empty($payers_list)) {
    $payers_list = [['id' => 1, 'name' => 'Staff 1'], ['id' => 2, 'name' => 'Staff 2']];
}

// Employees: staff 1, 2 (owners – no cost record) + in-service staff excluding 1, 2, 3. staff_id 3 is not allowed.
$staffs_list = [];
$owners_recipients = $db->read("SELECT id, name, project FROM staffs WHERE id IN (1, 2) ORDER BY id");
if ($owners_recipients) {
    foreach ($owners_recipients as $s) {
        $staffs_list[] = [
            'id' => (int)$s['id'],
            'name' => $s['name'] ?? 'Staff ' . $s['id'],
            'project' => trim($s['project'] ?? ''),
            'is_all' => false,
            'skip_cost' => true,
        ];
    }
}
$staffs_from_db = $db->read("SELECT id, name, project FROM staffs WHERE id NOT IN (1, 2, 3) AND present = 1 ORDER BY name");
if ($staffs_from_db) {
    foreach ($staffs_from_db as $s) {
        $proj = trim($s['project'] ?? '');
        $staffs_list[] = [
            'id' => (int)$s['id'],
            'name' => $s['name'] ?? 'Staff ' . $s['id'],
            'project' => $proj,
            'is_all' => (strtolower($proj) === 'all'),
            'skip_cost' => false,
        ];
    }
}
$allowed_recipient_ids = array_column($staffs_list, 'id');
$staff_project_by_id = [];
$staff_is_all_by_id = [];
$staff_skip_cost_by_id = [];
foreach ($staffs_list as $s) {
    $staff_project_by_id[$s['id']] = $s['project'];
    $staff_is_all_by_id[$s['id']] = $s['is_all'];
    $staff_skip_cost_by_id[$s['id']] = !empty($s['skip_cost']);
}

// Projects for cost distribution (must load before POST handling for validation)
$course_categories = $db->read("SELECT keyword, project_name FROM course_categories ORDER BY project_name");
if ($course_categories === false) $course_categories = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paid_by = (int)($_POST['paid_by'] ?? 0);
    $staff_id = (int)($_POST['staff_id'] ?? 0);
    $amount = (int)($_POST['amount'] ?? 0);
    $date = $_POST['date'] ?? date('Y-m-d');
    $cost_category_id = (int)($_POST['cost_category_id'] ?? 0);

    $employee_skip_cost = !empty($staff_skip_cost_by_id[$staff_id]);
    $employee_is_all = !empty($staff_is_all_by_id[$staff_id]);
    $employee_project = isset($staff_project_by_id[$staff_id]) ? trim($staff_project_by_id[$staff_id]) : '';

    // staff_id 3 is not allowed
    if ($staff_id === 3) {
        $error = 'This employee (staff 3) is not allowed to receive salary here.';
    } elseif ($paid_by <= 0 || $staff_id <= 0 || $amount <= 0) {
        $error = 'Paid by, pay to (employee), and total amount are required.';
    } elseif (!in_array($paid_by, [1, 2], true)) {
        $error = 'Invalid payer. Only business owners (staff 1 or 2) can pay salary.';
    } elseif (!in_array($staff_id, $allowed_recipient_ids, true)) {
        $error = 'Invalid employee. Select an allowed employee.';
    } elseif (!$employee_skip_cost && $cost_category_id <= 0) {
        $error = 'Cost category is required so the salary is recorded in cost data.';
    } else {
        // Per-project cost distribution (only when not skip_cost)
        $cost_per_project = [];
        $total_cost = 0;
        if ($employee_skip_cost) {
            $cost_per_project = [];
            $total_cost = 0;
        } elseif ($employee_is_all) {
            foreach ($course_categories as $cc) {
                $key = $cc['keyword'];
                $amt = (int)($_POST['cost_project'][$key] ?? 0);
                if ($amt > 0) {
                    $cost_per_project[$key] = $amt;
                    $total_cost += $amt;
                }
            }
        } else {
            $cost_per_project = $employee_project !== '' ? [$employee_project => $amount] : [];
            $total_cost = $amount;
        }

        if (!$employee_skip_cost && $employee_is_all && $total_cost !== $amount) {
            $error = 'Sum of cost per project (' . number_format($total_cost) . ' MMK) must equal total salary amount (' . number_format($amount) . ' MMK).';
        } elseif (!$employee_skip_cost && $employee_is_all && empty($cost_per_project)) {
            $error = 'Enter at least one project with amount > 0 in the cost distribution.';
        } elseif (!$employee_skip_cost && !$employee_is_all && $employee_project === '') {
            $error = 'Selected employee has no project set. Set project in Staff or use distribution (project = all).';
        } else {
        $date_esc = $conn->real_escape_string($date);

        $recipient_row = $db->read("SELECT name FROM staffs WHERE id = $staff_id");
        $recipient_name = $recipient_row && !empty($recipient_row[0]['name']) ? $recipient_row[0]['name'] : "Staff $staff_id";
        $project_labels = [];
        foreach (array_keys($cost_per_project) as $kw) {
            $found = false;
            foreach ($course_categories as $cc) {
                if ($cc['keyword'] === $kw) {
                    $project_labels[] = $cc['project_name'] ?? $kw;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $project_labels[] = $kw;
            }
        }
        $project_names_display = implode(', ', $project_labels);
        $title = "Salary - $recipient_name - $project_names_display";
        $title_esc = $conn->real_escape_string($title);

        // Salary record: single project field – use first project with amount, or comma-separated keywords
        $salary_project_value = $employee_skip_cost ? '' : implode(',', array_keys($cost_per_project));

        // 1. Fund: one transaction (total amount) from payer; transfer_id = fund row id
        $prev = $db->read("SELECT current_balance FROM funds WHERE staff_id = $paid_by ORDER BY id DESC LIMIT 1");
        $prev_balance = $prev ? (int)$prev[0]['current_balance'] : 0;
        $new_balance = $prev_balance - $amount;
        $sql_funds = "INSERT INTO funds (title, type, amount, current_balance, date, staff_id, transfer_id) VALUES ('$title_esc', 1, $amount, $new_balance, '$date_esc', $paid_by, 0)";
        $fund_id = $db->save($sql_funds);

        if ($fund_id) {
            $db->save("UPDATE funds SET transfer_id = $fund_id WHERE id = $fund_id");
            $salary_project_esc = $conn->real_escape_string($salary_project_value);
            // 2. Salary: one record (total amount)
            $db->save("INSERT INTO salaries (staff_id, amount, project, date, transfer_id) VALUES ($staff_id, $amount, '$salary_project_esc', '$date_esc', $fund_id)");
            // 3. Cost: one record per project (only when not skip_cost for staff 1, 2)
            if (!$employee_skip_cost) {
                foreach ($cost_per_project as $major => $cost_amt) {
                    $major_esc = $conn->real_escape_string($major);
                    $db->save("INSERT INTO costs (cost_category_id, title, amount, major, date, transfer_id) VALUES ($cost_category_id, '$title_esc', $cost_amt, '$major_esc', '$date_esc', $fund_id)");
                }
            }
            $msg = $employee_skip_cost
                ? 'Salary paid. Transaction and salary record updated (no cost record).'
                : 'Salary paid. Transaction, salary record and ' . count($cost_per_project) . ' cost record(s) updated.';
            header('Location: ' . $base . '/funds.php?msg=' . urlencode($msg));
            exit;
        } else {
            $error = 'Failed to record transaction.';
        }
        }
    }
}

$cost_categories = $db->read("SELECT id, title FROM cost_categories ORDER BY title");
if ($cost_categories === false) $cost_categories = [];
$salary_category_id = 0;
foreach ($cost_categories as $cc) {
    if (stripos($cc['title'], 'salary') !== false) {
        $salary_category_id = (int)$cc['id'];
        break;
    }
}
if ($salary_category_id === 0 && !empty($cost_categories)) {
    $salary_category_id = (int)$cost_categories[0]['id'];
}

// Staff project info for JS: id => { project, is_all, project_name, skip_cost }
$staff_project_info_js = [];
foreach ($staffs_list as $s) {
    $pname = $s['project'];
    foreach ($course_categories as $cc) {
        if (($cc['keyword'] ?? '') === $s['project']) {
            $pname = $cc['project_name'] ?? $s['project'];
            break;
        }
    }
    $staff_project_info_js[$s['id']] = [
        'project' => $s['project'],
        'is_all' => $s['is_all'],
        'project_name' => $pname,
        'skip_cost' => !empty($s['skip_cost']),
    ];
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Pay Salary</h1>
<p class="period-hint">One transaction (total), one salary record (total), and one cost record per project (distributed). Transaction amount = salary amount = sum of cost amounts. All linked by transfer_id.</p>

<?php if ($error): ?>
<p style="color: var(--error); margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<div class="content-card">
  <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
    <h2>Pay salary</h2>
    <a href="<?php echo $base; ?>/funds.php" class="btn btn-secondary btn-sm">← Back to Remaining Balance</a>
  </div>
  <form method="post" action="">
    <div class="form-group">
      <label>Paid by (business owner)</label>
      <select name="paid_by" required>
        <option value="">Select who pays</option>
        <?php foreach ($payers_list as $p): ?>
        <option value="<?php echo (int)$p['id']; ?>" <?php echo (isset($_POST['paid_by']) && (int)$_POST['paid_by'] === (int)$p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <span class="card-sub">Fund transaction (Out) is recorded for this owner.</span>
    </div>
    <div class="form-group">
      <label>Pay to (employee)</label>
      <select name="staff_id" required>
        <option value="">Select employee</option>
        <?php foreach ($staffs_list as $s): ?>
        <option value="<?php echo (int)$s['id']; ?>" <?php echo (isset($_POST['staff_id']) && (int)$_POST['staff_id'] === (int)$s['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <span class="card-sub">Salary record is created for this employee.</span>
    </div>
    <div class="form-group">
      <label>Total salary amount (MMK)</label>
      <input type="number" name="amount" id="salary_total" required min="1" value="<?php echo isset($_POST['amount']) ? (int)$_POST['amount'] : ''; ?>">
      <span class="card-sub" id="amount_hint">Enter total salary. If employee has project "all", distribute below; otherwise cost is recorded under the employee's project.</span>
    </div>
    <div class="form-group">
      <label>Date</label>
      <input type="date" name="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : date('Y-m-d'); ?>">
    </div>
    <div id="cost_category_wrap" class="form-group">
      <label>Cost category</label>
      <select name="cost_category_id" id="cost_category_id_select">
        <option value="">Select (required for cost data)</option>
        <?php foreach ($cost_categories as $cc): ?>
        <option value="<?php echo (int)$cc['id']; ?>" <?php echo (isset($_POST['cost_category_id']) ? (int)$_POST['cost_category_id'] === (int)$cc['id'] : $salary_category_id === (int)$cc['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cc['title']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="cost_single_project_msg" class="form-message" style="display: none; margin-top: 16px; padding: 12px; background: var(--bg-secondary); border-radius: 8px;">
      Cost will be recorded under the selected employee's project: <strong id="cost_single_project_name"></strong>. No distribution needed.
    </div>
    <div id="cost_distribution_wrap" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
      <h3 style="margin: 0 0 8px; font-size: 16px;">Cost per project (distribution)</h3>
      <p class="card-sub" style="margin-bottom: 16px;">Distribute the total salary across projects. Sum of amounts below must equal total salary. A staff can serve multiple projects (e.g. 1000 + 1000 = 2000).</p>
      <?php foreach ($course_categories as $cc):
        $key = $cc['keyword'];
        $val = isset($_POST['cost_project'][$key]) ? (int)$_POST['cost_project'][$key] : 0;
      ?>
      <div class="form-group">
        <label>Amount for <?php echo htmlspecialchars($cc['project_name'] ?? $cc['keyword']); ?> (MMK)</label>
        <input type="number" name="cost_project[<?php echo htmlspecialchars($key); ?>]" class="cost-project-input" value="<?php echo $val; ?>" min="0" step="1" data-project="<?php echo htmlspecialchars($key); ?>">
      </div>
      <?php endforeach; ?>
      <p class="card-sub" style="margin-top: 8px;"><strong>Sum of distribution: <span id="cost_sum">0</span> MMK</strong> — must equal total salary amount above.</p>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary" id="btn_submit">Pay salary</button>
      <a href="<?php echo $base; ?>/funds.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<script>
(function() {
  var staffProjectById = <?php echo json_encode($staff_project_info_js); ?>;
  var employeeSelect = document.querySelector('select[name="staff_id"]');
  var distributionWrap = document.getElementById('cost_distribution_wrap');
  var singleMsg = document.getElementById('cost_single_project_msg');
  var singleNameEl = document.getElementById('cost_single_project_name');
  var totalInput = document.getElementById('salary_total');
  var costInputs = document.querySelectorAll('.cost-project-input');
  var sumEl = document.getElementById('cost_sum');

  var costCategoryWrap = document.getElementById('cost_category_wrap');
  var costCategorySelect = document.getElementById('cost_category_id_select');

  function setCostSectionVisibility() {
    var staffId = employeeSelect ? parseInt(employeeSelect.value, 10) : 0;
    var info = staffId ? staffProjectById[staffId] : null;
    var skipCost = info && info.skip_cost;
    if (skipCost) {
      if (costCategoryWrap) costCategoryWrap.style.display = 'none';
      if (distributionWrap) distributionWrap.style.display = 'none';
      if (singleMsg) singleMsg.style.display = 'none';
      if (costCategorySelect) costCategorySelect.removeAttribute('required');
    } else {
      if (costCategoryWrap) costCategoryWrap.style.display = '';
      if (costCategorySelect) costCategorySelect.setAttribute('required', 'required');
      if (info && info.is_all) {
        if (distributionWrap) distributionWrap.style.display = '';
        if (singleMsg) singleMsg.style.display = 'none';
      } else if (info && info.project) {
        if (distributionWrap) distributionWrap.style.display = 'none';
        if (singleMsg) {
          singleMsg.style.display = 'block';
          if (singleNameEl) singleNameEl.textContent = info.project_name || info.project;
        }
      } else {
        if (distributionWrap) distributionWrap.style.display = 'none';
        if (singleMsg) singleMsg.style.display = 'none';
      }
    }
  }

  function updateSum() {
    var sum = 0;
    if (costInputs.length) {
      costInputs.forEach(function(inp) { sum += parseInt(inp.value, 10) || 0; });
    }
    if (sumEl) sumEl.textContent = sum.toLocaleString();
    return sum;
  }

  if (employeeSelect) {
    employeeSelect.addEventListener('change', setCostSectionVisibility);
    setCostSectionVisibility();
  }
  if (totalInput && costInputs.length) {
    costInputs.forEach(function(inp) { inp.addEventListener('input', updateSum); });
    updateSum();
  }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
