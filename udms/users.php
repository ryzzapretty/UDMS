<?php require 'config.php'; require_role('Admin'); $u=me(); $title='User Management';
$deps=q("SELECT * FROM departments ORDER BY department_name")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check(); $act=$_POST['act']??'';
  try{
    if($act==='toggle'){ $tid=(int)$_POST['id']; if($tid==$u['id']) throw new Exception('You cannot deactivate your own account.');
      $x=q("SELECT * FROM users WHERE id=?",[$tid])->fetch(); if(!$x) throw new Exception('User not found.'); $ns=$x['status']==='Active'?'Inactive':'Active';
      q("UPDATE users SET status=? WHERE id=?",[$ns,$tid]); log_act('User status changed',null,"{$x['username']} set to $ns"); if($ns==='Active') notify($tid,'Your account is now active. Welcome to UDMS.','dashboard.php');
      flash('success',"{$x['full_name']} is now $ns."); }
    elseif($act==='save'){ $id=(int)($_POST['id']??0); $n=trim($_POST['full_name']??''); $un=trim($_POST['username']??''); $em=trim($_POST['email']??''); $pw=$_POST['password']??'';
      $ro=$_POST['role']??''; $dep=(int)($_POST['department_id']??0)?:null; $st=$_POST['status']??''; $idn=trim($_POST['id_number']??'');
      if(strlen($n)<3) throw new Exception('Enter the full name.'); if(!preg_match('/^[A-Za-z0-9_.]{3,30}$/',$un)) throw new Exception('Username: 3-30 letters, numbers, dot or underscore.');
      if(!filter_var($em,FILTER_VALIDATE_EMAIL)) throw new Exception('Enter a valid email.'); if(!in_array($ro,['Admin','Faculty','Student','Guest'],true)) throw new Exception('Invalid role.');
      if(!in_array($st,['Active','Inactive','Pending'],true)) throw new Exception('Invalid status.'); if($dep&&!q("SELECT 1 FROM departments WHERE id=?",[$dep])->fetch()) throw new Exception('Invalid department.');
      if(($id===0||$pw!=='')&&(strlen($pw)<8||!preg_match('/[A-Za-z]/',$pw)||!preg_match('/\d/',$pw))) throw new Exception('Password needs 8+ characters with letters and numbers.');
      if(q("SELECT 1 FROM users WHERE (username=? OR email=?) AND id<>?",[$un,$em,$id])->fetch()) throw new Exception('Username or email already exists.');
      if($id){ if($id==$u['id']&&($ro!=='Admin'||$st!=='Active')) throw new Exception('You cannot remove your own admin access.');
        q("UPDATE users SET full_name=?,username=?,email=?,role=?,department_id=?,status=?,id_number=? WHERE id=?",[$n,$un,$em,$ro,$dep,$st,$idn,$id]);
        if($pw!=='') q("UPDATE users SET password=? WHERE id=?",[password_hash($pw,PASSWORD_DEFAULT),$id]);
        log_act('User updated',null,"Updated account $un ($ro, $st)"); flash('success','User updated.'); }
      else{ q("INSERT INTO users(full_name,username,email,password,role,department_id,id_number,status) VALUES(?,?,?,?,?,?,?,?)",[$n,$un,$em,password_hash($pw,PASSWORD_DEFAULT),$ro,$dep,$idn,$st]);
        log_act('User created',null,"Created account $un ($ro)"); flash('success','User created.'); } }
  }catch(Exception $x){ flash('error',$x->getMessage()); } redirect('users.php'); }
$w=['1=1']; $p=[]; $qs=trim($_GET['q']??''); $sf=$_GET['status']??''; $rf=$_GET['role']??'';
if($qs!==''){ $w[]='(u.full_name LIKE :a OR u.username LIKE :b OR u.email LIKE :c)'; $p[':a']=$p[':b']=$p[':c']="%$qs%"; }
if(in_array($sf,['Active','Inactive','Pending'],true)){ $w[]='u.status=:s'; $p[':s']=$sf; } if(in_array($rf,['Admin','Faculty','Student','Guest'],true)){ $w[]='u.role=:r'; $p[':r']=$rf; }
$rows=q("SELECT u.*,d.department_name FROM users u LEFT JOIN departments d ON d.id=u.department_id WHERE ".implode(' AND ',$w)." ORDER BY FIELD(u.status,'Pending','Active','Inactive'),u.id",$p)->fetchAll();
include 'includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="page-title mb-0">User Management</h1><button class="btn btn-primary" onclick="openUser(null)"><i class="bi bi-person-plus"></i> Add User</button></div>
<form class="card border-0 shadow-sm mb-3"><div class="card-body row g-2"><div class="col-md-5"><input name="q" class="form-control" placeholder="Name, username or email" value="<?=e($qs)?>"></div>
<div class="col-md-2"><select name="role" class="form-select"><option value="">Role</option><?php foreach(['Admin','Faculty','Student','Guest'] as $x) echo '<option '.($rf===$x?'selected':'').'>'.$x.'</option>'; ?></select></div>
<div class="col-md-2"><select name="status" class="form-select"><option value="">Status</option><?php foreach(['Active','Inactive','Pending'] as $x) echo '<option '.($sf===$x?'selected':'').'>'.$x.'</option>'; ?></select></div>
<div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a href="users.php" class="btn btn-light">Reset</a></div></div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>User ID</th><th>Name</th><th>Username</th><th>Role</th><th>Department</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php foreach($rows as $x): $c=['Active'=>'success','Inactive'=>'secondary','Pending'=>'warning text-dark'][$x['status']]; ?><tr><td>U-<?=str_pad($x['id'],4,'0',STR_PAD_LEFT)?></td><td><?=e($x['full_name'])?><div class="small text-muted"><?=e($x['email'])?> <?=$x['id_number']?'&middot; '.e($x['id_number']):''?></div></td><td><?=e($x['username'])?></td><td><?=e($x['role'])?></td><td><?=e($x['department_name']?:'—')?></td><td><span class="badge bg-<?=$c?>"><?=e($x['status'])?></span></td>
<td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" data-u="<?=e(json_encode(['id'=>$x['id'],'full_name'=>$x['full_name'],'username'=>$x['username'],'email'=>$x['email'],'role'=>$x['role'],'department_id'=>$x['department_id'],'status'=>$x['status'],'id_number'=>$x['id_number']]))?>" onclick="openUser(JSON.parse(this.dataset.u))">Edit</button>
 <a class="btn btn-sm btn-outline-secondary" href="logs.php?user=<?=$x['id']?>">Activity</a>
 <?php if($x['id']!=$u['id']) echo post_btn('toggle',$x['id'],$x['status']==='Active'?'Deactivate':'Activate',$x['status']==='Active'?'btn-outline-danger':'btn-outline-success',($x['status']==='Active'?'Deactivate ':'Activate ').$x['full_name'].'?'); ?></td></tr>
<?php endforeach; if(!$rows) echo '<tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>'; ?></tbody></table></div></div>
<div class="modal fade" id="userM" tabindex="-1"><div class="modal-dialog modal-lg"><form method="post" class="modal-content"><?=csrf_field()?><input type="hidden" name="act" value="save"><input type="hidden" name="id" id="uid">
 <div class="modal-header"><h5 class="modal-title" id="uTitle">User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3">
 <div class="col-md-6"><label class="form-label">Full Name</label><input name="full_name" id="f_full_name" class="form-control" required></div>
 <div class="col-md-6"><label class="form-label">Username</label><input name="username" id="f_username" class="form-control" required></div>
 <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" id="f_email" class="form-control" required></div>
 <div class="col-md-6"><label class="form-label">Password <small class="text-muted" id="pwHint"></small></label><input type="password" name="password" class="form-control" autocomplete="new-password"></div>
 <div class="col-md-4"><label class="form-label">Role</label><select name="role" id="f_role" class="form-select"><?php foreach(['Admin','Faculty','Student','Guest'] as $x) echo "<option>$x</option>"; ?></select></div>
 <div class="col-md-4"><label class="form-label">Department</label><select name="department_id" id="f_department_id" class="form-select"><option value="">None</option><?php foreach($deps as $c) echo '<option value="'.$c['id'].'">'.e($c['department_name']).'</option>'; ?></select></div>
 <div class="col-md-4"><label class="form-label">Status</label><select name="status" id="f_status" class="form-select"><?php foreach(['Active','Inactive','Pending'] as $x) echo "<option>$x</option>"; ?></select></div>
 <div class="col-12"><label class="form-label">Student / Employee ID</label><input name="id_number" id="f_id_number" class="form-control"></div></div>
 <div class="modal-footer"><button class="btn btn-primary">Save User</button></div></form></div></div>
<script>function openUser(x){const m=new bootstrap.Modal('#userM');document.getElementById('uid').value=x?x.id:'';document.getElementById('uTitle').textContent=x?'Edit User':'Add User';
document.getElementById('pwHint').textContent=x?'(leave blank to keep)':'(required)';
['full_name','username','email','role','department_id','status','id_number'].forEach(k=>{document.getElementById('f_'+k).value=x?(x[k]??''):(k==='role'?'Student':k==='status'?'Active':'');});m.show();}</script>
<?php include 'includes/footer.php'; ?>
