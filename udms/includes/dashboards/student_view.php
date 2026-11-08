<?php
// includes/dashboards/student_view.php
// Student Academic Document Center View
?>

<!-- ==========================================
     STUDENT ACADEMIC IDENTITY & QUOTA BANNER
========================================== -->
<div class="role-context-banner student-context">
    <div class="context-badge-icon student">
        <i class="bi bi-person-badge-fill"></i>
    </div>
    <div class="context-badge-text">
        <span class="context-dept">
            <?= dashEscape($studentDept['department_name'] ?? 'College Department') ?>
        </span>
        <span class="context-sub">
            Student ID: <strong><?= dashEscape($u['id_number'] ?: 'STU-'.$u['id']) ?></strong> &bull; 
            Plan: <span class="badge bg-<?= $stg['tier'] === 'Pro' ? 'warning text-dark' : 'secondary' ?>"><?= dashEscape($stg['tier']) ?></span>
            &bull; <?= $stg['size_formatted'] ?> Used
        </span>
    </div>
    <div class="context-action-group">
        <a href="document_form.php?mode=upload" class="btn-context-action">
            <i class="bi bi-cloud-arrow-up-fill"></i> Upload New Document
        </a>
    </div>
</div>

<!-- ==========================================
     STUDENT STORAGE QUOTA CARD
========================================== -->
<section class="student-storage-panel">
    <div class="storage-panel-body">
        <div class="storage-info-left">
            <div class="storage-icon <?= $stg['tier'] === 'Pro' ? 'pro' : 'free' ?>">
                <i class="bi bi-<?= $stg['tier'] === 'Pro' ? 'star-fill' : 'hdd-network-fill' ?>"></i>
            </div>
            <div>
                <div class="storage-title">
                    Storage Quota &bull; <?= dashEscape($stg['tier']) ?> Plan
                    <?php if ($stg['tier'] === 'Pro'): ?>
                        <span class="badge-pro-inline"><i class="bi bi-award-fill"></i> PRO ACTIVE</span>
                    <?php endif; ?>
                </div>
                <div class="storage-subtitle">
                    <strong><?= (int)$stg['count'] ?> of <?= (int)$stg['limit'] ?></strong> upload slots used
                    (<?= (int)$stg['remaining'] ?> slot<?= $stg['remaining'] === 1 ? '' : 's' ?> remaining)
                    &bull; <?= $stg['size_formatted'] ?> total uploaded
                </div>
            </div>
        </div>
        <div class="storage-info-right">
            <div class="storage-bar-wrapper">
                <div class="storage-bar-track">
                    <div class="storage-bar-fill <?= $stg['percent'] >= 90 ? 'critical' : ($stg['percent'] >= 75 ? 'warning' : 'normal') ?>"
                         style="width: <?= (int)$stg['percent'] ?>%;"></div>
                </div>
                <span class="storage-percent-label"><?= (int)$stg['percent'] ?>%</span>
            </div>
            <a href="subscription.php" class="btn-storage-manage <?= $stg['tier'] === 'Pro' ? 'manage' : 'upgrade' ?>">
                <?= $stg['tier'] === 'Pro' ? '<i class="bi bi-gear-fill"></i> Manage Plan' : '<i class="bi bi-stars"></i> Upgrade to Pro' ?>
            </a>
        </div>
    </div>
</section>

<!-- ==========================================
     ACTION REQUIRED ALERT BANNER (REVISIONS / REJECTIONS)
========================================== -->
<?php if (!empty($studentActionItems)): ?>
    <section class="action-required-panel">
        <div class="action-req-header">
            <div class="action-req-title">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <span>Action Required: You have <?= count($studentActionItems) ?> document<?= count($studentActionItems) > 1 ? 's' : '' ?> returned for revision or attention</span>
            </div>
        </div>
        <div class="action-req-list">
            <?php foreach ($studentActionItems as $item): ?>
                <div class="action-req-item">
                    <div class="action-req-body">
                        <div class="action-req-doc-title">
                            <span class="doc-code"><?= dashEscape($item['document_id']) ?></span> &bull; 
                            <strong><?= dashEscape($item['title']) ?></strong>
                            <span class="status-badge <?= dashStatusClass($item['status']) ?>">
                                <?= dashEscape($item['status']) ?>
                            </span>
                        </div>
                        <?php if (!empty($item['review_comments'])): ?>
                            <div class="action-req-feedback">
                                <i class="bi bi-chat-quote-fill"></i>
                                <span><strong>Reviewer Feedback:</strong> "<?= dashEscape($item['review_comments']) ?>"</span>
                                <?php if (!empty($item['reviewer_name'])): ?>
                                    <small>&mdash; <?= dashEscape($item['reviewer_name']) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="action-req-cta">
                        <a href="document_form.php?id=<?= (int)$item['id'] ?>" class="btn-action-revise">
                            <i class="bi bi-pencil-fill"></i> Update & Resubmit
                        </a>
                        <a href="document_view.php?id=<?= (int)$item['id'] ?>" class="btn-action-view">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- ==========================================
     STUDENT KPI METRIC CARDS
========================================== -->
<section class="stats-grid">

    <!-- TOTAL SUBMISSIONS -->
    <a href="documents.php?v=mine" class="stat-card blue">
        <div class="stat-icon">
            <?= dashIcon('file', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($studentTotalDocs) ?></div>
            <div class="stat-label">Total Uploads</div>
        </div>
    </a>

    <!-- APPROVED -->
    <a href="documents.php?v=mine&status=Approved" class="stat-card green">
        <div class="stat-icon">
            <?= dashIcon('check', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($studentApprovedCount) ?></div>
            <div class="stat-label">Approved Submissions</div>
        </div>
    </a>

    <!-- PENDING REVIEW -->
    <a href="documents.php?v=pending" class="stat-card yellow">
        <div class="stat-icon">
            <?= dashIcon('clock', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($studentPendingCount) ?></div>
            <div class="stat-label">Pending Faculty Review</div>
        </div>
    </a>

    <!-- ACTION REQUIRED / REVISION -->
    <a href="documents.php?v=mine" class="stat-card <?= $studentActionRequiredCount > 0 ? 'red' : 'purple' ?>">
        <div class="stat-icon">
            <?= dashIcon($studentActionRequiredCount > 0 ? 'x' : 'share', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number">
                <?= number_format($studentActionRequiredCount) ?>
                <?php if ($studentActionRequiredCount > 0): ?>
                    <span class="pulse-indicator pulse-red" title="Needs attention"></span>
                <?php endif; ?>
            </div>
            <div class="stat-label">Needs Revision / Rejected</div>
        </div>
    </a>

</section>

<!-- ==========================================
     STUDENT QUICK ACTION HUB
========================================== -->
<div class="quick-action-bar">
    <div class="quick-action-title">
        <i class="bi bi-mortarboard-fill"></i> Student Shortcuts
    </div>
    <div class="quick-action-buttons">
        <a href="document_form.php?mode=upload" class="qa-btn qa-btn-primary">
            <i class="bi bi-cloud-arrow-up-fill"></i> Upload Requirement / File
        </a>
        <a href="documents.php?v=submissions" class="qa-btn">
            <i class="bi bi-send-check"></i> Track Submissions
        </a>
        <a href="documents.php?v=shared" class="qa-btn">
            <i class="bi bi-folder-symlink"></i> Shared Course Materials
        </a>
        <a href="subscription.php" class="qa-btn">
            <i class="bi bi-gem"></i> Storage Plan (<?= dashEscape($stg['tier']) ?>)
        </a>
        <a href="notifications.php" class="qa-btn">
            <i class="bi bi-bell"></i> Alerts & Feedback
        </a>
    </div>
</div>

<!-- ==========================================
     STUDENT CONTENT GRID
========================================== -->
<div class="content-grid">

    <!-- LEFT COLUMN -->
    <div>

        <!-- MY RECENT SUBMISSIONS -->
        <section class="panel mb-4">
            <div class="panel-header">
                <h3>
                    <?= dashIcon('file') ?>
                    My Recent Submissions
                </h3>
                <a href="documents.php?v=submissions">Track All &rarr;</a>
            </div>

            <div class="document-list">
                <?php if (empty($studentRecentDocs)): ?>
                    <div class="empty-state">
                        <i class="bi bi-cloud-arrow-up empty-state-icon"></i>
                        <p class="empty-title">No submissions yet.</p>
                        <p class="empty-desc">Upload your course requirements, clearances, or forms to get started.</p>
                        <a href="document_form.php?mode=upload" class="empty-action">Upload First Document</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($studentRecentDocs as $d): ?>
                        <?php
                            $ext = strtolower(pathinfo($d['file_name'] ?? $d['title'], PATHINFO_EXTENSION));
                            $iconClass = dashFileIconClass($ext);
                            $isEditable = in_array($d['status'], ['Draft', 'Returned for Revision', 'Rejected'], true);
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
                                    v<?= dashEscape($d['current_version']) ?> &bull; 
                                    <?= dashEscape(dashDate($d['updated_at'] ?: $d['created_at'])) ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="status-badge <?= dashStatusClass($d['status']) ?>">
                                    <?= dashEscape($d['status']) ?>
                                </span>
                                <?php if ($isEditable): ?>
                                    <a href="document_form.php?id=<?= (int)$d['id'] ?>" class="btn-action-edit-tiny" title="Edit / Re-submit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- MATERIALS & SYLLABI SHARED WITH ME -->
        <section class="panel">
            <div class="panel-header">
                <h3>
                    <?= dashIcon('share') ?>
                    Materials & Syllabi Shared With Me
                </h3>
                <a href="documents.php?v=shared">View All &rarr;</a>
            </div>

            <div class="document-list">
                <?php if (empty($studentSharedDocs)): ?>
                    <div class="empty-state">
                        <i class="bi bi-folder-check empty-state-icon"></i>
                        <p class="empty-desc">No shared course materials or syllabi available right now.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($studentSharedDocs as $d): ?>
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
                                    Shared by <strong><?= dashEscape($d['owner_name']) ?></strong> &bull; 
                                    <?= dashEscape(dashDate($d['shared_at'])) ?>
                                </div>
                            </div>
                            <a href="document_view.php?id=<?= (int)$d['id'] ?>" class="btn-table-icon" title="View Document">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <!-- RIGHT COLUMN -->
    <div>

        <!-- NOTIFICATIONS & REVIEWER COMMENTS -->
        <section class="panel mb-4">
            <div class="panel-header">
                <h3>
                    <?= dashIcon('bell') ?>
                    Review Feedback & Notifications
                </h3>
                <a href="notifications.php">All &rarr;</a>
            </div>

            <div class="announcement-list">
                <?php if (empty($studentNotifications)): ?>
                    <div class="empty-state">No notifications.</div>
                <?php else: ?>
                    <?php foreach ($studentNotifications as $n): ?>
                        <div class="announcement">
                            <div class="announcement-icon">
                                <?= dashIcon('bell', 20) ?>
                            </div>
                            <div class="announcement-body">
                                <a class="document-name" href="notifications.php?go=<?= (int)$n['id'] ?>">
                                    <?= dashEscape($n['message']) ?>
                                </a>
                                <div class="announcement-date">
                                    <?= dashEscape(dashDate($n['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- SUBMISSION TIPS & GUIDELINES -->
        <section class="panel">
            <div class="panel-header">
                <h3>
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    Student Submission Guide
                </h3>
            </div>

            <div class="guide-box">
                <div class="guide-item">
                    <i class="bi bi-file-earmark-check-fill text-success"></i>
                    <div>
                        <strong>Accepted File Formats</strong>
                        <p class="text-muted small mb-0">PDF, Word (DOCX/DOC), Excel (XLSX), Images (PNG, JPG). Max file size is 10 MB.</p>
                    </div>
                </div>
                <div class="guide-item">
                    <i class="bi bi-clock-history text-warning"></i>
                    <div>
                        <strong>Faculty Review Process</strong>
                        <p class="text-muted small mb-0">After uploading, click "Submit for Review". Your department faculty will verify and approve your document.</p>
                    </div>
                </div>
                <div class="guide-item">
                    <i class="bi bi-stars text-primary"></i>
                    <div>
                        <strong>Need More Space?</strong>
                        <p class="text-muted small mb-0">Free tier provides 20 file slots. Upgrade to Pro for 100 slots and priority processing.</p>
                    </div>
                </div>
            </div>
        </section>

    </div>

</div>

