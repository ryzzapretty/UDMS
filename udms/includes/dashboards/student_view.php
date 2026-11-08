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
            <i class="bi bi-mortarboard-fill me-1"></i> Grade Level: <?= dashEscape($studentDept['department_name'] ?? 'Senior High') ?>
        </span>
        <span class="context-sub">
            Student ID: <strong><?= dashEscape($u['id_number'] ?: 'STU-'.$u['id']) ?></strong> &bull; 
            Grade Level: <strong><?= dashEscape($studentDept['department_name'] ?? 'Senior High') ?></strong> &bull;
            Plan: <span class="badge bg-<?= $stg['tier'] === 'Pro' ? 'warning text-dark' : 'secondary' ?>"><?= dashEscape($stg['tier']) ?></span>
            &bull; <?= $stg['size_formatted'] ?> Used
        </span>
    </div>
    <div class="context-action-group">
        <a href="#submission-deadlines" class="btn-context-action" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #b91c1c;">
            <i class="bi bi-calendar-check-fill text-danger"></i> Deadlines Calendar
        </a>
        <?php if (($studentDept['department_name'] ?? '') !== 'Basic Education' && (int)($u['department_id'] ?? 0) !== 1): ?>
        <a href="document_form.php?mode=upload" class="btn-context-action">
            <i class="bi bi-cloud-arrow-up-fill"></i> Upload New Document
        </a>
        <?php endif; ?>
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
        <a href="#submission-deadlines" class="qa-btn" style="border-color:#fca5a5;background:#fff1f2;color:#b91c1c;font-weight:600;">
            <i class="bi bi-calendar-event-fill text-danger"></i> Deadlines Calendar
        </a>
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
     ACADEMIC SUBMISSION DEADLINES BY DOCUMENT TYPE
========================================== -->
<section class="panel mb-4" id="submission-deadlines" style="border: 1px solid #fee2e2;">
    <div class="panel-header" style="background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border-bottom: 1px solid #fecaca; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; height: auto; padding: 14px 20px;">
        <div>
            <h3 class="mb-0" style="color: #991b1b; font-size: 16px;">
                <i class="bi bi-calendar-check-fill text-danger"></i>
                Academic Submission Deadlines by Document Type
            </h3>
            <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                Current Academic Year 2026–2027 &bull; Official institutional cutoff dates for student submissions
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 11px;">
                <i class="bi bi-clock-history"></i> Semester Cutoffs Active
            </span>
            <a href="document_form.php?mode=upload" class="btn btn-sm btn-primary py-1 px-3" style="font-size: 12px;">
                <i class="bi bi-cloud-arrow-up-fill"></i> Upload Requirement
            </a>
        </div>
    </div>

    <div class="p-3">
        <?php
        $studentSubmittedTypes = [];
        $subRows = q("SELECT document_type, id, status, is_late, submission_timing, created_at FROM documents WHERE uploaded_by = ? AND status <> 'Archived' ORDER BY id DESC", [$me])->fetchAll();
        foreach ($subRows as $sr) {
            if (!isset($studentSubmittedTypes[$sr['document_type']])) {
                $studentSubmittedTypes[$sr['document_type']] = $sr;
            }
        }
        $deadlines = DOCUMENT_DEADLINES;
        $now = time();
        $missingCount = 0;
        foreach ($deadlines as $dtKey => $info) {
            $ts = strtotime($info['deadline_date']);
            if ($now > $ts && !isset($studentSubmittedTypes[$dtKey])) {
                $missingCount++;
            }
        }
        ?>

        <?php if ($missingCount > 0): ?>
        <div class="alert alert-danger border border-danger-subtle d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3 mb-3" id="missing-requirements" style="background: #fff5f5;">
            <div>
                <i class="bi bi-exclamation-octagon-fill text-danger fs-5 me-2"></i>
                <strong>Action Needed &bull; Missing / Overdue Requirements (<?= $missingCount ?>):</strong> You have <?= $missingCount ?> requirement<?= $missingCount > 1 ? 's' : '' ?> past the official submission deadline. You are given the chance to submit now; your professor will review and mark them as <strong>Delayed / Late Submitted</strong>.
            </div>
            <a href="document_form.php?mode=upload&late=1" class="btn btn-sm btn-danger py-1 px-3 text-nowrap" style="font-size: 12px;">
                <i class="bi bi-cloud-arrow-up-fill"></i> Submit Overdue Requirements
            </a>
        </div>
        <?php endif; ?>

        <!-- Explanatory note on where deadlines can be found -->
        <div class="alert alert-light border border-info-subtle d-flex align-items-center gap-2 py-2 px-3 mb-3" style="background: #f0f9ff; font-size: 13px;">
            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
            <div>
                <strong>Where to see submission deadlines:</strong> You can review the complete deadline schedule in this table at any time, or check the <strong>real-time deadline notice</strong> displayed automatically whenever you choose a document type on the <a href="document_form.php?mode=upload" class="fw-semibold text-primary">Upload Document</a> page.
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 180px;">Document Type</th>
                        <th style="min-width: 150px;">Category</th>
                        <th style="min-width: 170px;">Applicable Period</th>
                        <th style="min-width: 190px;">Submission Deadline</th>
                        <th style="min-width: 140px;">Status</th>
                        <th class="text-end" style="min-width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($deadlines as $dtKey => $info):
                        $ts = strtotime($info['deadline_date']);
                        $diffDays = ceil(($ts - $now) / 86400);
                        $isPast = $diffDays < 0;
                        $badgeText = $isPast ? 'Cutoff Passed' : ($diffDays === 0 ? 'Due Today' : ($diffDays <= 7 ? "$diffDays days remaining" : "$diffDays days remaining"));
                        $hasSub = isset($studentSubmittedTypes[$dtKey]);
                        $subRecord = $hasSub ? $studentSubmittedTypes[$dtKey] : null;
                        $isSubLate = $hasSub && (!empty($subRecord['is_late']) || ($subRecord['submission_timing'] ?? '') === 'Delayed / Late Submitted');
                    ?>
                    <tr class="<?= ($isPast && !$hasSub) ? 'table-danger-subtle' : '' ?>">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: <?= $isPast && !$hasSub ? '#fecaca' : '#fee2e2' ?>; color: #dc2626; flex-shrink: 0;">
                                    <i class="bi bi-<?= dashEscape($info['icon']) ?>"></i>
                                </div>
                                <div>
                                    <strong class="text-dark d-block"><?= dashEscape($info['title']) ?></strong>
                                    <span class="text-muted small" style="font-size: 11px;"><?= dashEscape($info['description']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= dashEscape($info['category']) ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold text-secondary"><?= dashEscape($info['term']) ?></div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= dashEscape($info['deadline_formatted']) ?></div>
                            <span class="badge bg-<?= $isPast ? 'danger' : ($diffDays <= 5 ? 'danger' : ($diffDays <= 14 ? 'warning text-dark' : 'secondary')) ?>" style="font-size: 10px;">
                                <i class="bi bi-clock"></i> <?= dashEscape($badgeText) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($hasSub): ?>
                                <?php if ($isSubLate): ?>
                                    <span class="badge bg-warning text-dark border border-warning px-2 py-1" title="Submitted past deadline">
                                        <i class="bi bi-clock-history"></i> Delayed / Late Submitted
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check2-circle"></i> Submitted (<?= dashEscape($subRecord['status']) ?>)
                                    </span>
                                <?php endif; ?>
                            <?php elseif ($isPast): ?>
                                <span class="badge bg-danger text-white px-2 py-1">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Missing / Overdue
                                </span>
                            <?php else: ?>
                                <span class="badge bg-<?= dashEscape($info['status_class']) ?>-subtle text-<?= dashEscape($info['status_class']) ?> border border-<?= dashEscape($info['status_class']) ?>-subtle px-2 py-1">
                                    <i class="bi bi-circle-fill" style="font-size: 7px; vertical-align: middle;"></i> <?= dashEscape($info['status']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($hasSub): ?>
                                <a href="document_view.php?id=<?= (int)$subRecord['id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 12px;">
                                    <i class="bi bi-eye"></i> View File
                                </a>
                            <?php elseif ($isPast): ?>
                                <a href="document_form.php?mode=upload&type=<?= urlencode($dtKey) ?>&late=1" class="btn btn-sm btn-danger py-1 px-2" style="font-size: 12px;" title="Submit overdue <?= dashEscape($info['title']) ?>">
                                    <i class="bi bi-cloud-arrow-up-fill"></i> Submit Late
                                </a>
                            <?php else: ?>
                                <a href="document_form.php?mode=upload&type=<?= urlencode($dtKey) ?>" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 12px;" title="Upload <?= dashEscape($info['title']) ?>">
                                    <i class="bi bi-cloud-upload"></i> Upload
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

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
                    <i class="bi bi-calendar-check-fill text-danger"></i>
                    <div>
                        <strong>Where to See Submission Deadlines?</strong>
                        <p class="text-muted small mb-0">Check the complete schedule in the <strong><a href="#submission-deadlines" class="text-primary text-decoration-none fw-semibold">Academic Submission Deadlines</a></strong> panel above, or check the live deadline notice when selecting document types on the <a href="document_form.php?mode=upload" class="text-decoration-none">Upload Form</a>.</p>
                    </div>
                </div>
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
                        <p class="text-muted small mb-0">After uploading, click "Submit for Review". Your grade level faculty will verify and approve your document.</p>
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

