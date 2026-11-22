<?php
require dirname(__DIR__) . '/app/bootstrap.php'; require_role('Head Administrator');
$rows=db()->query("SELECT full_name,staff_id,email,role,account_status,created_at FROM administrators WHERE role='Lecturer' ORDER BY full_name")->fetchAll();
require dirname(__DIR__).'/app/layout.php'; page_header('Lecturers'); ?>
<section class="page-title"><div><h1>Lecturer Management</h1><p>Lecturer accounts and approval status.</p></div></section><section class="panel"><div class="table-wrap"><table><thead><tr><th>Name</th><th>Staff ID</th><th>Email</th><th>Status</th><th>Created</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e($r['full_name']) ?></td><td><?= e($r['staff_id']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['account_status']) ?></td><td><?= e($r['created_at']) ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="empty">No lecturer accounts found.</td></tr><?php endif; ?></tbody></table></div></section><?php page_footer(); ?>
