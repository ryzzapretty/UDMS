<?php
require 'config.php';
require_login();

$u = me();
$isStudent = $u['role'] === 'Student';
$stg = student_storage_info($u['id']);
$title = 'Storage & Subscription';

// Handle upgrade / downgrade actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['act'] ?? '';

    try {
        if (!$isStudent) {
            throw new Exception('Subscriptions are currently only required for Student accounts. Staff and Administrators have unlimited storage.');
        }

        if ($act === 'upgrade') {
            $plan = $_POST['plan'] ?? 'pro';
            $limit = 100;
            $tierName = 'Pro';
            $expires = date('Y-m-d H:i:s', strtotime('+6 months'));

            q("UPDATE users SET subscription_tier=?, storage_limit=?, subscription_expires_at=? WHERE id=?", [
                $tierName, $limit, $expires, $u['id']
            ]);

            log_act('Subscription upgraded', null, "Upgraded to Student Pro (Storage limit: {$limit} files)", $u['id']);
            notify($u['id'], "🎉 Congratulations! You have successfully upgraded to Student Pro. Your storage quota is now {$limit} documents.", 'subscription.php');
            notify_admins("Student {$u['full_name']} ({$u['username']}) upgraded to Student Pro (100 files limit).", 'users.php');

            flash('success', "🎉 Welcome to Student Pro! Your storage capacity has been upgraded to {$limit} documents.");
            redirect('subscription.php');
        } elseif ($act === 'downgrade') {
            q("UPDATE users SET subscription_tier='Free', storage_limit=20, subscription_expires_at=NULL WHERE id=?", [$u['id']]);

            log_act('Subscription changed', null, 'Switched to Free plan (Storage limit: 20 files)', $u['id']);
            notify($u['id'], 'Your subscription has been changed back to the Free plan (20 files limit).', 'subscription.php');

            flash('info', 'Your account has been switched to the Free plan (20 documents limit). Existing files remain safe.');
            redirect('subscription.php');
        } else {
            throw new Exception('Unknown action.');
        }
    } catch (Exception $e) {
        flash('error', $e->getMessage());
        redirect('subscription.php');
    }
}

// Refresh user storage info after any updates
$stg = student_storage_info($u['id']);
include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h1 class="page-title mb-1">My Storage &amp; Subscription</h1>
    <p class="text-muted mb-0">Manage your document storage capacity and upgrade options</p>
  </div>
  <?php if ($isStudent): ?>
    <div>
      <span class="badge bg-<?= $stg['tier'] === 'Pro' ? 'primary' : 'secondary' ?> fs-6 px-3 py-2">
        <i class="bi bi-<?= $stg['tier'] === 'Pro' ? 'star-fill' : 'folder2' ?>"></i>
        <?= e($stg['tier']) ?> Plan (<?= $stg['limit'] ?> Files Limit)
      </span>
    </div>
  <?php endif; ?>
</div>

<?php if (!$isStudent): ?>
  <!-- Non-student notice -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 text-center">
      <div class="display-4 text-primary mb-3"><i class="bi bi-shield-check"></i></div>
      <h4 class="fw-bold">Unlimited Institutional Storage</h4>
      <p class="text-muted mb-3">
        As a member of the university <strong><?= e($u['role']) ?></strong>, your account has unlimited document storage privileges.
        Storage restrictions and subscription tiers apply exclusively to Student accounts.
      </p>
      <a href="dashboard.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Return to Dashboard</a>
    </div>
  </div>
<?php else: ?>

  <!-- Storage Meter Card -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
      <div class="row align-items-center g-3">
        <div class="col-md-7">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fw-bold fs-5">
              <i class="bi bi-hdd-network text-primary"></i> Current Storage Usage
            </span>
            <span class="badge bg-<?= $stg['percent'] >= 90 ? 'danger' : ($stg['percent'] >= 75 ? 'warning text-dark' : 'success') ?> fs-6">
              <?= $stg['count'] ?> of <?= $stg['limit'] ?> Used (<?= $stg['percent'] ?>%)
            </span>
          </div>

          <!-- Quota Progress Bar -->
          <div class="progress mb-2" style="height: 14px; border-radius: 8px;">
            <div class="progress-bar <?= $stg['percent'] >= 90 ? 'bg-danger' : ($stg['percent'] >= 75 ? 'bg-warning' : 'bg-primary') ?>"
                 role="progressbar"
                 style="width: <?= $stg['percent'] ?>%;"
                 aria-valuenow="<?= $stg['count'] ?>"
                 aria-valuemin="0"
                 aria-valuemax="<?= $stg['limit'] ?>">
            </div>
          </div>

          <div class="d-flex justify-content-between text-muted small mt-1">
            <span><strong><?= $stg['remaining'] ?></strong> document slots available &middot; Total Size: <strong><?= $stg['size_formatted'] ?></strong></span>
            <span>Plan Capacity: <strong><?= $stg['limit'] ?> Files</strong></span>
          </div>
        </div>

        <div class="col-md-5 border-start-md ps-md-4">
          <?php if ($stg['tier'] === 'Pro'): ?>
            <div class="d-flex align-items-center gap-3">
              <div class="fs-1 text-warning"><i class="bi bi-patch-check-fill"></i></div>
              <div>
                <div class="fw-bold text-success">Student Pro Active</div>
                <div class="small text-muted">Enjoy expanded 100-file capacity for all your academic records and projects.</div>
                <?php if ($stg['expires_at']): ?>
                  <div class="small text-muted mt-1"><i class="bi bi-clock"></i> Valid until <?= dt($stg['expires_at']) ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="d-flex align-items-center gap-3">
              <div class="fs-1 text-<?= $stg['percent'] >= 90 ? 'danger' : 'primary' ?>">
                <i class="bi bi-<?= $stg['percent'] >= 90 ? 'exclamation-circle-fill' : 'info-circle-fill' ?>"></i>
              </div>
              <div>
                <div class="fw-bold">Free Plan Limit: 20 Files</div>
                <div class="small text-muted">
                  <?= $stg['percent'] >= 100
                      ? '<span class="text-danger fw-semibold">Quota reached! You cannot upload new files until you upgrade or delete old files.</span>'
                      : 'You can store up to 20 documents for free. Need more space for your academic clearance or thesis?' ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Pricing Cards -->
  <div class="row g-4 mb-4">
    <!-- Free Tier Card -->
    <div class="col-md-6">
      <div class="card h-100 border-0 shadow-sm position-relative <?= $stg['tier'] === 'Free' ? 'border-2 border-secondary' : '' ?>">
        <?php if ($stg['tier'] === 'Free'): ?>
          <span class="position-absolute top-0 end-0 badge bg-secondary m-3">Current Plan</span>
        <?php endif; ?>
        <div class="card-body p-4 d-flex flex-column">
          <div class="mb-3">
            <span class="badge bg-light text-dark border px-3 py-1 fw-bold">STANDARD</span>
            <h3 class="fw-bold mt-2 mb-1">Free Student Plan</h3>
            <p class="text-muted small">Standard storage quota for basic submissions and records.</p>
          </div>

          <div class="display-6 fw-bold mb-3 text-dark">
            ₱0 <span class="fs-6 text-muted fw-normal">/ semester</span>
          </div>

          <ul class="list-unstyled mb-4 flex-grow-1">
            <li class="py-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <strong>20 Stored Documents</strong> capacity
            </li>
            <li class="py-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              10 MB max upload file size
            </li>
            <li class="py-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              Standard document review workflow
            </li>
            <li class="py-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              Full access to approved public documents
            </li>
            <li class="py-2 text-muted d-flex align-items-center gap-2">
              <i class="bi bi-x-circle text-muted"></i>
              No expanded thesis or portfolio storage
            </li>
          </ul>

          <div>
            <?php if ($stg['tier'] === 'Free'): ?>
              <button class="btn btn-secondary w-100 py-2" disabled>
                <i class="bi bi-check2"></i> Active Plan
              </button>
            <?php else: ?>
              <form method="post" class="js-confirm" data-msg="Downgrade to the Free plan? Your storage limit will return to 20 files.">
                <?= csrf_field() ?>
                <input type="hidden" name="act" value="downgrade">
                <button class="btn btn-outline-secondary w-100 py-2">
                  Switch to Free Plan
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Pro Tier Card -->
    <div class="col-md-6">
      <div class="card h-100 border-2 border-primary shadow position-relative" style="background: linear-gradient(180deg, #ffffff 0%, #f4f8ff 100%);">
        <span class="position-absolute top-0 end-0 badge bg-warning text-dark m-3 fw-bold">
          <i class="bi bi-star-fill"></i> RECOMMENDED
        </span>
        <div class="card-body p-4 d-flex flex-column">
          <div class="mb-3">
            <span class="badge bg-primary px-3 py-1 fw-bold">STUDENT PRO</span>
            <h3 class="fw-bold mt-2 mb-1 text-primary">Expanded Student Pro</h3>
            <p class="text-muted small">Designed for thesis students, scholars, and multi-term portfolios.</p>
          </div>

          <div class="display-6 fw-bold mb-3 text-primary">
            ₱99 <span class="fs-6 text-muted fw-normal">/ semester (or Demo 1-Click)</span>
          </div>

          <ul class="list-unstyled mb-4 flex-grow-1">
            <li class="py-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              <strong class="text-primary">100 Stored Documents</strong> (5x capacity!)
            </li>
            <li class="py-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              10 MB max upload file size
            </li>
            <li class="py-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              Student Pro Verified badge in system
            </li>
            <li class="py-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              Priority document review visibility
            </li>
            <li class="py-2 d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              Ideal for capstone, thesis drafts &amp; clearances
            </li>
          </ul>

          <div>
            <?php if ($stg['tier'] === 'Pro'): ?>
              <button class="btn btn-success w-100 py-2 fw-semibold" disabled>
                <i class="bi bi-patch-check-fill"></i> Pro Plan Active
              </button>
            <?php else: ?>
              <form method="post" class="js-confirm" data-msg="Subscribe to Student Pro? This immediately activates your 100-document storage capacity.">
                <?= csrf_field() ?>
                <input type="hidden" name="act" value="upgrade">
                <input type="hidden" name="plan" value="pro">
                <button class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                  <i class="bi bi-gem"></i> Subscribe to Pro (Instant Upgrade)
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Frequently Asked Questions -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
      <strong><i class="bi bi-question-circle text-primary"></i> Storage &amp; Subscription FAQs</strong>
    </div>
    <div class="card-body">
      <div class="row g-4">
        <div class="col-md-6">
          <h6 class="fw-bold"><i class="bi bi-dot text-primary fs-4 align-middle"></i> What counts towards my 20-file limit?</h6>
          <p class="text-muted small">
            All active documents uploaded by your account (including Drafts, documents Pending Review, Approved submissions, and Returned for Revision) count towards your limit. Permanently deleted or archived documents do not count.
          </p>
        </div>
        <div class="col-md-6">
          <h6 class="fw-bold"><i class="bi bi-dot text-primary fs-4 align-middle"></i> What happens if I reach the 20-document limit?</h6>
          <p class="text-muted small">
            You will not be able to upload or create new documents until you either upgrade your account to Student Pro or archive/delete unneeded draft submissions.
          </p>
        </div>
        <div class="col-md-6">
          <h6 class="fw-bold"><i class="bi bi-dot text-primary fs-4 align-middle"></i> Will I lose existing files if I downgrade?</h6>
          <p class="text-muted small">
            No! None of your existing files will ever be deleted automatically. If you have more than 20 files and downgrade, your existing files remain accessible and downloadable, but you cannot upload new ones until your storage is back below 20.
          </p>
        </div>
        <div class="col-md-6">
          <h6 class="fw-bold"><i class="bi bi-dot text-primary fs-4 align-middle"></i> Can an Administrator manually upgrade my account?</h6>
          <p class="text-muted small">
            Yes, University Administrators can adjust your storage quota or subscription tier directly from the User Management console if requested.
          </p>
        </div>
      </div>
    </div>
  </div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>

