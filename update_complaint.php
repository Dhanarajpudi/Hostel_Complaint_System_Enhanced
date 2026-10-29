<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin.php');
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = (string)($_POST['status'] ?? '');
$note = trim((string)($_POST['admin_note'] ?? ''));
if (!$id || !in_array($status,['Submitted','In Progress','Resolved','Rejected'],true)) {
    flash('Invalid complaint update.', 'error'); redirect('admin.php');
}
$stmt = db()->prepare('UPDATE complaints SET status=?, admin_note=? WHERE id=?');
$stmt->bind_param('ssi', $status, $note, $id); $stmt->execute();
flash('Complaint #' . $id . ' updated.');
redirect('complaint.php?id=' . $id);
