<?php
/**
 * Owner income - founder salary records summarized by project and by year.
 */
$page_title = 'Owner Income';
require_once __DIR__ . '/config.php';

$projects = financial_project_rows($db);
$current_year = (int)date('Y');
$allowed_owner_ids = [1, 2];
$selected_owner_id = isset($_GET['owner_id']) ? (int)$_GET['owner_id'] : 1;
if (!in_array($selected_owner_id, $allowed_owner_ids, true)) {
    $selected_owner_id = 1;
}

$owner_rows = $db->read("SELECT id, name FROM staffs WHERE id IN (1, 2) ORDER BY id");
$owners = [
    1 => ['id' => 1, 'name' => 'Owner 1'],
    2 => ['id' => 2, 'name' => 'Owner 2'],
];
if ($owner_rows) {
    foreach ($owner_rows as $row) {
        $owner_id = (int)$row['id'];
        if (isset($owners[$owner_id])) {
            $owners[$owner_id]['name'] = trim($row['name'] ?? '') ?: 'Owner ' . $owner_id;
        }
    }
}
$selected_owner_name = $owners[$selected_owner_id]['name'];

$owner_income_total_all_time = 0;
$owner_income_project_rows = [];
$owner_income_year_rows = [];
$owner_income_project_keys = [];
$owner_income_project_labels = [];

$owner_income_by_project_raw = $db->read(
    "SELECT project, COALESCE(SUM(amount), 0) AS total, COUNT(*) AS payment_count, MAX(date) AS last_paid_date
     FROM salaries
     WHERE staff_id = $selected_owner_id
     GROUP BY project
     ORDER BY total DESC"
);
if ($owner_income_by_project_raw) {
    foreach ($owner_income_by_project_raw as $r) {
        $project_key = trim($r['project'] ?? '');
        $project_row = financial_project_by_key($projects, $project_key);
        $project_label = $project_key === '' ? 'General owner income' : financial_project_label($projects, $project_key);
        $total = (int)$r['total'];
        $owner_income_total_all_time += $total;
        $owner_income_project_keys[] = $project_key;
        $owner_income_project_labels[$project_key] = $project_label;
        $owner_income_project_rows[] = [
            'project' => $project_key,
            'project_row' => $project_row,
            'project_label' => $project_label,
            'total' => $total,
            'payment_count' => (int)$r['payment_count'],
            'last_paid_date' => $r['last_paid_date'],
        ];
    }
}

$owner_income_by_year_raw = $db->read(
    "SELECT YEAR(date) AS y, project, COALESCE(SUM(amount), 0) AS total
     FROM salaries
     WHERE staff_id = $selected_owner_id
     GROUP BY YEAR(date), project
     ORDER BY y DESC, project"
);
if ($owner_income_by_year_raw) {
    foreach ($owner_income_by_year_raw as $r) {
        $year_key = (int)$r['y'];
        $project_key = trim($r['project'] ?? '');
        $project_label = $project_key === '' ? 'General owner income' : financial_project_label($projects, $project_key);
        if (!isset($owner_income_year_rows[$year_key])) {
            $owner_income_year_rows[$year_key] = ['year' => $year_key, 'total' => 0, 'projects' => []];
        }
        if (!in_array($project_key, $owner_income_project_keys, true)) {
            $owner_income_project_keys[] = $project_key;
        }
        $owner_income_project_labels[$project_key] = $project_label;
        $amount = (int)$r['total'];
        $owner_income_year_rows[$year_key]['total'] += $amount;
        $owner_income_year_rows[$year_key]['projects'][$project_key] = $amount;
    }
}
$owner_income_project_keys = array_values(array_unique($owner_income_project_keys));
$owner_income_current_year_total = isset($owner_income_year_rows[$current_year]) ? $owner_income_year_rows[$current_year]['total'] : 0;
$owner_income_latest_year = !empty($owner_income_year_rows) ? max(array_keys($owner_income_year_rows)) : $current_year;
$owner_income_latest_year_total = isset($owner_income_year_rows[$owner_income_latest_year]) ? $owner_income_year_rows[$owner_income_latest_year]['total'] : 0;

function format_money($n) { return number_format($n); }
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="dashboard-page">
  <div class="admin-page-heading">
    <div>
      <p class="eyebrow">OWNER INCOME</p>
      <h1><?php echo htmlspecialchars($selected_owner_name); ?> income</h1>
    </div>
    <form method="get" action="" class="filter-toolbar dashboard-period-form" aria-label="Owner income filter">
      <div class="filter-group">
        <label>Owner</label>
        <select name="owner_id">
          <?php foreach ($owners as $owner): ?>
          <option value="<?php echo (int)$owner['id']; ?>" <?php echo $selected_owner_id === (int)$owner['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($owner['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
    </form>
  </div>

  <section class="metrics-grid" aria-label="Owner income overview">
    <article class="metric-card glass">
      <span><?php echo console_icon('users', 16); ?></span>
      <small>All-time income</small>
      <strong class="positive"><?php echo format_money($owner_income_total_all_time); ?> MMK</strong>
      <p><?php echo htmlspecialchars($selected_owner_name); ?></p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('folder', 16); ?></span>
      <small>Project groups</small>
      <strong><?php echo count($owner_income_project_rows); ?></strong>
      <p>Income grouped by project</p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('chart', 16); ?></span>
      <small><?php echo (int)$current_year; ?> income</small>
      <strong><?php echo format_money($owner_income_current_year_total); ?> MMK</strong>
      <p>Current calendar year</p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('wallet', 16); ?></span>
      <small>Latest year</small>
      <strong><?php echo format_money($owner_income_latest_year_total); ?> MMK</strong>
      <p><?php echo (int)$owner_income_latest_year; ?></p>
    </article>
  </section>

  <div class="admin-grid">
    <section class="panel wide glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">ALL TIME</p>
          <h2>Income by project</h2>
        </div>
        <span class="card-sub">Total: <?php echo format_money($owner_income_total_all_time); ?> MMK</span>
      </div>
      <?php if (!empty($owner_income_project_rows)): ?>
      <div class="mini-metrics-grid">
        <?php foreach ($owner_income_project_rows as $row): ?>
        <?php $income_share = $owner_income_total_all_time ? round(($row['total'] / $owner_income_total_all_time) * 100, 1) : 0; ?>
        <article class="mini-metric">
          <span class="project-card-icon"><?php echo financial_project_icon_html($row['project_row'], 'project-seal', 'folder', 16); ?></span>
          <small><?php echo htmlspecialchars($row['project_label']); ?></small>
          <strong class="positive"><?php echo format_money($row['total']); ?> MMK</strong>
          <span><?php echo $income_share; ?>% of owner income</span>
        </article>
        <?php endforeach; ?>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Project</th>
              <th class="num">All-time income</th>
              <th class="num">Share</th>
              <th class="num">Payments</th>
              <th>Last paid</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($owner_income_project_rows as $row): ?>
            <?php $income_share = $owner_income_total_all_time ? round(($row['total'] / $owner_income_total_all_time) * 100, 1) : 0; ?>
            <tr>
              <td>
                <span class="project-label">
                  <?php echo financial_project_icon_html($row['project_row'], 'project-seal', 'folder', 16); ?>
                  <span class="project-label-text">
                    <strong><?php echo htmlspecialchars($row['project_label']); ?></strong>
                    <small><?php echo $row['project'] === '' ? 'No project assigned' : htmlspecialchars($row['project']); ?></small>
                  </span>
                </span>
              </td>
              <td class="num"><?php echo format_money($row['total']); ?> MMK</td>
              <td class="num"><?php echo $income_share; ?>%</td>
              <td class="num"><?php echo (int)$row['payment_count']; ?></td>
              <td><?php echo $row['last_paid_date'] ? htmlspecialchars(date('M j, Y', strtotime($row['last_paid_date']))) : '-'; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="empty-state">No owner income records found.</div>
      <?php endif; ?>
    </section>

    <section class="panel wide glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">YEARLY</p>
          <h2>Income year by year</h2>
        </div>
        <span class="card-sub">Project income and yearly total</span>
      </div>
      <?php if (!empty($owner_income_year_rows)): ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Year</th>
              <?php foreach ($owner_income_project_keys as $project_key): ?>
              <?php $year_project = financial_project_by_key($projects, $project_key); ?>
              <th class="num">
                <span class="project-label">
                  <?php echo financial_project_icon_html($year_project, 'project-seal', 'folder', 16); ?>
                  <span><?php echo htmlspecialchars($owner_income_project_labels[$project_key] ?? ($project_key === '' ? 'General owner income' : $project_key)); ?></span>
                </span>
              </th>
              <?php endforeach; ?>
              <th class="num">Total income</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($owner_income_year_rows as $row): ?>
            <tr>
              <td><strong><?php echo (int)$row['year']; ?></strong></td>
              <?php foreach ($owner_income_project_keys as $project_key): ?>
              <td class="num"><?php echo format_money($row['projects'][$project_key] ?? 0); ?> MMK</td>
              <?php endforeach; ?>
              <td class="num"><strong><?php echo format_money($row['total']); ?> MMK</strong></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="empty-state">No owner income records found.</div>
      <?php endif; ?>
    </section>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
