<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();
$filters = ['All','Submitted','In Progress','Resolved','Rejected'];
$status = (string)($_GET['status'] ?? 'All');
if (!in_array($status,$filters,true)) $status = 'All';
$search = trim((string)($_GET['q'] ?? ''));
$sql = "SELECT c.*, u.full_name, u.email, u.room_number FROM complaints c JOIN users u ON u.id=c.user_id WHERE 1=1";
$params = []; $types = '';
if ($status !== 'All') { $sql .= ' AND c.status=?'; $types .= 's'; $params[] = $status; }
if ($search !== '') { $sql .= ' AND (c.title LIKE ? OR c.location LIKE ? OR u.full_name LIKE ? OR CAST(c.id AS CHAR) LIKE ?)'; $like = '%' . $search . '%'; $types .= 'ssss'; array_push($params,$like,$like,$like,$like); }
$sql .= ' ORDER BY FIELD(c.priority,"Urgent","High","Medium","Low"), c.created_at DESC';
$stmt = db()->prepare($sql);
if ($types !== '') $stmt->bind_param($types, ...$params);
$stmt->execute(); $complaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$counts = db()->query("SELECT COUNT(*) total, SUM(status='Submitted') submitted, SUM(status='In Progress') progress, SUM(status='Resolved') resolved, SUM(status='Rejected') rejected FROM complaints")->fetch_assoc();
$pageTitle = 'Admin console';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-row"><div><div class="eyebrow">HOSTEL OPERATIONS</div><h1>Admin console</h1><p class="muted">Review reports, prioritize issues, and keep students informed.</p></div><div class="admin-chip">● Administrator access</div></section>
<section class="stats-grid stats-five"><article class="stat-card"><div class="stat-top"><span>Total</span><span class="stat-icon purple">▤</span></div><div class="stat-number"><?= (int)$counts['total'] ?></div></article><article class="stat-card"><div class="stat-top"><span>Submitted</span><span class="stat-icon blue">↗</span></div><div class="stat-number"><?= (int)$counts['submitted'] ?></div></article><article class="stat-card"><div class="stat-top"><span>In progress</span><span class="stat-icon amber">◷</span></div><div class="stat-number"><?= (int)$counts['progress'] ?></div></article><article class="stat-card"><div class="stat-top"><span>Resolved</span><span class="stat-icon green">✓</span></div><div class="stat-number"><?= (int)$counts['resolved'] ?></div></article><article class="stat-card"><div class="stat-top"><span>Rejected</span><span class="stat-icon red">×</span></div><div class="stat-number"><?= (int)$counts['rejected'] ?></div></article></section>
<section class="section-heading"><div><h2>Complaint queue</h2><p class="muted">Urgent and high-priority reports appear first.</p></div></section>
<section class="table-card"><form class="filter-bar" method="get"><div class="search-box"><span>⌕</span><input name="q" value="<?= e($search) ?>" placeholder="Search complaint, student, room or ID"></div><select name="status"><?php foreach ($filters as $f): ?><option <?= $status===$f?'selected':'' ?>><?= e($f) ?></option><?php endforeach; ?></select><button class="button button-primary button-small">Filter</button><a class="button button-quiet button-small" href="admin.php">Reset</a></form>
<?php if (!$complaints): ?><div class="empty-state"><h3>No matching complaints</h3><p>Try another search or status filter.</p></div><?php else: ?><div class="table-scroll"><table><thead><tr><th>Complaint</th><th>Student</th><th>Category</th><th>Priority</th><th>Status</th><th>Created</th><th>Action</th></tr></thead><tbody><?php foreach ($complaints as $c): ?><tr><td><b>#<?= (int)$c['id'] ?> · <?= e($c['title']) ?></b><small><?= e($c['location']) ?></small></td><td><?= e($c['full_name']) ?><small>Room <?= e($c['room_number'] ?: '—') ?></small></td><td><?= e($c['category']) ?></td><td><span class="priority priority-<?= strtolower(e($c['priority'])) ?>"><?= e($c['priority']) ?></span></td><td><span class="status status-<?= strtolower(str_replace(' ','-',e($c['status']))) ?>"><?= e($c['status']) ?></span></td><td><?= e(date('d M Y',strtotime($c['created_at']))) ?></td><td><a class="row-link" href="complaint.php?id=<?= (int)$c['id'] ?>">Review →</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
