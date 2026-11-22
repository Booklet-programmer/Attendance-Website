<?php
require dirname(__DIR__) . '/app/bootstrap.php';
if (!empty($_SESSION['user']['id'])) {
    log_activity((int)$_SESSION['user']['id'], 'LOGOUT', 'Administrator signed out.');
}
$_SESSION = [];
session_destroy();
header('Location: '.base_url('login.php'));
exit;
