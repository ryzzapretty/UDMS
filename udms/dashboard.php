<?php require 'config.php'; require_role('Admin','Faculty','Student'); $u=me(); $r=$u['role']; $me=$u['id']; $title="$r Dashboard";
function card($l,$v,$i,$c,$h='#'){ return "<div class='col-6 col-xl-3'><a href='".e($h)."' class='text-decoration-none'><div class='card stat border-0 shadow-sm'><div class='card-body d-flex align-items-center'><div class='icon bg-$c bg-opacity-10 text-$c'><i class='bi bi-$i'></i></div><div class='ms-3'><div class='fs-3 fw-bold text-dark lh-1'>".(int)$v."</div><div class='text-muted small mt-1'>".e($l)."</div></div></div></div></a></div>"; }
function cnt($s,$p=[]){ return (int)q($s,$p)->fetchColumn(); }
$sharedN=cnt("SELECT COUNT(*) FROM document_shares s JOIN documents d ON d.id=s.document_id WHERE s.shared_with=? AND d.status<>'Archived'",[$me]);
$own=fn($st)=>cnt("SELECT COUNT(*) FROM documents WHERE uploaded_by=? AND status".$st,[$me]);
if($r==='Admin'){
  $recent=q("SELECT d.*,u.full_name FROM documents d JOIN users u ON u.id=d.uploaded_by ORDER BY d.created_at DESC LIMIT 6")->fetchAll();
  $acts=q("SELECT a.*,u.full_name FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 8")->fetchAll();
  $pend=q("SELECT d.*,u.full_name FROM documents d JOIN users u ON u.id=d.uploaded_by WHERE d.status='Pending Review' ORDER BY d.updated_at LIMIT 6")->fetchAll();
  $chart=q("SELECT status,COUNT(*) c FROM documents GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
} else {
  $recent=q("SELECT d.*,u.full_name FROM documents d JOIN users u ON u.id=d.uploaded_by WHERE d.uploaded_by=? ORDER BY d.updated_at DESC LIMIT 6",[$me])->fetchAll();
  $acts=q("SELECT a.*,u.full_name FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id WHERE a.user_id=? ORDER BY a.id DESC LIMIT 6",[$me])->fetchAll();
  $notes=q("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 5",[$me])->fetchAll();
}
include 'includes/header.php'; ?>
<h1 class="page-title">Welcome, <?=e(explode(' ',$u['full_name'])[0])?></h1>
<div class="row g-3 mb-4">
<?php if($r==='Admin'):
 echo card('Total Users',cnt("SELECT COUNT(*) FROM users"),'people','primary','users.php'),card('Total Documents',cnt("SELECT COUNT(*) FROM documents"),'files','info','documents.php?v=all'),
  card('Pending Reviews',cnt("SELECT COUNT(*) FROM documents WHERE status='Pending Review'"),'hourglass-split','warning','documents.php?v=pending'),card('Approved Documents',cnt("SELECT COUNT(*) FROM documents WHERE status='Approved'"),'check-circle','success','documents.php?v=all&status=Approved'),
  card('Rejected Documents',cnt("SELECT COUNT(*) FROM documents WHERE status='Rejected'"),'x-circle','danger','documents.php?v=all&status=Rejected'),card('Archived Documents',cnt("SELECT COUNT(*) FROM documents WHERE status='Archived'"),'archive','secondary','documents.php?v=archive'),
  card('Pending Accounts',cnt("SELECT COUNT(*) FROM users WHERE status='Pending'"),'person-exclamation','warning','users.php?status=Pending');
elseif($r==='Faculty'):
 echo card('My Documents',$own("<>'Archived'"),'folder2-open','primary','documents.php?v=mine'),card('Pending Documents',$own(" IN ('Pending Review','Returned for Revision')"),'hourglass-split','warning','documents.php?v=pending'),
  card('Approved Documents',$own("='Approved'"),'check-circle','success','documents.php?v=mine&status=Approved'),card('Rejected Documents',$own("='Rejected'"),'x-circle','danger','documents.php?v=mine&status=Rejected'),card('Shared With Me',$sharedN,'share','info','documents.php?v=shared');
else:
 echo card('My Documents',$own("<>'Archived'"),'folder2-open','primary','documents.php?v=mine'),card('Submitted Documents',$own(" NOT IN ('Draft','Archived')"),'send-check','info','documents.php?v=submissions'),
  card('Pending Documents',$own(" IN ('Pending Review','Returned for Revision')"),'hourglass-split','warning','documents.php?v=submissions&status=Pending Review'),card('Approved Documents',$own("='Approved'"),'check-circle','success','documents.php?v=submissions&status=Approved'),
  card('Rejected Documents',$own("='Rejected'"),'x-circle','danger','documents.php?v=submissions&status=Rejected'),card('Shared With Me',$sharedN,'share','secondary','documents.php?v=shared');
endif; ?></div>
<div class="row g-3">
 <div class="col-lg-<?=$r==='Admin'?8:7?>"><div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white d-flex justify-content-between"><strong>Recent Documents</strong><a href="documents.php?v=<?=$r==='Admin'?'all':'mine'?>" class="small">View all</a></div>
  <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>ID</th><th>Title</th><?php if($r==='Admin') echo '<th>By</th>'; ?><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach($recent as $d): ?><tr><td><?=e($d['document_id'])?></td><td><?=e($d['title'])?></td><?php if($r==='Admin') echo '<td>'.e($d['full_name']).'</td>'; ?><td><?=badge($d['status'])?></td><td><a class="btn btn-sm btn-outline-primary" href="document_view.php?id=<?=$d['id']?>">View</a></td></tr>
  <?php endforeach; if(!$recent) echo '<tr><td colspan="5" class="text-center text-muted py-4">No documents yet. <a href="document_form.php?mode=upload">Upload your first document</a>.</td></tr>'; ?></tbody></table></div></div>
 <?php if($r==='Admin'): ?><div class="card border-0 shadow-sm"><div class="card-header bg-white"><strong>Pending Reviews</strong></div><div class="table-responsive"><table class="table align-middle mb-0"><tbody>
  <?php foreach($pend as $d): ?><tr><td><?=e($d['document_id'])?></td><td><?=e($d['title'])?><div class="small text-muted"><?=e($d['full_name'])?></div></td><td class="text-end"><a class="btn btn-sm btn-primary" href="document_view.php?id=<?=$d['id']?>">Review</a></td></tr>
  <?php endforeach; if(!$pend) echo '<tr><td class="text-center text-muted py-3">Nothing is waiting for review.</td></tr>'; ?></tbody></table></div></div><?php endif; ?></div>
 <div class="col-lg-<?=$r==='Admin'?4:5?>">
  <?php if($r==='Admin'): ?><div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white"><strong>Documents by Status</strong></div><div class="card-body"><canvas id="ch" height="220"></canvas></div></div>
  <?php else: ?><div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white d-flex justify-content-between"><strong>Notifications</strong><a href="notifications.php" class="small">All</a></div><ul class="list-group list-group-flush">
   <?php foreach($notes as $n): ?><li class="list-group-item small <?=$n['is_read']?'':'fw-semibold'?>"><a class="text-decoration-none text-dark" href="notifications.php?go=<?=$n['id']?>"><?=e($n['message'])?></a><div class="text-muted fw-normal"><?=dt($n['created_at'])?></div></li>
   <?php endforeach; if(!$notes) echo '<li class="list-group-item text-muted small">No notifications yet.</li>'; ?></ul></div><?php endif; ?>
  <div class="card border-0 shadow-sm"><div class="card-header bg-white"><strong>Recent Activities</strong></div><ul class="list-group list-group-flush">
   <?php foreach($acts as $a): ?><li class="list-group-item small"><strong><?=e($a['action'])?></strong><?php if($r==='Admin') echo ' <span class="text-muted">by '.e($a['full_name']??'system').'</span>'; ?><div class="text-muted"><?=e($a['description'])?> &middot; <?=dt($a['created_at'])?></div></li>
   <?php endforeach; if(!$acts) echo '<li class="list-group-item text-muted small">No activity yet.</li>'; ?></ul></div></div></div>
<?php if($r==='Admin'): ?><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>const cd=<?=json_encode($chart)?>;new Chart(document.getElementById('ch'),{type:'doughnut',data:{labels:Object.keys(cd),datasets:[{data:Object.values(cd),backgroundColor:['#6c757d','#f0b429','#2e9e5b','#b3261e','#3aa6c8','#212529']}]},options:{plugins:{legend:{position:'bottom'}}}});</script><?php endif; ?>
<?php include 'includes/footer.php'; ?>
