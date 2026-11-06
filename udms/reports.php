<?php require 'config.php'; require_role('Admin'); $title='Reports';
$df=preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['from']??'')?$_GET['from']:date('Y-m-01'); $dt2=preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['to']??'')?$_GET['to']:date('Y-m-d');
$rp=[':f'=>$df,':t'=>$dt2]; $rng="DATE(d.created_at) BETWEEN :f AND :t";
if(isset($_GET['csv'])){ log_act('Report generated',null,"Document report CSV $df to $dt2");
  header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="udms-report-'.$df.'-to-'.$dt2.'.csv"'); $o=fopen('php://output','w');
  fputcsv($o,['Document ID','Title','Type','Category','Department','Uploaded By','Status','Access Level','Version','Created','Approved']);
  $cl=fn($s)=>preg_match('/^[=+\-@]/',(string)$s)?"'".$s:$s;
  foreach(q("SELECT d.*,c.category_name,dp.department_name,u.full_name FROM documents d LEFT JOIN categories c ON c.id=d.category_id LEFT JOIN departments dp ON dp.id=d.department_id JOIN users u ON u.id=d.uploaded_by WHERE $rng ORDER BY d.id",$rp) as $r)
    fputcsv($o,array_map($cl,[$r['document_id'],$r['title'],$r['document_type'],$r['category_name'],$r['department_name'],$r['full_name'],$r['status'],$r['access_level'],$r['current_version'],$r['created_at'],$r['approved_at']])); exit; }
$byStatus=q("SELECT status,COUNT(*) c FROM documents d WHERE $rng GROUP BY status",$rp)->fetchAll(); $byCat=q("SELECT COALESCE(c.category_name,'Uncategorized') n,COUNT(*) c FROM documents d LEFT JOIN categories c ON c.id=d.category_id WHERE $rng GROUP BY n ORDER BY c DESC",$rp)->fetchAll();
$byDep=q("SELECT COALESCE(x.department_name,'None') n,COUNT(*) c FROM documents d LEFT JOIN departments x ON x.id=d.department_id WHERE $rng GROUP BY n ORDER BY c DESC",$rp)->fetchAll();
$top=q("SELECT u.full_name n,COUNT(*) c FROM documents d JOIN users u ON u.id=d.uploaded_by WHERE $rng GROUP BY u.id ORDER BY c DESC LIMIT 5",$rp)->fetchAll();
$users=q("SELECT role n,COUNT(*) c FROM users GROUP BY role")->fetchAll(); include 'includes/header.php';
function tbl($h,$rows){ echo '<div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white"><strong>'.e($h).'</strong></div><table class="table mb-0">'; foreach($rows as $r) echo '<tr><td>'.e($r['n']??$r['status']).'</td><td class="text-end">'.$r['c'].'</td></tr>'; if(!$rows) echo '<tr><td class="text-muted">No data in this period.</td></tr>'; echo '</table></div></div>'; } ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2"><h1 class="page-title mb-0">System Reports</h1><button class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button></div>
<form class="card border-0 shadow-sm mb-3"><div class="card-body row g-2 align-items-end"><div class="col-md-3"><label class="form-label small">From</label><input type="date" name="from" class="form-control" value="<?=e($df)?>"></div><div class="col-md-3"><label class="form-label small">To</label><input type="date" name="to" class="form-control" value="<?=e($dt2)?>"></div>
<div class="col-md-6 d-flex gap-2"><button class="btn btn-primary">Generate</button><a class="btn btn-success" href="reports.php?csv=1&from=<?=e($df)?>&to=<?=e($dt2)?>"><i class="bi bi-filetype-csv"></i> Export CSV</a></div></div></form>
<div class="row g-3"><?php tbl('Documents by Status',$byStatus); tbl('Documents by Category',$byCat); tbl('Documents by Department',$byDep); tbl('Top Uploaders',$top); tbl('Users by Role (all time)',$users); ?></div>
<?php include 'includes/footer.php'; ?>
