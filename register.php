<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $room = trim((string)($_POST['room_number'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter your name, a valid email, and a password of at least 8 characters.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare("INSERT INTO users (full_name,email,password_hash,room_number,role) VALUES (?,?,?,?, 'student')");
            $stmt->bind_param('ssss', $name, $email, $hash, $room);
            $stmt->execute();
            flash('Account created. You can now sign in.');
            redirect('index.php');
        } catch (mysqli_sql_exception $ex) {
            $error = $ex->getCode() === 1062 ? 'An account with that email already exists.' : 'Unable to create your account. Please try again.';
        }
    }
}
$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<section class="narrow-card">
  <a class="back-link" href="index.php">← Back to sign in</a>
  <div class="card-icon">＋</div><h1>Create your account</h1><p class="muted">Register as a student to report and track hostel issues.</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form-stack">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Full name<input name="full_name" placeholder="Your full name" maxlength="120" required></label>
    <label>Email address<input type="email" name="email" placeholder="you@example.com" required></label>
    <label>Room number <span class="optional">Optional</span><input name="room_number" placeholder="e.g. B-204" maxlength="30"></label>
    <label>Password<input type="password" name="password" minlength="8" placeholder="At least 8 characters" required></label>
    <button class="button button-primary button-wide" type="submit">Create account →</button>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
