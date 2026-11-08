<?php
require_once 'config.php';
require_role('Admin', 'Faculty', 'Student');

$u = me();
$r = $u['role'];
$me = (int)$u['id'];

// Allow Administrator to toggle and preview other dashboards without logging out
$previewRole = ($r === 'Admin' && isset($_GET['preview_role']) && in_array($_GET['preview_role'], ['Admin', 'Faculty', 'Student'], true))
    ? $_GET['preview_role']
    : $r;
$viewRole = $previewRole;
$unreadNotificationsCount = dashCount("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0", [$me]);

function dashCount($sql, $params = []) {
    return (int)q($sql, $params)->fetchColumn();
}

function dashEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function dashDate($date) {
    if (!$date) return '—';
    $time = strtotime($date);
    return $time ? date('M d, Y • h:i A', $time) : '—';
}

function dashStatusClass($status) {
    switch ($status) {
        case 'Approved':
            return 'approved';
        case 'Rejected':
            return 'rejected';
        case 'Pending Review':
            return 'pending';
        case 'Returned for Revision':
            return 'revision';
        case 'Archived':
            return 'archived';
        default:
            return 'draft';
    }
}

function dashFileIconClass($ext) {
    switch (strtolower((string)$ext)) {
        case 'pdf':
            return 'bi-file-earmark-pdf-fill';
        case 'doc':
        case 'docx':
            return 'bi-file-earmark-word-fill';
        case 'xls':
        case 'xlsx':
            return 'bi-file-earmark-excel-fill';
        case 'ppt':
        case 'pptx':
            return 'bi-file-earmark-slides-fill';
        case 'jpg':
        case 'jpeg':
        case 'png':
            return 'bi-file-earmark-image-fill';
        default:
            return 'bi-file-earmark-text-fill';
    }
}

function dashFormatBytes($bytes) {
    $bytes = (int)$bytes;
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}

function dashIcon($name, $size = 24) {
    $icons = [
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>',
        'upload' => '<path d="M12 16V3m-5 5 5-5 5 5M4 16v4h16v-4"/>',
        'folder' => '<path d="M3 7V5h7l2 3h9v12H3z"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 3"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3M4.9 4.9 7 7m10 10 2.1 2.1M19.1 4.9 17 7M7 17l-2.1 2.1"/>',
        'logout' => '<path d="M10 3H4v18h6M14 7l5 5-5 5m5-5H8"/>',
        'file' => '<path d="M6 2h8l5 5v15H6zM14 2v5h5M9 12h7M9 16h7"/>',
        'check' => '<path d="m4 12 5 5L20 6"/>',
        'x' => '<path d="M5 5 19 19M19 5 5 19"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 10h18c0-2-3-3-3-10M10 21h4"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v1"/>',
        'share' => '<circle cx="18" cy="5" r="2"/><circle cx="6" cy="12" r="2"/><circle cx="18" cy="19" r="2"/><path d="m8 11 8-5m-8 7 8 5"/>'
    ];

    return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' .
        ($icons[$name] ?? $icons['file']) .
        '</svg>';
}

/* ========================================================
   ROLE-SPECIFIC DATA RETRIEVAL
   ======================================================== */

if ($viewRole === 'Admin') {

    $totalDocs = dashCount("SELECT COUNT(*) FROM documents WHERE status <> 'Archived'");
    $approvedDocsCount = dashCount("SELECT COUNT(*) FROM documents WHERE status = 'Approved'");
    $activeUsersCount = dashCount("SELECT COUNT(*) FROM users WHERE status = 'Active'");
    $pendingUsersCount = dashCount("SELECT COUNT(*) FROM users WHERE status = 'Pending'");
    $archivedUsersCount = dashCount("SELECT COUNT(*) FROM users WHERE status = 'Archived'");
    $archivedDocsCount = dashCount("SELECT COUNT(*) FROM documents WHERE status = 'Archived'");
    $totalStorageBytes = (int)q("SELECT COALESCE(SUM(file_size), 0) FROM documents WHERE status <> 'Archived'")->fetchColumn();

    $pendingUsers = q(
        "SELECT u.*, d.department_name
         FROM users u
         LEFT JOIN departments d ON d.id = u.department_id
         WHERE u.status = 'Pending'
         ORDER BY u.id DESC
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

    $recentDocs = q(
        "SELECT d.*, u.full_name AS uploader_name, u.role AS uploader_role,
                dp.department_name, c.category_name
         FROM documents d
         JOIN users u ON u.id = d.uploaded_by
         LEFT JOIN departments dp ON dp.id = d.department_id
         LEFT JOIN categories c ON c.id = d.category_id
         WHERE d.status <> 'Archived'
         ORDER BY d.created_at DESC
         LIMIT 6"
    )->fetchAll(PDO::FETCH_ASSOC);

    $deptBreakdown = q(
        "SELECT dp.id, dp.department_name,
                COUNT(d.id) AS total_count,
                SUM(CASE WHEN d.status = 'Approved' THEN 1 ELSE 0 END) AS approved_count
         FROM departments dp
         LEFT JOIN documents d ON d.department_id = dp.id AND d.status <> 'Archived'
         GROUP BY dp.id, dp.department_name
         ORDER BY dp.id ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $catBreakdown = q(
        "SELECT c.id, c.category_name,
                COUNT(d.id) AS total_count,
                SUM(CASE WHEN d.status = 'Approved' THEN 1 ELSE 0 END) AS approved_count
         FROM categories c
         LEFT JOIN documents d ON d.category_id = c.id AND d.status <> 'Archived'
         GROUP BY c.id, c.category_name
         ORDER BY total_count DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $activities = q(
        "SELECT a.*, u.full_name, u.role
         FROM activity_logs a
         LEFT JOIN users u ON u.id = a.user_id
         ORDER BY a.id DESC
         LIMIT 6"
    )->fetchAll(PDO::FETCH_ASSOC);

    $userRoleCounts = q(
        "SELECT role, COUNT(*) AS c FROM users WHERE status='Active' GROUP BY role"
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    $userStatusCounts = q(
        "SELECT status, COUNT(*) AS c FROM users GROUP BY status"
    )->fetchAll(PDO::FETCH_KEY_PAIR);

} elseif ($viewRole === 'Faculty') {

    $deptId = (int)($u['department_id'] ?? 0);
    if ($deptId === 0 && $r === 'Admin') {
        $firstDept = q("SELECT id FROM departments LIMIT 1")->fetch();
        if ($firstDept) $deptId = (int)$firstDept['id'];
    }

    $facultyDept = null;
    if ($deptId > 0) {
        $facultyDept = q("SELECT * FROM departments WHERE id = ?", [$deptId])->fetch();
    }

    $pendingStudentRequestsCount = $deptId > 0 ? dashCount(
        "SELECT COUNT(*) FROM documents d
         JOIN users u ON u.id = d.uploaded_by
         WHERE d.status = 'Pending Review' AND u.role = 'Student' AND d.department_id = ?",
        [$deptId]
    ) : 0;

    $pendingStudentRequests = $deptId > 0 ? q(
        "SELECT d.*, u.full_name AS student_name, u.id_number, u.email AS student_email, c.category_name
         FROM documents d
         JOIN users u ON u.id = d.uploaded_by
         LEFT JOIN categories c ON c.id = d.category_id
         WHERE d.status = 'Pending Review' AND u.role = 'Student' AND d.department_id = ?
         ORDER BY d.created_at ASC
         LIMIT 6",
        [$deptId]
    )->fetchAll(PDO::FETCH_ASSOC) : [];

    $deptDocsCount = dashCount(
        "SELECT COUNT(*) FROM documents d
         WHERE d.status = 'Approved'
         AND (d.access_level = 'Public' OR d.access_level = 'Faculty Only' OR (d.access_level = 'Department' AND d.department_id = ?))",
        [$deptId]
    );

    $myDocsCount = dashCount(
        "SELECT COUNT(*) FROM documents WHERE uploaded_by = ? AND status <> 'Archived'",
        [$me]
    );

    $sharedWithMeCount = dashCount(
        "SELECT COUNT(DISTINCT d.id) FROM documents d
         LEFT JOIN document_shares ds ON ds.document_id = d.id
         WHERE d.status <> 'Archived'
         AND (
             ds.shared_with = ? OR ds.shared_by = ?
             OR d.access_level IN ('Public', 'Faculty Only')
             OR (d.access_level IN ('Private', 'Department') AND (ds.id IS NOT NULL OR d.status = 'Approved'))
         )",
        [$me, $me]
    );

    $facultyRecentDocs = q(
        "SELECT d.*, c.category_name
         FROM documents d
         LEFT JOIN categories c ON c.id = d.category_id
         WHERE d.uploaded_by = ? AND d.status <> 'Archived'
         ORDER BY d.updated_at DESC
         LIMIT 6",
        [$me]
    )->fetchAll(PDO::FETCH_ASSOC);

    $facultySharedDocs = q(
        "SELECT DISTINCT d.*, u.full_name AS owner_name, COALESCE(ds.created_at, d.updated_at, d.created_at) AS shared_at, c.category_name, dp.department_name
         FROM documents d
         JOIN users u ON u.id = d.uploaded_by
         LEFT JOIN document_shares ds ON ds.document_id = d.id
         LEFT JOIN categories c ON c.id = d.category_id
         LEFT JOIN departments dp ON dp.id = d.department_id
         WHERE d.status <> 'Archived'
         AND (
             ds.shared_with = ? OR ds.shared_by = ?
             OR d.access_level IN ('Public', 'Faculty Only')
             OR (d.access_level IN ('Private', 'Department') AND (ds.id IS NOT NULL OR d.status = 'Approved'))
         )
         ORDER BY shared_at DESC
         LIMIT 6",
        [$me, $me]
    )->fetchAll(PDO::FETCH_ASSOC);

    $facultyNotifications = q(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 5",
        [$me]
    )->fetchAll(PDO::FETCH_ASSOC);

} else { // Student

    $deptId = (int)($u['department_id'] ?? 0);
    if ($deptId === 0 && $r === 'Admin') {
        $firstDept = q("SELECT id FROM departments LIMIT 1")->fetch();
        if ($firstDept) $deptId = (int)$firstDept['id'];
    }

    $studentDept = null;
    if ($deptId > 0) {
        $studentDept = q("SELECT * FROM departments WHERE id = ?", [$deptId])->fetch();
    }

    $stg = student_storage_info($me);

    $studentTotalDocs = dashCount(
        "SELECT COUNT(*) FROM documents WHERE uploaded_by = ? AND status NOT IN ('Draft', 'Archived')",
        [$me]
    );
    $studentApprovedCount = dashCount(
        "SELECT COUNT(*) FROM documents WHERE uploaded_by = ? AND status = 'Approved'",
        [$me]
    );
    $studentPendingCount = dashCount(
        "SELECT COUNT(*) FROM documents WHERE uploaded_by = ? AND status = 'Pending Review'",
        [$me]
    );
    $studentActionRequiredCount = dashCount(
        "SELECT COUNT(*) FROM documents WHERE uploaded_by = ? AND status IN ('Returned for Revision', 'Rejected') AND status <> 'Approved'",
        [$me]
    );
    $studentSharedCount = dashCount(
        "SELECT COUNT(*) FROM document_shares WHERE shared_with = ?",
        [$me]
    );

    $studentActionItems = q(
        "SELECT d.*, dr.action AS review_action, dr.comments AS review_comments, dr.reviewed_at, u.full_name AS reviewer_name
         FROM documents d
         LEFT JOIN document_reviews dr ON dr.document_id = d.id AND dr.id = (
             SELECT MAX(id) FROM document_reviews WHERE document_id = d.id
         )
         LEFT JOIN users u ON u.id = dr.reviewer_id
         WHERE d.uploaded_by = ? AND d.status IN ('Returned for Revision', 'Rejected') AND d.status <> 'Approved'
         ORDER BY d.updated_at DESC
         LIMIT 4",
        [$me]
    )->fetchAll(PDO::FETCH_ASSOC);

    $studentRecentDocs = q(
        "SELECT d.*, c.category_name, dp.department_name
         FROM documents d
         LEFT JOIN categories c ON c.id = d.category_id
         LEFT JOIN departments dp ON dp.id = d.department_id
         WHERE d.uploaded_by = ? AND d.status <> 'Archived'
         ORDER BY d.updated_at DESC
         LIMIT 6",
        [$me]
    )->fetchAll(PDO::FETCH_ASSOC);

    $studentSharedDocs = q(
        "SELECT d.*, u.full_name AS owner_name, c.category_name, ds.created_at AS shared_at
         FROM document_shares ds
         JOIN documents d ON d.id = ds.document_id
         JOIN users u ON u.id = d.uploaded_by
         LEFT JOIN categories c ON c.id = d.category_id
         WHERE ds.shared_with = ? AND d.status <> 'Archived'
         ORDER BY ds.created_at DESC
         LIMIT 5",
        [$me]
    )->fetchAll(PDO::FETCH_ASSOC);

    $studentNotifications = q(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 5",
        [$me]
    )->fetchAll(PDO::FETCH_ASSOC);

}

$firstName = explode(' ', trim($u['full_name'] ?? 'User'))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= dashEscape($viewRole); ?> Dashboard | UDMS</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
/* ==========================================
   GLOBAL DESIGN VARIABLES
========================================== */
:root {
    --navy: #00265f;
    --dark: #001a46;
    --blue: #0065db;
    --gold: #ffd139;
    --light: #f3f8ff;
    --text: #09275d;
    --border: #d9e5f5;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f7fbff;
    color: var(--text);
}

a {
    text-decoration: none;
}

button {
    cursor: pointer;
    font-family: inherit;
}

/* ==========================================
   TOP HEADER
========================================== */
.top-header {
    height: 100px;
    background: linear-gradient(110deg, #001a45, #063779, #001c4c);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 3%;
    gap: 20px;
    position: relative;
    z-index: 30;
    border-bottom: 5px solid var(--gold);
}

.header-brand {
    display: flex;
    align-items: center;
    gap: 20px;
    color: white;
}

.header-brand img {
    width: 95px;
    height: 85px;
    object-fit: contain;
}

.brand-divider {
    width: 2px;
    height: 65px;
    background: white;
}

.header-brand h1 {
    font-size: 39px;
    letter-spacing: 1px;
    line-height: 1;
}

.header-brand p {
    font-size: 15px;
    margin-top: 6px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.mobile-menu {
    display: none;
    font-size: 28px;
    background: none;
    border: none;
    color: white;
}

/* ==========================================
   MAIN DASHBOARD LAYOUT & SIDEBAR
========================================== */
.dashboard-layout {
    display: flex;
    min-height: calc(100vh - 100px);
}

.dashboard-main {
    flex: 1;
    min-width: 0;
    padding: 24px 30px 40px;
}

/* ==========================================
   CAMPUS BANNER
========================================== */
.dashboard-banner {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    margin-bottom: 22px;
    min-height: 170px;
    background: linear-gradient(rgba(0, 26, 70, 0.72), rgba(0, 38, 95, 0.88)),
                url('assets/St. Clare College Campus Banner.png') center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 34px;
    box-shadow: 0 8px 24px rgba(0, 30, 80, 0.16);
}

.banner-info {
    display: flex;
    align-items: center;
    gap: 24px;
    color: white;
    position: relative;
    z-index: 3;
}

.banner-logo {
    width: 82px;
    height: 82px;
    object-fit: contain;
}

.banner-divider {
    width: 2px;
    height: 70px;
    background: rgba(255, 255, 255, 0.65);
}

.banner-text h2 {
    font-size: 26px;
    color: white;
    line-height: 1.15;
    font-weight: 800;
}

.banner-text p {
    margin-top: 6px;
    font-size: 14px;
    color: #ffd139;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.banner-wave {
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    line-height: 0;
    z-index: 2;
    opacity: 0.85;
}

/* ==========================================
   ADMIN PREVIEW SWITCHER
========================================== */
.admin-role-switcher {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    background: #ffffff;
    border: 1px solid #c7dcff;
    border-radius: 14px;
    padding: 10px 18px;
    margin-bottom: 20px;
    box-shadow: 0 3px 10px rgba(0, 45, 115, 0.06);
}

.ars-label {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--navy);
    display: flex;
    align-items: center;
    gap: 8px;
}

.ars-options {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.ars-pill {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 600;
    background: #f0f4fa;
    color: #3b506f;
    border: 1px solid #d2deec;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.ars-pill:hover {
    background: #e2ecf8;
    color: #002d73;
}

.ars-pill.active {
    background: #00265f;
    color: #ffd139;
    border-color: #00265f;
    box-shadow: 0 2px 6px rgba(0, 38, 95, 0.3);
}

/* ==========================================
   WELCOME ROW
========================================== */
.welcome-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 22px;
}

.welcome-row h2 {
    font-size: 24px;
    font-weight: 800;
    color: var(--navy);
}

.welcome-row p {
    font-size: 14px;
    color: #5d6f88;
    margin-top: 4px;
}

.role-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.3px;
}

.role-label-admin {
    background: #eaf1fb;
    color: #003780;
    border: 1px solid #b7d1f8;
}

.role-label-faculty {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.role-label-student {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fde68a;
}

/* ==========================================
   STATISTIC CARDS
========================================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 22px;
}

.stat-card {
    position: relative;
    min-height: 110px;
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 18px 20px;
    border-radius: 16px;
    background: white;
    border: 1px solid var(--border);
    box-shadow: 0 4px 12px rgba(0, 45, 115, 0.07);
    overflow: hidden;
    color: var(--text);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 45, 115, 0.14);
}

.stat-card::after {
    content: "";
    position: absolute;
    width: 110%;
    height: 55px;
    bottom: -30px;
    left: -5%;
    border-radius: 50% 50% 0 0;
    opacity: 0.14;
}

.stat-card.blue::after { background: #1685ff; }
.stat-card.green::after { background: #00b971; }
.stat-card.yellow::after { background: #ffb900; }
.stat-card.red::after { background: #f52e3c; }
.stat-card.purple::after { background: #8b5cf6; }

.stat-icon {
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    border-radius: 50%;
    color: white;
    display: grid;
    place-items: center;
}

.stat-icon svg {
    width: 32px;
    height: 32px;
}

.blue .stat-icon { background: linear-gradient(135deg, #1687ff, #0051c6); }
.green .stat-icon { background: linear-gradient(135deg, #08c67e, #00a85f); }
.yellow .stat-icon { background: linear-gradient(135deg, #ffc326, #ff9d00); }
.red .stat-icon { background: linear-gradient(135deg, #ff3647, #e51429); }
.purple .stat-icon { background: linear-gradient(135deg, #8b5cf6, #6366f1); }

.stat-details {
    position: relative;
    z-index: 2;
    min-width: 0;
}

.stat-number {
    font-size: 34px;
    font-weight: 900;
    line-height: 1;
    display: flex;
    align-items: center;
}

.stat-label {
    font-size: 13.5px;
    margin-top: 6px;
    color: #4b6282;
    font-weight: 600;
}

/* Pulse indicator for pending items */
.pulse-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #f59e0b;
    margin-left: 8px;
    vertical-align: middle;
    animation: pulse-warn 1.8s infinite;
}
.pulse-indicator.pulse-red {
    background: #ef4444;
    animation: pulse-danger 1.8s infinite;
}
@keyframes pulse-warn {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
    70% { transform: scale(1.15); box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}
@keyframes pulse-danger {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1.15); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* ==========================================
   ROLE CONTEXT BANNER
========================================== */
.role-context-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    background: linear-gradient(135deg, #ffffff 0%, #f4f8fe 100%);
    border: 1px solid var(--border);
    border-left: 5px solid #0065db;
    border-radius: 14px;
    padding: 16px 20px;
    margin-bottom: 22px;
    box-shadow: 0 4px 12px rgba(0, 45, 115, 0.05);
}

.role-context-banner.student-context {
    border-left-color: #ffd139;
}

.context-badge-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #eaf3ff;
    color: #0065db;
    display: grid;
    place-items: center;
    font-size: 22px;
    flex-shrink: 0;
}

.context-badge-icon.student {
    background: #fff8e1;
    color: #d97706;
}

.context-badge-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 200px;
}

.context-dept {
    font-size: 16px;
    font-weight: 700;
    color: var(--navy);
}

.context-sub {
    font-size: 13px;
    color: #61738e;
}

.context-action-alert {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff8e6;
    border: 1px solid #ffd139;
    color: #92400e;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: 0.2s;
}

.context-action-alert:hover {
    background: #fef08a;
    color: #78350f;
}

.btn-context-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0065db;
    color: #ffffff;
    padding: 9px 18px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: 0.2s;
}

.btn-context-action:hover {
    background: #004caa;
    color: #ffffff;
}

/* ==========================================
   SYSTEM META BAR & QUICK ACTIONS
========================================== */
.system-meta-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 20px;
    margin-bottom: 20px;
    box-shadow: 0 3px 8px rgba(0, 45, 115, 0.05);
    font-size: 13.5px;
    color: #3b506f;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.meta-item i {
    color: #0065db;
    font-size: 16px;
}

.meta-divider {
    width: 1px;
    height: 20px;
    background: #dbe4f0;
}

.quick-action-bar {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 16px 20px;
    margin-bottom: 22px;
    box-shadow: 0 4px 12px rgba(0, 45, 115, 0.06);
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.quick-action-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--navy);
    display: flex;
    align-items: center;
    gap: 8px;
}

.quick-action-title i {
    color: #eab308;
    font-size: 16px;
}

.quick-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.qa-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    background: #f0f6ff;
    color: #004caa;
    border: 1px solid #c7dcff;
    text-decoration: none;
    transition: all 0.2s ease;
}

.qa-btn:hover {
    background: #0065db;
    color: #ffffff;
    border-color: #0065db;
    transform: translateY(-1px);
}

.qa-btn-warning {
    background: #fffbeb;
    color: #b45309;
    border-color: #fde68a;
}

.qa-btn-warning:hover {
    background: #f59e0b;
    color: #ffffff;
    border-color: #f59e0b;
}

.qa-btn-primary {
    background: #0065db;
    color: #ffffff;
    border-color: #0065db;
}

.qa-btn-primary:hover {
    background: #004caa;
    color: #ffffff;
}

/* ==========================================
   STUDENT STORAGE QUOTA PANEL
========================================== */
.student-storage-panel {
    background: white;
    border-radius: 16px;
    border: 1px solid var(--border);
    padding: 18px 24px;
    margin-bottom: 22px;
    box-shadow: 0 4px 12px rgba(0, 45, 115, 0.08);
}

.storage-panel-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.storage-info-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.storage-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    font-size: 22px;
    flex-shrink: 0;
}

.storage-icon.free {
    background: #eaf4ff;
    color: #0058cf;
}

.storage-icon.pro {
    background: #fff6dd;
    color: #d97706;
}

.storage-title {
    font-weight: 700;
    font-size: 15px;
    color: var(--navy);
    display: flex;
    align-items: center;
    gap: 8px;
}

.badge-pro-inline {
    background: #fff6dd;
    color: #b45309;
    border: 1px solid #fde68a;
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 10px;
}

.storage-subtitle {
    font-size: 13px;
    color: #61738e;
    margin-top: 2px;
}

.storage-info-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.storage-bar-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.storage-bar-track {
    width: 140px;
    height: 10px;
    background: #e2eaf5;
    border-radius: 6px;
    overflow: hidden;
}

.storage-bar-fill {
    height: 100%;
    border-radius: 6px;
    transition: width 0.3s ease;
}

.storage-bar-fill.normal { background: #0065db; }
.storage-bar-fill.warning { background: #f59e0b; }
.storage-bar-fill.critical { background: #dc2626; }

.storage-percent-label {
    font-size: 12px;
    font-weight: 700;
    color: #4b6282;
    min-width: 34px;
}

.btn-storage-manage {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: 0.2s;
}

.btn-storage-manage.upgrade {
    background: #0065db;
    color: #ffffff;
    border: 1px solid transparent;
}

.btn-storage-manage.upgrade:hover {
    background: #004caa;
    color: #ffffff;
}

.btn-storage-manage.manage {
    background: #f0f6ff;
    color: #0065db;
    border: 1px solid #c7dcff;
}

.btn-storage-manage.manage:hover {
    background: #0065db;
    color: #ffffff;
}

/* ==========================================
   STUDENT ACTION REQUIRED ALERT PANEL
========================================== */
.action-required-panel {
    background: #fff8f8;
    border: 1px solid #fecaca;
    border-left: 5px solid #ef4444;
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 22px;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08);
}

.action-req-header {
    margin-bottom: 14px;
}

.action-req-title {
    font-size: 15px;
    font-weight: 700;
    color: #991b1b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-req-title i {
    font-size: 18px;
    color: #dc2626;
}

.action-req-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.action-req-item {
    background: white;
    border: 1px solid #fee2e2;
    border-radius: 10px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.action-req-body {
    flex: 1;
    min-width: 260px;
}

.action-req-doc-title {
    font-size: 14px;
    color: var(--navy);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.action-req-feedback {
    margin-top: 6px;
    font-size: 12.5px;
    color: #7f1d1d;
    background: #fef2f2;
    padding: 6px 12px;
    border-radius: 6px;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}

.action-req-feedback i {
    margin-top: 2px;
    flex-shrink: 0;
}

.action-req-cta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-action-revise {
    background: #dc2626;
    color: white;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-action-revise:hover {
    background: #b91c1c;
    color: white;
}

.btn-action-view {
    font-size: 12px;
    color: #4b5563;
    padding: 6px 10px;
    text-decoration: none;
}

.btn-action-view:hover {
    text-decoration: underline;
}

/* ==========================================
   CONTENT PANELS & TABLES
========================================== */
.content-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 20px;
    align-items: start;
}

.panel {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: 0 4px 14px rgba(0, 45, 115, 0.06);
}

.panel.mb-4 {
    margin-bottom: 22px;
}

.panel-header {
    height: 58px;
    border-bottom: 1px solid #e3edf9;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.panel-header h3 {
    font-size: 15.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--navy);
}

.panel-header a {
    font-size: 13px;
    color: var(--blue);
    font-weight: 600;
}

.panel-header a:hover {
    text-decoration: underline;
}

.document-list {
    padding: 10px 16px;
}

.document-row {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 10px;
    border-bottom: 1px solid #ecf3fc;
    transition: 0.15s;
}

.document-row:last-child {
    border-bottom: none;
}

.document-row:hover {
    background: #f7fbff;
    border-radius: 10px;
}

.file-type {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #e8f2ff;
    color: #0065db;
    display: grid;
    place-items: center;
    font-size: 20px;
    flex-shrink: 0;
}

.file-type.pdf {
    background: #ffe8eb;
    color: #ea1d2d;
}

.document-info {
    flex: 1;
    min-width: 0;
}

.document-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.document-name:hover {
    color: var(--blue);
}

.document-date {
    font-size: 12px;
    color: #657793;
    margin-top: 3px;
}

.doc-code {
    font-weight: 700;
    color: #003780;
}

.badge-subtle {
    background: #eef4fc;
    color: #315180;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 11px;
}

.badge-role-tiny {
    background: #e0ecff;
    color: #004caa;
    padding: 1px 6px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
}

.badge-count-warning {
    background: #f59e0b;
    color: white;
    padding: 2px 7px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 800;
}

/* Status Badges */
.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    text-transform: capitalize;
    white-space: nowrap;
}

.status-badge.approved { background: #d7f7e9; color: #00874e; }
.status-badge.pending { background: #fff1cc; color: #9c6c00; }
.status-badge.rejected { background: #ffd6da; color: #c40012; }
.status-badge.revision { background: #e0f2fe; color: #0369a1; }
.status-badge.archived { background: #e2e8f0; color: #475569; }
.status-badge.draft { background: #f1f5f9; color: #64748b; }

.action-btn-review {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #b45309;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: 0.2s;
    white-space: nowrap;
}

.action-btn-review:hover {
    background: #f59e0b;
    color: white;
    border-color: #f59e0b;
}

.btn-action-edit-tiny {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: 0.2s;
}

.btn-action-edit-tiny:hover {
    background: #0065db;
    color: white;
}

.btn-table-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f0f6ff;
    color: #0065db;
    display: grid;
    place-items: center;
    font-size: 15px;
    transition: 0.2s;
}

.btn-table-icon:hover {
    background: #0065db;
    color: white;
}

/* ==========================================
   DEPARTMENT BREAKDOWN WIDGET
========================================== */
.dept-summary-list {
    padding: 8px 16px;
}

.dept-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 8px;
    border-bottom: 1px solid #edf4fc;
}

.dept-summary-row:last-child {
    border-bottom: none;
}

.dept-summary-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.dept-name {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--navy);
}

.dept-counts {
    font-size: 12px;
    color: #64748b;
}

.text-success { color: #10b981 !important; }
.text-warning-dark { color: #d97706 !important; }

.dept-link {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f0f6ff;
    color: #0065db;
    display: grid;
    place-items: center;
    font-size: 18px;
    transition: 0.2s;
}

.dept-link:hover {
    background: #0065db;
    color: white;
}

/* ==========================================
   ANNOUNCEMENTS & ACTIVITY LOGS
========================================== */
.announcement-list {
    padding: 10px 16px;
}

.announcement {
    display: flex;
    gap: 14px;
    padding: 12px 10px;
    border-bottom: 1px solid #ecf3fc;
}

.announcement:last-child {
    border-bottom: none;
}

.announcement-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #eef5ff;
    color: var(--blue);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.announcement-body {
    flex: 1;
    min-width: 0;
}

.announcement-body strong {
    font-size: 13.5px;
    color: var(--navy);
}

.announcement-body p {
    font-size: 12.5px;
    color: #52637a;
    margin-top: 3px;
}

.announcement-date {
    font-size: 11.5px;
    color: #8c9fb6;
    margin-top: 4px;
}

/* ==========================================
   GUIDE BOX
========================================== */
.guide-box {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.guide-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.guide-item i {
    font-size: 20px;
    margin-top: 2px;
    flex-shrink: 0;
}

.guide-item strong {
    font-size: 13.5px;
    color: var(--navy);
    display: block;
    margin-bottom: 2px;
}

/* ==========================================
   EMPTY STATES
========================================== */
.empty-state {
    text-align: center;
    padding: 35px 20px;
    color: #61738e;
}

.empty-state-icon {
    font-size: 42px;
    display: block;
    margin-bottom: 10px;
    color: #94a3b8;
}

.empty-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 4px;
}

.empty-desc {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 12px;
}

.empty-action {
    display: inline-block;
    padding: 8px 18px;
    background: #0065db;
    color: white;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.empty-action:hover {
    background: #004caa;
    color: white;
}

/* ==========================================
   DASHBOARD FOOTER
========================================== */
.dashboard-footer {
    height: 60px;
    background: #001f4c;
    color: white;
    font-size: 13px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 3%;
    border-top: 3px solid var(--gold);
}

.footer-motto {
    font-weight: bold;
    color: var(--gold);
}

/* ==========================================
   RESPONSIVE DESIGN
========================================== */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 991px) {
    .mobile-menu {
        display: block;
    }
    .top-header {
        height: 85px;
    }
    .header-brand h1 {
        font-size: 28px;
    }
    .header-brand p {
        font-size: 12px;
    }
    .header-brand img {
        width: 70px;
        height: 65px;
    }
    .dashboard-main {
        padding: 18px;
    }
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .dashboard-banner {
        padding: 20px;
        min-height: 150px;
    }
    .banner-text h2 {
        font-size: 20px;
    }
    .dashboard-footer {
        flex-direction: column;
        height: auto;
        padding: 15px;
        gap: 6px;
        text-align: center;
    }
}
</style>
</head>
<body>

<!-- ==========================================
     TOP HEADER
========================================== -->
<header class="top-header">
    <a href="dashboard.php" class="header-brand">
        <img src="assets/udms-logo.png" alt="UDMS Logo">
        <div class="brand-divider"></div>
        <div>
            <h1>UDMS</h1>
            <p>St. Clare College of Caloocan</p>
        </div>
    </a>

    <div class="header-right">
        <a href="notifications.php" class="dashboard-notify-link" title="Notifications" style="color:white;font-size:22px;display:flex;align-items:center;position:relative;text-decoration:none;">
            <i class="bi bi-bell-fill"></i>
            <?php if (!empty($unreadNotificationsCount)): ?>
                <span style="position:absolute;top:-4px;right:-7px;background:#ef4444;color:white;font-size:10px;font-weight:700;border-radius:10px;padding:2px 6px;min-width:18px;text-align:center;line-height:1;box-shadow:0 0 0 2px #001a45;"><?= (int)$unreadNotificationsCount ?></span>
            <?php endif; ?>
        </a>
        <button class="mobile-menu" id="mobileMenu" type="button" aria-label="Toggle sidebar" aria-expanded="false">
            ☰
        </button>
    </div>
</header>

<!-- ==========================================
     DASHBOARD BODY
========================================== -->
<div class="dashboard-layout">
    <!-- Sidebar navigation -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="dashboard-main">

        <!-- SCHOOL BANNER -->
        <section class="dashboard-banner">
            <div class="banner-info">
                <img class="banner-logo" src="assets/udms-logo.png" alt="UDMS Logo">
                <div class="banner-divider"></div>
                <div class="banner-text">
                    <h2>
                        University<br>
                        Document Management<br>
                        System
                    </h2>
                    <p>Organize • Access • Share</p>
                </div>
            </div>
            <div class="banner-wave">
                <svg viewBox="0 0 1200 80" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0 0 C350 110 850 70 1200 0 L1200 80 L0 80 Z" fill="#ffd139"/>
                    <path d="M0 15 C350 120 850 85 1200 20 L1200 80 L0 80 Z" fill="#00265f"/>
                </svg>
            </div>
        </section>

        <!-- ADMIN PREVIEW SWITCHER (VISIBLE ONLY TO SYSTEM ADMINISTRATORS) -->
        <?php if ($r === 'Admin'): ?>
            <div class="admin-role-switcher">
                <div class="ars-label">
                    <i class="bi bi-sliders"></i> Role View Switcher:
                </div>
                <div class="ars-options">
                    <a href="dashboard.php" class="ars-pill <?= $viewRole === 'Admin' ? 'active' : '' ?>">
                        <i class="bi bi-shield-lock-fill"></i> Admin Dashboard
                    </a>
                    <a href="dashboard.php?preview_role=Faculty" class="ars-pill <?= $viewRole === 'Faculty' ? 'active' : '' ?>">
                        <i class="bi bi-mortarboard-fill"></i> Faculty View
                    </a>
                    <a href="dashboard.php?preview_role=Student" class="ars-pill <?= $viewRole === 'Student' ? 'active' : '' ?>">
                        <i class="bi bi-person-badge-fill"></i> Student View
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- WELCOME ROW -->
        <div class="welcome-row">
            <div>
                <h2>Welcome back, <?= dashEscape($firstName); ?>!</h2>
                <p>
                    <?php if ($viewRole === 'Admin'): ?>
                        Executive University Document & System Management Overview.
                    <?php elseif ($viewRole === 'Faculty'): ?>
                        Grade Level Review Portal & Academic Curriculum Hub.
                    <?php else: ?>
                        Academic Submissions, Clearances & Quota Overview.
                    <?php endif; ?>
                </p>
            </div>
            <span class="role-label role-label-<?= strtolower($viewRole) ?>">
                <i class="bi bi-<?= $viewRole === 'Admin' ? 'shield-check' : ($viewRole === 'Faculty' ? 'mortarboard' : 'person-badge') ?>"></i>
                <?= dashEscape($viewRole); ?> Account
                <?php if ($r === 'Admin' && $viewRole !== 'Admin'): ?>
                    (Preview Mode)
                <?php endif; ?>
            </span>
        </div>

        <!-- ==========================================
             ROLE-SPECIFIC DASHBOARD CONTENT VIEW
        ========================================== -->
        <?php
        if ($viewRole === 'Admin') {
            include __DIR__ . '/includes/dashboards/admin_view.php';
        } elseif ($viewRole === 'Faculty') {
            include __DIR__ . '/includes/dashboards/faculty_view.php';
        } else {
            include __DIR__ . '/includes/dashboards/student_view.php';
        }
        ?>

    </main>
</div>

<!-- ==========================================
     FOOTER
========================================== -->
<footer class="dashboard-footer">
    <span>
        © <?= date('Y'); ?> St. Clare College of Caloocan. All rights reserved.
    </span>
    <span>
        University Document Management System
    </span>
    <span class="footer-motto">
        Faith. Excellence. Service.
    </span>
</footer>

<!-- ==========================================
     JAVASCRIPT
========================================== -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/app.js"></script>
<script>
const mobileMenu = document.getElementById("mobileMenu");
const sidebar = document.getElementById("sidebar") ||
                document.getElementById("sb") ||
                document.querySelector(".udms-sidebar");

if (mobileMenu && sidebar) {
    mobileMenu.addEventListener("click", function(event) {
        event.stopPropagation();
        const isOpen = sidebar.classList.toggle("open");
        mobileMenu.setAttribute("aria-expanded", String(isOpen));
    });
}

document.addEventListener("click", function(event) {
    if (sidebar && mobileMenu && window.innerWidth <= 991 &&
        !sidebar.contains(event.target) && !mobileMenu.contains(event.target)) {
        sidebar.classList.remove("open");
        mobileMenu.setAttribute("aria-expanded", "false");
    }
});

document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        if (sidebar) sidebar.classList.remove("open");
        if (mobileMenu) mobileMenu.setAttribute("aria-expanded", "false");
    }
});
</script>

<?php if(!empty($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
<?php if(!(me() && stripos($f['m'] ?? '', 'logged out') !== false)): ?>
<script>flashMsg(<?=json_encode($f['t'])?>,<?=json_encode($f['m'])?>);</script>
<?php endif; endif; ?>

</body>
</html>
