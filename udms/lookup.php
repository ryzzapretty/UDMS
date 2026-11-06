<?php require 'config.php'; require_role('Admin'); $u=me();
$t=$_GET['t']??'categories'; if(!in_array($t,['categories','departments'],true)) $t='categories';
$cfg=['categories'=>['Categories','category_name','category_id'],'departments'=>['Departments','department_name','department_id']]; [$label,$col,$fk]=$cfg[$t]; $title=$label;
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check(); $act=$_POST['act']??''; $id=(int)($_POST['id']??0);
  try{
    if($act==='save'){ $n=trim($_POST['name']??''); $ds=trim($_POST['description']??''); if($n===''||mb_strlen($n)>120) throw new Exception('Enter a name (max 120 characters).');
      if(q("SELECT 1 FROM $t WHERE $col=? AND id<>?",[$n,$id])->fetch()) throw new Exception('That name already exists.');
      if($id) q("UPDATE $t SET $col=?,description=? WHERE id=?",[$n,$ds,$id]); else q("INSERT INTO $t($col,description) VALUES(?,?)",[$n,$ds]);
      log_act(rtrim($label,'s').($id?' updated':' created'),null,$n); flash('success',rtrim($label,'s').' saved.'); }
    elseif($act==='delete'){ $inUse=(int)q("SELECT COUNT(*) FROM documents WHERE $fk=?",[$id])->fetchColumn()+($t==='departments'?(int)q("SELECT COUNT(*) FROM users WHERE department_id=?",[$id])->fetchColumn():0);
      if($inUse) throw new Exception("This item is used by $inUse record(s) and cannot be deleted."); q("DELETE FROM $t WHERE id=?",[$id]); log_act(rtrim($label,'s').' deleted',null,"ID $id"); flash('success','Deleted.'); }
  }catch(Exception $x){ flash('error',$x->getMessage()); } redirect("lookup.php?t=$t"); }
$rows=q("SELECT x.*,(SELECT COUNT(*) FROM documents d WHERE d.$fk=x.id) AS n FROM $t x ORDER BY $col")->fetchAll();
include 'includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="page-title mb-0"><?=e($label)?></h1><button class="btn btn-primary" onclick="openL(null)"><i class="bi bi-plus-lg"></i> Add <?=e(rtrim($label,'s'))?></button></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Name</th><th>Description</th><th>Documents</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php foreach($rows as $x): ?><tr><td><?=e($x[$col])?></td><td class="text-muted"><?=e($x['description'])?></td><td><?=$x['n']?></td><td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" data-r="<?=e(json_encode(['id'=>$x['id'],'name'=>$x[$col],'description'=>$x['description']]))?>" onclick="openL(JSON.parse(this.dataset.r))">Edit</button> <?=post_btn('delete',$x['id'],'Delete','btn-outline-danger','Delete "'.$x[$col].'"?')?></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<div class="modal fade" id="lM" tabindex="-1"><div class="modal-dialog"><form method="post" class="modal-content"><?=csrf_field()?><input type="hidden" name="act" value="save"><input type="hidden" name="id" id="lid">
<div class="modal-header"><h5 class="modal-title"><?=e(rtrim($label,'s'))?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
<label class="form-label">Name</label><input name="name" id="lname" class="form-control mb-3" required><label class="form-label">Description</label><textarea name="description" id="ldesc" class="form-control" rows="3"></textarea></div>
<div class="modal-footer"><button class="btn btn-primary">Save</button></div></form></div></div>
<script>function openL(x){document.getElementById('lid').value=x?x.id:'';document.getElementById('lname').value=x?x.name:'';document.getElementById('ldesc').value=x?(x.description||''):'';new bootstrap.Modal('#lM').show();}</script>
<?php include 'includes/footer.php'; ?>
