<?php require 'config.php'; require_role('Admin'); $title='Activity Logs';
$w=['1=1']; $p=[]; $uid=(int)($_GET['user']??0); $ac=trim($_GET['action']??''); $df=$_GET['from']??''; $dtt=$_GET['to']??'';
if($uid){ $w[]='a.user_id=:u'; $p[':u']=$uid; } if($ac!==''){ $w[]='a.action LIKE :ac'; $p[':ac']="%$ac%"; }
if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$df)){ $w[]='DATE(a.created_at)>=:f'; $p[':f']=$df; } if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$dtt)){ $w[]='DATE(a.created_at)<=:t'; $p[':t']=$dtt; }
$rows=q("SELECT a.*,u.full_name,d.document_id AS code FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id LEFT JOIN documents d ON d.id=a.document_id WHERE ".implode(' AND ',$w)." ORDER BY a.id DESC LIMIT 300",$p)->fetchAll();
$users=q("SELECT id,full_name FROM users ORDER BY full_name")->fetchAll(); include 'includes/header.php'; ?>
<h1 class="page-title">Activity Logs</h1>
<form class="card border-0 shadow-sm mb-3"><div class="card-body row g-2"><div class="col-md-3"><select name="user" class="form-select"><option value="">All users</option><?php foreach($users as $x) echo '<option value="'.$x['id'].'" '.($uid==$x['id']?'selected':'').'>'.e($x['full_name']).'</option>'; ?></select></div>
<div class="col-md-3"><input name="action" class="form-control" placeholder="Action (e.g. approved)" value="<?=e($ac)?>"></div><div class="col-md-2"><input type="date" name="from" class="form-control" value="<?=e($df)?>"></div><div class="col-md-2"><input type="date" name="to" class="form-control" value="<?=e($dtt)?>"></div>
<div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a href="logs.php" class="btn btn-light">Reset</a></div></div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Date</th><th>Time</th><th>User</th><th>Action</th><th>Document</th><th>Description</th></tr></thead><tbody>
<?php foreach($rows as $x): $ts=strtotime($x['created_at']); ?><tr><td class="text-nowrap"><?=date('M d, Y',$ts)?></td><td class="text-nowrap"><?=date('h:i:s A',$ts)?></td><td><?=e($x['full_name']??'System')?></td><td><?=e($x['action'])?></td><td><?=$x['code']?'<a href="document_view.php?id='.$x['document_id'].'">'.e($x['code']).'</a>':'—'?></td><td class="small"><?=e($x['description'])?></td></tr>
<?php endforeach; if(!$rows) echo '<tr><td colspan="6" class="text-center text-muted py-4">No activity found.</td></tr>'; ?></tbody></table></div></div>
<?php include 'includes/footer.php'; ?>
