<?php $u=me(); $public=$public??false; $title=$title??APP; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title)?> | UDMS</title>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600&family=Source+Serif+4:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="assets/style.css?v=<?=file_exists(__DIR__.'/../assets/style.css')?filemtime(__DIR__.'/../assets/style.css'):time()?>" rel="stylesheet"></head><body>
<?php if(!$public && $u): $nav=nav_for($u['role']); $unread=(int)q("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0",[$u['id']])->fetchColumn(); ?>
<div class="app"><aside class="sidebar" id="sb">
 <div class="brand"><i class="bi bi-mortarboard-fill"></i><div>UDMS<small>Document Repository</small></div></div>
 <ul class="nav flex-column"><?php foreach($nav as [$l,$h,$i]): ?>
  <li><a class="nav-link <?=nav_active($h)?'active':''?>" href="<?=e($h)?>"><i class="bi bi-<?=$i?>"></i> <?=e($l)?>
  <?php if($l==='Notifications'&&$unread): ?><span class="badge bg-danger ms-auto"><?=$unread?></span><?php endif; ?></a></li><?php endforeach; ?></ul></aside>
<div class="main"><header class="topbar">
 <button class="btn btn-light d-lg-none" id="sbtn" aria-label="Menu"><i class="bi bi-list"></i></button>
 <form action="search.php" class="flex-fill mx-2"><div class="input-group"><input name="q" class="form-control" placeholder="Search documents by title, ID or keyword" value="<?=e($_GET['q']??'')?>"><button class="btn btn-primary" aria-label="Search"><i class="bi bi-search"></i></button></div></form>
 <a href="notifications.php" class="btn btn-light position-relative" aria-label="Notifications"><i class="bi bi-bell"></i><?php if($unread): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?=$unread?></span><?php endif; ?></a>
 <div class="ms-3 small lh-sm d-none d-md-block text-end"><strong><?=e($u['full_name'])?></strong><br><span class="text-muted"><?=e($u['role'])?></span></div></header>
<div class="content">
<?php endif; ?>
