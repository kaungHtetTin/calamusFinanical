<?php
$page_title = 'Salaries';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = '';
$error = '';

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$date_from = sprintf('%04d-01-01', $year);
$date_to = sprintf('%04d-12-31', $year);
$date_from_esc = $conn->real_escape_string($date_from);
$date_to_esc = $conn->real_escape_string($date_to);

// List with project_name from course_categories (keyword = project) – like Salary::get
$list = $db->read("SELECT s.*, st.name AS staff_name, cc.project_name FROM salaries s LEFT JOIN staffs st ON s.staff_id = st.id LEFT JOIN course_categories cc ON cc.keyword = s.project WHERE s.date >= '$date_from_esc' AND s.date <= '$date_to_esc' ORDER BY s.date DESC, s.id DESC");
if ($list === false) $list = [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Salaries</h1>

<?php if ($message): ?>
<p style="color: var(--success); margin-bottom: 16px;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($error): ?>
<p style="color: var(--error); margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="get" action="" class="filters-bar">
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

<div class="content-card" style="margin-top: 24px;">
  <div class="card-header">
    <h2>Salary history</h2>
  </div>
  <?php if (empty($list)): ?>
  <div class="empty-state">No records in this date range.</div>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Staff</th>
        <th>Project</th>
        <th class="num">Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($list as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['date']); ?></td>
        <td><?php echo htmlspecialchars($row['staff_name'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($row['project_name'] ?? $row['project'] ?? '—'); ?></td>
        <td class="num"><?php echo number_format($row['amount']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
