<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid complaint ID.'); }
$stmt = db()->prepare('SELECT c.*, u.full_name, u.email, u.room_number FROM complaints c JOIN users u ON u.id=c.user_id WHERE c.id=?');
$stmt->bind_param('i', $id); $stmt->execute(); $c = $stmt->get_result()->fetch_assoc();
if (!$c) { http_response_code(404); exit('Complaint not found.'); }
$me = current_user();
if ($me['role'] !== 'admin' && (int)$c['user_id'] !== (int)$me['id']) { http_response_code(403); exit('Access denied.'); }
$pageTitle = 'Complaint #' . $id;
require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="<?= $me['role']==='admin' ? 'admin.php' : 'dashboard.php' ?>">← Back to complaints</a>
<section class="detail-layout"><article class="detail-card"><div class="detail-title-row"><div><div class="eyebrow">COMPLAINT #<?= (int)$c['id'] ?></div><h1><?= e($c['title']) ?></h1></div><span class="status status-<?= strtolower(str_replace(' ','-',e($c['status']))) ?>"><?= e($c['status']) ?></span></div>
<div class="detail-meta"><span>▤ <?= e($c['category']) ?></span><span>⌖ <?= e($c['location']) ?></span><span>◷ <?= e(date('d M Y, h:i A',strtotime($c['created_at']))) ?></span></div>
<h3>Description</h3><p class="description-text"><?= nl2br(e($c['description'])) ?></p>
<div class="detail-grid"><div><small>Priority</small><div><span class="priority priority-<?= strtolower(e($c['priority'])) ?>"><?= e($c['priority']) ?></span></div></div><div><small>Last updated</small><b><?= e(date('d M Y, h:i A',strtotime($c['updated_at']))) ?></b></div></div>
<div class="timeline"><div class="timeline-dot"></div><div><b>Complaint submitted</b><p><?= e(date('d M Y, h:i A',strtotime($c['created_at']))) ?></p></div></div>
<?php if ($c['status'] !== 'Submitted'): ?><div class="timeline"><div class="timeline-dot timeline-active"></div><div><b>Status: <?= e($c['status']) ?></b><p>Last updated <?= e(date('d M Y, h:i A',strtotime($c['updated_at']))) ?></p></div></div><?php endif; ?>
<?php if ($c['admin_note']): ?><div class="admin-note"><b>Update from hostel administration</b><p><?= nl2br(e($c['admin_note'])) ?></p></div><?php endif; ?>
</article>
<?php if ($me['role']==='admin'): ?><aside class="detail-card admin-update"><h2>Update complaint</h2><p class="muted">Change the status and leave a note for the student.</p><form method="post" action="update_complaint.php" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><label>Status<select name="status"><?php foreach (['Submitted','In Progress','Resolved','Rejected'] as $v): ?><option <?= $c['status']===$v?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select></label><label>Admin note<textarea name="admin_note" rows="5" placeholder="Add an update or resolution note..."><?= e($c['admin_note']) ?></textarea></label><button class="button button-primary button-wide">Save update</button></form><hr><h3>Student details</h3><p><?= e($c['full_name']) ?><br><span class="muted"><?= e($c['email']) ?></span><br>Room <?= e($c['room_number'] ?: '—') ?></p></aside><?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
