<?php
/**
 * Staff CRUD – list staffs (exclude id 1,2,3), filter by in/out service. Add via staff-edit.php.
 */
$page_title = 'Staff';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = isset($_GET['msg']) ? trim($_GET['msg']) : '';
$error = '';

// Filter: in_service (default), out_service, all
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'in_service';
if (!in_array($status_filter, ['in_service', 'out_service', 'all'], true)) {
    $status_filter = 'in_service';
}

// POST: delete only (add is on staff-edit.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($id > 0 && !in_array($id, [1, 2, 3], true)) {
        $db->save("DELETE FROM staffs WHERE id = $id");
        header('Location: ' . $base . '/staffs.php?status=' . urlencode($status_filter) . '&msg=' . urlencode('Staff deleted.'));
        exit;
    }
}

// List: exclude staff id 1, 2, 3 (business owners)
$where = "id NOT IN (1, 2, 3)";
if ($status_filter === 'in_service') {
    $where .= " AND present = 1";
} elseif ($status_filter === 'out_service') {
    $where .= " AND present = 0";
}
$list = $db->read("SELECT * FROM staffs WHERE $where ORDER BY name");
if ($list === false) $list = [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Staff</h1>

<?php if ($message): ?>
<p class="form-message form-message-success"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($error): ?>
<p class="form-message form-message-error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<div class="content-card">
  <div class="card-header">
    <h2>Staff list</h2>
    <div class="card-header-actions">
      <form method="get" action="" class="filters-inline">
        <label class="filter-label">Status</label>
        <select name="status" onchange="this.form.submit()">
          <option value="in_service" <?php echo $status_filter === 'in_service' ? 'selected' : ''; ?>>In service</option>
          <option value="out_service" <?php echo $status_filter === 'out_service' ? 'selected' : ''; ?>>Out of service</option>
          <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
        </select>
      </form>
      <a href="<?php echo $base; ?>/staff-edit.php" class="btn btn-primary btn-sm">+ Add staff</a>
    </div>
  </div>
  <?php if (empty($list)): ?>
  <div class="empty-state">No staff in this filter. <a href="<?php echo $base; ?>/staff-edit.php">Add staff</a></div>
  <?php else: ?>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Rank</th>
          <th class="num">Ranking</th>
          <th>Project</th>
          <th>In service</th>
          <th class="col-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list as $row): ?>
        <tr>
          <td><?php echo (int)$row['id']; ?></td>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td><?php echo htmlspecialchars($row['rank']); ?></td>
          <td class="num"><?php echo (int)$row['ranking']; ?></td>
          <td><?php echo htmlspecialchars($row['project']); ?></td>
          <td><?php echo (int)$row['present'] ? 'Yes' : 'No'; ?></td>
          <td class="actions-cell col-actions">
            <a href="<?php echo $base; ?>/staff-edit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
            <form method="post" action="" class="form-inline" onsubmit="return confirm('Delete this staff?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
