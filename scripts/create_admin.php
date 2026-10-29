<?php
require __DIR__ . '/../includes/bootstrap.php';
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this script from the container CLI only.\n");
}
$email = strtolower(trim((string)($argv[1] ?? '')));
$name = trim((string)($argv[2] ?? 'Hostel Administrator'));
$password = (string)($argv[3] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Usage: php scripts/create_admin.php admin@example.com \"Admin Name\" \"StrongPasswordAtLeast12\"\n");
    exit(1);
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = db()->prepare("INSERT INTO users (full_name,email,password_hash,role) VALUES (?,?,?,'admin') ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), password_hash=VALUES(password_hash), role='admin'");
$stmt->bind_param('sss', $name, $email, $hash);
$stmt->execute();
echo "Admin account created/updated for {$email}\n";
