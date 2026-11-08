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
  <!-- User Profile Section at top of Sidebar -->
  <div class="sidebar-user-profile">
    <div class="sidebar-user-avatar">
      <i class="bi bi-person-fill"></i>
    </div>
    <div class="sidebar-user-details">
      <div class="sidebar-user-name" title="<?=e($sideUser['full_name']??'User')?>"><?=e($sideUser['full_name']??'User')?></div>
      <div class="sidebar-user-role-badge">
        <span><?=e($sideUser['role']??'Member')?></span>
        <?php if(($sideUser['role']??'')==='Student' && ($sideUser['subscription_tier']??'')==='Pro'): ?>
          <span class="sidebar-pro-pill" title="Student Pro Tier"><i class="bi bi-star-fill"></i> PRO</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="sidebar-user-divider"></div>

  <nav class="sidebar-navigation">
    <?php foreach ($sideNav as [$label,$href,$icon]): ?>
      <?php
        $names = ['Documents'=>'All Documents','Users'=>'Manage Users','Profile'=>'Settings','Upload Document'=>'Upload Documents','Pending Reviews'=>'Pending Requests','Pending Documents'=>'Pending Requests','Student Requests'=>'Review Student Requests','My Subscription'=>'My Subscription'];
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
