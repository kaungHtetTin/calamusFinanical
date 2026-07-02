<?php
/**
 * Costs list and analytics. All cost data comes from the `costs` table only.
 */
$page_title = 'Costs';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = isset($_GET['msg']) ? trim($_GET['msg']) : '';

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$date_from = sprintf('%04d-%02d-01', $year, $month);
$date_to = date('Y-m-t', strtotime($date_from));
$major_filter = isset($_GET['major']) ? trim($_GET['major']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$date_from_esc = $conn->real_escape_string($date_from);
$date_to_esc = $conn->real_escape_string($date_to);

$where = "c.date >= '$date_from_esc' AND c.date <= '$date_to_esc'";
if ($major_filter !== '') {
    $major_esc = $conn->real_escape_string($major_filter);
    $where .= " AND c.major = '$major_esc'";
}
if ($category_id > 0) {
    $where .= " AND c.cost_category_id = $category_id";
}
$list = $db->read("SELECT c.*, cc.title AS category_title FROM costs c LEFT JOIN cost_categories cc ON c.cost_category_id = cc.id WHERE $where ORDER BY c.date DESC, c.id DESC");
if ($list === false) $list = [];

$by_category = $db->read("SELECT c.cost_category_id, cc.title AS category_title, SUM(c.amount) AS total FROM costs c LEFT JOIN cost_categories cc ON c.cost_category_id = cc.id GROUP BY c.cost_category_id ORDER BY total DESC");
if ($by_category === false) $by_category = [];
$by_project = $db->read("SELECT c.major, COALESCE(NULLIF(l.display_name, ''), l.name, c.major) AS project_name, SUM(c.amount) AS total FROM costs c LEFT JOIN languages l ON l.code = c.major GROUP BY c.major ORDER BY total DESC");
if ($by_project === false) $by_project = [];
$total_cost_all_time = 0;
foreach ($by_category as $r) {
    $total_cost_all_time += (int)$r['total'];
}

$total_income_row = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments");
$total_income = $total_income_row ? (int)$total_income_row[0]['total'] : 0;
$net_income_cost = $total_income - $total_cost_all_time;

$period_total = 0;
foreach ($list as $row) {
    $period_total += (int)$row['amount'];
}

$categories = $db->read("SELECT id, title FROM cost_categories ORDER BY title");
if ($categories === false) $categories = [];
$course_categories = financial_project_rows($db);
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="costs-page">
  <div class="admin-page-heading">
    <div>
      <p class="eyebrow"><?php echo date('F Y', strtotime($date_from)); ?></p>
      <h1>Costs</h1>
    </div>
  </div>

  <?php if ($message): ?>
  <p class="form-message form-message-success" role="status"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="get" action="" class="filter-toolbar costs-filter" aria-label="Cost filters">
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
    <div class="filter-group">
      <label>Project</label>
      <select name="major">
        <option value="">All projects</option>
        <?php foreach ($course_categories as $cc): ?>
        <option value="<?php echo htmlspecialchars($cc['keyword']); ?>" <?php echo $major_filter === $cc['keyword'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cc['project_name'] ?? $cc['keyword']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-group">
      <label>Category</label>
      <select name="category_id">
        <option value="0">All categories</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?php echo (int)$cat['id']; ?>" <?php echo $category_id === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['title']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
  </form>

  <section class="metrics-grid costs-metrics" aria-label="Cost overview">
    <article class="metric-card glass">
      <span><?php echo console_icon('wallet', 16); ?></span>
      <small>Total income</small>
      <strong class="positive"><?php echo number_format($total_income); ?> MMK</strong>
      <p>All time payments</p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('cost', 16); ?></span>
      <small>Total cost</small>
      <strong class="negative"><?php echo number_format($total_cost_all_time); ?> MMK</strong>
      <p>All recorded costs</p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('chart', 16); ?></span>
      <small>Net</small>
      <strong class="<?php echo $net_income_cost >= 0 ? 'positive' : 'negative'; ?>"><?php echo number_format($net_income_cost); ?> MMK</strong>
      <p><?php echo $net_income_cost >= 0 ? 'Surplus' : 'Deficit'; ?></p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('grid', 16); ?></span>
      <small>Filtered period</small>
      <strong><?php echo number_format($period_total); ?> MMK</strong>
      <p><?php echo count($list); ?> cost rows</p>
    </article>
  </section>

  <div class="admin-grid costs-grid">
    <section class="panel glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">ANALYTICS</p>
          <h2>Cost analytics</h2>
        </div>
        <span class="card-sub">All time</span>
      </div>
      <?php if (empty($by_category) && empty($by_project)): ?>
      <div class="empty-state">No cost data yet. Add costs to see analytics.</div>
      <?php else: ?>
      <div class="analytics-charts cost-analytics-grid">
        <?php if (!empty($by_category)): ?>
        <div class="chart-card">
          <h3 class="chart-title">By category</h3>
          <div class="chart-frame"><canvas id="chartByCategory"></canvas></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($by_project)): ?>
        <div class="chart-card">
          <h3 class="chart-title">By project</h3>
          <div class="chart-frame"><canvas id="chartByProject"></canvas></div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </section>

    <section class="panel glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">LIST</p>
          <h2>Cost list</h2>
        </div>
        <span class="status status-neutral"><?php echo count($list); ?> rows</span>
      </div>
      <?php if (empty($list)): ?>
      <div class="empty-state">No costs in this range.</div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="data-table cost-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Category</th>
              <th>Title</th>
              <th>Project</th>
              <th class="num">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $row): ?>
            <?php $row_project = financial_project_by_key($course_categories, $row['major'] ?? ''); ?>
            <tr>
              <td><?php echo htmlspecialchars($row['date']); ?></td>
              <td><span class="status status-neutral"><?php echo htmlspecialchars($row['category_title'] ?? '-'); ?></span></td>
              <td><strong><?php echo htmlspecialchars($row['title']); ?></strong><small>Transfer #<?php echo (int)($row['transfer_id'] ?? 0); ?></small></td>
              <td>
                <span class="project-label">
                  <?php echo financial_project_icon_html($row_project, 'project-seal', 'chart', 16); ?>
                  <span><?php echo htmlspecialchars(financial_project_label($course_categories, $row['major'] ?? '')); ?></span>
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
</div>

<?php if (!empty($by_category) || !empty($by_project)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
  var byCategory = <?php echo json_encode(array_map(function($r) { return ['label' => $r['category_title'] ?: 'Uncategorized', 'total' => (int)$r['total']]; }, $by_category)); ?>;
  var byProject = <?php echo json_encode(array_map(function($r) {
    $label = trim($r['project_name'] ?? '') ?: (trim($r['major'] ?? '') ?: 'No project');
    return ['label' => $label, 'total' => (int)$r['total']];
  }, $by_project)); ?>;
  var colors = ['#545760', '#168255', '#b77700', '#ce4444', '#2874bc', '#7b8795'];
  function color(i) { return colors[i % colors.length]; }

  if (byCategory.length) {
    new Chart(document.getElementById('chartByCategory'), {
      type: 'doughnut',
      data: { labels: byCategory.map(function(r) { return r.label; }), datasets: [{ data: byCategory.map(function(r) { return r.total; }), backgroundColor: byCategory.map(function(_, i) { return color(i); }), borderWidth: 1 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function(ctx) { var v = ctx.raw; var t = ctx.dataset.data.reduce(function(a,b){ return a+b; }, 0); return (ctx.label || '') + ': ' + v.toLocaleString() + ' MMK (' + (t ? Math.round(100 * v / t) : 0) + '%)'; } } } } }
    });
  }
  if (byProject.length) {
    new Chart(document.getElementById('chartByProject'), {
      type: 'bar',
      data: { labels: byProject.map(function(r) { return r.label; }), datasets: [{ label: 'Cost (MMK)', data: byProject.map(function(r) { return r.total; }), backgroundColor: byProject.map(function(_, i) { return color(i); }), borderWidth: 0 }] },
      options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx) { return ctx.raw.toLocaleString() + ' MMK'; } } } }, scales: { x: { beginAtZero: true, ticks: { callback: function(v) { return v.toLocaleString(); } } } } }
    });
  }
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
