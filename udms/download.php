<?php require 'config.php'; require_login();
$id=(int)($_GET['id']??0); $d=doc_load($id);
if(!$d||!can_view($d)){ http_response_code(404); die('Document not found or you do not have access.'); }
$fp=$d['file_path']; $fn=$d['file_name']; $lbl='';
if(!empty($_GET['v'])){ $ver=q("SELECT * FROM document_versions WHERE id=? AND document_id=?",[(int)$_GET['v'],$id])->fetch();
  if(!$ver){ http_response_code(404); die('Version not found.'); } $fp=$ver['file_path']; $fn=$ver['file_name']; $lbl=' (version '.$ver['version_number'].')'; }
$path=UPLOAD_DIR.basename($fp); if(!is_file($path)){ http_response_code(404); die('The stored file is missing.'); }
$ext=strtolower(pathinfo($path,PATHINFO_EXTENSION));
$mimes=['pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','txt'=>'text/plain; charset=utf-8'];
$inline=!empty($_GET['inline'])&&isset($mimes[$ext]);
if(!$inline) log_act('Document downloaded',$id,"Downloaded {$d['document_id']}$lbl");
header('Content-Type: '.($mimes[$ext]??'application/octet-stream'));
header('Content-Disposition: '.($inline?'inline':'attachment').'; filename="'.str_replace(['"',"\r","\n"],'',$fn).'"');
header('Content-Length: '.filesize($path)); header('Cache-Control: private, no-store'); readfile($path);
