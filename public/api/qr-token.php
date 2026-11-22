<?php
require dirname(__DIR__,2) . '/app/bootstrap.php';
require_role('Head Administrator','Lecturer');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
$id=(int)($_GET['session']??0);
$q=db()->prepare("SELECT s.session_id, s.session_status, ta.administrator_id FROM attendance_sessions s JOIN teaching_assignments ta ON ta.assignment_id=s.assignment_id WHERE s.session_id=:id");
$q->execute(['id'=>$id]); $s=$q->fetch();
if(!$s || $s['session_status']!=='Active' || ($_SESSION['user']['role']==='Lecturer' && (int)$s['administrator_id']!==(int)$_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['error'=>'Session unavailable']); exit; }
$raw=bin2hex(random_bytes(32)); $hash=hash('sha256',$raw);
$ttl=max(60,(int)($_ENV['QR_TTL_SECONDS']??600));
db()->prepare('INSERT INTO qr_tokens(session_id,token_hash,expires_at) VALUES(:id,:hash,NOW() + (:ttl * INTERVAL \'1 second\'))')->execute(['id'=>$id,'hash'=>$hash,'ttl'=>$ttl]);
echo json_encode(['token'=>$raw,'expires_in'=>$ttl]);
