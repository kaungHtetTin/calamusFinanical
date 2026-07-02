<?php
$page_title = 'Cost Categories';
require_once __DIR__ . '/config.php';

$message = '';
$error = '';

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

<div class="management-page">
  <div class="admin-page-heading">
    <div>
      <p class="eyebrow">COST SETUP</p>
      <h1>Cost Categories</h1>
    </div>
  </div>

  <?php if ($message): ?>
  <p class="form-message form-message-success" role="status"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>
  <?php if ($error): ?>
  <p class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <div class="admin-grid management-grid">
    <section class="panel glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">CREATE</p>
          <h2>Add category</h2>
        </div>
      </div>
      <form method="post" action="" class="panel-form">
        <input type="hidden" name="action" value="add">
        <label class="form-field">
          <span>Title</span>
          <input type="text" name="title" required maxlength="225">
        </label>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?php echo console_icon('plus', 15); ?> Add category</button>
        </div>
      </form>
    </section>

    <section class="panel glass">
      <div class="panel-heading">
        <div>
          <p class="eyebrow">LIST</p>
          <h2>Categories</h2>
        </div>
        <span class="status status-neutral"><?php echo count($categories); ?> rows</span>
      </div>
      <?php if (empty($categories)): ?>
      <div class="empty-state">No categories yet.</div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="data-table management-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th class="col-actions">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
              <td><?php echo (int)$c['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($c['title']); ?></strong></td>
              <td class="col-actions">
                <form method="post" action="" class="form-inline inline-actions" onsubmit="return confirm('Delete this category?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                  <button type="submit" class="icon-btn small danger" aria-label="Delete category" title="Delete">
                    <?php echo console_icon('trash', 14); ?>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </section>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
