<?php
// includes/dashboards/faculty_view.php
// Faculty & Academic Staff Portal View
?>

<!-- ==========================================
     FACULTY GRADE LEVEL BADGE
========================================== -->
<div class="role-context-banner">
    <div class="context-badge-icon">
        <i class="bi bi-mortarboard-fill"></i>
    </div>
    <div class="context-badge-text">
        <span class="context-dept">
            <?= dashEscape($facultyDept['department_name'] ?? 'Grade Level') ?>
        </span>
        <span class="context-sub">
            Academic Staff &bull; ID: <?= dashEscape($u['id_number'] ?: 'FAC-'.$u['id']) ?> &bull; Grade Level Reviewer
        </span>
    </div>
    <?php if ($pendingStudentRequestsCount > 0): ?>
        <a href="documents.php?v=student_requests" class="context-action-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= (int)$pendingStudentRequestsCount ?> Student Submission<?= $pendingStudentRequestsCount > 1 ? 's' : '' ?> Require Review</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    <?php endif; ?>
</div>

<!-- ==========================================
     FACULTY KPI METRIC CARDS
========================================== -->
<section class="stats-grid">

    <!-- PENDING STUDENT REQUESTS -->
    <a href="documents.php?v=student_requests" class="stat-card yellow">
        <div class="stat-icon">
            <?= dashIcon('clock', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number">
                <?= number_format($pendingStudentRequestsCount) ?>
                <?php if ($pendingStudentRequestsCount > 0): ?>
                    <span class="pulse-indicator" title="Pending reviews"></span>
                <?php endif; ?>
            </div>
            <div class="stat-label">Student Requests to Review</div>
        </div>
    </a>

    <!-- GRADE LEVEL DOCUMENTS -->
    <a href="documents.php?v=all" class="stat-card blue">
        <div class="stat-icon">
            <?= dashIcon('folder', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($deptDocsCount) ?></div>
            <div class="stat-label">Grade Level Documents</div>
        </div>
    </a>

    <!-- MY UPLOADS / SYLLABI -->
    <a href="documents.php?v=mine" class="stat-card green">
        <div class="stat-icon">
            <?= dashIcon('file', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($myDocsCount) ?></div>
            <div class="stat-label">My Uploaded Documents</div>
        </div>
    </a>

    <!-- SHARED WITH ME -->
    <a href="documents.php?v=shared" class="stat-card purple">
        <div class="stat-icon">
            <?= dashIcon('share', 34) ?>
        </div>
        <div class="stat-details">
            <div class="stat-number"><?= number_format($sharedWithMeCount) ?></div>
            <div class="stat-label">Shared With Me</div>
        </div>
    </a>

</section>

<!-- ==========================================
     FACULTY QUICK ACTION HUB
========================================== -->
<div class="quick-action-bar">
    <div class="quick-action-title">
        <i class="bi bi-briefcase-fill"></i> Faculty Actions
    </div>
    <div class="quick-action-buttons">
        <a href="documents.php?v=student_requests" class="qa-btn qa-btn-warning">
            <i class="bi bi-clipboard-check"></i> Review Student Requests (<?= (int)$pendingStudentRequestsCount ?>)
        </a>
        <a href="document_form.php?mode=create" class="qa-btn">
            <i class="bi bi-file-earmark-plus"></i> Create Document / Syllabus
        </a>
        <a href="document_form.php?mode=upload" class="qa-btn">
            <i class="bi bi-cloud-arrow-up"></i> Upload Course File
        </a>
        <a href="documents.php?v=shared" class="qa-btn">
            <i class="bi bi-share"></i> Shared Documents
        </a>
        <a href="notifications.php" class="qa-btn">
            <i class="bi bi-bell"></i> Notifications
        </a>
    </div>
</div>

<!-- ==========================================
     FACULTY CONTENT GRID
========================================== -->
<div class="content-grid">

    <!-- LEFT COLUMN -->
    <div>

        <!-- PENDING STUDENT REQUESTS REVIEW QUEUE -->
        <section class="panel mb-4">
            <div class="panel-header">
                <h3>
                    <i class="bi bi-clipboard-check-fill text-warning"></i>
                    Student Requests Awaiting Review
                    <?php if ($pendingStudentRequestsCount > 0): ?>
                        <span class="badge-count-warning"><?= (int)$pendingStudentRequestsCount ?></span>
                    <?php endif; ?>
                </h3>
                <a href="documents.php?v=student_requests">View All &rarr;</a>
            </div>

            <div class="document-list">
                <?php if (empty($pendingStudentRequests)): ?>
                    <div class="empty-state">
                        <i class="bi bi-check-circle empty-state-icon text-success"></i>
                        <p class="empty-title">All Student Submissions Reviewed</p>
                        <p class="empty-desc">There are no pending student documents in your grade level waiting for review.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingStudentRequests as $d): ?>
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
                                    Student: <strong><?= dashEscape($d['student_name']) ?></strong>
                                    <?php if (!empty($d['id_number'])): ?>
                                        (<?= dashEscape($d['id_number']) ?>)
                                    <?php endif; ?>
                                    &bull; <?= dashEscape(dashDate($d['created_at'])) ?>
                                </div>
                            </div>
                            <a class="action-btn-review" href="document_view.php?id=<?= (int)$d['id'] ?>">
                                <i class="bi bi-check2-square"></i> Review
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- MY RECENT UPLOADS / SYLLABI -->
        <section class="panel">
            <div class="panel-header">
                <h3>
                    <?= dashIcon('file') ?>
                    My Uploaded Documents & Syllabi
                </h3>
                <a href="documents.php?v=mine">View All (<?= (int)$myDocsCount ?>) &rarr;</a>
            </div>

            <div class="document-list">
                <?php if (empty($facultyRecentDocs)): ?>
                    <div class="empty-state">
                        <i class="bi bi-folder-plus empty-state-icon"></i>
                        <p class="empty-title">No documents uploaded yet.</p>
                        <a href="document_form.php?mode=upload" class="empty-action">Upload course syllabus or policy</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($facultyRecentDocs as $d): ?>
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
                                    v<?= dashEscape($d['current_version']) ?> &bull; 
                                    <?= dashEscape(dashDate($d['updated_at'] ?: $d['created_at'])) ?>
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

        <!-- SHARED WITH ME -->
        <section class="panel mb-4">
            <div class="panel-header">
                <h3>
                    <?= dashIcon('share') ?>
                    Shared With Me & Faculty Resources
                </h3>
                <a href="documents.php?v=shared">View All &rarr;</a>
            </div>

            <div class="document-list">
                <?php if (empty($facultySharedDocs)): ?>
                    <div class="empty-state">
                        <i class="bi bi-people empty-state-icon"></i>
                        <p class="empty-desc">No shared documents from colleagues or administration yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($facultySharedDocs as $d): ?>
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
                                    <span class="badge bg-light text-dark border me-1" style="font-size: 10px;"><?= dashEscape($d['department_name'] ?? 'All Grade Levels') ?></span>
                                    <span class="badge bg-light text-secondary border me-1" style="font-size: 10px;"><i class="bi bi-<?= ($d['access_level']??'')==='Public'?'globe':(($d['access_level']??'')==='Private'?'lock-fill':'building') ?>"></i> <?= dashEscape($d['access_level'] ?? 'Shared') ?></span>
                                    Shared by <strong><?= dashEscape($d['owner_name']) ?></strong> &bull; 
                                    <?= dashEscape(dashDate($d['shared_at'])) ?>
                                </div>
                            </div>
                            <a href="document_view.php?id=<?= (int)$d['id'] ?>" class="btn-table-icon" title="View Document">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- RECENT NOTIFICATIONS -->
        <section class="panel">
            <div class="panel-header">
                <h3>
                    <?= dashIcon('bell') ?>
                    Notifications & Alerts
                </h3>
                <a href="notifications.php">All &rarr;</a>
            </div>

            <div class="announcement-list">
                <?php if (empty($facultyNotifications)): ?>
                    <div class="empty-state">No notifications.</div>
                <?php else: ?>
                    <?php foreach ($facultyNotifications as $n): ?>
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

    </div>

</div>

