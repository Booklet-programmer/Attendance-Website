<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_role('Head Administrator','Lecturer');
$user=$_SESSION['user'];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $assignmentId=(int)($_POST['assignment_id']??0);
    $title=trim((string)($_POST['session_title']??''));
    if ($title==='') { flash('Enter a session title.','error'); header('Location: '.base_url('sessions.php')); exit; }
    if ($user['role']==='Lecturer') {
        $check=db()->prepare('SELECT assignment_id FROM teaching_assignments WHERE assignment_id=:id AND administrator_id=:admin');
        $check->execute(['id'=>$assignmentId,'admin'=>$user['id']]);
    } else {
        $check=db()->prepare('SELECT assignment_id FROM teaching_assignments WHERE assignment_id=:id');
        $check->execute(['id'=>$assignmentId]);
    }
    if (!$check->fetch()) { flash('Choose one of your assigned courses.','error'); header('Location: '.base_url('sessions.php')); exit; }
    $stmt=db()->prepare("INSERT INTO attendance_sessions(assignment_id,session_title) VALUES(:assignment,:title) RETURNING session_id");
    $stmt->execute(['assignment'=>$assignmentId,'title'=>$title]);
    $id=(int)$stmt->fetchColumn();
    log_activity((int)$user['id'],'SESSION_CREATED',"Created attendance session #{$id}.");
    header('Location: '.base_url('qr-display.php?session='.$id)); exit;
}
if ($user['role']==='Lecturer') {
    $q=db()->prepare("SELECT ta.assignment_id,c.course_code,c.course_name FROM teaching_assignments ta JOIN courses c ON c.course_id=ta.course_id WHERE ta.administrator_id=:id ORDER BY c.course_code");
    $q->execute(['id'=>$user['id']]); $assignments=$q->fetchAll();
} else {
    $assignments=db()->query("SELECT ta.assignment_id,c.course_code,c.course_name,a.full_name FROM teaching_assignments ta JOIN courses c ON c.course_id=ta.course_id JOIN administrators a ON a.administrator_id=ta.administrator_id ORDER BY c.course_code")->fetchAll();
}
$list=db()->query("SELECT s.session_id,s.session_title,s.session_date,s.session_status,c.course_code,c.course_name,a.full_name FROM attendance_sessions s JOIN teaching_assignments ta ON ta.assignment_id=s.assignment_id JOIN courses c ON c.course_id=ta.course_id JOIN administrators a ON a.administrator_id=ta.administrator_id ORDER BY s.created_at DESC LIMIT 50")->fetchAll();
require dirname(__DIR__).'/app/layout.php'; page_header('Attendance Sessions'); ?>
<section class="page-title"><div><h1>Attendance Sessions</h1><p>Create and monitor QR attendance sessions.</p></div></section>
<section class="panel"><h2>Create a session</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Assigned course<select name="assignment_id" required><option value="">Select course</option><?php foreach($assignments as $a): ?><option value="<?= (int)$a['assignment_id'] ?>"><?= e($a['course_code'].' — '.$a['course_name']) ?><?= isset($a['full_name'])?' · '.e($a['full_name']):'' ?></option><?php endforeach; ?></select></label><label>Session title<input name="session_title" placeholder="e.g. Week 4 Lecture" required></label><button class="btn primary">Create and show QR</button></form></section>
<section class="panel"><h2>Recent sessions</h2><div class="table-wrap"><table><thead><tr><th>Title</th><th>Course</th><th>Lecturer</th><th>Date</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($list as $r): ?><tr><td><?= e($r['session_title']) ?></td><td><?= e($r['course_code'].' '.$r['course_name']) ?></td><td><?= e($r['full_name']) ?></td><td><?= e($r['session_date']) ?></td><td><?= e($r['session_status']) ?></td><td><a class="btn small-btn" href="<?= e(base_url('qr-display.php?session='.(int)$r['session_id'])) ?>">Show QR</a></td></tr><?php endforeach; ?><?php if(!$list): ?><tr><td colspan="6" class="empty">No sessions found.</td></tr><?php endif; ?></tbody></table></div></section>
<?php page_footer(); ?>
