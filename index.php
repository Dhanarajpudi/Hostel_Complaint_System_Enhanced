<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        $stmt = db()->prepare('SELECT id, full_name, email, password_hash, role, room_number FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            redirect('dashboard.php');
        }
        $error = 'Email or password is incorrect.';
    }
}
$pageTitle = 'Welcome back';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
  <div class="hero-copy">
    <div class="eyebrow"><span class="pulse"></span> A better way to live on campus</div>
    <h1>Your comfort.<br><span>Our priority.</span></h1>
    <p>Report hostel issues, follow every update, and help make your campus a better place to live.</p>
    <div class="hero-points"><div><span class="point-icon">✓</span><span><b>Simple reporting</b><small>Submit an issue in minutes</small></span></div><div><span class="point-icon">↗</span><span><b>Transparent tracking</b><small>Know what happens next</small></span></div><div><span class="point-icon">⌁</span><span><b>One place to manage it</b><small>All your complaints in one view</small></span></div></div>
  </div>
  <div class="auth-card">
    <div class="card-icon">↗</div><h2>Welcome back</h2><p class="muted">Sign in to your HostelCare account.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form-stack">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label>Email address<input type="email" name="email" placeholder="you@example.com" autocomplete="email" required></label>
      <label>Password<input type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required></label>
      <button class="button button-primary button-wide" type="submit">Sign in <span>→</span></button>
    </form>
    <p class="auth-bottom">New to HostelCare? <a href="register.php">Create an account</a></p>
    <div class="secure-note"><span>♢</span> Your account details are protected</div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
