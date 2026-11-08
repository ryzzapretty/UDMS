<?php
$u=me(); $public=$public??false; $title=$title??APP;
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title)?> | UDMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="assets/style.css?v=<?=file_exists(__DIR__.'/../assets/style.css')?filemtime(__DIR__.'/../assets/style.css'):time()?>" rel="stylesheet"></head><body>
<?php if(!$public && $u): $unread=(int)q("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0",[$u['id']])->fetchColumn(); ?>
<div class="udms-app">
<header class="udms-top-header">
  <a class="udms-brand" href="dashboard.php"><img src="assets/udms-logo.png" alt="UDMS logo"><span class="udms-brand-line"></span><span><strong>UDMS</strong><small>St. Clare College of Caloocan</small></span></a>
  <div class="udms-account">
    <a href="notifications.php" class="text-white text-decoration-none position-relative" title="Notifications" aria-label="Notifications">
      <i class="bi bi-bell-fill fs-5"></i>
      <?php if($unread): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px;padding:3px 5px;"><?=$unread?></span><?php endif; ?>
    </a>
  </div>
  <button type="button" id="sbtn" class="udms-mobile-toggle" aria-label="Toggle navigation" aria-expanded="false"><i class="bi bi-list"></i></button>
</header>
<div class="udms-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="udms-main">
<section class="udms-page-banner" aria-label="UDMS campus banner">
  <div class="udms-banner-logo"><img src="assets/udms-logo.png" alt=""></div><span class="udms-banner-divider"></span>
  <div class="udms-banner-copy"><span class="udms-banner-eyebrow">UNIVERSITY DOCUMENT MANAGEMENT SYSTEM</span><h2><?=e($title)?></h2><p>Organize &bull; Access &bull; Share</p></div>
</section>
<div class="udms-page-content">
<?php endif; ?>
