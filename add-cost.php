<?php
/**
 * Add cost – standalone page from Costs.
 */
$page_title = 'Add Cost';
require_once __DIR__ . '/config.php';

$base = FINANCIAL_BASE;
$message = '';
$error = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cost_category_id = (int)($_POST['cost_category_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $amount = (int)($_POST['amount'] ?? 0);
    $major = trim($_POST['major'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');
    $transfer_id = (int)($_POST['transfer_id'] ?? 0);

    if ($cost_category_id <= 0 || $title === '' || $amount <= 0) {
        $error = 'Category, title and amount are required.';
    } else {
        $title_esc = $conn->real_escape_string($title);
        $major_esc = $conn->real_escape_string($major);
        $date_esc = $conn->real_escape_string($date);
        $sql = "INSERT INTO costs (cost_category_id, title, amount, major, date, transfer_id) VALUES ($cost_category_id, '$title_esc', $amount, '$major_esc', '$date_esc', $transfer_id)";
        if ($db->save($sql)) {
            header('Location: ' . $base . '/costs.php?msg=' . urlencode('Cost added.'));
            exit;
        } else {
            $error = 'Failed to add cost.';
        }
    }
}

$categories = $db->read("SELECT id, title FROM cost_categories ORDER BY title");
if ($categories === false) $categories = [];
$course_categories = $db->read("SELECT keyword, project_name FROM course_categories ORDER BY project_name");
if ($course_categories === false) $course_categories = [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<h1 class="page-title">Add Cost</h1>
<p class="period-hint">Record a new cost with category, title, amount and optional project.</p>

<?php if ($error): ?>
<p style="color: var(--error); margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<div class="content-card">
  <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
    <h2>New cost</h2>
    <a href="<?php echo $base; ?>/costs.php" class="btn btn-secondary btn-sm">← Back to Costs</a>
  </div>
  <form method="post" action="">
    <div class="form-group">
      <label>Category</label>
      <select name="cost_category_id" required>
        <option value="">Select</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?php echo (int)$cat['id']; ?>" <?php echo (isset($_POST['cost_category_id']) && (int)$_POST['cost_category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['title']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Title</label>
      <input type="text" name="title" required maxlength="225" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
    </div>
    <div class="form-group">
      <label>Amount</label>
      <input type="number" name="amount" required min="1" value="<?php echo isset($_POST['amount']) ? (int)$_POST['amount'] : ''; ?>">
    </div>
    <div class="form-group">
      <label>Project (major)</label>
      <select name="major">
        <option value="">—</option>
        <?php foreach ($course_categories as $cc): ?>
        <option value="<?php echo htmlspecialchars($cc['keyword']); ?>" <?php echo (isset($_POST['major']) && $_POST['major'] === $cc['keyword']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cc['project_name'] ?? $cc['keyword']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Date</label>
      <input type="date" name="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : date('Y-m-d'); ?>">
    </div>
    <div class="form-group">
      <label>Transfer ID (optional)</label>
      <input type="number" name="transfer_id" value="<?php echo isset($_POST['transfer_id']) ? (int)$_POST['transfer_id'] : 0; ?>" min="0">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Add cost</button>
      <a href="<?php echo $base; ?>/costs.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
