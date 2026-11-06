<?php require 'config.php'; require_role('Admin','Faculty','Student'); $u=me(); $title='Notifications';
if(isset($_GET['go'])){ $n=q("SELECT * FROM notifications WHERE id=? AND user_id=?",[(int)$_GET['go'],$u['id']])->fetch();
  if($n){ q("UPDATE notifications SET is_read=1 WHERE id=?",[$n['id']]); if($n['link']&&preg_match('/^[a-z_]+\.php(\?[\w=&]*)?$/',$n['link'])) redirect($n['link']); } redirect('notifications.php'); }
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check(); if(($_POST['act']??'')==='readall'){ q("UPDATE notifications SET is_read=1 WHERE user_id=?",[$u['id']]); flash('success','All notifications marked as read.'); } redirect('notifications.php'); }
$rows=q("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 100",[$u['id']])->fetchAll(); include 'includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="page-title mb-0">Notifications</h1><?=post_btn('readall',0,'Mark all as read','btn-outline-primary','Mark every notification as read?')?></div>
<div class="card border-0 shadow-sm"><ul class="list-group list-group-flush">
<?php foreach($rows as $n): ?><li class="list-group-item d-flex gap-3 align-items-start <?=$n['is_read']?'':'bg-light'?>"><i class="bi bi-<?=$n['is_read']?'bell':'bell-fill text-warning'?> mt-1"></i><div class="flex-fill"><a class="text-decoration-none text-dark <?=$n['is_read']?'':'fw-semibold'?>" href="notifications.php?go=<?=$n['id']?>"><?=e($n['message'])?></a><div class="small text-muted"><?=dt($n['created_at'])?></div></div></li>
<?php endforeach; if(!$rows) echo '<li class="list-group-item text-center text-muted py-5">You are all caught up. Document updates will appear here.</li>'; ?></ul></div>
<?php include 'includes/footer.php'; ?>
