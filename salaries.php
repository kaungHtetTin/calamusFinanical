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

$projects_list = financial_project_rows($db);
$list = $db->read("SELECT s.*, st.name AS staff_name FROM salaries s LEFT JOIN staffs st ON s.staff_id = st.id WHERE s.date >= '$date_from_esc' AND s.date <= '$date_to_esc' ORDER BY s.date DESC, s.id DESC");
if ($list === false) $list = [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="salary-page">
  <div class="admin-page-heading">
    <div>
      <p class="eyebrow">Team Payments</p>
      <h1>Salaries</h1>
    </div>
  </div>

  <?php if ($message): ?>
  <div class="form-message form-message-success"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div class="form-message form-message-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="get" action="" class="filter-toolbar salary-filter">
    <label class="filter-group">
      <span class="filter-label">Year</span>
      <select name="year">
        <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 5; $y--): ?>
        <option value="<?php echo $y; ?>" <?php echo $year === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <button type="submit" class="btn secondary">Apply</button>
  </form>

  <section class="panel glass">
    <div class="panel-heading">
      <div>
        <p class="eyebrow">Salary Records</p>
        <h2>Salary history</h2>
      </div>
    </div>
    <?php if (empty($list)): ?>
    <div class="empty-state">No records in this date range.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="data-table salary-table">
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
          <?php
            $project_value = trim($row['project'] ?? '');
            $project_icon_key = strpos($project_value, ',') !== false ? trim(explode(',', $project_value)[0]) : $project_value;
            $row_project = financial_project_by_key($projects_list, $project_icon_key);
          ?>
          <tr>
            <td><?php echo htmlspecialchars($row['date']); ?></td>
            <td><strong><?php echo htmlspecialchars($row['staff_name'] ?? '-'); ?></strong></td>
            <td>
              <span class="project-label">
                <?php echo financial_project_icon_html($row_project, 'project-seal', 'chart', 16); ?>
                <span><?php echo htmlspecialchars(financial_project_label($projects_list, $row['project'] ?? '')); ?></span>
              </span>
            </td>
            <td class="num"><?php echo number_format($row['amount']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
