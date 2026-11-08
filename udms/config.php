<?php
if(defined('UDMS_CONFIG_LOADED')) return;
define('UDMS_CONFIG_LOADED', true);
// ---------- Database settings (edit to match your MySQL) ----------
define('DB_HOST','localhost'); define('DB_NAME','udms'); define('DB_USER','root'); define('DB_PASS','');
define('UPLOAD_DIR', __DIR__.'/uploads/'); define('MAX_SIZE', 10*1024*1024); define('APP','University Document Management System');
const EXTS=['pdf','doc','docx','xls','xlsx','ppt','pptx','jpg','jpeg','png'];
const TYPES=['Learning Module','Lesson / Syllabus','Grade Sheet / Assessment','Student Record','Faculty Record','Academic Document','Memorandum','Report','Form','Certificate','Research Document','Administrative Document','Official Letter','Other'];
const LEVELS=['Public','Faculty Only','Department','Private'];
const STATUSES=['Draft','Pending Review','Approved','Rejected','Returned for Revision','Archived'];
const DOCUMENT_DEADLINES = [
  'Learning Module' => [
    'title' => 'Learning Module',
    'icon' => 'journal-bookmark-fill',
    'term' => 'Midterm Academic Period',
    'deadline_date' => '2026-11-25 17:00:00',
    'deadline_formatted' => 'Nov 25, 2026 (5:00 PM)',
    'category' => 'Learning Modules & Lessons',
    'description' => 'Midterm modules, laboratory worksheets, and module activity portfolios.',
    'status' => 'Open',
    'status_class' => 'success'
  ],
  'Lesson / Syllabus' => [
    'title' => 'Lesson / Syllabus',
    'icon' => 'book-half',
    'term' => 'Term Orientation Period',
    'deadline_date' => '2026-11-05 17:00:00',
    'deadline_formatted' => 'Nov 05, 2026 (5:00 PM)',
    'category' => 'Curriculum & Syllabus',
    'description' => 'Course syllabus acknowledgments and subject curriculum guides.',
    'status' => 'Cutoff Passed',
    'status_class' => 'danger'
  ],
  'Grade Sheet / Assessment' => [
    'title' => 'Grade Sheet / Assessment',
    'icon' => 'clipboard-data-fill',
    'term' => 'Quarterly Assessment Window',
    'deadline_date' => '2026-12-05 17:00:00',
    'deadline_formatted' => 'Dec 05, 2026 (5:00 PM)',
    'category' => 'Student Grades & Assessments',
    'description' => 'Quarterly grade breakdown submissions and grading sheet certifications.',
    'status' => 'Open',
    'status_class' => 'success'
  ],
  'Student Record' => [
    'title' => 'Student Record',
    'icon' => 'person-lines-fill',
    'term' => 'Registrar Verification Period',
    'deadline_date' => '2026-11-30 17:00:00',
    'deadline_formatted' => 'Nov 30, 2026 (5:00 PM)',
    'category' => 'Academic Records',
    'description' => 'Official enrollment verifications, transcript requests, and student ID filings.',
    'status' => 'Open',
    'status_class' => 'success'
  ],
  'Form' => [
    'title' => 'Form',
    'icon' => 'file-earmark-text-fill',
    'term' => 'Mid-Semester Clearance',
    'deadline_date' => '2026-12-15 17:00:00',
    'deadline_formatted' => 'Dec 15, 2026 (5:00 PM)',
    'category' => 'Administrative Policies',
    'description' => 'Clearance forms, student handbook acknowledgments, and waiver submissions.',
    'status' => 'Upcoming',
    'status_class' => 'info'
  ],
  'Certificate' => [
    'title' => 'Certificate',
    'icon' => 'award-fill',
    'term' => 'End of Term Evaluation',
    'deadline_date' => '2026-12-18 17:00:00',
    'deadline_formatted' => 'Dec 18, 2026 (5:00 PM)',
    'category' => 'Academic Records',
    'description' => 'Good moral certificates, completion credentials, and honors filings.',
    'status' => 'Upcoming',
    'status_class' => 'info'
  ],
  'Research Document' => [
    'title' => 'Research Document',
    'icon' => 'search-heart-fill',
    'term' => 'Pre-Defense Draft Submission',
    'deadline_date' => '2026-12-10 17:00:00',
    'deadline_formatted' => 'Dec 10, 2026 (5:00 PM)',
    'category' => 'Curriculum & Syllabus',
    'description' => 'Capstone proposals, thesis chapters, and investigatory project manuscripts.',
    'status' => 'Upcoming',
    'status_class' => 'info'
  ],
  'Official Letter' => [
    'title' => 'Official Letter',
    'icon' => 'envelope-paper-fill',
    'term' => 'Appeals & Special Petitions',
    'deadline_date' => '2026-12-08 17:00:00',
    'deadline_formatted' => 'Dec 08, 2026 (5:00 PM)',
    'category' => 'Administrative Policies',
    'description' => 'Special consideration petitions, grade inquiries, and academic appeals.',
    'status' => 'Open',
    'status_class' => 'success'
  ],
  'Administrative Document' => [
    'title' => 'Administrative Document',
    'icon' => 'building-check',
    'term' => 'Campus Compliance Review',
    'deadline_date' => '2026-12-20 17:00:00',
    'deadline_formatted' => 'Dec 20, 2026 (5:00 PM)',
    'category' => 'Administrative Policies',
    'description' => 'Institutional policy acknowledgments and department compliance records.',
    'status' => 'Upcoming',
    'status_class' => 'info'
  ],
  'Other' => [
    'title' => 'Other Documents',
    'icon' => 'folder2-open',
    'term' => 'General Academic Filings',
    'deadline_date' => '2026-12-22 17:00:00',
    'deadline_formatted' => 'Dec 22, 2026 (5:00 PM)',
    'category' => 'Academic Records',
    'description' => 'Miscellaneous academic submissions and supporting documentation.',
    'status' => 'Open',
    'status_class' => 'success'
  ]
];
if(session_status()===PHP_SESSION_NONE){ session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']); session_start(); }
header('X-Frame-Options: SAMEORIGIN'); header('X-Content-Type-Options: nosniff');

if (!function_exists('check_db_schema')) {
function check_db_schema(){
  static $done=false; if($done) return; $done=true;
  try {
    $cols = db()->query("SHOW COLUMNS FROM users LIKE 'storage_limit'")->fetchAll();
    if(empty($cols)){
      db()->exec("ALTER TABLE users ADD COLUMN storage_limit INT NOT NULL DEFAULT 20");
      db()->exec("ALTER TABLE users ADD COLUMN subscription_tier ENUM('Free','Pro') NOT NULL DEFAULT 'Free'");
      db()->exec("ALTER TABLE users ADD COLUMN subscription_expires_at DATETIME NULL");
    }
    $archCol = db()->query("SHOW COLUMNS FROM users LIKE 'archived_at'")->fetchAll();
    if(empty($archCol)){
      db()->exec("ALTER TABLE users MODIFY COLUMN status ENUM('Active','Inactive','Pending','Archived') NOT NULL DEFAULT 'Pending'");
      db()->exec("ALTER TABLE users ADD COLUMN archived_at DATETIME NULL");
    }
    $lateCol = db()->query("SHOW COLUMNS FROM documents LIKE 'is_late'")->fetchAll();
    if(empty($lateCol)){
      db()->exec("ALTER TABLE documents ADD COLUMN is_late TINYINT(1) NOT NULL DEFAULT 0");
      db()->exec("ALTER TABLE documents ADD COLUMN submission_timing VARCHAR(50) NOT NULL DEFAULT 'On Time'");
    }
  } catch(Exception $e){}
}
}
function db(){ static $p; if(!$p){ $p=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]); check_db_schema(); } return $p; }
function q($sql,$p=[]){ $s=db()->prepare($sql); $s->execute($p); return $s; }
function e($s){ return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function redirect($u){ header("Location: $u"); exit; }
function flash($t,$m){ $_SESSION['flash']=['t'=>$t,'m'=>$m]; }
function dt($s){ return $s?date('M d, Y h:i A',strtotime($s)):'—'; }
function csrf(){ if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.csrf().'">'; }
function csrf_check(){ if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){ http_response_code(400); die('Invalid security token. Please go back, refresh the page and try again.'); } }

function is_submission_late($docType, $time = null) {
  if (empty($docType) || !isset(DOCUMENT_DEADLINES[$docType]['deadline_date'])) return false;
  $deadlineTs = strtotime(DOCUMENT_DEADLINES[$docType]['deadline_date']);
  $checkTs = $time ? (is_numeric($time) ? (int)$time : strtotime($time)) : time();
  return $checkTs > $deadlineTs;
}

// ---------- Authentication / authorization ----------
function me(){ static $u=false; if($u===false){ $u=null; if(!empty($_SESSION['uid'])){ $u=q("SELECT * FROM users WHERE id=? AND status='Active'",[$_SESSION['uid']])->fetch()?:null; if(!$u) unset($_SESSION['uid']); } } return $u; }
function require_login(){ if(!me()) redirect('login.php'); }
function forbid($m='You do not have permission to access this page.'){
  http_response_code(403); $title='Access Denied';
  if(me()){ include __DIR__.'/includes/header.php'; echo '<div class="alert alert-danger"><i class="bi bi-shield-lock"></i> '.e($m).'</div>'; include __DIR__.'/includes/footer.php'; } else echo e($m);
  exit; }
function require_role(...$r){ require_login(); if(!in_array(me()['role'],$r,true)) forbid(); }

function visible_sql($u,$a='d'){ // SQL condition = documents this user may see
  if($u['role']==='Admin') return ['1=1',[]];
  $p=[':v_me'=>$u['id'],':v_me2'=>$u['id']];
  if($u['role']==='Faculty'){
    // Allow professors access to shared documents publicly and privately across each grade level
    $p=[':v_me'=>$u['id'],':v_me2'=>$u['id'],':v_me3'=>$u['id']];
    $s="($a.uploaded_by=:v_me
        OR EXISTS(SELECT 1 FROM document_shares vs WHERE vs.document_id=$a.id AND (vs.shared_with=:v_me2 OR vs.shared_by=:v_me3))
        OR ($a.status='Approved' AND ($a.access_level IN ('Public', 'Faculty Only', 'Department', 'Private') OR EXISTS(SELECT 1 FROM document_shares vs2 WHERE vs2.document_id=$a.id)))
        OR ($a.status='Pending Review' AND $a.department_id IS NOT NULL))";
    return [$s,$p];
  }
  $s="($a.uploaded_by=:v_me OR EXISTS(SELECT 1 FROM document_shares vs WHERE vs.document_id=$a.id AND vs.shared_with=:v_me2) OR ($a.status='Approved' AND ($a.access_level='Public'";
  if($u['role']==='Faculty') $s.=" OR $a.access_level='Faculty Only'";
  if($u['department_id']){ $s.=" OR ($a.access_level='Department' AND $a.department_id=:v_dep)"; $p[':v_dep']=$u['department_id']; }
  return [$s.")))",$p]; }
function can_view($d){ $u=me(); if(!$u||!$d) return false; if($u['role']==='Admin') return true;
  if(!in_array($u['role'],['Faculty','Student'],true)) return false;
  if($d['uploaded_by']==$u['id']) return true;
  if(faculty_can_review($d,$u)) return true;
  if(q("SELECT 1 FROM document_shares WHERE document_id=? AND (shared_with=? OR shared_by=?)",[$d['id'],$u['id'],$u['id']])->fetch()) return true;
  if($u['role']==='Faculty'){
    // Professor access: allow access to shared documents publicly and privately for each grade level
    if($d['status']==='Approved') return true;
    if(q("SELECT 1 FROM document_shares WHERE document_id=?", [$d['id']])->fetch()) return true;
  }
  if($d['status']!=='Approved') return false;
  switch($d['access_level']){ case 'Public':return true; case 'Faculty Only':return $u['role']==='Faculty';
    case 'Department':return $u['department_id'] && $u['department_id']==$d['department_id']; } return false; }
// Faculty staff can review pending Student documents in their own department.
function faculty_can_review($d,$u){
  if(($u['role']??'')!=='Faculty' || ($d['status']??'')!=='Pending Review') return false;
  if(empty($u['department_id']) || empty($d['department_id']) || (int)$u['department_id']!==(int)$d['department_id']) return false;
  $owner=q("SELECT role FROM users WHERE id=?",[$d['uploaded_by']])->fetch();
  return $owner && $owner['role']==='Student';
}
function perms($d,$u){ $adm=$u['role']==='Admin'; $fac=$u['role']==='Faculty'; $own=$d['uploaded_by']==$u['id']; $st=$d['status']; $v=can_view($d);
  $editable=in_array($st,['Draft','Returned for Revision','Rejected'],true);
  return ['view'=>$v,'edit'=>$v&&($adm||($own&&$editable)),'submit'=>$v&&($adm||$own)&&$editable,'review'=>($adm&&$st==='Pending Review')||faculty_can_review($d,$u),
   'archive'=>$v&&$st!=='Archived'&&($adm||($own&&$fac&&$st==='Approved')),'restore'=>$adm&&$st==='Archived','delete'=>$adm&&$st==='Archived',
   'share'=>$v&&$st!=='Archived'&&($adm||$fac)&&($st==='Approved'||$adm||$own)]; }

// ---------- Logging / notifications ----------
function log_act($a,$doc=null,$d='',$uid=null){ q("INSERT INTO activity_logs(user_id,action,document_id,description) VALUES(?,?,?,?)",[$uid??(me()['id']??null),$a,$doc,$d]); }
function notify($uid,$m,$link=null){ q("INSERT INTO notifications(user_id,message,link) VALUES(?,?,?)",[$uid,$m,$link]); }
function notify_admins($m,$link=null){ foreach(q("SELECT id FROM users WHERE role='Admin' AND status='Active'")->fetchAll() as $a) notify($a['id'],$m,$link); }

// ---------- Documents ----------
function doc_load($id){ return q("SELECT d.*,c.category_name,dp.department_name,u.full_name AS uploader FROM documents d LEFT JOIN categories c ON c.id=d.category_id LEFT JOIN departments dp ON dp.id=d.department_id JOIN users u ON u.id=d.uploaded_by WHERE d.id=?",[$id])->fetch(); }
function submit_doc($id,$code,$title){ $u=me();
  q("UPDATE documents SET status='Pending Review' WHERE id=?",[$id]);
  q("INSERT INTO document_reviews(document_id,reviewer_id,action,comments) VALUES(?,?,?,?)",[$id,$u['id'],'Submitted','Submitted for review']);
  log_act('Document submitted',$id,"Submitted $code for review");
  notify($u['id'],"Your document $code \"$title\" was submitted and is pending review.","document_view.php?id=$id");
  notify_admins("Document $code \"$title\" requires review.","document_view.php?id=$id"); }
function next_version($cur,$major){ [$a,$b]=array_map('intval',explode('.',$cur.'.0')); return $major?($a+1).'.0':"$a.".($b+1); }
function safe_name($n){ $n=preg_replace('/[^\w\.\- ]/u','_',basename($n)); return substr($n,0,120); }
function save_upload($f){
  if(!isset($f['error'])||$f['error']!==UPLOAD_ERR_OK) throw new Exception('The file could not be uploaded. Please choose a file and try again.');
  if($f['size']>MAX_SIZE) throw new Exception('The file is larger than the 10 MB limit.');
  $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
  $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  if(str_starts_with($mime, 'video/') || in_array($ext, ['mp4','avi','mov','mkv','webm','wmv','flv','m4v','3gp','ts'], true)) {
    throw new Exception('Video files are strictly prohibited. Only file documents up to 10 MB are allowed.');
  }
  if(!in_array($ext,EXTS,true)) throw new Exception('File type not allowed. Only file documents ('.strtoupper(implode(', ',EXTS)).') up to 10 MB are allowed. No videos.');
  $strict=['pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png'];
  if(isset($strict[$ext])&&$mime!==$strict[$ext]) throw new Exception('The file content does not match its extension.');
  if(preg_match('~^(text/html|text/x-php|application/x-httpd|application/x-sh)~',$mime)) throw new Exception('This file content is not allowed.');
  $name=bin2hex(random_bytes(16)).'.'.$ext;
  if(!move_uploaded_file($f['tmp_name'],UPLOAD_DIR.$name)) throw new Exception('The file could not be saved on the server.');
  return ['path'=>$name,'name'=>safe_name($f['name']),'ext'=>$ext,'size'=>(int)$f['size']]; }

function student_storage_info($uid = null){
  $u = $uid ? q("SELECT * FROM users WHERE id=?", [$uid])->fetch() : me();
  if(!$u || $u['role'] !== 'Student'){
    return ['is_student'=>false,'unlimited'=>true,'count'=>0,'limit'=>0,'remaining'=>9999,'percent'=>0,'tier'=>'Unlimited','can_upload'=>true,'expires_at'=>null,'size_bytes'=>0,'size_formatted'=>'0 KB'];
  }
  $tier = $u['subscription_tier'] ?? 'Free';
  $expires_at = $u['subscription_expires_at'] ?? null;
  if($tier === 'Pro' && $expires_at && strtotime($expires_at) < time()){
    q("UPDATE users SET subscription_tier='Free', storage_limit=20, subscription_expires_at=NULL WHERE id=?", [$u['id']]);
    $tier = 'Free';
    $u['storage_limit'] = 20;
    $expires_at = null;
    notify($u['id'], 'Your Student Pro subscription has expired and your account has returned to the Free plan (20 files limit).', 'subscription.php');
  }
  $limit = (int)($u['storage_limit'] ?? 20);
  if($limit <= 0) $limit = 20;
  $row = q("SELECT COUNT(*) AS c, COALESCE(SUM(file_size), 0) AS total_bytes FROM documents WHERE uploaded_by=? AND status<>'Archived'", [$u['id']])->fetch();
  $count = (int)$row['c'];
  $bytes = (int)$row['total_bytes'];
  $percent = min(100, round(($count / $limit) * 100));
  return [
    'is_student' => true,
    'unlimited' => false,
    'count' => $count,
    'limit' => $limit,
    'remaining' => max(0, $limit - $count),
    'percent' => $percent,
    'tier' => $tier,
    'can_upload' => $count < $limit,
    'expires_at' => $expires_at,
    'size_bytes' => $bytes,
    'size_formatted' => $bytes > 1048576 ? number_format($bytes / 1048576, 1) . ' MB' : number_format($bytes / 1024, 1) . ' KB'
  ];
}

// ---------- UI helpers ----------
function badge($s){ $c=['Draft'=>'secondary','Pending Review'=>'warning text-dark','Approved'=>'success','Rejected'=>'danger','Returned for Revision'=>'info text-dark','Archived'=>'dark'][$s]??'secondary'; return "<span class='badge bg-$c'>".e($s)."</span>"; }
function post_btn($act,$id,$label,$cls='btn-outline-primary',$msg='',$extra=''){
  return '<form method="post" action="actions.php" class="d-inline js-confirm" data-msg="'.e($msg).'">'.csrf_field().'<input type="hidden" name="act" value="'.e($act).'"><input type="hidden" name="id" value="'.(int)$id.'">'.$extra.'<button class="btn btn-sm '.$cls.'">'.$label.'</button></form>'; }
function nav_for($r){
  $n=[['Dashboard','dashboard.php','speedometer2']]; $tail=[['Notifications','notifications.php','bell'],['Profile','profile.php','person-circle'],['Logout','logout.php','box-arrow-left']];
  if($r==='Admin') return array_merge($n,[['Documents','documents.php?v=all','folder2-open'],['Archive','documents.php?v=archive','archive'],['Users','users.php','people'],['Activity Logs','logs.php','clock-history'],['Reports','reports.php','bar-chart']],$tail);
  if($r==='Faculty') return array_merge($n,[['Student Requests','documents.php?v=student_requests','clipboard-check'],['My Documents','documents.php?v=mine','folder2-open'],['Create Document','document_form.php?mode=create','file-earmark-plus'],['Upload Document','document_form.php?mode=upload','cloud-upload'],['Pending Documents','documents.php?v=pending','hourglass-split'],['Shared Documents','documents.php?v=shared','share'],['Archive','documents.php?v=archive','archive']],$tail);
  return array_merge($n,[['My Documents','documents.php?v=mine','folder2-open'],['Upload Document','document_form.php?mode=upload','cloud-upload'],['My Submissions','documents.php?v=submissions','send-check'],['Shared Documents','documents.php?v=shared','share'],['My Subscription','subscription.php','gem']],$tail); }
function nav_active($h){ $p=parse_url($h); if($p['path']!==basename($_SERVER['SCRIPT_NAME'])) return false;
  if(isset($p['query'])){ parse_str($p['query'],$a); foreach($a as $k=>$v) if(($_GET[$k]??null)!==$v) return false; return true; }
  return empty($_GET['v'])&&empty($_GET['t'])&&empty($_GET['mode']); }
