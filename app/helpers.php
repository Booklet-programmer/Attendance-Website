<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function base_url(string $path = ''): string {
    $base = rtrim($_ENV['APP_URL'] ?? '', '/');
    if ($base === '') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $base = $scheme . '://' . $host;
    }
    return $base . '/' . ltrim($path, '/');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Your form session expired. Please go back and try again.');
    }
}
function require_login(): void {
    if (empty($_SESSION['user'])) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}
function require_role(string ...$roles): void {
    require_login();
    if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
}
function log_activity(?int $adminId, string $action, string $description): void {
    $stmt = db()->prepare('INSERT INTO system_activity_logs(administrator_id, action_type, description) VALUES(:id,:action,:description)');
    $stmt->execute(['id'=>$adminId, 'action'=>$action, 'description'=>$description]);
}
function flash(string $message, string $type='success'): void {
    $_SESSION['flash'] = ['message'=>$message, 'type'=>$type];
}
