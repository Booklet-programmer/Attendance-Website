<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_login();
require dirname(__DIR__) . '/app/layout.php';
$students=(int)db()->query("SELECT COUNT(*) FROM students")->fetchColumn();
$lecturers=(int)db()->query("SELECT COUNT(*) FROM administrators WHERE role='Lecturer'")->fetchColumn();
$sessions=(int)db()->query("SELECT COUNT(*) FROM attendance_sessions")->fetchColumn();
$present=(int)db()->query("SELECT COUNT(*) FROM attendance_records WHERE check_in_at::date=CURRENT_DATE")->fetchColumn();
$recent=db()->query("SELECT s.session_title,s.session_date,s.session_status,c.course_name,a.full_name FROM attendance_sessions s JOIN teaching_assignments ta ON ta.assignment_id=s.assignment_id JOIN courses c ON c.course_id=ta.course_id JOIN administrators a ON a.administrator_id=ta.administrator_id ORDER BY s.created_at DESC LIMIT 8")->fetchAll();
page_header('Dashboard'); ?>
<section class="hero"><div><span class="eyebrow">UNIVERSITY OF PROFESSIONAL STUDIES, ACCRA</span><h1>Welcome to UPSA Smart QR Attendance</h1><p>Secure, efficient attendance monitoring for teaching and learning.</p></div><span class="status-pill">● System ready</span></section>
<section class="stats"><article class="stat"><span>Students</span><strong><?= $students ?></strong><small>Registered students</small></article><article class="stat"><span>Lecturers</span><strong><?= $lecturers ?></strong><small>Lecturer accounts</small></article><article class="stat"><span>Sessions</span><strong><?= $sessions ?></strong><small>Attendance sessions</small></article><article class="stat"><span>Today's check-ins</span><strong><?= $present ?></strong><small>Recorded today</small></article></section>
<section class="panel"><div class="panel-head"><h2>Recent attendance sessions</h2><a class="btn outline" href="<?= e(base_url('sessions.php')) ?>">View sessions</a></div>
<div class="table-wrap"><table><thead><tr><th>Session</th><th>Course</th><th>Lecturer</th><th>Date</th><th>Status</th></tr></thead><tbody>
<?php foreach($recent as $r): ?><tr><td><?= e($r['session_title']) ?></td><td><?= e($r['course_name']) ?></td><td><?= e($r['full_name']) ?></td><td><?= e($r['session_date']) ?></td><td><span class="badge"><?= e($r['session_status']) ?></span></td></tr><?php endforeach; ?>
<?php if(!$recent): ?><tr><td colspan="5" class="empty">No attendance sessions have been created yet.</td></tr><?php endif; ?></tbody></table></div></section>
<?php page_footer(); ?>
