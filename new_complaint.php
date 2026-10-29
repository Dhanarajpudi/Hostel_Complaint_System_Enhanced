<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $category = (string)($_POST['category'] ?? 'Other');
    $description = trim((string)($_POST['description'] ?? ''));
    $location = trim((string)($_POST['location'] ?? ''));
    $priority = (string)($_POST['priority'] ?? 'Medium');
    $categories = ['Maintenance','Electrical','Plumbing','Cleanliness','Internet','Security','Other'];
    $priorities = ['Low','Medium','High','Urgent'];
    if (mb_strlen($title) < 4 || mb_strlen($description) < 10 || $location === '' || !in_array($category,$categories,true) || !in_array($priority,$priorities,true)) {
        $error = 'Please complete all required fields. The title must be 4+ characters and description at least 10 characters.';
    } else {
        $uid = (int)current_user()['id'];
        $stmt = db()->prepare('INSERT INTO complaints (user_id,title,category,description,location,priority) VALUES (?,?,?,?,?,?)');
        $stmt->bind_param('isssss', $uid, $title, $category, $description, $location, $priority);
        $stmt->execute();
        flash('Complaint submitted successfully. Your reference is #' . $stmt->insert_id . '.');
        redirect('complaint.php?id=' . $stmt->insert_id);
    }
}
$pageTitle = 'New complaint';
require __DIR__ . '/includes/header.php';
?>
<section class="form-page"><a class="back-link" href="dashboard.php">← Back to dashboard</a><div class="form-page-heading"><div class="card-icon">＋</div><div><h1>Raise a complaint</h1><p class="muted">Tell us what needs attention. We’ll keep you updated.</p></div></div>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-panel">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="form-grid"><label>Complaint title *<input name="title" maxlength="160" placeholder="e.g. Water leakage in bathroom" required></label>
<label>Category *<select name="category"><?php foreach (['Maintenance','Electrical','Plumbing','Cleanliness','Internet','Security','Other'] as $v): ?><option><?= e($v) ?></option><?php endforeach; ?></select></label>
<label>Location / room *<input name="location" maxlength="160" placeholder="e.g. Block B, Room 204" required></label>
<label>Priority<select name="priority"><option>Low</option><option selected>Medium</option><option>High</option><option>Urgent</option></select></label>
<label class="full-span">Describe the issue *<textarea name="description" rows="6" placeholder="Share details that will help the hostel team resolve this..." required minlength="10"></textarea><small class="field-hint">Please avoid including passwords or other sensitive information.</small></label></div>
<div class="form-actions"><a class="button button-quiet" href="dashboard.php">Cancel</a><button class="button button-primary" type="submit">Submit complaint →</button></div>
</form></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
