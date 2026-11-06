<?php require 'config.php'; require_role('Admin','Faculty','Student'); $u=me(); $stu=$u['role']==='Student';
$id=(int)($_GET['id']??0); $doc=null; $mode=$_GET['mode']??'upload';
if($id){ $doc=doc_load($id); if(!$doc||!perms($doc,$u)['edit']) forbid('You cannot edit this document.'); $mode='edit'; }
elseif($mode==='create'){ require_role('Admin','Faculty'); } else $mode='upload';
$cats=q("SELECT * FROM categories ORDER BY category_name")->fetchAll(); $deps=q("SELECT * FROM departments ORDER BY department_name")->fetchAll(); $err=[];
$f=$doc?:['title'=>'','description'=>'','document_type'=>'Other','category_id'=>'','department_id'=>$u['department_id'],'access_level'=>$stu?'Private':'Department','tags'=>'','date_received'=>date('Y-m-d')];
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check();
  $t=trim($_POST['title']??''); $ds=trim($_POST['description']??''); $ty=$_POST['document_type']??''; $cat=(int)($_POST['category_id']??0); $dep=(int)($_POST['department_id']??0);
  $lv=$_POST['access_level']??'Private'; $tags=trim($_POST['tags']??''); $dr=$_POST['date_received']??''; $content=trim($_POST['content']??'');
  if($stu){ $lv='Private'; if($u['department_id']) $dep=(int)$u['department_id']; }
  if($t===''||mb_strlen($t)>200) $err[]='Title is required (maximum 200 characters).';
  if(!in_array($ty,TYPES,true)) $err[]='Choose a valid document type.';
  if(!q("SELECT 1 FROM categories WHERE id=?",[$cat])->fetch()) $err[]='Choose a category.';
  if(!q("SELECT 1 FROM departments WHERE id=?",[$dep])->fetch()) $err[]='Choose a department.';
  if(!in_array($lv,LEVELS,true)) $err[]='Choose an access level.';
  if($dr!==''&&!DateTime::createFromFormat('Y-m-d',$dr)) $err[]='Date received is not valid.';
  if(mb_strlen($tags)>255) $err[]='Tags are too long.';
  $f=array_merge($f,['title'=>$t,'description'=>$ds,'document_type'=>$ty,'category_id'=>$cat,'department_id'=>$dep,'access_level'=>$lv,'tags'=>$tags,'date_received'=>$dr]);
  if(!$err){ $file=null;
    try{
      if($mode==='create'){ if($content==='') throw new Exception('Write the document content.'); $sn=bin2hex(random_bytes(16)).'.txt'; file_put_contents(UPLOAD_DIR.$sn,$content); $file=['path'=>$sn,'name'=>safe_name($t).'.txt','ext'=>'txt','size'=>strlen($content)]; }
      elseif(!empty($_FILES['file']['name'])) $file=save_upload($_FILES['file']);
      elseif($mode==='upload') throw new Exception('Choose a file to upload.');
      $submit=isset($_POST['submit_now']); db()->beginTransaction();
      if($mode==='edit'){
        q("UPDATE documents SET title=?,description=?,document_type=?,category_id=?,department_id=?,access_level=?,tags=?,date_received=? WHERE id=?",[$t,$ds,$ty,$cat,$dep,$lv,$tags,$dr?:null,$id]);
        $code=$doc['document_id']; $docId=$id;
        if($file){ $nv=next_version($doc['current_version'],($_POST['vtype']??'minor')==='major');
          q("UPDATE documents SET file_name=?,file_path=?,file_type=?,file_size=?,current_version=? WHERE id=?",[$file['name'],$file['path'],$file['ext'],$file['size'],$nv,$id]);
          q("INSERT INTO document_versions(document_id,version_number,file_name,file_path,uploaded_by) VALUES(?,?,?,?,?)",[$id,$nv,$file['name'],$file['path'],$u['id']]);
          log_act('Document edited',$id,"New version $nv of $code uploaded"); }
        else log_act('Document edited',$id,"Details of $code updated");
      } else {
        q("INSERT INTO documents(document_id,title,description,document_type,category_id,department_id,file_name,file_path,file_type,file_size,tags,date_received,uploaded_by,status,access_level,current_version) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,'Draft',?,'1.0')",
          [uniqid('T'),$t,$ds,$ty,$cat,$dep,$file['name'],$file['path'],$file['ext'],$file['size'],$tags,$dr?:null,$u['id'],$lv]);
        $docId=(int)db()->lastInsertId(); $code=sprintf('DOC-%04d',$docId); q("UPDATE documents SET document_id=? WHERE id=?",[$code,$docId]);
        q("INSERT INTO document_versions(document_id,version_number,file_name,file_path,uploaded_by) VALUES(?,?,?,?,?)",[$docId,'1.0',$file['name'],$file['path'],$u['id']]);
        log_act($mode==='create'?'Document created':'Document uploaded',$docId,($mode==='create'?'Created ':'Uploaded ')."$code \"$t\""); }
      $cur=q("SELECT status FROM documents WHERE id=?",[$docId])->fetchColumn();
      if($submit&&in_array($cur,['Draft','Returned for Revision','Rejected'],true)) submit_doc($docId,$code,$t);
      db()->commit(); flash('success',$submit?"$code saved and submitted for review.":"$code saved."); redirect("document_view.php?id=$docId");
    }catch(Exception $x){ if(db()->inTransaction()) db()->rollBack(); if($file&&is_file(UPLOAD_DIR.$file['path'])) unlink(UPLOAD_DIR.$file['path']); $err[]=$x->getMessage(); } } }
$title=['create'=>'Create Document','upload'=>'Upload Document','edit'=>'Edit Document'][$mode]; include 'includes/header.php'; ?>
<h1 class="page-title"><?=e($title)?><?php if($doc) echo ' <small class="text-muted fs-6">'.e($doc['document_id']).' &middot; v'.e($doc['current_version']).'</small>'; ?></h1>
<?php if($err): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($err as $x) echo '<li>'.e($x).'</li>'; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="card border-0 shadow-sm"><div class="card-body row g-3"><?=csrf_field()?>
 <div class="col-md-8"><label class="form-label">Document Title *</label><input name="title" maxlength="200" class="form-control" required value="<?=e($f['title'])?>"></div>
 <div class="col-md-4"><label class="form-label">Document Type *</label><select name="document_type" class="form-select"><?php foreach(TYPES as $x) echo '<option '.($f['document_type']===$x?'selected':'').'>'.e($x).'</option>'; ?></select></div>
 <div class="col-12"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control"><?=e($f['description'])?></textarea></div>
 <div class="col-md-4"><label class="form-label">Category *</label><select name="category_id" class="form-select" required><option value="">Select category</option><?php foreach($cats as $c) echo '<option value="'.$c['id'].'" '.($f['category_id']==$c['id']?'selected':'').'>'.e($c['category_name']).'</option>'; ?></select></div>
 <div class="col-md-4"><label class="form-label">Department *</label><select name="department_id" class="form-select" required <?=($stu&&$u['department_id'])?'disabled':''?>><option value="">Select department</option><?php foreach($deps as $c) echo '<option value="'.$c['id'].'" '.($f['department_id']==$c['id']?'selected':'').'>'.e($c['department_name']).'</option>'; ?></select></div>
 <div class="col-md-4"><label class="form-label">Access Level *</label><select name="access_level" class="form-select" <?=$stu?'disabled':''?>><?php foreach(LEVELS as $x) echo '<option '.($f['access_level']===$x?'selected':'').'>'.e($x).'</option>'; ?></select>
  <div class="form-text"><?=$stu?'Student uploads stay private until an administrator changes the access level.':'Applies once the document is approved. Private = you, admins and people you share with.'?></div></div>
 <div class="col-md-8"><label class="form-label">Tags</label><input name="tags" class="form-control" placeholder="clearance, 2026, registrar" value="<?=e($f['tags'])?>"></div>
 <div class="col-md-4"><label class="form-label">Date Received</label><input type="date" name="date_received" class="form-control" value="<?=e($f['date_received'])?>"></div>
 <?php if($mode==='create'): ?><div class="col-12"><label class="form-label">Document Content *</label><textarea name="content" rows="10" class="form-control" required><?=e($_POST['content']??'')?></textarea></div>
 <?php else: ?><div class="col-md-8"><label class="form-label"><?=$mode==='edit'?'Upload New Version (optional)':'File *'?></label><input type="file" name="file" class="form-control" <?=$mode==='upload'?'required':''?> accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"><div class="form-text">PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG. Maximum 10 MB.</div></div>
  <?php if($mode==='edit'): ?><div class="col-md-4"><label class="form-label">Version Type</label><select name="vtype" class="form-select"><option value="minor">Minor change (<?=e(next_version($doc['current_version'],false))?>)</option><option value="major">Major change (<?=e(next_version($doc['current_version'],true))?>)</option></select></div><?php endif; endif; ?>
</div><div class="card-footer bg-white d-flex gap-2 flex-wrap"><button class="btn btn-outline-primary" name="save" value="1"><i class="bi bi-save"></i> Save as Draft</button>
 <?php if(!$doc||in_array($doc['status'],['Draft','Returned for Revision','Rejected'])): ?><button class="btn btn-primary" name="submit_now" value="1"><i class="bi bi-send"></i> Save &amp; Submit for Review</button><?php endif; ?>
 <a href="dashboard.php" class="btn btn-light">Cancel</a></div></form>
<?php include 'includes/footer.php'; ?>
