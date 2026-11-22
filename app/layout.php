<?php
function page_header(string $title): void { ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> | UPSA Smart QR Attendance</title><link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>"></head><body>
<div class="app-shell"><aside class="sidebar"><div class="brand"><div class="crest">UPSA</div><strong>Smart QR<br>Attendance</strong><small>Innovating for Excellence</small></div>
<nav><a href="<?= e(base_url('dashboard.php')) ?>">▦ Dashboard</a>
<?php if (($_SESSION['user']['role'] ?? '') === 'Head Administrator'): ?>
<a href="<?= e(base_url('students.php')) ?>">♙ Students</a><a href="<?= e(base_url('lecturers.php')) ?>">♟ Lecturers</a>
<?php endif; ?><a href="<?= e(base_url('sessions.php')) ?>">▣ Attendance Sessions</a><a href="<?= e(base_url('logout.php')) ?>">↪ Sign out</a></nav>
<div class="sidebar-foot">University of Professional Studies, Accra<br><em>Academic Excellence Through Innovation</em></div></aside>
<main class="main"><header class="topbar"><span>UPSA · Smart QR Attendance System</span><span><?= e($_SESSION['user']['name'] ?? '') ?> · <?= e($_SESSION['user']['role'] ?? '') ?></span></header>
<?php if (!empty($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
<div class="alert <?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endif; ?>
<?php }
function page_footer(): void { ?></main></div><script src="<?= e(base_url('assets/js/app.js')) ?>"></script></body></html><?php }
