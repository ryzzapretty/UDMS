<?php require_once 'config.php'; require_role('Admin','Faculty','Student'); $u=me(); $stu=$u['role']==='Student';
if ($stu && (int)($u['department_id'] ?? 0) === 1) {
    flash('danger', 'Document upload is not permitted for Basic Education.');
    redirect('documents.php');
}
$id=(int)($_GET['id']??0); $doc=null; $mode=$_GET['mode']??'upload';
if($id){ $doc=doc_load($id); if(!$doc||!perms($doc,$u)['edit']) forbid('You cannot edit this document.'); $mode='edit'; }
elseif($mode==='create'){ require_role('Admin','Faculty'); } else $mode='upload';
$cats=q("SELECT * FROM categories ORDER BY category_name")->fetchAll(); $deps=q("SELECT * FROM departments ORDER BY id")->fetchAll(); $err=[];

$defaultCat = !empty($cats) ? (int)$cats[0]['id'] : '';
$defaultCatName = !empty($cats) ? $cats[0]['category_name'] : '';
$catTypeMap = [
    'Academic Records' => 'Student Record',
    'Administrative Policies' => 'Administrative Document',
    'Curriculum & Syllabus' => 'Lesson / Syllabus',
    'Faculty Clearances' => 'Faculty Record',
    'Learning Modules & Lessons' => 'Learning Module',
    'Student Grades & Assessments' => 'Grade Sheet / Assessment'
];
$defaultDocType = $catTypeMap[$defaultCatName] ?? (TYPES[0] ?? 'Other');
$reqType = $_GET['type'] ?? '';
if ($reqType && in_array($reqType, TYPES, true)) {
    $defaultDocType = $reqType;
}

$f=$doc?:[
    'title'=>$defaultCatName,
    'description'=>'',
    'document_type'=>$defaultDocType,
    'category_id'=>$defaultCat,
    'department_id'=>$u['department_id'],
    'access_level'=>$stu?'Private':'Department',
    'tags'=>'',
    'date_received'=>date('Y-m-d')
];
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check();
  $t=trim($_POST['title']??''); $ds=trim($_POST['description']??''); $ty=$_POST['document_type']??''; $cat=(int)($_POST['category_id']??0); $dep=(int)($_POST['department_id']??0);
  $lv=$_POST['access_level']??'Private'; $tags=''; $dr=date('Y-m-d'); $content=trim($_POST['content']??'');
  if($stu){ $lv='Private'; if($u['department_id']) $dep=(int)$u['department_id']; }
  if($t===''||mb_strlen($t)>200) $err[]='Title is required (maximum 200 characters).';
  if(!in_array($ty,TYPES,true)) $err[]='Choose a valid document type.';
  if(!q("SELECT 1 FROM categories WHERE id=?",[$cat])->fetch()) $err[]='Choose a category.';
  if(!q("SELECT 1 FROM departments WHERE id=?",[$dep])->fetch()) $err[]='Choose a grade level.';
  if(!in_array($lv,LEVELS,true)) $err[]='Choose an access level.';
  if($dr!==''&&!DateTime::createFromFormat('Y-m-d',$dr)) $err[]='Date received is not valid.';
  if(mb_strlen($tags)>255) $err[]='Tags are too long.';
  $f=array_merge($f,['title'=>$t,'description'=>$ds,'document_type'=>$ty,'category_id'=>$cat,'department_id'=>$dep,'access_level'=>$lv,'tags'=>$tags,'date_received'=>$dr]);
  if(!$err){ $file=null;
    try{
      if($mode!=='edit' && $stu){
        $stg=student_storage_info($u['id']);
        if(!$stg['can_upload']){
          throw new Exception("Storage limit reached! Your {$stg['tier']} Plan allows a maximum of {$stg['limit']} stored documents ({$stg['count']}/{$stg['limit']} used). Please upgrade to Student Pro or delete older documents to continue.");
        }
      }
      if($mode==='create'){ if($content==='') throw new Exception('Write the document content.'); $sn=bin2hex(random_bytes(16)).'.txt'; file_put_contents(UPLOAD_DIR.$sn,$content); $file=['path'=>$sn,'name'=>safe_name($t).'.txt','ext'=>'txt','size'=>strlen($content)]; }
      elseif(!empty($_FILES['file']['name'])) $file=save_upload($_FILES['file']);
      elseif($mode==='upload') throw new Exception('Choose a file to upload.');
      $submit=isset($_POST['submit_now']); db()->beginTransaction();
      $isLate = is_submission_late($ty) || !empty($_POST['is_late_flag']) || (!empty($_GET['late']));
      $subTiming = $isLate ? 'Delayed / Late Submitted' : 'On Time';
      if ($isLate) { $tags = 'delayed, late submitted'; }
      if($mode==='edit'){
        q("UPDATE documents SET title=?,description=?,document_type=?,category_id=?,department_id=?,access_level=?,tags=?,date_received=?,is_late=?,submission_timing=? WHERE id=?",[$t,$ds,$ty,$cat,$dep,$lv,$tags,$dr?:null,$isLate?1:0,$subTiming,$id]);
        $code=$doc['document_id']; $docId=$id;
        if($file){ $nv=next_version($doc['current_version'],($_POST['vtype']??'minor')==='major');
          q("UPDATE documents SET file_name=?,file_path=?,file_type=?,file_size=?,current_version=? WHERE id=?",[$file['name'],$file['path'],$file['ext'],$file['size'],$nv,$id]);
          q("INSERT INTO document_versions(document_id,version_number,file_name,file_path,uploaded_by) VALUES(?,?,?,?,?)",[$id,$nv,$file['name'],$file['path'],$u['id']]);
          log_act('Document edited',$id,"New version $nv of $code uploaded"); }
        else log_act('Document edited',$id,"Details of $code updated");
      } else {
        q("INSERT INTO documents(document_id,title,description,document_type,category_id,department_id,file_name,file_path,file_type,file_size,tags,date_received,uploaded_by,status,access_level,current_version,is_late,submission_timing) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,'Draft',?,'1.0',?,?)",
          [uniqid('T'),$t,$ds,$ty,$cat,$dep,$file['name'],$file['path'],$file['ext'],$file['size'],$tags,$dr?:null,$u['id'],$lv,$isLate?1:0,$subTiming]);
        $docId=(int)db()->lastInsertId(); $code=sprintf('DOC-%04d',$docId); q("UPDATE documents SET document_id=? WHERE id=?",[$code,$docId]);
        q("INSERT INTO document_versions(document_id,version_number,file_name,file_path,uploaded_by) VALUES(?,?,?,?,?)",[$docId,'1.0',$file['name'],$file['path'],$u['id']]);
        log_act($mode==='create'?'Document created':'Document uploaded',$docId,($mode==='create'?'Created ':'Uploaded ')."$code \"$t\""); }
      $cur=q("SELECT status FROM documents WHERE id=?",[$docId])->fetchColumn();
      if($submit&&in_array($cur,['Draft','Returned for Revision','Rejected'],true)) submit_doc($docId,$code,$t);
      db()->commit();
      if ($submit && $isLate) {
        flash('warning', "$code saved and submitted as Delayed / Late Submitted. Your professor will review and mark this submission accordingly.");
      } else {
        flash('success', $submit ? "$code saved and submitted for review." : "$code saved.");
      }
      redirect("document_view.php?id=$docId");
    }catch(Exception $x){ if(db()->inTransaction()) db()->rollBack(); if($file&&is_file(UPLOAD_DIR.$file['path'])) unlink(UPLOAD_DIR.$file['path']); $err[]=$x->getMessage(); } } }
$title=['create'=>'Create Document','upload'=>'Upload Document','edit'=>'Edit Document'][$mode];
$stg=student_storage_info($u['id']);
$storageBlocked=($mode!=='edit'&&$stu&&!$stg['can_upload']);
include 'includes/header.php'; ?>
<h1 class="page-title"><?=e($title)?><?php if($doc) echo ' <small class="text-muted fs-6">'.e($doc['document_id']).' &middot; v'.e($doc['current_version']).'</small>'; ?></h1>
<?php if($stu): ?>
  <?php if($storageBlocked): ?>
    <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
        <strong>Storage Limit Reached (<?= $stg['count'] ?>/<?= $stg['limit'] ?>)!</strong> Your <strong><?= e($stg['tier']) ?> Plan</strong> storage quota is full.
        You cannot upload new documents until you upgrade your subscription or delete unneeded files.
      </div>
      <a href="subscription.php" class="btn btn-sm btn-primary fw-bold text-nowrap"><i class="bi bi-gem"></i> Upgrade to Student Pro (100 Files)</a>
    </div>
  <?php else: ?>
    <div class="alert alert-light border d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 py-2">
      <div class="small">
        <i class="bi bi-hdd-network text-primary me-1"></i> Storage Quota: <strong><?= $stg['count'] ?> / <?= $stg['limit'] ?></strong> documents stored (<strong><?= $stg['remaining'] ?></strong> remaining on <?= e($stg['tier']) ?> Plan).
      </div>
      <?php if($stg['tier'] !== 'Pro'): ?>
        <a href="subscription.php" class="btn btn-sm btn-outline-primary py-0 small"><i class="bi bi-gem"></i> Upgrade Storage</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
<?php if($err): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($err as $x) echo '<li>'.e($x).'</li>'; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="card border-0 shadow-sm"><div class="card-body row g-3"><?=csrf_field()?>
 <div class="col-md-8"><label class="form-label">Document Title *</label><input id="docTitleInput" name="title" maxlength="200" class="form-control" required value="<?=e($f['title'])?>"></div>
 <div class="col-md-4"><label class="form-label">Document Type *</label><select id="docTypeSelect" name="document_type" class="form-select"><?php foreach(TYPES as $x) echo '<option '.($f['document_type']===$x?'selected':'').'>'.e($x).'</option>'; ?></select></div>
 <!-- Real-Time Submission Deadline Box for Students & Uploaders -->
 <div class="col-12">
   <div id="deadlineNoticeBox" class="p-3 rounded-3 border d-flex align-items-start gap-3" style="background: #fff8f8; border-color: #fecaca;">
     <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #fee2e2; color: #dc2626; flex-shrink: 0;">
       <i class="bi bi-calendar-check-fill fs-5"></i>
     </div>
     <div class="flex-grow-1">
       <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
         <span class="fw-bold text-dark" id="deadlineNoticeTitle" style="font-size: 14px;">Submission Deadline: Learning Module</span>
         <span class="badge bg-success" id="deadlineNoticeStatus">Open Window</span>
       </div>
       <div class="small text-muted mt-1">
         <strong>Submission Cutoff:</strong> <span class="fw-bold text-dark" id="deadlineNoticeDate">Nov 25, 2026 (5:00 PM)</span> &bull; 
         <span class="fw-bold text-danger" id="deadlineNoticeRemaining">16 days remaining</span> &bull; 
         <span class="text-secondary" id="deadlineNoticeTerm">Midterm Academic Period</span>
       </div>
       <div class="small text-muted mt-1" id="deadlineNoticeDesc">
         Midterm modules, laboratory worksheets, and module activity portfolios.
       </div>
     </div>
     <a href="dashboard.php#submission-deadlines" class="btn btn-sm btn-outline-danger text-nowrap align-self-center small" title="View all submission deadlines on your dashboard" style="font-size: 12px;">
       <i class="bi bi-calendar3"></i> All Deadlines
     </a>
   </div>
 </div>
 <div class="col-12"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control"><?=e($f['description'])?></textarea></div>
 <div class="col-md-4"><label class="form-label">Category *</label><select id="docCategorySelect" name="category_id" class="form-select" required><option value="">Select category</option><?php foreach($cats as $c) echo '<option value="'.$c['id'].'" data-catname="'.e($c['category_name']).'" '.($f['category_id']==$c['id']?'selected':'').'>'.e($c['category_name']).'</option>'; ?></select></div>
 <div class="col-md-4"><label class="form-label">Grade Level *</label><select name="department_id" class="form-select" required <?=($stu&&$u['department_id'])?'disabled':''?>><option value="">Select grade level</option><?php foreach($deps as $c) echo '<option value="'.$c['id'].'" '.($f['department_id']==$c['id']?'selected':'').'>'.e($c['department_name']).'</option>'; ?></select></div>
 <div class="col-md-4"><label class="form-label">Access Level *</label><select name="access_level" class="form-select" <?=$stu?'disabled':''?>><?php foreach(LEVELS as $x) echo '<option '.($f['access_level']===$x?'selected':'').'>'.e($x).'</option>'; ?></select>
  <div class="form-text"><?=$stu?'Student uploads stay private until an administrator changes the access level.':'Applies once the document is approved. Private = you, admins and people you share with.'?></div></div>
 <div class="col-md-4"><label class="form-label">Date Received (Auto-generated)</label><input type="date" name="date_received" class="form-control" value="<?=e($f['date_received']?:date('Y-m-d'))?>" readonly style="background-color: #e9ecef; cursor: not-allowed; pointer-events: none;" tabindex="-1"><div class="form-text text-muted">Fixed to submission date (non-editable).</div></div>
 <?php if($mode==='create'): ?><div class="col-12"><label class="form-label">Document Content *</label><textarea name="content" rows="10" class="form-control" required><?=e($_POST['content']??'')?></textarea></div>
 <?php else: ?><div class="col-md-8"><label class="form-label"><?=$mode==='edit'?'Upload New Version (optional)':'File *'?></label><input type="file" name="file" class="form-control" <?=$mode==='upload'?'required':''?> accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"><div class="form-text text-danger fw-semibold"><i class="bi bi-shield-lock-fill"></i> Documents only (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG). <strong>NO VIDEO FILES ALLOWED.</strong> Maximum size: 10 MB.</div></div>
  <?php if($mode==='edit'): ?><div class="col-md-4"><label class="form-label">Version Type</label><select name="vtype" class="form-select"><option value="minor">Minor change (<?=e(next_version($doc['current_version'],false))?>)</option><option value="major">Major change (<?=e(next_version($doc['current_version'],true))?>)</option></select></div><?php endif; endif; ?>
</div><div class="card-footer bg-white d-flex gap-2 flex-wrap"><button class="btn btn-outline-primary" name="save" value="1" <?=$storageBlocked?'disabled':''?>><i class="bi bi-save"></i> Save as Draft</button>
 <?php if(!$doc||in_array($doc['status'],['Draft','Returned for Revision','Rejected'])): ?><button class="btn btn-primary" name="submit_now" value="1" <?=$storageBlocked?'disabled':''?>><i class="bi bi-send"></i> Save &amp; Submit for Review</button><?php endif; ?>
 <a href="dashboard.php" class="btn btn-light">Cancel</a></div></form>
<script>
(function(){
  var catSelect = document.getElementById('docCategorySelect');
  var typeSelect = document.getElementById('docTypeSelect');
  var titleInput = document.getElementById('docTitleInput');
  var deadlines = <?= json_encode(DOCUMENT_DEADLINES) ?>;

  var typeMap = {
    'Academic Records': 'Student Record',
    'Administrative Policies': 'Administrative Document',
    'Curriculum & Syllabus': 'Lesson / Syllabus',
    'Faculty Clearances': 'Faculty Record',
    'Learning Modules & Lessons': 'Learning Module',
    'Student Grades & Assessments': 'Grade Sheet / Assessment'
  };

  function updateDeadlineNotice() {
    if (!typeSelect) return;
    var curType = typeSelect.value;
    var info = deadlines[curType] || deadlines['Other'] || {
      title: curType,
      term: 'General Academic Submission',
      deadline_formatted: 'Consult Faculty Adviser',
      description: 'Check syllabus or consult with your faculty for submission details.',
      status: 'Open',
      status_class: 'success',
      deadline_date: null
    };

    var titleEl = document.getElementById('deadlineNoticeTitle');
    var statusEl = document.getElementById('deadlineNoticeStatus');
    var dateEl = document.getElementById('deadlineNoticeDate');
    var remEl = document.getElementById('deadlineNoticeRemaining');
    var termEl = document.getElementById('deadlineNoticeTerm');
    var descEl = document.getElementById('deadlineNoticeDesc');

    if (titleEl) titleEl.textContent = 'Submission Deadline: ' + info.title;
    if (statusEl) {
      statusEl.textContent = info.status;
      statusEl.className = 'badge bg-' + (info.status_class || 'primary');
    }
    if (dateEl) dateEl.textContent = info.deadline_formatted;
    if (termEl) termEl.textContent = info.term;
    if (descEl) descEl.textContent = info.description;

    if (remEl && info.deadline_date) {
      var d = new Date(info.deadline_date.replace(/-/g, '/'));
      var diffDays = Math.ceil((d - new Date()) / (1000 * 60 * 60 * 24));
      if (diffDays < 0) {
        remEl.textContent = 'Cutoff passed';
        remEl.className = 'fw-bold text-danger';
      } else if (diffDays === 0) {
        remEl.textContent = 'Due Today';
        remEl.className = 'fw-bold text-danger';
      } else {
        remEl.textContent = diffDays + ' days remaining';
        remEl.className = diffDays <= 7 ? 'fw-bold text-danger' : 'fw-bold text-danger';
      }
    }
  }

  function syncCategory(){
    if (!catSelect) return;
    var opt = catSelect.options[catSelect.selectedIndex];
    if (opt && opt.value) {
      var name = opt.getAttribute('data-catname') || opt.textContent.trim();
      if (titleInput) {
        titleInput.value = name;
      }
      if (typeSelect && typeMap[name]) {
        typeSelect.value = typeMap[name];
      }
    }
    updateDeadlineNotice();
  }

  if (catSelect) {
    catSelect.addEventListener('change', syncCategory);
  }
  if (typeSelect) {
    typeSelect.addEventListener('change', updateDeadlineNotice);
  }
  // Initialize deadline box on page load
  updateDeadlineNotice();
})();
</script>
<?php include 'includes/footer.php'; ?>
