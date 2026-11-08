<?php
// includes/dashboards/admin_view.php
// Admin Executive Monitoring, Reports & User Administration Dashboard
?>

<!-- ==========================================
     ADMIN MONITORING KPI METRIC CARDS
========================================== -->
<section class="stats-grid">

    <!-- ACTIVE USERS -->
    <a href="users.php?status=Active" class="stat-card purple">
        <div class="stat-icon">
            <?= dashIcon('users', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number">
                <?= number_format($activeUsersCount) ?>
                <?php if ($pendingUsersCount > 0): ?>
                    <span class="pulse-indicator" title="<?= (int)$pendingUsersCount ?> pending approval"></span>
                <?php endif; ?>
            </div>
            <div class="stat-label">Active Campus Users</div>
        </div>
    </a>

    <!-- TOTAL SYSTEM DOCUMENTS -->
    <a href="documents.php?v=all" class="stat-card blue">
        <div class="stat-icon">
            <?= dashIcon('file', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($totalDocs) ?></div>
            <div class="stat-label">Total Active Documents</div>
        </div>
    </a>

    <!-- SYSTEM MONITORING & REPORTS -->
    <a href="reports.php" class="stat-card green">
        <div class="stat-icon">
            <i class="bi bi-graph-up-arrow" style="font-size:28px;"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($approvedDocsCount) ?></div>
            <div class="stat-label">System Reports & Memos</div>
        </div>
    </a>

    <!-- ARCHIVED USERS & SOFT-DELETED ACCOUNTS -->
    <a href="users.php?status=Archived" class="stat-card yellow">
        <div class="stat-icon">
            <i class="bi bi-archive-fill" style="font-size:28px;"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($archivedUsersCount) ?></div>
            <div class="stat-label">Archived / Soft-Deleted Users</div>
        </div>
    </a>

</section>

<!-- ==========================================
     SYSTEM MONITORING & STORAGE HEALTH BAR
========================================== -->
<div class="system-meta-bar">
    <div class="meta-item">
        <i class="bi bi-hdd-fill"></i>
        <span><strong>Storage Consumed:</strong> <?= dashFormatBytes($totalStorageBytes) ?></span>
    </div>
    <div class="meta-divider"></div>
    <div class="meta-item">
        <i class="bi bi-person-check-fill text-success"></i>
        <span><strong>User Breakdown:</strong> 
            <?= (int)($userStatusCounts['Active'] ?? 0) ?> Active &bull; 
            <?= (int)($userStatusCounts['Pending'] ?? 0) ?> Pending &bull; 
            <?= (int)($userStatusCounts['Inactive'] ?? 0) ?> Inactive &bull; 
            <?= (int)($userStatusCounts['Archived'] ?? 0) ?> Archived
        </span>
    </div>
    <div class="meta-divider"></div>
    <div class="meta-item">
        <i class="bi bi-filetype-csv text-primary"></i>
        <span><a href="reports.php" class="text-primary fw-semibold">Open Monitoring Reports & Export CSV &rarr;</a></span>
    </div>
</div>

<!-- ==========================================
     ADMIN QUICK MANAGEMENT & MONITORING HUB
========================================== -->
<div class="quick-action-bar">
    <div class="quick-action-title">
        <i class="bi bi-shield-check"></i> Admin Operations & Monitoring Hub
    </div>
    <div class="quick-action-buttons">
        <a href="users.php" class="qa-btn qa-btn-primary">
            <i class="bi bi-people-fill"></i> User Management
        </a>
        <a href="users.php?status=Pending" class="qa-btn <?= $pendingUsersCount > 0 ? 'qa-btn-warning' : '' ?>">
            <i class="bi bi-hourglass-split"></i> Pending Approvals (<?= (int)$pendingUsersCount ?>)
        </a>
        <a href="users.php?status=Archived" class="qa-btn">
            <i class="bi bi-archive-fill"></i> Archived Accounts (<?= (int)$archivedUsersCount ?>)
        </a>
        <a href="reports.php" class="qa-btn">
            <i class="bi bi-graph-up-arrow"></i> System Reports & CSV
        </a>
        <a href="logs.php" class="qa-btn">
            <i class="bi bi-clock-history"></i> Live Activity Logs
        </a>
        <a href="documents.php?v=all" class="qa-btn">
            <i class="bi bi-folder2-open"></i> Browse All Documents
        </a>
    </div>
</div>

<!-- ==========================================
     ADMIN CONTENT GRID
========================================== -->
<div class="content-grid">

    <!-- LEFT COLUMN -->
    <div>

        <!-- PENDING USER APPROVALS (IF ANY) -->
        <?php if (!empty($pendingUsers)): ?>
            <section class="panel mb-4" style="border-left: 5px solid #f59e0b;">
                <div class="panel-header">
                    <h3>
                        <i class="bi bi-person-fill-exclamation text-warning"></i>
                        Pending User Signups Awaiting Approval
                        <span class="badge-count-warning"><?= count($pendingUsers) ?></span>
                    </h3>
                    <a href="users.php?status=Pending">View All Pending &rarr;</a>
                </div>

                <div class="document-list">
                    <?php foreach ($pendingUsers as $pu): ?>
                        <div class="document-row">
                            <div class="file-type" style="background:#fffbeb;color:#b45309;">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                            <div class="document-info">
                                <a class="document-name" href="users.php?q=<?= urlencode($pu['username']) ?>">
                                    <?= dashEscape($pu['full_name']) ?>
                                </a>
                                <div class="document-date">
                                    <code><?= dashEscape($pu['username']) ?></code> &bull; 
                                    <?= dashEscape($pu['email']) ?> &bull; 
                                    Role: <strong><?= dashEscape($pu['role']) ?></strong>
                                    <?php if (!empty($pu['department_name'])): ?>
                                        &bull; <?= dashEscape($pu['department_name']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="post" action="users.php" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="act" value="toggle">
                                <input type="hidden" name="id" value="<?= (int)$pu['id'] ?>">
                                <button class="btn btn-sm btn-success" title="Approve & Activate Account">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- LIVE AUDIT ACTIVITY LOGS -->
        <section class="panel mb-4">
            <div class="panel-header">
                <h3>
                    <i class="bi bi-activity text-primary"></i>
                    Live System Activity Audit Stream
                </h3>
                <a href="logs.php">Full Audit Log &rarr;</a>
            </div>

            <div class="announcement-list">
                <?php if (empty($activities)): ?>
                    <div class="empty-state">No recent activities recorded.</div>
                <?php else: ?>
                    <?php foreach ($activities as $a): ?>
                        <div class="announcement">
                            <div class="announcement-icon">
                                <?= dashIcon('info', 20) ?>
                            </div>
                            <div class="announcement-body">
                                <strong><?= dashEscape($a['action']) ?></strong>
                                <p><?= dashEscape($a['description']) ?></p>
                                <div class="announcement-date">
                                    <span class="fw-semibold"><?= dashEscape($a['full_name'] ?? 'System') ?></span>
                                    <?php if (!empty($a['role'])): ?>
                                        <span class="badge-role-tiny"><?= dashEscape($a['role']) ?></span>
                                    <?php endif; ?>
                                    &bull; <?= dashEscape(dashDate($a['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- RECENT CAMPUS DOCUMENTS MONITORING -->
        <section class="panel">
            <div class="panel-header">
                <h3>
                    <?= dashIcon('file') ?>
                    Recent Campus Documents
                </h3>
                <a href="documents.php?v=all">Browse All &rarr;</a>
            </div>

            <div class="document-list">
                <?php if (empty($recentDocs)): ?>
                    <div class="empty-state">No recent documents found.</div>
                <?php else: ?>
                    <?php foreach ($recentDocs as $d): ?>
                        <?php
                            $ext = strtolower(pathinfo($d['file_name'] ?? $d['title'], PATHINFO_EXTENSION));
                            $iconClass = dashFileIconClass($ext);
                        ?>
                        <div class="document-row">
                            <div class="file-type <?= $ext === 'pdf' ? 'pdf' : '' ?>">
                                <i class="bi <?= $iconClass ?>"></i>
                            </div>
                            <div class="document-info">
                                <a class="document-name" href="document_view.php?id=<?= (int)$d['id'] ?>">
                                    <?= dashEscape($d['title']) ?>
                                </a>
                                <div class="document-date">
                                    <span class="doc-code"><?= dashEscape($d['document_id']) ?></span> &bull; 
                                    <?= dashEscape(dashDate($d['created_at'])) ?> &bull; 
                                    <?= dashEscape($d['uploader_name']) ?>
                                    <?php if (!empty($d['department_name'])): ?>
                                        &bull; <span class="badge-subtle"><?= dashEscape($d['department_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="status-badge <?= dashStatusClass($d['status']) ?>">
                                <?= dashEscape($d['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <!-- RIGHT COLUMN -->
    <div>

        <!-- USER ADMINISTRATION & STATUS OVERVIEW -->
        <section class="panel mb-4">
            <div class="panel-header">
                <h3>
                    <i class="bi bi-people-fill text-primary"></i>
                    User Accounts & Archive Status
                </h3>
                <a href="users.php">Manage Users &rarr;</a>
            </div>

            <div class="dept-summary-list">
                <div class="dept-summary-row">
                    <div class="dept-summary-info">
                        <span class="dept-name"><i class="bi bi-person-check-fill text-success"></i> Active Users</span>
                        <span class="dept-counts">Authorized campus members with full access</span>
                    </div>
                    <a href="users.php?status=Active" class="badge bg-success text-decoration-none py-2 px-3 fs-6">
                        <?= (int)($userStatusCounts['Active'] ?? 0) ?>
                    </a>
                </div>

                <div class="dept-summary-row">
                    <div class="dept-summary-info">
                        <span class="dept-name"><i class="bi bi-hourglass-split text-warning"></i> Pending User Approvals</span>
                        <span class="dept-counts">New user signups requiring administrator verification</span>
                    </div>
                    <a href="users.php?status=Pending" class="badge bg-warning text-dark text-decoration-none py-2 px-3 fs-6">
                        <?= (int)($userStatusCounts['Pending'] ?? 0) ?>
                    </a>
                </div>

                <div class="dept-summary-row">
                    <div class="dept-summary-info">
                        <span class="dept-name"><i class="bi bi-person-x-fill text-secondary"></i> Inactive Users</span>
                        <span class="dept-counts">Deactivated user accounts</span>
                    </div>
                    <a href="users.php?status=Inactive" class="badge bg-secondary text-decoration-none py-2 px-3 fs-6">
                        <?= (int)($userStatusCounts['Inactive'] ?? 0) ?>
                    </a>
                </div>

                <div class="dept-summary-row">
                    <div class="dept-summary-info">
                        <span class="dept-name"><i class="bi bi-archive-fill text-dark"></i> Archived / Soft-Deleted Users</span>
                        <span class="dept-counts">Preserved accounts; documents and history retained</span>
                    </div>
                    <a href="users.php?status=Archived" class="badge bg-dark text-decoration-none py-2 px-3 fs-6">
                        <?= (int)($userStatusCounts['Archived'] ?? 0) ?>
                    </a>
                </div>
            </div>
        </section>

        <!-- DEPARTMENT MONITORING -->
        <section class="panel mb-4">
            <div class="panel-header">
                <h3>
                    <i class="bi bi-buildings-fill text-primary"></i>
                    Department Monitoring
                </h3>
                <a href="reports.php">Reports &rarr;</a>
            </div>

            <div class="dept-summary-list">
                <?php if (empty($deptBreakdown)): ?>
                    <div class="empty-state">No departments registered.</div>
                <?php else: ?>
                    <?php foreach ($deptBreakdown as $dept): ?>
                        <div class="dept-summary-row">
                            <div class="dept-summary-info">
                                <span class="dept-name"><?= dashEscape($dept['department_name']) ?></span>
                                <span class="dept-counts">
                                    <strong><?= (int)$dept['total_count'] ?></strong> document<?= (int)$dept['total_count'] === 1 ? '' : 's' ?>
                                    &bull; <span class="text-success"><?= (int)$dept['approved_count'] ?> approved</span>
                                </span>
                            </div>
                            <a href="documents.php?v=all&dept=<?= (int)$dept['id'] ?>" class="dept-link" title="Filter All Documents by <?= dashEscape($dept['department_name']) ?>">
                                <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- CATEGORY MONITORING -->
        <section class="panel">
            <div class="panel-header">
                <h3>
                    <i class="bi bi-tags-fill text-primary"></i>
                    Category Monitoring
                </h3>
                <a href="reports.php">Reports &rarr;</a>
            </div>

            <div class="dept-summary-list">
                <?php if (empty($catBreakdown)): ?>
                    <div class="empty-state">No categories registered.</div>
                <?php else: ?>
                    <?php foreach ($catBreakdown as $cat): ?>
                        <div class="dept-summary-row">
                            <div class="dept-summary-info">
                                <span class="dept-name"><?= dashEscape($cat['category_name']) ?></span>
                                <span class="dept-counts">
                                    <strong><?= (int)$cat['total_count'] ?></strong> document<?= (int)$cat['total_count'] === 1 ? '' : 's' ?>
                                </span>
                            </div>
                            <a href="documents.php?v=all&category=<?= (int)$cat['id'] ?>" class="dept-link" title="Filter All Documents by <?= dashEscape($cat['category_name']) ?>">
                                <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>

</div>
