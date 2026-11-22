<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$token=(string)($_GET['t']??'');
if ($token==='') { http_response_code(400); exit('Invalid QR code.'); }
$hash=hash('sha256',$token);
$q=db()->prepare("SELECT s.session_id,s.session_title,c.course_code,c.course_name FROM qr_tokens qt JOIN attendance_sessions s ON s.session_id=qt.session_id JOIN teaching_assignments ta ON ta.assignment_id=s.assignment_id JOIN courses c ON c.course_id=ta.course_id WHERE qt.token_hash=:hash AND qt.expires_at>NOW() AND s.session_status='Active' ORDER BY qt.created_at DESC LIMIT 1");
$q->execute(['hash'=>$hash]); $session=$q->fetch();
if(!$session) { http_response_code(410); exit('This QR code has expired. Please scan the current code displayed by your lecturer.'); }
if(empty($_SESSION['student_id'])) { $_SESSION['pending_qr']=$token; header('Location: '.base_url('student-login.php')); exit; }
$stmt=db()->prepare("INSERT INTO attendance_records(session_id,student_id) VALUES(:session,:student) ON CONFLICT(session_id,student_id) DO NOTHING");
$stmt->execute(['session'=>$session['session_id'],'student'=>$_SESSION['student_id']]);
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>"><title>Attendance recorded</title></head><body class="login-page"><div class="login-card"><div class="success-mark">✓</div><h1>Attendance received</h1><p><?= e($session['course_code'].' — '.$session['course_name']) ?></p><p>Your attendance has been submitted. You may close this page.</p></div></body></html>
