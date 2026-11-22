<?php
require dirname(__DIR__) . '/app/bootstrap.php';
if (!empty($_SESSION['user'])) { header('Location: '.base_url('dashboard.php')); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = db()->prepare("SELECT administrator_id, full_name, username, password_hash, role, account_status FROM administrators WHERE username=:username LIMIT 1");
    $stmt->execute(['username'=>$username]);
    $user = $stmt->fetch();
    if ($user && $user['account_status'] === 'Approved' && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>(int)$user['administrator_id'], 'name'=>$user['full_name'], 'username'=>$user['username'], 'role'=>$user['role']];
        log_activity((int)$user['administrator_id'], 'LOGIN', 'Administrator signed in.');
        header('Location: '.base_url('dashboard.php')); exit;
    }
    $error = 'Login was unsuccessful. Check your details and account approval status.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in | UPSA Smart QR Attendance</title><link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>"></head><body class="login-page">
<div class="login-card"><div class="login-brand"><div class="crest large">UPSA</div><h1>Smart QR Attendance</h1><p>University of Professional Studies, Accra</p></div>
<h2>Administrator / Lecturer Sign in</h2><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Username</label><input name="username" required autocomplete="username"><label>Password</label><input type="password" name="password" required autocomplete="current-password"><button class="btn primary full">Sign in securely</button></form>
<p class="muted small">Access is restricted to approved university administrators and lecturers.</p></div></body></html>
