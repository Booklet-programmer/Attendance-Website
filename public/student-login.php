<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $number=trim((string)($_POST['student_number']??'')); $password=(string)($_POST['password']??'');
 $q=db()->prepare("SELECT student_id,password_hash,student_status FROM students WHERE student_number=:number"); $q->execute(['number'=>$number]); $s=$q->fetch();
 if($s && $s['student_status']==='Active' && password_verify($password,$s['password_hash'])){
   $_SESSION['student_id']=(int)$s['student_id']; $token=$_SESSION['pending_qr']??''; unset($_SESSION['pending_qr']);
   if($token!==''){header('Location: '.base_url('scan.php?t='.rawurlencode($token)));exit;}
 }
 $error='Sign-in failed. Check your student number and password.';
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>"><title>Student Sign in | UPSA</title></head><body class="login-page"><div class="login-card"><div class="crest large">UPSA</div><h1>Student attendance sign in</h1><p>Sign in to confirm your attendance.</p><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Student number</label><input name="student_number" required><label>Password</label><input type="password" name="password" required><button class="btn primary full">Continue</button></form></div></body></html>
