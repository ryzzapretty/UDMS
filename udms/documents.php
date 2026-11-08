<?php require_once 'config.php'; require_role('Admin','Faculty','Student'); $u=me(); $r=$u['role'];
$v=defined('SEARCH_PAGE')?'search':($_GET['v']??'all'); if(!in_array($v,['all','mine','pending','submissions','shared','archive','search','student_requests'],true)) $v='all';
[$vis,$w_p]=visible_sql($u); $w=[$vis]; $p=$w_p; $adm=$r==='Admin';
if($v==='student_requests'){
  if(!in_array($r,['Faculty','Admin'],true)) forbid('Only staff and administrators can review student requests.');
  $w=["d.status='Pending Review'","u.role='Student'"]; $p=[];
  if($r==='Faculty'){
    if(empty($u['department_id'])) { $w[]='1=0'; }
    else { $w[]='d.department_id=:staff_department'; $p[':staff_department']=$u['department_id']; }
  }
}

switch($v){
 case 'student_requests': $t='Student Document Requests'; break;
 case 'mine': $t='My Documents'; $w[]="d.uploaded_by=:me"; $p[':me']=$u['id']; $w[]="d.status<>'Archived'"; break;
 case 'pending': $t=$adm?'Pending Reviews':'Pending Documents'; if($adm) $w[]="d.status='Pending Review'"; else { $w[]="d.uploaded_by=:me"; $p[':me']=$u['id']; $w[]="d.status IN ('Pending Review','Returned for Revision')"; } break;
 case 'submissions': $t='My Submissions'; $w[]="d.uploaded_by=:me"; $p[':me']=$u['id']; $w[]="d.status NOT IN ('Draft','Archived')"; break;
 case 'shared': $t='Shared Documents'; $w[]="EXISTS(SELECT 1 FROM document_shares sh WHERE sh.document_id=d.id AND sh.shared_with=:me)"; $p[':me']=$u['id']; $w[]="d.status<>'Archived'"; break;
 case 'archive': $t='Archive'; $w[]="d.status='Archived'"; if(!$adm){ $w[]="d.uploaded_by=:me"; $p[':me']=$u['id']; } break;
 case 'search': $t='Search Documents'; $w[]="d.status<>'Archived'"; break;
 default: $t='All Documents'; $w[]="d.status<>'Archived'"; }
$title=$t;
$f=['q'=>trim($_GET['q']??''),'category'=>(int)($_GET['category']??0),'type'=>$_GET['type']??'','dept'=>(int)($_GET['dept']??0),'status'=>$_GET['status']??'','from'=>$_GET['from']??'','to'=>$_GET['to']??'','by'=>trim($_GET['by']??'')];
if($f['q']!==''){ $w[]="(d.title LIKE :k1 OR d.document_id LIKE :k2 OR d.description LIKE :k3 OR d.tags LIKE :k4 OR d.file_name LIKE :k5)"; foreach(range(1,5) as $i) $p[":k$i"]='%'.$f['q'].'%'; }
if($f['category']){ $w[]="d.category_id=:cat"; $p[':cat']=$f['category']; }
if(in_array($f['type'],TYPES,true)){ $w[]="d.document_type=:ty"; $p[':ty']=$f['type']; }
if($f['dept']){ $w[]="d.department_id=:dp"; $p[':dp']=$f['dept']; }
if(in_array($f['status'],STATUSES,true) && $v!=='archive'){ $w[]="d.status=:st"; $p[':st']=$f['status']; }
if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$f['from'])){ $w[]="DATE(d.created_at)>=:df"; $p[':df']=$f['from']; }
if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$f['to'])){ $w[]="DATE(d.created_at)<=:dtt"; $p[':dtt']=$f['to']; }
if($f['by']!==''){ $w[]="u.full_name LIKE :by"; $p[':by']='%'.$f['by'].'%'; }
$rows=q("SELECT d.*,c.category_name,dp.department_name,u.full_name AS uploader FROM documents d LEFT JOIN categories c ON c.id=d.category_id LEFT JOIN departments dp ON dp.id=d.department_id JOIN users u ON u.id=d.uploaded_by WHERE ".implode(' AND ',$w)." ORDER BY d.updated_at DESC LIMIT 200",$p)->fetchAll();
$cats=q("SELECT * FROM categories ORDER BY category_name")->fetchAll(); $deps=q("SELECT * FROM departments ORDER BY department_name")->fetchAll();
include 'includes/header.php'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <h1 class="page-title mb-0"><?=e($t)?></h1>
  <div class="d-flex gap-2">
    <?php if($r==='Faculty'): ?><a href="document_form.php?mode=create" class="btn btn-outline-primary"><i class="bi bi-file-earmark-plus"></i> Create Document</a><?php endif; ?>
    <?php if($r!=='Admin'): ?><a href="document_form.php?mode=upload" class="btn btn-primary"><i class="bi bi-cloud-upload"></i> Upload Document</a><?php endif; ?>
  </div>
</div>
<?php if ($v === 'all' || $v === 'search'): ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-3 px-3">
    <!-- Department Quick Filter Selection -->
    <div class="mb-2 d-flex flex-wrap align-items-center gap-2">
      <span class="small fw-bold text-muted text-uppercase" style="font-size:11px;min-width:95px;"><i class="bi bi-buildings"></i> Department:</span>
      <a href="documents.php?v=<?=e($v)?><?= $f['category'] ? '&category='.$f['category'] : '' ?>" class="btn btn-sm <?= empty($f['dept']) ? 'btn-primary' : 'btn-outline-secondary' ?> py-1 px-2" style="font-size:12px;border-radius:15px;">All Departments</a>
      <?php foreach($deps as $d): ?>
        <a href="documents.php?v=<?=e($v)?>&dept=<?=$d['id']?><?= $f['category'] ? '&category='.$f['category'] : '' ?>" class="btn btn-sm <?= (int)$f['dept']===(int)$d['id'] ? 'btn-primary' : 'btn-outline-secondary' ?> py-1 px-2" style="font-size:12px;border-radius:15px;"><?=e($d['department_name'])?></a>
      <?php endforeach; ?>
      <?php if ($adm): ?>
        <a href="lookup.php?t=departments" class="btn btn-sm btn-link text-decoration-none text-muted py-0 px-1 ms-auto small" title="Configure Departments in settings"><i class="bi bi-gear"></i> Manage</a>
      <?php endif; ?>
    </div>
    <!-- Category Quick Filter Selection -->
    <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
      <span class="small fw-bold text-muted text-uppercase" style="font-size:11px;min-width:95px;"><i class="bi bi-tags"></i> Category:</span>
      <a href="documents.php?v=<?=e($v)?><?= $f['dept'] ? '&dept='.$f['dept'] : '' ?>" class="btn btn-sm <?= empty($f['category']) ? 'btn-primary' : 'btn-outline-secondary' ?> py-1 px-2" style="font-size:12px;border-radius:15px;">All Categories</a>
      <?php foreach($cats as $c): ?>
        <a href="documents.php?v=<?=e($v)?>&category=<?=$c['id']?><?= $f['dept'] ? '&dept='.$f['dept'] : '' ?>" class="btn btn-sm <?= (int)$f['category']===(int)$c['id'] ? 'btn-primary' : 'btn-outline-secondary' ?> py-1 px-2" style="font-size:12px;border-radius:15px;"><?=e($c['category_name'])?></a>
      <?php endforeach; ?>
      <?php if ($adm): ?>
        <a href="lookup.php?t=categories" class="btn btn-sm btn-link text-decoration-none text-muted py-0 px-1 ms-auto small" title="Configure Categories in settings"><i class="bi bi-gear"></i> Manage</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
<?php if($r==='Student'): $stg=student_storage_info($u['id']); ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <div>
        <span class="badge bg-<?= $stg['tier'] === 'Pro' ? 'primary' : 'secondary' ?>"><?= e($stg['tier']) ?> Plan</span>
        <strong class="ms-1 small">Storage Quota:</strong>
        <span class="small text-muted"><?= $stg['count'] ?> / <?= $stg['limit'] ?> Files (<?= $stg['remaining'] ?> remaining)</span>
      </div>
      <div class="progress" style="width: 160px; height: 8px;">
        <div class="progress-bar <?= $stg['percent'] >= 90 ? 'bg-danger' : ($stg['percent'] >= 75 ? 'bg-warning' : 'bg-primary') ?>" style="width: <?= $stg['percent'] ?>%"></div>
      </div>
    </div>
    <div>
      <?php if($stg['tier'] !== 'Pro'): ?>
        <a href="subscription.php" class="btn btn-sm btn-outline-primary py-0 small"><i class="bi bi-gem"></i> Upgrade to Pro (100 Files)</a>
      <?php else: ?>
        <a href="subscription.php" class="badge bg-light text-primary border text-decoration-none py-1 px-2"><i class="bi bi-star-fill text-warning"></i> Student Pro Active</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
<form class="card border-0 shadow-sm mb-3" method="get"><div class="card-body row g-2">
 <input type="hidden" name="v" value="<?=e($v==='search'?'':$v)?>">
 <div class="col-md-4"><input name="q" class="form-control" placeholder="Title, document ID, keyword, tag" value="<?=e($f['q'])?>"></div>
 <div class="col-md-2"><select name="category" class="form-select"><option value="">Category</option><?php foreach($cats as $c) echo '<option value="'.$c['id'].'" '.($f['category']==$c['id']?'selected':'').'>'.e($c['category_name']).'</option>'; ?></select></div>
 <div class="col-md-3"><select name="type" class="form-select"><option value="">Document type</option><?php foreach(TYPES as $x) echo '<option '.($f['type']===$x?'selected':'').'>'.e($x).'</option>'; ?></select></div>
 <div class="col-md-3"><select name="dept" class="form-select"><option value="">Department</option><?php foreach($deps as $c) echo '<option value="'.$c['id'].'" '.($f['dept']==$c['id']?'selected':'').'>'.e($c['department_name']).'</option>'; ?></select></div>
 <div class="col-md-2"><select name="status" class="form-select"><option value="">Status</option><?php foreach(STATUSES as $x) echo '<option '.($f['status']===$x?'selected':'').'>'.e($x).'</option>'; ?></select></div>
 <div class="col-md-2"><input type="date" name="from" class="form-control" title="From date" value="<?=e($f['from'])?>"></div>
 <div class="col-md-2"><input type="date" name="to" class="form-control" title="To date" value="<?=e($f['to'])?>"></div>
 <div class="col-md-3"><input name="by" class="form-control" placeholder="Uploaded by" value="<?=e($f['by'])?>"></div>
 <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Search</button><a class="btn btn-light" href="<?=$v==='search'?'search.php':'documents.php?v='.e($v)?>">Reset</a></div></div></form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Document ID</th><th>Title</th><th>Type</th><th>Department</th><th>Uploaded By</th><th>Status</th><th>Version</th><th>Updated</th><th class="text-end">Action</th></tr></thead><tbody>
<?php foreach($rows as $d): ?><tr><td class="text-nowrap"><?=e($d['document_id'])?></td><td><a href="document_view.php?id=<?=$d['id']?>" class="fw-semibold text-decoration-none"><?=e($d['title'])?></a><div class="small text-muted"><?=e($d['category_name']?:'Uncategorized')?></div></td>
 <td><?=e($d['document_type'])?></td><td><?=e($d['department_name']?:'—')?></td><td><?=e($d['uploader'])?></td><td><?=badge($d['status'])?></td><td>v<?=e($d['current_version'])?></td><td class="small text-nowrap"><?=dt($d['updated_at'])?></td>
 <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="document_view.php?id=<?=$d['id']?>"><?= $v==='student_requests' ? 'Review Request' : 'View' ?></a>
  <a class="btn btn-sm btn-outline-secondary" href="download.php?id=<?=$d['id']?>" title="Download"><i class="bi bi-download"></i></a>
  <?php if($adm&&$d['status']==='Archived'){ echo post_btn('restore',$d['id'],'Restore','btn-outline-success','Restore this document to the active list?'); } ?></td></tr>
<?php endforeach; if(!$rows): ?><tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-folder-x fs-2 d-block"></i>No documents match. Try clearing the filters<?=$v==='mine'?' or upload a document':''?>.</td></tr><?php endif; ?></tbody></table></div>
<div class="card-footer bg-white small text-muted"><?=count($rows)?> document(s). Results show only documents you are authorized to access.</div></div>
<?php include 'includes/footer.php'; ?>
