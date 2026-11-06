<?php require 'config.php'; require_role('Admin','Faculty','Student'); $u=me();
if($_SERVER['REQUEST_METHOD']!=='POST') redirect('dashboard.php'); csrf_check();
$act=$_POST['act']??''; $id=(int)($_POST['id']??0); $d=doc_load($id);
if(!$d||!can_view($d)){ flash('error','Document not found or access denied.'); redirect('dashboard.php'); }
$p=perms($d,$u); $code=$d['document_id']; $t=$d['title']; $back="document_view.php?id=$id"; $link=$back;
try{ switch($act){
 case 'submit': if(!$p['submit']) throw new Exception('You cannot submit this document.'); submit_doc($id,$code,$t); flash('success',"$code submitted for review."); break;
 case 'review': if(!$p['review']) throw new Exception('Only an administrator can review pending documents.');
   $dec=$_POST['decision']??''; $cm=trim($_POST['comments']??''); $map=['approve'=>['Approved','Approved','approved'],'reject'=>['Rejected','Rejected','rejected'],'revise'=>['Returned for Revision','Revision Requested','returned for revision']];
   if(!isset($map[$dec])) throw new Exception('Unknown decision.'); if($dec!=='approve'&&$cm==='') throw new Exception('A comment is required.');
   [$ns,$al,$verb]=$map[$dec]; q("UPDATE documents SET status=?,approved_at=".($dec==='approve'?'NOW()':'approved_at')." WHERE id=?",[$ns,$id]);
   q("INSERT INTO document_reviews(document_id,reviewer_id,action,comments) VALUES(?,?,?,?)",[$id,$u['id'],$al,$cm]);
   log_act("Document $verb",$id,"$al: $code".($cm?" - $cm":'')); notify($d['uploaded_by'],"Your document $code \"$t\" was $verb.".($cm?" Comment: $cm":''),$link);
   flash('success',"$code $verb."); break;
 case 'archive': if(!$p['archive']) throw new Exception('You cannot archive this document.');
   q("UPDATE documents SET prev_status=status,status='Archived',archived_at=NOW(),archived_by=? WHERE id=?",[$u['id'],$id]);
   log_act('Document archived',$id,"Archived $code"); if($d['uploaded_by']!=$u['id']) notify($d['uploaded_by'],"Your document $code \"$t\" was archived.",$link); flash('success',"$code moved to the archive."); break;
 case 'restore': if(!$p['restore']) throw new Exception('Only an administrator can restore documents.');
   $ns=in_array($d['prev_status'],STATUSES,true)&&$d['prev_status']!=='Archived'?$d['prev_status']:'Approved';
   q("UPDATE documents SET status=?,archived_at=NULL,archived_by=NULL WHERE id=?",[$ns,$id]); log_act('Document restored',$id,"Restored $code to $ns");
   if($d['uploaded_by']!=$u['id']) notify($d['uploaded_by'],"Your document $code \"$t\" was restored.",$link); flash('success',"$code restored."); break;
 case 'delete': if(!$p['delete']) throw new Exception('Only an administrator can permanently delete archived documents.');
   $files=array_column(q("SELECT file_path FROM document_versions WHERE document_id=?",[$id])->fetchAll(),'file_path'); $files[]=$d['file_path'];
   q("DELETE FROM documents WHERE id=?",[$id]); foreach(array_unique($files) as $fp){ $fp=UPLOAD_DIR.basename($fp); if(is_file($fp)) unlink($fp); }
   log_act('Document deleted',null,"Permanently deleted $code \"$t\""); flash('success',"$code permanently deleted."); redirect('documents.php?v=archive');
 case 'share': if(!$p['share']) throw new Exception('You cannot share this document.');
   $to=array_unique(array_map('intval',(array)($_POST['to']??[]))); $n=0;
   foreach($to as $uid){ $x=q("SELECT * FROM users WHERE id=? AND status='Active' AND role IN ('Admin','Faculty','Student')",[$uid])->fetch(); if(!$x||$uid==$u['id']) continue;
     if(q("INSERT IGNORE INTO document_shares(document_id,shared_by,shared_with) VALUES(?,?,?)",[$id,$u['id'],$uid])->rowCount()){ $n++; notify($uid,"{$u['full_name']} shared $code \"$t\" with you.",$link); log_act('Document shared',$id,"Shared $code with {$x['full_name']}"); } }
   if(!$n) throw new Exception('No new recipients were added.'); flash('success',"Shared with $n user(s)."); break;
 case 'unshare': if(!($u['role']==='Admin'||$d['uploaded_by']==$u['id'])) throw new Exception('Not allowed.');
   q("DELETE FROM document_shares WHERE id=? AND document_id=?",[(int)($_POST['sid']??0),$id]); log_act('Document unshared',$id,"Removed a share on $code"); flash('success','Access removed.'); break;
 default: throw new Exception('Unknown action.'); }
}catch(Exception $x){ flash('error',$x->getMessage()); }
redirect($back);
