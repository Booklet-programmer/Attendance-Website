<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_role('Head Administrator','Lecturer');
$id=(int)($_GET['session']??0);
$stmt=db()->prepare("SELECT s.session_id,s.session_title,s.session_status,ta.administrator_id FROM attendance_sessions s JOIN teaching_assignments ta ON ta.assignment_id=s.assignment_id WHERE s.session_id=:id");
$stmt->execute(['id'=>$id]); $session=$stmt->fetch();
if(!$session || ($_SESSION['user']['role']==='Lecturer' && (int)$session['administrator_id']!==(int)$_SESSION['user']['id'])) { http_response_code(404); exit('Session not found.'); }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Scan QR | UPSA</title><link rel="stylesheet" href="<?= e(base_url('assets/css/app.css')) ?>"></head><body class="qr-page">
<!-- Dedicated display: QR only in the visible display area. -->
<div class="qr-only" aria-label="Attendance QR code"><div id="qr-code"></div></div>
<script>
const sessionId=<?= $id ?>;
const tokenEndpoint=<?= json_encode(base_url('api/qr-token.php')) ?>;
const scanBase=<?= json_encode(base_url('scan.php?t=')) ?>;
async function refreshQr(){
  const response=await fetch(tokenEndpoint+'?session='+sessionId,{credentials:'same-origin',cache:'no-store'});
  if(!response.ok) return;
  const data=await response.json();
  const target=document.getElementById('qr-code');
  target.innerHTML='';
  const payload=scanBase+encodeURIComponent(data.token);
  if(window.QRCode) new QRCode(target,{text:payload,width:440,height:440,colorDark:'#071b3b',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.H});
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
refreshQr(); setInterval(refreshQr, <?= max(60,(int)($_ENV['QR_TTL_SECONDS']??600))*1000 ?>);
</script></body></html>
