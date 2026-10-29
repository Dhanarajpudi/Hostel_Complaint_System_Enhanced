<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$me = current_user();
$conn = db();
if ($me['role'] === 'admin') {
    $stats = $conn->query("SELECT COUNT(*) total, SUM(status='Submitted') submitted, SUM(status='In Progress') progress, SUM(status='Resolved') resolved FROM complaints")->fetch_assoc();
    $stmt = $conn->prepare("SELECT c.*, u.full_name, u.room_number FROM complaints c JOIN users u ON u.id=c.user_id ORDER BY c.created_at DESC LIMIT 8");
    $stmt->execute();
} else {
    $stmtStats = $conn->prepare("SELECT COUNT(*) total, SUM(status='Submitted') submitted, SUM(status='In Progress') progress, SUM(status='Resolved') resolved FROM complaints WHERE user_id=?");
    $stmtStats->bind_param('i', $me['id']); $stmtStats->execute(); $stats = $stmtStats->get_result()->fetch_assoc();
    $stmt = $conn->prepare("SELECT c.*, u.full_name, u.room_number FROM complaints c JOIN users u ON u.id=c.user_id WHERE c.user_id=? ORDER BY c.created_at DESC LIMIT 8");
    $stmt->bind_param('i', $me['id']); $stmt->execute();
}
$complaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-row"><div><div class="eyebrow">YOUR HOSTEL, YOUR VOICE</div><h1>Hello, <?= e(explode(' ', $me['full_name'])[0]) ?> <span class="wave">✦</span></h1><p class="muted"><?= $me['role'] === 'admin' ? 'Here is the latest overview of hostel issues.' : 'Here is your complaint activity at a glance.' ?></p></div><a class="button button-primary" href="new_complaint.php">＋ New complaint</a></section>
<section class="stats-grid">
  <article class="stat-card"><div class="stat-top"><span>Total complaints</span><span class="stat-icon purple">▤</span></div><div class="stat-number"><?= (int)($stats['total'] ?? 0) ?></div><small>All recorded requests</small></article>
  <article class="stat-card"><div class="stat-top"><span>Submitted</span><span class="stat-icon blue">↗</span></div><div class="stat-number"><?= (int)($stats['submitted'] ?? 0) ?></div><small>Awaiting review</small></article>
  <article class="stat-card"><div class="stat-top"><span>In progress</span><span class="stat-icon amber">◷</span></div><div class="stat-number"><?= (int)($stats['progress'] ?? 0) ?></div><small>Being worked on</small></article>
  <article class="stat-card"><div class="stat-top"><span>Resolved</span><span class="stat-icon green">✓</span></div><div class="stat-number"><?= (int)($stats['resolved'] ?? 0) ?></div><small>Issues completed</small></article>
</section>
<section class="section-heading"><div><h2><?= $me['role'] === 'admin' ? 'Recent complaints' : 'Your recent complaints' ?></h2><p class="muted">Stay up to date with every report.</p></div><?php if ($me['role'] === 'admin'): ?><a class="text-link" href="admin.php">Manage all →</a><?php endif; ?></section>
<section class="table-card">
<?php if (!$complaints): ?>
  <div class="empty-state"><div class="empty-icon">▤</div><h3>No complaints yet</h3><p>Your complaint history will appear here after you submit your first report.</p><a class="button button-primary" href="new_complaint.php">Submit a complaint</a></div>
<?php else: ?>
  <div class="table-scroll"><table><thead><tr><th>Complaint</th><?php if ($me['role']==='admin'): ?><th>Student</th><?php endif; ?><th>Category</th><th>Priority</th><th>Status</th><th>Updated</th><th></th></tr></thead><tbody>
  <?php foreach ($complaints as $c): ?><tr><td><b>#<?= (int)$c['id'] ?> · <?= e($c['title']) ?></b><small><?= e($c['location']) ?></small></td><?php if ($me['role']==='admin'): ?><td><?= e($c['full_name']) ?><small>Room <?= e($c['room_number'] ?: '—') ?></small></td><?php endif; ?><td><?= e($c['category']) ?></td><td><span class="priority priority-<?= strtolower(e($c['priority'])) ?>"><?= e($c['priority']) ?></span></td><td><span class="status status-<?= strtolower(str_replace(' ','-',e($c['status']))) ?>"><?= e($c['status']) ?></span></td><td><?= e(date('d M Y', strtotime($c['updated_at']))) ?></td><td><a class="row-link" href="complaint.php?id=<?= (int)$c['id'] ?>">View →</a></td></tr><?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
