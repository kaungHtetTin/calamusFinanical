<?php
/**
 * Add cost - standalone page from Costs.
 */
$page_title = 'Add Cost';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = '';
$error = '';
$course_categories = financial_project_rows($db);
$project_keys = array_column($course_categories, 'keyword');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cost_category_id = (int)($_POST['cost_category_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $amount = (int)($_POST['amount'] ?? 0);
    $major = trim($_POST['major'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');
    $transfer_id = (int)($_POST['transfer_id'] ?? 0);

    if ($cost_category_id <= 0 || $title === '' || $amount <= 0) {
        $error = 'Category, title and amount are required.';
    } elseif ($major !== '' && !in_array($major, $project_keys, true)) {
        $error = 'Select a valid project.';
    } else {
        $title_esc = $conn->real_escape_string($title);
        $major_esc = $conn->real_escape_string($major);
        $date_esc = $conn->real_escape_string($date);
        $sql = "INSERT INTO costs (cost_category_id, title, amount, major, date, transfer_id) VALUES ($cost_category_id, '$title_esc', $amount, '$major_esc', '$date_esc', $transfer_id)";
        if ($db->save($sql)) {
            header('Location: ' . $base . '/costs.php?msg=' . urlencode('Cost added.'));
            exit;
        }
        $error = 'Failed to add cost.';
    }
}

$categories = $db->read("SELECT id, title FROM cost_categories ORDER BY title");
if ($categories === false) $categories = [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="form-page">
  <div class="admin-page-heading">
    <div>
      <p class="eyebrow">Cost Management</p>
      <h1>Add Cost</h1>
    </div>
    <a href="<?php echo $base; ?>/costs.php" class="btn secondary">Back to Costs</a>
  </div>

  <?php if ($error): ?>
  <div class="form-message form-message-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <section class="panel glass form-panel">
    <div class="panel-heading">
      <div>
        <p class="eyebrow">New Record</p>
        <h2>Cost details</h2>
      </div>
    </div>
    <form method="post" action="" class="crud-grid">
      <label class="form-field">
        <span>Category</span>
        <select name="cost_category_id" required>
          <option value="">Select category</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?php echo (int)$cat['id']; ?>" <?php echo (isset($_POST['cost_category_id']) && (int)$_POST['cost_category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['title']); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="form-field">
        <span>Amount</span>
        <input type="number" name="amount" required min="1" value="<?php echo isset($_POST['amount']) ? (int)$_POST['amount'] : ''; ?>">
      </label>
      <label class="form-field span-2">
        <span>Title</span>
        <input type="text" name="title" required maxlength="225" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
      </label>
      <label class="form-field">
        <span>Project</span>
        <select name="major">
          <option value="">No project</option>
          <?php foreach ($course_categories as $cc): ?>
          <option value="<?php echo htmlspecialchars($cc['keyword']); ?>" <?php echo (isset($_POST['major']) && $_POST['major'] === $cc['keyword']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cc['project_name'] ?? $cc['keyword']); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="form-field">
        <span>Date</span>
        <input type="date" name="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : date('Y-m-d'); ?>">
      </label>
      <label class="form-field span-2">
        <span>Transfer ID (optional)</span>
        <input type="number" name="transfer_id" value="<?php echo isset($_POST['transfer_id']) ? (int)$_POST['transfer_id'] : 0; ?>" min="0">
      </label>
      <div class="form-actions span-2">
        <button type="submit" class="btn primary">Add cost</button>
        <a href="<?php echo $base; ?>/costs.php" class="btn secondary">Cancel</a>
      </div>
    </form>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
