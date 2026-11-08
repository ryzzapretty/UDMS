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
if (($sideUser['role'] ?? '') === 'Student' && (int)($sideUser['department_id'] ?? 0) === 1) {
    $sideNav = array_values(array_filter($sideNav, static function ($item) {
        return !in_array($item[0] ?? '', ['Upload Document', 'Upload Documents'], true);
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
      <?php if(!empty($sideUser['department_id'])): ?>
        <?php
          $sideGradeRow = q("SELECT department_name FROM departments WHERE id = ?", [(int)$sideUser['department_id']])->fetch();
          $sideGradeName = $sideGradeRow['department_name'] ?? 'Senior High';
        ?>
        <div class="sidebar-user-grade" style="font-size: 11px; color: #cbd5e1; margin-top: 4px; font-weight: 500;">
          <i class="bi bi-mortarboard-fill text-warning"></i> Grade Level: <span class="text-white fw-bold"><?=e($sideGradeName)?></span>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="sidebar-user-divider"></div>

  <nav class="sidebar-navigation">
    <?php foreach ($sideNav as [$label,$href,$icon]): ?>
      <?php
        $names = ['Documents'=>'All Documents','Users'=>'Manage Users','Profile'=>'Settings','Upload Document'=>'Upload Documents','Pending Reviews'=>'Pending Requests','Pending Documents'=>'Pending Requests','Student Requests'=>'Review Student Requests','My Subscription'=>'My Subscription'];
        $display = $names[$label] ?? $label;
        $isLogout = ($label === 'Logout');
      ?>
      <?php if ($isLogout): ?>
      <a href="<?=e($href)?>" class="sidebar-link logout-link" id="sidebarLogoutBtn" role="button">
        <i class="bi bi-<?=e($icon)?>" aria-hidden="true"></i>
        <span><?=e($display)?></span>
      </a>
      <?php else: ?>
      <a href="<?=e($href)?>" class="sidebar-link <?=nav_active($href)?'active':''?>" <?=nav_active($href)?'aria-current="page"':''?>>
        <i class="bi bi-<?=e($icon)?>" aria-hidden="true"></i>
        <span><?=e($display)?></span>
        <?php if ($label==='Notifications' && $sideUnread): ?><span class="sidebar-count"><?= (int)$sideUnread ?></span><?php endif; ?>
      </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>
  <?php if(($sideUser['role']??'') === 'Student'): ?>
  <div class="px-3 pb-3 mt-auto">
    <a href="dashboard.php#submission-deadlines" class="text-decoration-none d-block p-2 rounded-3 shadow-sm" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); transition: all 0.2s ease;">
      <div class="d-flex align-items-center gap-2 text-white small fw-bold">
        <i class="bi bi-calendar-check-fill text-danger"></i>
        <span>Submission Deadlines</span>
      </div>
      <div style="font-size: 11px; color: #cbd5e1; margin-top: 3px; line-height: 1.3;">
        View cutoff schedules per document type &rarr;
      </div>
    </a>
  </div>
  <?php endif; ?>
  <div class="sidebar-decoration" aria-hidden="true"></div>
</aside>
<script>
(function() {
  var logoutBtn = document.getElementById('sidebarLogoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var href = logoutBtn.getAttribute('href');
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Logging out…',
          text: 'Please wait while we sign you out.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: function() { Swal.showLoading(); }
        });
        setTimeout(function() { window.location.href = href; }, 600);
      } else {
        window.location.href = href;
      }
    });
  }
})();
</script>
