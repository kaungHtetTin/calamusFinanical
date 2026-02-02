<?php
/**
 * Staff edit – create (no id) or update (id in GET).
 */
$page_title = 'Edit Staff';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = '';
$error = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$row = null;

if ($is_edit) {
    $rows = $db->read("SELECT * FROM staffs WHERE id = $id");
    $row = $rows && !empty($rows[0]) ? $rows[0] : null;
    if (!$row) {
        header('Location: ' . $base . '/staffs.php?msg=' . urlencode('Staff not found.'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $rank = trim($_POST['rank'] ?? '');
    $ranking = (int)($_POST['ranking'] ?? 0);
    $project = trim($_POST['project'] ?? '');
    $present = isset($_POST['present']) && $_POST['present'] ? 1 : 0;
    $post_id = (int)($_POST['id'] ?? 0);

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $name_esc = $conn->real_escape_string($name);
        $rank_esc = $conn->real_escape_string($rank);
        $project_esc = $conn->real_escape_string(mb_substr($project, 0, 11));

        if ($post_id > 0) {
            $sql = "UPDATE staffs SET name = '$name_esc', rank = '$rank_esc', ranking = $ranking, project = '$project_esc', present = $present WHERE id = $post_id";
            if ($db->save($sql)) {
                header('Location: ' . $base . '/staffs.php?msg=' . urlencode('Staff updated.'));
                exit;
            } else {
                $error = 'Failed to update staff.';
            }
        } else {
            $sql = "INSERT INTO staffs (name, rank, ranking, project, present) VALUES ('$name_esc', '$rank_esc', $ranking, '$project_esc', $present)";
            if ($db->save($sql)) {
                header('Location: ' . $base . '/staffs.php?msg=' . urlencode('Staff added.'));
                exit;
            } else {
                $error = 'Failed to add staff.';
            }
        }
    }
    $row = [
        'id' => $post_id ?: $id,
        'name' => $name,
        'rank' => $rank,
        'ranking' => $ranking,
        'project' => $project,
        'present' => $present,
    ];
}

if (!$row && $is_edit) {
    $row = null;
} elseif (!$row) {
    $row = ['id' => 0, 'name' => '', 'rank' => '', 'ranking' => 0, 'project' => '', 'present' => 1];
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title"><?php echo $is_edit ? 'Edit staff' : 'Add staff'; ?></h1>

<?php if ($error): ?>
<p class="form-message form-message-error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<div class="content-card">
  <div class="card-header">
    <h2><?php echo $is_edit ? 'Edit staff' : 'New staff'; ?></h2>
    <a href="<?php echo $base; ?>/staffs.php" class="btn btn-secondary btn-sm">← Back to Staff</a>
  </div>
  <form method="post" action="">
    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
    <div class="content-card-body">
      <div class="form-row form-row-2">
        <div class="form-group">
          <label for="name">Name <span class="required">*</span></label>
          <input type="text" id="name" name="name" required maxlength="50" value="<?php echo htmlspecialchars($row['name'] ?? ''); ?>" placeholder="Full name">
        </div>
        <div class="form-group">
          <label for="rank">Rank</label>
          <input type="text" id="rank" name="rank" maxlength="225" value="<?php echo htmlspecialchars($row['rank'] ?? ''); ?>" placeholder="e.g. Manager">
        </div>
      </div>
      <div class="form-row form-row-2">
        <div class="form-group">
          <label for="ranking">Ranking (number)</label>
          <input type="number" id="ranking" name="ranking" value="<?php echo (int)($row['ranking'] ?? 0); ?>" min="0" step="1">
        </div>
        <div class="form-group">
          <label for="project">Project (code, max 11)</label>
          <input type="text" id="project" name="project" maxlength="11" value="<?php echo htmlspecialchars($row['project'] ?? ''); ?>" placeholder="Optional">
        </div>
      </div>
      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" name="present" value="1" <?php echo !empty($row['present']) ? 'checked' : ''; ?>>
          In service (present)
        </label>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Update staff' : 'Add staff'; ?></button>
        <a href="<?php echo $base; ?>/staffs.php" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
