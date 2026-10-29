<?php
$pageTitle = $pageTitle ?? 'HostelCare';
$me = current_user();
$flashMessage = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> · HostelCare</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= $me ? 'dashboard.php' : 'index.php' ?>"><span class="brand-mark">H</span><span>Hostel<span class="brand-light">Care</span><small>HOSTEL SUPPORT PORTAL</small></span></a>
  <?php if ($me): ?>
  <nav class="nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="new_complaint.php">New complaint</a>
    <?php if ($me['role'] === 'admin'): ?><a href="admin.php">Admin console</a><?php endif; ?>
  </nav>
  <div class="user-menu"><span class="avatar"><?= e(strtoupper(substr($me['full_name'], 0, 1))) ?></span><span class="user-name"><?= e($me['full_name']) ?></span><a class="button button-quiet button-small" href="logout.php">Log out</a></div>
  <?php endif; ?>
</header>
<main class="page-wrap">
<?php if ($flashMessage): ?>
  <div class="alert alert-<?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div>
<?php endif; ?>
