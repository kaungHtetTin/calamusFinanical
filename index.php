<?php
/**
 * Dashboard – Sale analysis: by all projects, by project, by year, by month, by day.
 * Period-over-period comparison for decision support.
 */
$page_title = 'Dashboard';
require_once __DIR__ . '/config.php';

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$this_month_start = sprintf('%04d-%02d-01', $year, $month);
$this_month_end = date('Y-m-t', strtotime($this_month_start));
$last_month_num = $month === 1 ? 12 : $month - 1;
$last_year_num = $month === 1 ? $year - 1 : $year;
$last_month_start = sprintf('%04d-%02d-01', $last_year_num, $last_month_num);
$last_month_end = date('Y-m-t', strtotime($last_month_start));
$same_month_last_year_start = sprintf('%04d-%02d-01', $year - 1, $month);
$same_month_last_year_end = date('Y-m-t', strtotime($same_month_last_year_start));

$date_cur_esc = $conn->real_escape_string($this_month_start);
$date_cur_end_esc = $conn->real_escape_string($this_month_end);
$date_last_esc = $conn->real_escape_string($last_month_start);
$date_last_end_esc = $conn->real_escape_string($last_month_end);
$date_yoy_esc = $conn->real_escape_string($same_month_last_year_start);
$date_yoy_end_esc = $conn->real_escape_string($same_month_last_year_end);

// Current period & last period (MoM) & same month last year (YoY) — all payments (any approve state)
$rev_current = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE date >= '$date_cur_esc' AND date <= '$date_cur_end_esc'");
$rev_last = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE date >= '$date_last_esc' AND date <= '$date_last_end_esc'");
$rev_same_month_last_year = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE date >= '$date_yoy_esc' AND date <= '$date_yoy_end_esc'");

$rev_this_period = $rev_current ? (int)$rev_current[0]['total'] : 0;
$rev_last_period = $rev_last ? (int)$rev_last[0]['total'] : 0;
$rev_yoy = $rev_same_month_last_year ? (int)$rev_same_month_last_year[0]['total'] : 0;

$change_mom = $rev_last_period ? round((($rev_this_period - $rev_last_period) / $rev_last_period) * 100, 1) : ($rev_this_period ? 100 : 0);
$change_yoy = $rev_yoy ? round((($rev_this_period - $rev_yoy) / $rev_yoy) * 100, 1) : ($rev_this_period ? 100 : 0);

// By project (this period + last period for comparison)
$projects = financial_project_rows($db);

$project_current_totals = [];
$project_current_rows = $db->read(
    "SELECT major, COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE date >= '$date_cur_esc' AND date <= '$date_cur_end_esc'
     GROUP BY major"
);
if ($project_current_rows) {
    foreach ($project_current_rows as $r) {
        $project_current_totals[$r['major']] = (int)$r['total'];
    }
}

$project_last_totals = [];
$project_last_rows = $db->read(
    "SELECT major, COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE date >= '$date_last_esc' AND date <= '$date_last_end_esc'
     GROUP BY major"
);
if ($project_last_rows) {
    foreach ($project_last_rows as $r) {
        $project_last_totals[$r['major']] = (int)$r['total'];
    }
}

foreach ($projects as $i => $p) {
    $major = $p['keyword'];
    $projects[$i]['total_sale'] = $project_current_totals[$major] ?? 0;
    $projects[$i]['last_sale'] = $project_last_totals[$major] ?? 0;
    $projects[$i]['change_pct'] = $projects[$i]['last_sale'] ? round((($projects[$i]['total_sale'] - $projects[$i]['last_sale']) / $projects[$i]['last_sale']) * 100, 1) : ($projects[$i]['total_sale'] ? 100 : 0);
    $projects[$i]['share_pct'] = $rev_this_period ? round(($projects[$i]['total_sale'] / $rev_this_period) * 100, 1) : 0;
}
// Sort by this period descending
usort($projects, function ($a, $b) { return $b['total_sale'] - $a['total_sale']; });

// By year (last 10 years)
$by_year = $db->read("SELECT YEAR(date) AS y, COALESCE(SUM(amount), 0) AS total FROM payments WHERE YEAR(date) >= " . ($year - 9) . " GROUP BY YEAR(date) ORDER BY y");
if ($by_year === false) $by_year = [];

// By month (selected year)
$by_month = $db->read("SELECT MONTH(date) AS m, COALESCE(SUM(amount), 0) AS total FROM payments WHERE YEAR(date) = $year GROUP BY MONTH(date) ORDER BY m");
if ($by_month === false) $by_month = [];
$month_labels = [];
$month_data = [];
for ($m = 1; $m <= 12; $m++) {
    $month_labels[] = date('M', mktime(0, 0, 0, $m, 1));
    $found = false;
    if ($by_month) { foreach ($by_month as $r) { if ((int)$r['m'] === $m) { $month_data[] = (int)$r['total']; $found = true; break; } } }
    if (!$found) $month_data[] = 0;
}

// By day (selected month)
$by_day = $db->read("SELECT date AS d, COALESCE(SUM(amount), 0) AS total FROM payments WHERE date >= '$date_cur_esc' AND date <= '$date_cur_end_esc' GROUP BY date ORDER BY date");
if ($by_day === false) $by_day = [];
$days_in_month = (int)date('t', strtotime($this_month_start));
$day_labels = [];
$day_data = [];
$by_day_map = [];
if ($by_day) { foreach ($by_day as $r) { $by_day_map[$r['d']] = (int)$r['total']; } }
for ($d = 1; $d <= $days_in_month; $d++) {
    $day_labels[] = $d;
    $d_str = sprintf('%04d-%02d-%02d', $year, $month, $d);
    $day_data[] = isset($by_day_map[$d_str]) ? $by_day_map[$d_str] : 0;
}

// Today's income (per project + total)
$today_date = date('Y-m-d');
$today_date_esc = $conn->real_escape_string($today_date);
$today_total_row = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE date = '$today_date_esc'");
$today_income_total = $today_total_row ? (int)$today_total_row[0]['total'] : 0;
$today_by_project_raw = $db->read("SELECT major, COALESCE(SUM(amount), 0) AS total FROM payments WHERE date = '$today_date_esc' GROUP BY major");
$today_by_project = [];
if ($today_by_project_raw) {
    foreach ($today_by_project_raw as $r) {
        $today_by_project[$r['major']] = (int)$r['total'];
    }
}
// Add today_sale to each project for display
foreach ($projects as $i => $p) {
    $projects[$i]['today_sale'] = isset($today_by_project[$p['keyword']]) ? $today_by_project[$p['keyword']] : 0;
}

// Remaining balance
$total_balance = 0;
$balance_rows = $db->read(
    "SELECT f.staff_id, f.current_balance
     FROM funds f
     INNER JOIN (
        SELECT staff_id, MAX(id) AS id
        FROM funds
        WHERE staff_id IN (1, 2, 3)
        GROUP BY staff_id
     ) latest ON latest.id = f.id"
);
if ($balance_rows) {
    foreach ($balance_rows as $row) {
        $total_balance += (int)$row['current_balance'];
    }
}

// Insight: top project
$top_project = null;
$top_share = 0;
if (!empty($projects) && $projects[0]['total_sale'] > 0) {
    $top_project = $projects[0]['project_name'] ?? $projects[0]['keyword'];
    $top_share = $projects[0]['share_pct'];
}

// Decision support: recommendations
$recommendations = [];
if ($rev_this_period > 0 || $rev_last_period > 0) {
    if ($rev_last_period > 0 && $change_mom < 0) {
        $recommendations[] = 'Revenue is down vs last month — review seasonal factors, marketing, or pipeline for ' . date('F', mktime(0, 0, 0, $month, 1)) . '.';
    }
    if ($rev_yoy > 0 && $change_yoy > 0) {
        $recommendations[] = 'Year-over-year growth is positive — consider sustaining or scaling current initiatives.';
    }
    if ($rev_yoy > 0 && $change_yoy < 0) {
        $recommendations[] = 'Revenue is below same month last year — compare campaigns and capacity to prior year.';
    }
    $top_growing = null;
    foreach ($projects as $p) {
        if ($p['last_sale'] > 0 && $p['change_pct'] > 0 && ($top_growing === null || $p['change_pct'] > $top_growing['change_pct'])) {
            $top_growing = $p;
        }
    }
    if ($top_growing) {
        $recommendations[] = 'Fastest growing project vs last period: <strong>' . htmlspecialchars($top_growing['project_name'] ?? $top_growing['keyword']) . '</strong> (+' . $top_growing['change_pct'] . '%) — consider allocating more focus or budget.';
    }
    if ($top_share >= 50 && $top_project) {
        $recommendations[] = 'Revenue is concentrated in ' . htmlspecialchars($top_project) . ' (' . $top_share . '%) — consider diversification or doubling down with clear targets.';
    }
}

$base = FINANCIAL_BASE;
function format_money($n) { return number_format($n); }
$period_label = date('F Y', strtotime($this_month_start));
$last_period_label = date('F Y', strtotime($last_month_start));
$today_label = date('l, F j, Y', strtotime($today_date));
$projects_by_today = $projects;
usort($projects_by_today, function ($a, $b) { return $b['today_sale'] - $a['today_sale']; });
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="dashboard-page">
  <div class="admin-page-heading">
    <div>
      <p class="eyebrow"><?php echo htmlspecialchars($today_label); ?></p>
      <h1>Sale analysis</h1>
    </div>
    <form method="get" action="" class="filter-toolbar dashboard-period-form" aria-label="Dashboard period filter">
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
          <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 9; $y--): ?>
          <option value="<?php echo $y; ?>" <?php echo $year === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
    </form>
  </div>

  <section class="metrics-grid" aria-label="Revenue overview">
    <article class="metric-card glass">
      <span><?php echo console_icon('chart', 16); ?></span>
      <small>This period</small>
      <strong class="positive"><?php echo format_money($rev_this_period); ?> MMK</strong>
      <p><?php echo htmlspecialchars($period_label); ?></p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('wallet', 16); ?></span>
      <small>Last period</small>
      <strong><?php echo format_money($rev_last_period); ?> MMK</strong>
      <p><?php echo htmlspecialchars($last_period_label); ?></p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('chart', 16); ?></span>
      <small>Change MoM</small>
      <strong class="<?php echo $change_mom >= 0 ? 'positive' : 'negative'; ?>"><?php echo $change_mom >= 0 ? '+' : ''; ?><?php echo $change_mom; ?>%</strong>
      <p>vs last month</p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('grid', 16); ?></span>
      <small>Same month last year</small>
      <strong><?php echo format_money($rev_yoy); ?> MMK</strong>
      <p>YoY: <?php echo $change_yoy >= 0 ? '+' : ''; ?><?php echo $change_yoy; ?>%</p>
    </article>
    <article class="metric-card glass">
      <span><?php echo console_icon('wallet', 16); ?></span>
      <small>Remaining balance</small>
      <strong><?php echo format_money($total_balance); ?> MMK</strong>
      <p>Staff 1, 2, 3</p>
    </article>
  </section>

  <div class="admin-grid">
    <section class="panel wide glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">TODAY</p>
          <h2>Income per project</h2>
        </div>
        <span class="card-sub">Total: <?php echo format_money($today_income_total); ?> MMK</span>
      </div>
      <?php if (!empty($projects_by_today)): ?>
      <div class="mini-metrics-grid">
        <?php foreach ($projects_by_today as $p): ?>
        <article class="mini-metric">
          <span class="project-card-icon"><?php echo financial_project_icon_html($p, 'project-seal', 'chart', 16); ?></span>
          <small><?php echo htmlspecialchars($p['project_name'] ?? $p['keyword']); ?></small>
          <strong class="<?php echo $p['today_sale'] > 0 ? 'positive' : ''; ?>"><?php echo format_money($p['today_sale']); ?> MMK</strong>
          <span><?php echo $p['today_sale'] > 0 ? 'Approved payments today' : 'No income today'; ?></span>
        </article>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="empty-state">No projects defined.</div>
      <?php endif; ?>
    </section>

    <?php if ($rev_this_period > 0 || $rev_last_period > 0): ?>
    <section class="panel glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">INSIGHT</p>
          <h2>Period signal</h2>
        </div>
      </div>
      <div class="alert-list">
        <?php if ($rev_last_period > 0): ?>
        <div>
          <span class="alert-icon <?php echo $change_mom >= 0 ? 'info' : 'warning'; ?>"><?php echo console_icon('chart', 15); ?></span>
          <p><strong>Revenue is <?php echo $change_mom >= 0 ? 'up' : 'down'; ?> <?php echo abs($change_mom); ?>%</strong><small>Compared with <?php echo htmlspecialchars($last_period_label); ?></small></p>
        </div>
        <?php endif; ?>
        <?php if ($rev_yoy > 0): ?>
        <div>
          <span class="alert-icon info"><?php echo console_icon('grid', 15); ?></span>
          <p><strong>Year over year: <?php echo $change_yoy >= 0 ? '+' : ''; ?><?php echo $change_yoy; ?>%</strong><small>Same month last year</small></p>
        </div>
        <?php endif; ?>
        <?php if ($top_project && $top_share > 0): ?>
        <div>
          <span class="alert-icon info"><?php echo console_icon('folder', 15); ?></span>
          <p><strong><?php echo htmlspecialchars($top_project); ?></strong><small><?php echo $top_share; ?>% of sales</small></p>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($recommendations)): ?>
    <section class="panel glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">NEXT STEPS</p>
          <h2>Recommendations</h2>
        </div>
      </div>
      <div class="recommendation-list">
        <?php foreach ($recommendations as $rec): ?>
        <p><?php echo $rec; ?></p>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($projects)): ?>
    <section class="panel wide glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">PRODUCTS</p>
          <h2>Sales by product</h2>
        </div>
        <span class="card-sub">This period vs last period</span>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Project</th>
              <th class="num">This period</th>
              <th class="num">Last period</th>
              <th class="num">Change %</th>
              <th class="num">Share</th>
              <th class="col-actions">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($projects as $p): ?>
            <tr>
              <td>
                <span class="project-label">
                  <?php echo financial_project_icon_html($p, 'project-seal', 'chart', 16); ?>
                  <span class="project-label-text">
                    <strong><?php echo htmlspecialchars($p['project_name'] ?? $p['keyword']); ?></strong>
                    <small><?php echo htmlspecialchars($p['keyword']); ?></small>
                  </span>
                </span>
              </td>
              <td class="num"><?php echo format_money($p['total_sale']); ?> MMK</td>
              <td class="num"><?php echo format_money($p['last_sale']); ?> MMK</td>
              <td class="num"><span class="status <?php echo $p['change_pct'] >= 0 ? 'status-success' : 'status-danger'; ?>"><?php echo $p['change_pct'] >= 0 ? '+' : ''; ?><?php echo $p['change_pct']; ?>%</span></td>
              <td class="num"><?php echo $p['share_pct']; ?>%</td>
              <td class="col-actions">
                <div class="inline-actions">
                  <a href="<?php echo $base; ?>/earning.php?major=<?php echo urlencode($p['keyword']); ?>&path=<?php echo urlencode($p['project_name'] ?? $p['keyword']); ?>" class="icon-btn small" aria-label="View <?php echo htmlspecialchars($p['project_name'] ?? $p['keyword']); ?> details" title="View details">
                    <?php echo console_icon('chart', 15); ?>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>

    <section class="panel wide glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">TREND</p>
          <h2>Sales trend</h2>
        </div>
        <span class="card-sub">By year, month, and day</span>
      </div>
      <div class="charts-grid dashboard-charts">
        <div class="chart-card">
          <h3 class="chart-title">By year</h3>
          <div class="chart-frame"><canvas id="chartByYear"></canvas></div>
        </div>
        <div class="chart-card">
          <h3 class="chart-title">By month (<?php echo $year; ?>)</h3>
          <div class="chart-frame"><canvas id="chartByMonth"></canvas></div>
        </div>
        <div class="chart-card chart-card-full">
          <h3 class="chart-title">By day (<?php echo $period_label; ?>)</h3>
          <div class="chart-frame"><canvas id="chartByDay"></canvas></div>
        </div>
      </div>
    </section>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
  var byYear = <?php echo json_encode(array_map(function($r) { return ['y' => (int)$r['y'], 'total' => (int)$r['total']]; }, $by_year)); ?>;
  var monthLabels = <?php echo json_encode($month_labels); ?>;
  var monthData = <?php echo json_encode($month_data); ?>;
  var dayLabels = <?php echo json_encode($day_labels); ?>;
  var dayData = <?php echo json_encode($day_data); ?>;
  var colors = ['#545760', '#168255', '#b77700', '#ce4444', '#2874bc'];
  function color(i) { return colors[i % colors.length]; }

  if (byYear.length) {
    new Chart(document.getElementById('chartByYear'), {
      type: 'bar',
      data: {
        labels: byYear.map(function(r) { return r.y; }),
        datasets: [{ label: 'Sales (MMK)', data: byYear.map(function(r) { return r.total; }), backgroundColor: byYear.map(function(_, i) { return color(i); }), borderWidth: 0 }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx) { return ctx.raw.toLocaleString() + ' MMK'; } } } }, scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return v.toLocaleString(); } } } } }
    });
  }
  new Chart(document.getElementById('chartByMonth'), {
    type: 'bar',
    data: { labels: monthLabels, datasets: [{ label: 'Sales (MMK)', data: monthData, backgroundColor: color(0), borderWidth: 0 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx) { return ctx.raw.toLocaleString() + ' MMK'; } } } }, scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return v.toLocaleString(); } } } } }
  });
  new Chart(document.getElementById('chartByDay'), {
    type: 'line',
    data: { labels: dayLabels, datasets: [{ label: 'Sales (MMK)', data: dayData, borderColor: color(0), backgroundColor: 'rgba(84, 87, 96, 0.1)', fill: true, tension: 0.2 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx) { return ctx.raw.toLocaleString() + ' MMK'; } } } }, scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return v.toLocaleString(); } } } } }
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
