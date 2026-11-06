<?php
// Shared role-aware navigation. Uses the project's existing nav_for() and nav_active().
$sideUser = $u ?? me();
$sideNav = nav_for($sideUser['role']);
// Hide document creation, uploads, personal documents and archive from Faculty only.
// Admin and Student menus remain unchanged.
if (($sideUser['role'] ?? '') === 'Faculty') {
    $hiddenForFaculty = ['Upload Document', 'Upload Documents', 'My Documents', 'Archive'];
    $sideNav = array_values(array_filter($sideNav, static function ($item) use ($hiddenForFaculty) {
        return !in_array($item[0] ?? '', $hiddenForFaculty, true);
    }));
}
$sideUnread = $unread ?? 0;
?>
<aside class="udms-sidebar" id="sb" aria-label="Main navigation">
  <nav class="sidebar-navigation">
    <?php foreach ($sideNav as [$label,$href,$icon]): ?>
      <?php
        $names = ['Documents'=>'All Documents','Users'=>'Manage Users','Profile'=>'Settings','Upload Document'=>'Upload Documents','Pending Reviews'=>'Pending Requests','Pending Documents'=>'Pending Requests','Student Requests'=>'Review Student Requests'];
        $display = $names[$label] ?? $label;
      ?>
      <a href="<?=e($href)?>" class="sidebar-link <?=nav_active($href)?'active':''?>" <?=nav_active($href)?'aria-current="page"':''?>>
        <i class="bi bi-<?=e($icon)?>" aria-hidden="true"></i>
        <span><?=e($display)?></span>
        <?php if ($label==='Notifications' && $sideUnread): ?><span class="sidebar-count"><?= (int)$sideUnread ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-decoration" aria-hidden="true"></div>
</aside>
