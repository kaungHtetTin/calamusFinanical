<?php
$page_title = 'Cost Categories';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = '';
$error = '';

// POST: add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $error = 'Title is required.';
        } else {
            $title_esc = $conn->real_escape_string($title);
            if ($db->save("INSERT INTO cost_categories (title) VALUES ('$title_esc')")) {
                $message = 'Category added.';
            } else {
                $error = 'Failed to add.';
            }
        }
    }
    // POST: delete
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($id > 0) {
            $db->save("DELETE FROM cost_categories WHERE id = $id");
            $message = 'Category deleted.';
        }
    }
}

$categories = $db->read("SELECT * FROM cost_categories ORDER BY title");
if ($categories === false) $categories = [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Cost Categories</h1>

<?php if ($message): ?>
<p style="color: var(--success); margin-bottom: 16px;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($error): ?>
<p style="color: var(--error); margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<div class="content-card">
  <div class="card-header">
    <h2>Add category</h2>
  </div>
  <form method="post" action="">
    <input type="hidden" name="action" value="add">
    <div class="form-group">
      <label>Title</label>
      <input type="text" name="title" required maxlength="225">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Add</button>
    </div>
  </form>
</div>

<div class="content-card">
  <div class="card-header">
    <h2>Categories</h2>
  </div>
  <?php if (empty($categories)): ?>
  <div class="empty-state">No categories yet.</div>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $c): ?>
      <tr>
        <td><?php echo (int)$c['id']; ?></td>
        <td><?php echo htmlspecialchars($c['title']); ?></td>
        <td>
          <form method="post" action="" style="display:inline;" onsubmit="return confirm('Delete this category?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
