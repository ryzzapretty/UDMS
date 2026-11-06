<?php require 'config.php'; require_role('Admin','Faculty','Student'); $u=me(); $title='Profile';
$deps=q("SELECT * FROM departments ORDER BY department_name")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check();
  try{ if(($_POST['act']??'')==='profile'){ $n=trim($_POST['full_name']??''); $em=trim($_POST['email']??''); $dep=(int)($_POST['department_id']??0)?:null;
      if(strlen($n)<3) throw new Exception('Enter your full name.'); if(!filter_var($em,FILTER_VALIDATE_EMAIL)) throw new Exception('Enter a valid email.');
      if(q("SELECT 1 FROM users WHERE email=? AND id<>?",[$em,$u['id']])->fetch()) throw new Exception('That email is already used.');
      if($u['role']!=='Admin'&&$dep&&!q("SELECT 1 FROM departments WHERE id=?",[$dep])->fetch()) throw new Exception('Invalid department.');
      if($u['role']!=='Admin') $dep=$dep?:$u['department_id']; else $dep=$dep?:$u['department_id'];
      q("UPDATE users SET full_name=?,email=?,department_id=? WHERE id=?",[$n,$em,$dep,$u['id']]); log_act('Profile updated',null,'Updated own profile'); flash('success','Profile updated.'); }
    elseif(($_POST['act']??'')==='password'){ $cur=$_POST['current']??''; $new=$_POST['password']??'';
      if(!password_verify($cur,$u['password'])) throw new Exception('Current password is incorrect.');
      if(strlen($new)<8||!preg_match('/[A-Za-z]/',$new)||!preg_match('/\d/',$new)) throw new Exception('New password needs 8+ characters with letters and numbers.');
      if($new!==($_POST['confirm']??'')) throw new Exception('New passwords do not match.');
      q("UPDATE users SET password=? WHERE id=?",[password_hash($new,PASSWORD_DEFAULT),$u['id']]); log_act('Password changed',null,'Changed own password'); flash('success','Password changed.'); }
  }catch(Exception $x){ flash('error',$x->getMessage()); } redirect('profile.php'); }
include 'includes/header.php'; ?>
<h1 class="page-title">My Profile</h1><div class="row g-3">
<div class="col-lg-7"><form method="post" class="card border-0 shadow-sm"><div class="card-body row g-3"><?=csrf_field()?><input type="hidden" name="act" value="profile">
 <div class="col-md-6"><label class="form-label">Username</label><input class="form-control" value="<?=e($u['username'])?>" disabled></div><div class="col-md-6"><label class="form-label">Role</label><input class="form-control" value="<?=e($u['role'])?>" disabled></div>
 <div class="col-12"><label class="form-label">Full Name</label><input name="full_name" class="form-control" required value="<?=e($u['full_name'])?>"></div>
 <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?=e($u['email'])?>"></div>
 <div class="col-md-6"><label class="form-label">Department</label><select name="department_id" class="form-select"><?php foreach($deps as $c) echo '<option value="'.$c['id'].'" '.($u['department_id']==$c['id']?'selected':'').'>'.e($c['department_name']).'</option>'; ?></select></div>
 <div class="col-12"><label class="form-label">Student / Employee ID</label><input class="form-control" value="<?=e($u['id_number'])?>" disabled></div>
 <div class="col-12"><button class="btn btn-primary">Save Profile</button></div></div></form></div>
<div class="col-lg-5"><form method="post" class="card border-0 shadow-sm"><div class="card-body"><?=csrf_field()?><input type="hidden" name="act" value="password"><h5>Change password</h5>
 <input type="password" name="current" class="form-control mb-2" placeholder="Current password" required autocomplete="current-password"><input type="password" name="password" class="form-control mb-2" placeholder="New password" required minlength="8" autocomplete="new-password"><input type="password" name="confirm" class="form-control mb-3" placeholder="Confirm new password" required autocomplete="new-password"><button class="btn btn-primary">Update Password</button></div></form></div></div>
<?php include 'includes/footer.php'; ?>
