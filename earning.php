<?php
/**
 * Earning (per project) – matches C:\xampp\htdocs\financial\earning.php flow.
 * Filter by major; show Total Earning, Total Cost, Net; payments list; costs list; add cost.
 */
$page_title = 'Earning';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$major = isset($_GET['major']) ? trim($_GET['major']) : '';
$path = isset($_GET['path']) ? trim($_GET['path']) : 'Earning';

if ($major === '') {
    header('Location: ' . $base . '/index.php');
    exit;
}
$major_esc = $conn->real_escape_string($major);

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$date_from = sprintf('%04d-%02d-01', $year, $month);
$date_to = date('Y-m-t', strtotime($date_from));

// Total earning (all payments) for this major in selected month
$total_earning = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE major = '$major_esc' AND date >= '$date_from' AND date <= '$date_to'");
$earning = $total_earning ? (int)$total_earning[0]['total'] : 0;

// Total cost for this major in selected month – from costs table only (no salaries/funds lookup)
$total_cost = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM costs WHERE major = '$major_esc' AND date >= '$date_from' AND date <= '$date_to'");
$cost = $total_cost ? (int)$total_cost[0]['total'] : 0;

$net = $earning - $cost;
$message = '';
$error = '';

// Payments list: join learners on learner_phone = payments.user_id; always select p.user_id so phone shows when join misses
$payments_list = $db->read("SELECT p.id, p.user_id, p.amount, p.date, p.approve, l.learner_name, l.learner_phone FROM payments p LEFT JOIN learners l ON l.learner_phone = p.user_id WHERE p.major = '$major_esc' AND p.date >= '$date_from' AND p.date <= '$date_to' ORDER BY p.date DESC, p.id DESC");
if ($payments_list === false) $payments_list = [];

// Costs list: join cost_categories
$costs_list = $db->read("SELECT c.*, cc.title AS category_title FROM costs c LEFT JOIN cost_categories cc ON c.cost_category_id = cc.id WHERE c.major = '$major_esc' AND c.date >= '$date_from' AND c.date <= '$date_to' ORDER BY c.date DESC, c.id DESC");
if ($costs_list === false) $costs_list = [];

// --- Statistics for current project (data-science style) ---
$payment_count = count($payments_list);
$cost_count = count($costs_list);
$payment_amounts = array_map(function ($r) { return (int)$r['amount']; }, $payments_list);
$unique_payers = count(array_unique(array_column($payments_list, 'user_id')));

$stats_mean = 0;
$stats_median = 0;
$stats_min = 0;
$stats_max = 0;
$stats_std = 0;
if ($payment_count > 0) {
    $stats_mean = (int)round(array_sum($payment_amounts) / $payment_count);
    sort($payment_amounts, SORT_NUMERIC);
    $mid = (int)floor($payment_count / 2);
    $stats_median = $payment_count % 2 ? $payment_amounts[$mid] : (int)round(($payment_amounts[$mid - 1] + $payment_amounts[$mid]) / 2);
    $stats_min = $payment_amounts[0];
    $stats_max = $payment_amounts[$payment_count - 1];
    if ($payment_count > 1) {
        $variance = array_sum(array_map(function ($x) use ($stats_mean) { return ($x - $stats_mean) ** 2; }, $payment_amounts)) / ($payment_count - 1);
        $stats_std = (int)round(sqrt($variance));
    }
}

$cost_earning_ratio_pct = $earning > 0 ? round(($cost / $earning) * 100, 1) : 0;
$net_margin_pct = $earning > 0 ? round(($net / $earning) * 100, 1) : 0;

// Sale for today (this project)
$today_date = date('Y-m-d');
$today_esc = $conn->real_escape_string($today_date);
$sale_of_today_row = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE major = '$major_esc' AND date = '$today_esc'");
$sale_of_today = $sale_of_today_row ? (int)$sale_of_today_row[0]['total'] : 0;

// Sale of month = $earning (already computed for selected month)
$sale_of_month = $earning;

// Sale of year (this project, selected year)
$year_from = sprintf('%04d-01-01', $year);
$year_to = sprintf('%04d-12-31', $year);
$sale_of_year_row = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE major = '$major_esc' AND date >= '$year_from' AND date <= '$year_to'");
$sale_of_year = $sale_of_year_row ? (int)$sale_of_year_row[0]['total'] : 0;

// Sale for all time (this project)
$sale_of_all_time_row = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE major = '$major_esc'");
$sale_of_all_time = $sale_of_all_time_row ? (int)$sale_of_all_time_row[0]['total'] : 0;

// Sale of year by month (for bar chart)
$sale_by_month_raw = $db->read("SELECT MONTH(date) AS m, COALESCE(SUM(amount), 0) AS total FROM payments WHERE major = '$major_esc' AND date >= '$year_from' AND date <= '$year_to' GROUP BY MONTH(date) ORDER BY m");
$sale_by_month = array_fill(1, 12, 0);
if ($sale_by_month_raw) {
    foreach ($sale_by_month_raw as $r) {
        $sale_by_month[(int)$r['m']] = (int)$r['total'];
    }
}
$chart_year_labels = [];
$chart_year_data = [];
for ($m = 1; $m <= 12; $m++) {
    $chart_year_labels[] = date('M', mktime(0, 0, 0, $m, 1));
    $chart_year_data[] = $sale_by_month[$m];
}

// Sale of month by day (for line chart)
$days_in_month = (int)date('t', strtotime($date_from));
$sale_by_day_raw = $db->read("SELECT DAY(date) AS d, COALESCE(SUM(amount), 0) AS total FROM payments WHERE major = '$major_esc' AND date >= '$date_from' AND date <= '$date_to' GROUP BY date ORDER BY date");
$sale_by_day = [];
if ($sale_by_day_raw) {
    foreach ($sale_by_day_raw as $r) {
        $sale_by_day[(int)$r['d']] = (int)$r['total'];
    }
}
$chart_month_labels = [];
$chart_month_data = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $chart_month_labels[] = (string)$d;
    $chart_month_data[] = isset($sale_by_day[$d]) ? $sale_by_day[$d] : 0;
}

// Previous month by day (for line chart – second line, reduced transparency)
$prev_month_ts = strtotime($date_from . ' -1 month');
$date_from_prev = date('Y-m-01', $prev_month_ts);
$date_to_prev = date('Y-m-t', $prev_month_ts);
$days_in_prev_month = (int)date('t', $prev_month_ts);
$sale_by_day_prev_raw = $db->read("SELECT DAY(date) AS d, COALESCE(SUM(amount), 0) AS total FROM payments WHERE major = '$major_esc' AND date >= '$date_from_prev' AND date <= '$date_to_prev' GROUP BY date ORDER BY date");
$sale_by_day_prev = [];
if ($sale_by_day_prev_raw) {
    foreach ($sale_by_day_prev_raw as $r) {
        $sale_by_day_prev[(int)$r['d']] = (int)$r['total'];
    }
}
$chart_prev_month_data = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $chart_prev_month_data[] = ($d <= $days_in_prev_month && isset($sale_by_day_prev[$d])) ? $sale_by_day_prev[$d] : 0;
}
$chart_prev_month_label = date('M Y', $prev_month_ts);

// --- Project-level cost analysis (all time) – costs table only ---
$cost_all_time_row = $db->read("SELECT COALESCE(SUM(amount), 0) AS total FROM costs WHERE major = '$major_esc'");
$cost_all_time = $cost_all_time_row ? (int)$cost_all_time_row[0]['total'] : 0;
$cost_by_category = $db->read("SELECT c.cost_category_id, cc.title AS category_title, SUM(c.amount) AS total FROM costs c LEFT JOIN cost_categories cc ON cc.id = c.cost_category_id WHERE c.major = '$major_esc' GROUP BY c.cost_category_id ORDER BY total DESC");
if ($cost_by_category === false) $cost_by_category = [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Earning (<?php echo htmlspecialchars($path); ?>)</h1>

<?php if ($message): ?>
<p style="color: var(--success); margin-bottom: 16px;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($error): ?>
<p style="color: var(--error); margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<div class="earning-page">
<form method="get" action="" class="filters-bar">
  <input type="hidden" name="major" value="<?php echo htmlspecialchars($major); ?>">
  <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>">
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

<!-- Sale: Today → Month → Year → All time (narrowest to broadest) -->
<div class="dashboard-cards">
  <div class="card">
    <div class="card-title">Sale for today (MMK)</div>
    <p class="card-value positive"><?php echo number_format($sale_of_today); ?></p>
    <span class="card-sub"><?php echo date('d M Y', strtotime($today_date)); ?></span>
  </div>
  <div class="card">
    <div class="card-title">Sale of month (MMK)</div>
    <p class="card-value positive"><?php echo number_format($sale_of_month); ?></p>
    <span class="card-sub"><?php echo date('F Y', strtotime($date_from)); ?></span>
  </div>
  <div class="card">
    <div class="card-title">Sale of year (MMK)</div>
    <p class="card-value positive"><?php echo number_format($sale_of_year); ?></p>
    <span class="card-sub"><?php echo (int)$year; ?></span>
  </div>
  <div class="card">
    <div class="card-title">Sale for all time (MMK)</div>
    <p class="card-value positive"><?php echo number_format($sale_of_all_time); ?></p>
    <span class="card-sub">All time</span>
  </div>
</div>

<!-- Sale of year – bar chart (by month) -->
<div class="content-card">
  <div class="card-header">
    <h2>Sale of year</h2>
    <span class="card-sub"><?php echo (int)$year; ?> — by month (MMK)</span>
  </div>
  <div class="chart-container" style="position: relative; height: 280px;">
    <canvas id="chartSaleOfYear"></canvas>
  </div>
</div>

<!-- Sale of month – line chart (by day) -->
<div class="content-card">
  <div class="card-header">
    <h2>Sale of month</h2>
    <span class="card-sub"><?php echo date('F Y', strtotime($date_from)); ?> — by day (MMK)</span>
  </div>
  <div class="chart-container" style="position: relative; height: 280px;">
    <canvas id="chartSaleOfMonth"></canvas>
  </div>
</div>

<!-- Total Earning, Total Cost, Net (selected month) -->
<div class="dashboard-cards">
  <div class="card">
    <div class="card-title">Total Earning (MMK)</div>
    <p class="card-value positive"><?php echo number_format($earning); ?></p>
  </div>
  <div class="card">
    <div class="card-title">Total Cost (MMK)</div>
    <p class="card-value negative"><?php echo number_format($cost); ?></p>
  </div>
  <div class="card">
    <div class="card-title">Net Earning (MMK)</div>
    <p class="card-value"><?php echo number_format($net); ?></p>
  </div>
</div>

<!-- Project statistics (current project, this period) -->
<div class="content-card">
  <div class="card-header">
    <h2>Project statistics</h2>
    <span class="card-sub"><?php echo htmlspecialchars($path); ?> — <?php echo date('F Y', strtotime($date_from)); ?></span>
  </div>
  <div class="stats-grid">
    <div class="stat-item">
      <span class="stat-label">Payment count (N)</span>
      <span class="stat-value"><?php echo number_format($payment_count); ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-label">Cost count</span>
      <span class="stat-value"><?php echo number_format($cost_count); ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-label">Unique payers</span>
      <span class="stat-value"><?php echo number_format($unique_payers); ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-label">Mean payment (MMK)</span>
      <span class="stat-value"><?php echo number_format($stats_mean); ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-label">Median payment (MMK)</span>
      <span class="stat-value"><?php echo number_format($stats_median); ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-label">Min / Max payment (MMK)</span>
      <span class="stat-value"><?php echo number_format($stats_min); ?> / <?php echo number_format($stats_max); ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-label">Std dev (payments)</span>
      <span class="stat-value"><?php echo number_format($stats_std); ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-label">Cost / Earning ratio</span>
      <span class="stat-value"><?php echo $cost_earning_ratio_pct; ?>%</span>
    </div>
    <div class="stat-item">
      <span class="stat-label">Net margin</span>
      <span class="stat-value"><?php echo $net_margin_pct; ?>%</span>
    </div>
  </div>
</div>

<!-- Cost analysis (all time) – project level -->
<div class="content-card">
  <div class="card-header">
    <h2>Cost analysis (all time)</h2>
    <span class="card-sub"><?php echo htmlspecialchars($path); ?> — <strong class="card-value negative"><?php echo number_format($cost_all_time); ?> MMK</strong></span>
  </div>
  <div class="earning-cost-analysis">
    <?php if (empty($cost_by_category)): ?>
    <div class="empty-state" style="margin: 0 24px 24px;">No cost data for this project yet.</div>
    <?php else: ?>
    <div class="cost-by-category-wrap" style="padding: 0 24px 24px;">
      <h3 class="chart-title" style="margin: 0 0 16px; font-size: 16px;">By category</h3>
      <div class="cost-analysis-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
        <div class="chart-container" style="position: relative; height: 260px; max-width: 320px;">
          <canvas id="chartCostByCategory"></canvas>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Category</th>
                <th class="num">Amount (MMK)</th>
                <th class="num">%</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cost_by_category as $r):
                $cat_total = (int)$r['total'];
                $pct = $cost_all_time > 0 ? round(($cat_total / $cost_all_time) * 100, 1) : 0;
              ?>
              <tr>
                <td><?php echo htmlspecialchars($r['category_title'] ?? '—'); ?></td>
                <td class="num"><?php echo number_format($cat_total); ?></td>
                <td class="num"><?php echo $pct; ?>%</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td>Total</td>
                <td class="num"><?php echo number_format($cost_all_time); ?></td>
                <td class="num">100%</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Payments -->
<div class="content-card">
  <div class="card-header">
    <h2>Payments</h2>
  </div>
  <?php if (empty($payments_list)): ?>
  <div class="empty-state">No payments in this period.</div>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Phone</th>
        <th class="num">Amount</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($payments_list as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['learner_name'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($row['learner_phone'] ?? $row['user_id'] ?? '—'); ?></td>
        <td class="num"><?php echo number_format($row['amount']); ?></td>
        <td><?php echo htmlspecialchars($row['date']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">Total</td>
        <td class="num"><?php echo number_format($earning); ?> MMK</td>
        <td></td>
      </tr>
    </tfoot>
  </table>
  <?php endif; ?>
</div>

<!-- Costs -->
<div class="content-card">
  <div class="card-header">
    <h2>Costs</h2>
  </div>
  <?php if (empty($costs_list)): ?>
  <div class="empty-state">No costs in this period.</div>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Category</th>
        <th class="num">Amount</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($costs_list as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['category_title'] ?? '—'); ?></td>
        <td class="num"><?php echo number_format($row['amount']); ?></td>
        <td><?php echo htmlspecialchars($row['date'] ?? '—'); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">Total</td>
        <td class="num"><?php echo number_format($cost); ?> MMK</td>
        <td></td>
      </tr>
    </tfoot>
  </table>
  <?php endif; ?>
</div>
</div><!-- /.earning-page -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
  var yearLabels = <?php echo json_encode($chart_year_labels); ?>;
  var yearData = <?php echo json_encode($chart_year_data); ?>;
  var monthLabels = <?php echo json_encode($chart_month_labels); ?>;
  var monthData = <?php echo json_encode($chart_month_data); ?>;
  var prevMonthData = <?php echo json_encode($chart_prev_month_data); ?>;
  var prevMonthLabel = <?php echo json_encode($chart_prev_month_label); ?>;
  var costByCategory = <?php echo json_encode(array_map(function($r) { return ['label' => $r['category_title'] ?? 'Uncategorized', 'total' => (int)$r['total']]; }, $cost_by_category)); ?>;

  // Sale of year – bar chart (by month)
  if (document.getElementById('chartSaleOfYear')) {
    new Chart(document.getElementById('chartSaleOfYear'), {
      type: 'bar',
      data: {
        labels: yearLabels,
        datasets: [{
          label: 'Sale (MMK)',
          data: yearData,
          backgroundColor: 'rgba(26, 115, 232, 0.7)',
          borderColor: '#1a73e8',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(ctx) { return ctx.raw.toLocaleString() + ' MMK'; }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: function(v) { return v.toLocaleString(); } }
          }
        }
      }
    });
  }

  // Cost analysis (all time) – doughnut by category
  if (costByCategory.length && document.getElementById('chartCostByCategory')) {
    var colors = ['#1a73e8', '#34a853', '#f9ab00', '#ea4335', '#9334e6', '#00acc1', '#5e35b1', '#43a047'];
    function color(i) { return colors[i % colors.length]; }
    new Chart(document.getElementById('chartCostByCategory'), {
      type: 'doughnut',
      data: {
        labels: costByCategory.map(function(r) { return r.label; }),
        datasets: [{
          data: costByCategory.map(function(r) { return r.total; }),
          backgroundColor: costByCategory.map(function(_, i) { return color(i); }),
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                var v = ctx.raw;
                var t = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                return (ctx.label || '') + ': ' + v.toLocaleString() + ' MMK (' + (t ? Math.round(100 * v / t) : 0) + '%)';
              }
            }
          }
        }
      }
    });
  }

  // Sale of month – line chart (by day): current month + previous month (different color, reduced transparency)
  if (document.getElementById('chartSaleOfMonth')) {
    new Chart(document.getElementById('chartSaleOfMonth'), {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [
          {
            label: 'Current month',
            data: monthData,
            borderColor: '#1a73e8',
            backgroundColor: 'rgba(26, 115, 232, 0.1)',
            fill: true,
            tension: 0.2,
            borderWidth: 2
          },
          {
            label: prevMonthLabel,
            data: prevMonthData,
            borderColor: 'rgba(95, 99, 104, 0.6)',
            backgroundColor: 'rgba(95, 99, 104, 0.08)',
            fill: true,
            tension: 0.2,
            borderWidth: 2,
            borderDash: [4, 2]
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' },
          tooltip: {
            callbacks: {
              label: function(ctx) { return (ctx.dataset.label || '') + ': ' + ctx.raw.toLocaleString() + ' MMK'; }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: function(v) { return v.toLocaleString(); } }
          }
        }
      }
    });
  }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
