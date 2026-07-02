<?php
/**
 * Staff CRUD - list staffs (exclude id 1,2,3), filter by in/out service.
 */
$page_title = 'Staff';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = isset($_GET['msg']) ? trim($_GET['msg']) : '';
$error = '';

$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'in_service';
if (!in_array($status_filter, ['in_service', 'out_service', 'all'], true)) {
    $status_filter = 'in_service';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($id > 0 && !in_array($id, [1, 2, 3], true)) {
        $db->save("DELETE FROM staffs WHERE id = $id");
        header('Location: ' . $base . '/staffs.php?status=' . urlencode($status_filter) . '&msg=' . urlencode('Staff deleted.'));
        exit;
    }
}

$where = "id NOT IN (1, 2, 3)";
if ($status_filter === 'in_service') {
    $where .= " AND present = 1";
} elseif ($status_filter === 'out_service') {
    $where .= " AND present = 0";
}
$list = $db->read("SELECT * FROM staffs WHERE $where ORDER BY name");
if ($list === false) $list = [];
$projects_list = financial_project_rows($db);
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="management-page">
  <div class="admin-page-heading">
    <div>
      <p class="eyebrow">TEAM MANAGEMENT</p>
      <h1>Staff</h1>
    </div>
    <a href="<?php echo $base; ?>/staff-edit.php" class="btn btn-primary"><?php echo console_icon('plus', 15); ?> Add staff</a>
  </div>

  <?php if ($message): ?>
  <p class="form-message form-message-success" role="status"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>
  <?php if ($error): ?>
  <p class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <section class="panel glass">
    <div class="panel-heading">
      <div>
        <p class="eyebrow">DIRECTORY</p>
        <h2>Staff list</h2>
      </div>
      <span class="status status-neutral"><?php echo count($list); ?> rows</span>
    </div>

    <form method="get" action="" class="filter-toolbar staff-filter" aria-label="Staff filters">
      <div class="filter-group">
        <label>Status</label>
        <select name="status">
          <option value="in_service" <?php echo $status_filter === 'in_service' ? 'selected' : ''; ?>>In service</option>
          <option value="out_service" <?php echo $status_filter === 'out_service' ? 'selected' : ''; ?>>Out of service</option>
          <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
        </select>
      </div>
      <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
    </form>

    <?php if (empty($list)): ?>
    <div class="empty-state">No staff in this filter. <a href="<?php echo $base; ?>/staff-edit.php">Add staff</a></div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="data-table staff-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Rank</th>
            <th class="num">Ranking</th>
            <th>Project</th>
            <th>Status</th>
            <th class="col-actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $row): ?>
          <?php $row_project = financial_project_by_key($projects_list, $row['project'] ?? ''); ?>
          <tr>
            <td><?php echo (int)$row['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong><small><?php echo htmlspecialchars(financial_project_label($projects_list, $row['project'] ?? '')); ?></small></td>
            <td><?php echo htmlspecialchars($row['rank']); ?></td>
            <td class="num"><?php echo (int)$row['ranking']; ?></td>
            <td>
              <span class="project-label">
                <?php echo financial_project_icon_html($row_project, 'project-seal', 'chart', 16); ?>
                <span><?php echo htmlspecialchars(financial_project_label($projects_list, $row['project'] ?? '')); ?></span>
              </span>
            </td>
            <td><span class="status <?php echo (int)$row['present'] ? 'status-success' : 'status-neutral'; ?>"><?php echo (int)$row['present'] ? 'In service' : 'Out of service'; ?></span></td>
            <td class="actions-cell col-actions">
              <div class="inline-actions">
                <a href="<?php echo $base; ?>/staff-edit.php?id=<?php echo (int)$row['id']; ?>" class="icon-btn small" aria-label="Edit staff" title="Edit">
                  <?php echo console_icon('edit', 14); ?>
                </a>
                <form method="post" action="" class="form-inline" onsubmit="return confirm('Delete this staff?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                  <button type="submit" class="icon-btn small danger" aria-label="Delete staff" title="Delete">
                    <?php echo console_icon('trash', 14); ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
