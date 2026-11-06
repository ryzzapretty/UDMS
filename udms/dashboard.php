
<?php
require_once 'config.php';
require_role('Admin', 'Faculty', 'Student');

$u = me();
$r = $u['role'];
$me = (int)$u['id'];

function dashCount($sql, $params = []) {
    return (int)q($sql, $params)->fetchColumn();
}

function dashEscape($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
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

/* ==========================================
   DATABASE COUNTS
========================================== */

if ($r === 'Admin') {

    $total = dashCount(
        "SELECT COUNT(*) FROM documents"
    );

    $approved = dashCount(
        "SELECT COUNT(*) FROM documents WHERE status='Approved'"
    );

    $pending = dashCount(
        "SELECT COUNT(*) FROM documents WHERE status='Pending Review'"
    );

    $rejected = dashCount(
        "SELECT COUNT(*) FROM documents WHERE status='Rejected'"
    );

    $recent = q(
        "SELECT d.*, u.full_name
         FROM documents d
         JOIN users u ON u.id = d.uploaded_by
         ORDER BY d.created_at DESC
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

    $pendingDocs = q(
        "SELECT d.*, u.full_name
         FROM documents d
         JOIN users u ON u.id = d.uploaded_by
         WHERE d.status = 'Pending Review'
         ORDER BY d.updated_at ASC
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

    $activities = q(
        "SELECT a.*, u.full_name
         FROM activity_logs a
         LEFT JOIN users u ON u.id = a.user_id
         ORDER BY a.id DESC
         LIMIT 4"
    )->fetchAll(PDO::FETCH_ASSOC);

} else {

    $total = dashCount(
        "SELECT COUNT(*) FROM documents
         WHERE uploaded_by = ? AND status <> 'Archived'",
        [$me]
    );

    $approved = dashCount(
        "SELECT COUNT(*) FROM documents
         WHERE uploaded_by = ? AND status = 'Approved'",
        [$me]
    );

    $pending = dashCount(
        "SELECT COUNT(*) FROM documents
         WHERE uploaded_by = ?
         AND status IN ('Pending Review', 'Returned for Revision')",
        [$me]
    );

    $rejected = dashCount(
        "SELECT COUNT(*) FROM documents
         WHERE uploaded_by = ? AND status = 'Rejected'",
        [$me]
    );

    $recent = q(
        "SELECT d.*, u.full_name
         FROM documents d
         JOIN users u ON u.id = d.uploaded_by
         WHERE d.uploaded_by = ?
         ORDER BY d.updated_at DESC
         LIMIT 5",
        [$me]
    )->fetchAll(PDO::FETCH_ASSOC);

    $notifications = q(
        "SELECT *
         FROM notifications
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 4",
        [$me]
    )->fetchAll(PDO::FETCH_ASSOC);

    $activities = q(
        "SELECT a.*, u.full_name
         FROM activity_logs a
         LEFT JOIN users u ON u.id = a.user_id
         WHERE a.user_id = ?
         ORDER BY a.id DESC
         LIMIT 4",
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

<title><?php echo dashEscape($r); ?> Dashboard | UDMS</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

/* ==========================================
   GLOBAL DESIGN
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
    background: linear-gradient(
        110deg,
        #001a45,
        #063779,
        #001c4c
    );
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
    gap: 28px;
}

.top-nav {
    display: flex;
    align-items: center;
    gap: 12px;
}

.top-nav a {
    color: white;
    padding: 11px 16px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    gap: 9px;
    font-size: 14px;
}

.top-nav a:hover,
.top-nav a.active {
    background: #075cc9;
}

.top-nav svg {
    width: 20px;
    height: 20px;
}

.user-menu {
    position: relative;
    border-left: 2px solid white;
    padding-left: 22px;
}

.user-menu-button {
    display: flex;
    align-items: center;
    gap: 9px;
    background: transparent;
    border: none;
    color: white;
    font-size: 14px;
}

.user-menu-button i {
    font-size: 27px;
}

.user-dropdown {
    display: none;
    position: absolute;
    top: 45px;
    right: 0;
    min-width: 190px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 8px 24px #001b4b40;
    padding: 8px;
    z-index: 50;
}

.user-dropdown.open {
    display: block;
}

.user-dropdown a {
    display: block;
    padding: 12px;
    color: var(--navy);
    border-radius: 7px;
    font-size: 13px;
}

.user-dropdown a:hover {
    background: #eaf3ff;
}

.mobile-menu {
    display: none;
    border: none;
    background: transparent;
    color: white;
    font-size: 27px;
}

/* ==========================================
   MAIN DASHBOARD LAYOUT
========================================== */

.dashboard-layout {
    display: flex;
    min-height: calc(100vh - 170px);
}

/* ==========================================
   SIDEBAR
========================================== */

.sidebar {
    width: 225px;
    flex-shrink: 0;
    background: linear-gradient(
        180deg,
        #00245d,
        #001b49
    );
    padding: 70px 10px 25px;
    position: relative;
    overflow: hidden;
}

.sidebar::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 180px;
    opacity: 0.13;
    pointer-events: none;
    background: url("assets/St. Clare College Campus Banner.png")
        center / cover;
    mask-image: linear-gradient(transparent, black);
}

.sidebar-menu {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 15px;

    color: white;
    padding: 15px 16px;
    border-radius: 15px;
    font-size: 14px;
    transition: 0.2s;
}

.sidebar-menu a svg {
    width: 23px;
    height: 23px;
    flex-shrink: 0;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background: linear-gradient(
        90deg,
        #0870ed,
        #0754c5
    );
}

.sidebar-menu a.active {
    font-weight: bold;
}

/* ==========================================
   MAIN CONTENT
========================================== */

.dashboard-main {
    flex: 1;
    min-width: 0;
    padding: 20px 22px 35px;
    background: #fbfdff;
}

/* ==========================================
   CAMPUS BANNER
========================================== */

.dashboard-banner {
    min-height: 245px;
    border-radius: 17px;
    overflow: hidden;
    position: relative;

    background-image:
        linear-gradient(
            90deg,
            rgba(226,242,255,0.96),
            rgba(226,242,255,0.88) 37%,
            rgba(226,242,255,0.05) 75%
        ),
        url("assets/St. Clare College Campus Banner.png");

    background-position: center;
    background-size: cover;

    display: flex;
    align-items: center;
    padding: 28px 38px;
    border: 1px solid #c9e0fa;
    box-shadow: 0 3px 12px #002b6c18;
}

.banner-info {
    display: flex;
    align-items: center;
    gap: 28px;
    position: relative;
    z-index: 2;
    max-width: 730px;
}

.banner-logo {
    width: 175px;
    height: 175px;
    object-fit: contain;
    flex-shrink: 0;
}

.banner-divider {
    height: 145px;
    width: 3px;
    background: #00285f;
    flex-shrink: 0;
}

.banner-text h2 {
    font-size: clamp(24px, 2.5vw, 37px);
    line-height: 1.08;
    font-weight: 900;
}

.banner-text p {
    font-size: 21px;
    font-style: italic;
    margin-top: 15px;
    font-weight: 600;
}

.banner-wave {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 55px;
}

.banner-wave svg {
    width: 100%;
    height: 100%;
}

/* ==========================================
   WELCOME TEXT
========================================== */

.welcome-row {
    margin: 20px 0 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.welcome-row h2 {
    font-size: 19px;
}

.welcome-row p {
    font-size: 13px;
    color: #61738e;
    margin-top: 4px;
}

.role-label {
    background: #e7f0ff;
    color: #004caa;
    border: 1px solid #c7dcff;
    padding: 9px 15px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: bold;
}

/* ==========================================
   STATISTIC CARDS
========================================== */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 22px;
}

.stat-card {
    position: relative;
    min-height: 115px;
    display: flex;
    align-items: center;
    gap: 20px;

    padding: 18px 20px;
    border-radius: 16px;
    background: white;
    border: 1px solid var(--border);

    box-shadow: 0 5px 12px #002d731a;
    overflow: hidden;
    color: var(--text);
    transition: 0.2s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 9px 20px #002d7326;
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

.stat-card.blue::after {
    background: #1685ff;
}

.stat-card.green::after {
    background: #00b971;
}

.stat-card.yellow::after {
    background: #ffb900;
}

.stat-card.red::after {
    background: #f52e3c;
}

.stat-icon {
    width: 70px;
    height: 70px;
    flex-shrink: 0;
    border-radius: 50%;
    color: white;

    display: grid;
    place-items: center;
}

.stat-icon svg {
    width: 35px;
    height: 35px;
}

.blue .stat-icon {
    background: linear-gradient(#1687ff, #0051c6);
}

.green .stat-icon {
    background: linear-gradient(#08c67e, #00a85f);
}

.yellow .stat-icon {
    background: linear-gradient(#ffc326, #ff9d00);
}

.red .stat-icon {
    background: linear-gradient(#ff3647, #e51429);
}

.stat-details {
    position: relative;
    z-index: 2;
    min-width: 0;
}

.stat-number {
    font-size: 37px;
    font-weight: 900;
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    margin-top: 8px;
}

/* ==========================================
   CONTENT PANELS
========================================== */

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
}

.panel {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid #dce6f4;
    box-shadow: 0 5px 15px #002b6c15;
    margin-bottom: 20px;
}

.panel-header {
    background: linear-gradient(
        100deg,
        #00245c,
        #001c4b
    );
    color: white;
    padding: 15px 20px;

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.panel-header h3 {
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.panel-header h3 svg {
    width: 22px;
    height: 22px;
}

.panel-header a {
    color: white;
    font-size: 13px;
    white-space: nowrap;
}

.panel-header a:hover {
    color: var(--gold);
}

/* ==========================================
   RECENT DOCUMENTS
========================================== */

.document-list {
    padding: 8px 20px;
}

.document-row {
    display: flex;
    align-items: center;
    gap: 14px;

    min-height: 59px;
    padding: 10px 0;
    border-bottom: 1px solid #e2eaf5;
}

.document-row:last-child {
    border-bottom: none;
}

.file-type {
    width: 35px;
    height: 39px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    font-size: 25px;
    color: #0066d8;
}

.file-type.pdf {
    color: #ee2834;
}

.document-info {
    flex: 1;
    min-width: 0;
}

.document-name {
    color: #092657;
    font-size: 13px;
    font-weight: bold;
    display: block;
    overflow-wrap: anywhere;
}

.document-name:hover {
    color: #0064d9;
}

.document-date {
    font-size: 11px;
    color: #61728b;
    margin-top: 5px;
}

.status-badge {
    font-size: 11px;
    font-weight: bold;
    border-radius: 18px;
    padding: 7px 12px;
    white-space: nowrap;
}

.status-badge.approved {
    color: #076739;
    background: #baf1d1;
}

.status-badge.pending {
    color: #865900;
    background: #ffe6a0;
}

.status-badge.rejected {
    color: #aa1421;
    background: #ffd1d5;
}

.status-badge.revision {
    color: #945500;
    background: #ffe1bd;
}

.status-badge.archived {
    color: #526078;
    background: #e4e9f0;
}

.status-badge.draft {
    color: #365579;
    background: #dbe9ff;
}

/* ==========================================
   ANNOUNCEMENTS / ACTIVITIES
========================================== */

.announcement-list {
    padding: 8px 20px;
}

.announcement {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #e3eaf4;
}

.announcement:last-child {
    border-bottom: none;
}

.announcement-icon {
    width: 45px;
    height: 45px;
    background: #e3efff;
    border-radius: 50%;
    color: #005bc7;

    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.announcement-body {
    flex: 1;
    min-width: 0;
}

.announcement-body strong {
    display: block;
    font-size: 13px;
    margin-bottom: 5px;
}

.announcement-body p {
    font-size: 12px;
    line-height: 1.5;
    color: #284369;
    overflow-wrap: anywhere;
}

.announcement-date {
    font-size: 10px;
    color: #6b7b92;
    margin-top: 7px;
}

/* ==========================================
   EMPTY STATES
========================================== */

.empty-state {
    text-align: center;
    padding: 30px 15px;
    color: #7a8ca5;
    font-size: 13px;
}

.empty-state a {
    color: #0064d8;
}

/* ==========================================
   FOOTER
========================================== */

.dashboard-footer {
    background: linear-gradient(
        110deg,
        #001b49,
        #073677,
        #001a45
    );
    color: white;
    border-top: 7px solid var(--gold);

    padding: 22px 30px;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;

    font-size: 12px;
}

.footer-motto {
    font-family: Georgia, serif;
    font-style: italic;
    font-size: 21px;
}

/* ==========================================
   RESPONSIVE DESIGN
========================================== */

@media (max-width: 1200px) {

    .stats-grid {
        gap: 12px;
    }

    .stat-card {
        padding: 14px;
        gap: 12px;
    }

    .stat-icon {
        width: 55px;
        height: 55px;
    }

    .stat-number {
        font-size: 30px;
    }

    .banner-logo {
        width: 130px;
        height: 130px;
    }

    .banner-divider {
        height: 120px;
    }
}

@media (max-width: 1000px) {

    .top-header {
        padding: 10px 20px;
    }

    .header-brand img {
        width: 70px;
        height: 70px;
    }

    .header-brand h1 {
        font-size: 28px;
    }

    .header-brand p {
        font-size: 11px;
    }

    .top-nav {
        display: none;
    }

    .sidebar {
        width: 75px;
        padding-left: 8px;
        padding-right: 8px;
    }

    .sidebar-menu a {
        justify-content: center;
        padding: 15px 10px;
    }

    .sidebar-menu a span {
        display: none;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 700px) {

    .top-header {
        height: 80px;
    }

    .header-brand {
        gap: 10px;
    }

    .header-brand img {
        width: 55px;
        height: 55px;
    }

    .brand-divider {
        height: 45px;
    }

    .header-brand h1 {
        font-size: 23px;
    }

    .header-brand p {
        font-size: 10px;
    }

    .user-menu-button span {
        display: none;
    }

    .mobile-menu {
        display: block;
    }

    .sidebar {
        position: fixed;
        top: 80px;
        left: -250px;
        bottom: 0;
        width: 230px;
        padding-top: 25px;
        z-index: 25;
        transition: left 0.3s;
    }

    .sidebar.open {
        left: 0;
    }

    .sidebar-menu a {
        justify-content: flex-start;
    }

    .sidebar-menu a span {
        display: inline;
    }

    .dashboard-main {
        padding: 15px;
    }

    .dashboard-banner {
        min-height: 200px;
        padding: 20px;
    }

    .banner-info {
        gap: 15px;
    }

    .banner-logo {
        width: 90px;
        height: 90px;
    }

    .banner-divider {
        height: 90px;
    }

    .banner-text h2 {
        font-size: 21px;
    }

    .banner-text p {
        font-size: 13px;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-footer {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 430px) {

    .stats-grid {
        gap: 10px;
    }

    .stat-card {
        min-height: 100px;
        padding: 12px;
        gap: 10px;
    }

    .stat-icon {
        width: 45px;
        height: 45px;
    }

    .stat-icon svg {
        width: 25px;
        height: 25px;
    }

    .stat-number {
        font-size: 25px;
    }

    .stat-label {
        font-size: 11px;
    }

    .banner-logo {
        width: 65px;
        height: 65px;
    }

    .banner-divider {
        height: 70px;
    }

    .banner-text h2 {
        font-size: 17px;
    }

    .banner-text p {
        font-size: 11px;
    }

    .document-list,
    .announcement-list {
        padding: 8px 12px;
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

        <nav class="top-nav">

            <a href="dashboard.php" class="active">
                <?php echo dashIcon('home', 20); ?>
                Home
            </a>

            <a href="landingpage.php#about">
                <?php echo dashIcon('info', 20); ?>
                About
            </a>

            <a href="landingpage.php#contact">
                <i class="bi bi-envelope-fill"></i>
                Contact
            </a>

        </nav>

        <div class="user-menu">

            <button
                class="user-menu-button"
                id="userMenuButton"
                type="button"
                aria-expanded="false"
            >

                <i class="bi bi-person-circle"></i>

                <span>
                    <?php echo dashEscape($u['full_name']); ?>
                </span>

                <i class="bi bi-chevron-down" style="font-size:12px"></i>

            </button>

            <div class="user-dropdown" id="userDropdown">

                <a href="profile.php">
                    <i class="bi bi-person"></i>
                    My Profile
                </a>

                <a href="notifications.php">
                    <i class="bi bi-bell"></i>
                    Notifications
                </a>

                <a href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>

            </div>

        </div>

        <button
            class="mobile-menu"
            id="mobileMenu"
            type="button"
            aria-label="Toggle sidebar"
            aria-expanded="false"
        >
            ☰
        </button>

    </div>

</header>

<!-- ==========================================
     DASHBOARD BODY
========================================== -->

<div class="dashboard-layout">
  <!-- Side bar -->

   <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->

    <main class="dashboard-main">

        <!-- SCHOOL BANNER -->

        <section class="dashboard-banner">

            <div class="banner-info">

                <img
                    class="banner-logo"
                    src="assets/udms-logo.png"
                    alt="UDMS Logo"
                >

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

                <svg viewBox="0 0 1200 80"
                     preserveAspectRatio="none"
                     aria-hidden="true">

                    <path
                        d="M0 0 C350 110 850 70 1200 0
                           L1200 80 L0 80 Z"
                        fill="#ffd139"
                    />

                    <path
                        d="M0 15 C350 120 850 85 1200 20
                           L1200 80 L0 80 Z"
                        fill="#00265f"
                    />

                </svg>

            </div>

        </section>

        <!-- WELCOME -->

        <div class="welcome-row">

            <div>

                <h2>
                    Welcome back,
                    <?php echo dashEscape($firstName); ?>!
                </h2>

                <p>
                    Here's an overview of your UDMS documents.
                </p>

            </div>

            <span class="role-label">
                <?php echo dashEscape($r); ?> Account
            </span>

        </div>

        <!-- ==========================================
             FOUR STATISTIC CARDS
        ========================================== -->

        <section class="stats-grid">

            <!-- TOTAL -->

            <a
                href="documents.php?v=<?php echo $r === 'Admin' ? 'all' : 'mine'; ?>"
                class="stat-card blue"
            >

                <div class="stat-icon">
                    <?php echo dashIcon('file', 35); ?>
                </div>

                <div class="stat-details">

                    <div class="stat-number">
                        <?php echo number_format($total); ?>
                    </div>

                    <div class="stat-label">
                        <?php echo $r === 'Admin'
                            ? 'Total Documents'
                            : 'My Documents'; ?>
                    </div>

                </div>

            </a>

            <!-- APPROVED -->

            <a
                href="documents.php?v=<?php echo $r === 'Admin' ? 'all' : 'mine'; ?>&status=Approved"
                class="stat-card green"
            >

                <div class="stat-icon">
                    <?php echo dashIcon('check', 35); ?>
                </div>

                <div class="stat-details">

                    <div class="stat-number">
                        <?php echo number_format($approved); ?>
                    </div>

                    <div class="stat-label">Approved</div>

                </div>

            </a>

            <!-- PENDING -->

            <a
                href="documents.php?v=pending"
                class="stat-card yellow"
            >

                <div class="stat-icon">
                    <?php echo dashIcon('clock', 35); ?>
                </div>

                <div class="stat-details">

                    <div class="stat-number">
                        <?php echo number_format($pending); ?>
                    </div>

                    <div class="stat-label">Pending</div>

                </div>

            </a>

            <!-- REJECTED -->

            <a
                href="documents.php?v=<?php echo $r === 'Admin' ? 'all' : 'mine'; ?>&status=Rejected"
                class="stat-card red"
            >

                <div class="stat-icon">
                    <?php echo dashIcon('x', 35); ?>
                </div>

                <div class="stat-details">

                    <div class="stat-number">
                        <?php echo number_format($rejected); ?>
                    </div>

                    <div class="stat-label">Rejected</div>

                </div>

            </a>

        </section>

        <!-- ==========================================
             RECENT DOCUMENTS AND UPDATES
        ========================================== -->

        <div class="content-grid">

            <!-- LEFT COLUMN -->

            <div>

                <!-- RECENT DOCUMENTS -->

                <section class="panel">

                    <div class="panel-header">

                        <h3>
                            <?php echo dashIcon('file'); ?>
                            Recent Documents
                        </h3>

                        <a href="documents.php?v=<?php echo $r === 'Admin' ? 'all' : 'mine'; ?>">
                            View All →
                        </a>

                    </div>

                    <div class="document-list">

                        <?php if (!$recent): ?>

                            <div class="empty-state">
                                No documents yet.
                                <a href="document_form.php?mode=upload">
                                    Upload your first document.
                                </a>
                            </div>

                        <?php else: ?>

                            <?php foreach ($recent as $d): ?>

                                <?php
                                $fileTitle = $d['title'] ?? 'Untitled Document';
                                $extension = strtolower(
                                    pathinfo($fileTitle, PATHINFO_EXTENSION)
                                );

                                $isPdf = $extension === 'pdf';
                                ?>

                                <div class="document-row">

                                    <div class="file-type <?php echo $isPdf ? 'pdf' : ''; ?>">

                                        <i class="bi <?php echo $isPdf
                                            ? 'bi-file-earmark-pdf-fill'
                                            : 'bi-file-earmark-text-fill'; ?>">
                                        </i>

                                    </div>

                                    <div class="document-info">

                                        <a
                                            class="document-name"
                                            href="document_view.php?id=<?php echo (int)$d['id']; ?>"
                                        >
                                            <?php echo dashEscape($fileTitle); ?>
                                        </a>

                                        <div class="document-date">
                                            <?php echo dashEscape(
                                                dashDate($d['created_at'] ?? $d['updated_at'] ?? null)
                                            ); ?>

                                            <?php if ($r === 'Admin'): ?>
                                                • <?php echo dashEscape($d['full_name']); ?>
                                            <?php endif; ?>
                                        </div>

                                    </div>

                                    <span class="status-badge <?php echo dashStatusClass($d['status']); ?>">

                                        <?php echo dashEscape($d['status']); ?>

                                    </span>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </section>

                <!-- ADMIN PENDING REVIEWS -->

                <?php if ($r === 'Admin'): ?>

                    <section class="panel">

                        <div class="panel-header">

                            <h3>
                                <?php echo dashIcon('clock'); ?>
                                Pending Reviews
                            </h3>

                            <a href="documents.php?v=pending">
                                View All →
                            </a>

                        </div>

                        <div class="document-list">

                            <?php if (!$pendingDocs): ?>

                                <div class="empty-state">
                                    No documents waiting for review.
                                </div>

                            <?php else: ?>

                                <?php foreach ($pendingDocs as $d): ?>

                                    <div class="document-row">

                                        <div class="file-type">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </div>

                                        <div class="document-info">

                                            <a
                                                class="document-name"
                                                href="document_view.php?id=<?php echo (int)$d['id']; ?>"
                                            >
                                                <?php echo dashEscape($d['title']); ?>
                                            </a>

                                            <div class="document-date">
                                                Uploaded by
                                                <?php echo dashEscape($d['full_name']); ?>
                                            </div>

                                        </div>

                                        <a
                                            class="status-badge pending"
                                            href="document_view.php?id=<?php echo (int)$d['id']; ?>"
                                        >
                                            Review
                                        </a>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </section>

                <?php endif; ?>

            </div>

            <!-- RIGHT COLUMN -->

            <div>

                <!-- ADMIN: RECENT SYSTEM ACTIVITY -->

                <?php if ($r === 'Admin'): ?>

                    <section class="panel">

                        <div class="panel-header">

                            <h3>
                                <?php echo dashIcon('bell'); ?>
                                Recent System Updates
                            </h3>

                            <a href="logs.php">
                                View All →
                            </a>

                        </div>

                        <div class="announcement-list">

                            <?php if (!$activities): ?>

                                <div class="empty-state">
                                    No system activities yet.
                                </div>

                            <?php else: ?>

                                <?php foreach ($activities as $a): ?>

                                    <div class="announcement">

                                        <div class="announcement-icon">
                                            <?php echo dashIcon('bell', 21); ?>
                                        </div>

                                        <div class="announcement-body">

                                            <strong>
                                                <?php echo dashEscape($a['action']); ?>
                                            </strong>

                                            <p>
                                                <?php echo dashEscape($a['description']); ?>
                                            </p>

                                            <div class="announcement-date">

                                                <?php echo dashEscape($a['full_name'] ?? 'System'); ?>

                                                •

                                                <?php echo dashEscape(
                                                    dashDate($a['created_at'])
                                                ); ?>

                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </section>

                <?php else: ?>

                    <!-- FACULTY / STUDENT NOTIFICATIONS -->

                    <section class="panel">

                        <div class="panel-header">

                            <h3>
                                <?php echo dashIcon('bell'); ?>
                                Notifications
                            </h3>

                            <a href="notifications.php">
                                View All →
                            </a>

                        </div>

                        <div class="announcement-list">

                            <?php if (!$notifications): ?>

                                <div class="empty-state">
                                    No notifications yet.
                                </div>

                            <?php else: ?>

                                <?php foreach ($notifications as $n): ?>

                                    <div class="announcement">

                                        <div class="announcement-icon">
                                            <?php echo dashIcon('bell', 21); ?>
                                        </div>

                                        <div class="announcement-body">

                                            <a
                                                class="document-name"
                                                href="notifications.php?go=<?php echo (int)$n['id']; ?>"
                                            >
                                                <?php echo dashEscape($n['message']); ?>
                                            </a>

                                            <div class="announcement-date">
                                                <?php echo dashEscape(
                                                    dashDate($n['created_at'])
                                                ); ?>
                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </section>

                <?php endif; ?>

                <!-- RECENT ACTIVITY -->

                <section class="panel">

                    <div class="panel-header">

                        <h3>
                            <?php echo dashIcon('clock'); ?>
                            Recent Activities
                        </h3>

                    </div>

                    <div class="announcement-list">

                        <?php if (!$activities): ?>

                            <div class="empty-state">
                                No recent activities.
                            </div>

                        <?php else: ?>

                            <?php foreach ($activities as $a): ?>

                                <div class="announcement">

                                    <div class="announcement-icon">
                                        <?php echo dashIcon('info', 21); ?>
                                    </div>

                                    <div class="announcement-body">

                                        <strong>
                                            <?php echo dashEscape($a['action']); ?>
                                        </strong>

                                        <p>
                                            <?php echo dashEscape($a['description']); ?>
                                        </p>

                                        <div class="announcement-date">
                                            <?php echo dashEscape(
                                                dashDate($a['created_at'])
                                            ); ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </section>

            </div>

        </div>

    </main>

</div>

<!-- ==========================================
     FOOTER
========================================== -->

<footer class="dashboard-footer">

    <span>
        © <?php echo date('Y'); ?>
        St. Clare College of Caloocan.
        All rights reserved.
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

<script>

const userMenuButton =
    document.getElementById("userMenuButton");

const userDropdown =
    document.getElementById("userDropdown");

const mobileMenu =
    document.getElementById("mobileMenu");

const sidebar =
    document.getElementById("sidebar");

/* USER DROPDOWN */

userMenuButton.addEventListener("click", function(event) {

    event.stopPropagation();

    const isOpen = userDropdown.classList.toggle("open");

    userMenuButton.setAttribute(
        "aria-expanded",
        String(isOpen)
    );

});

/* MOBILE SIDEBAR */

mobileMenu.addEventListener("click", function(event) {

    event.stopPropagation();

    const isOpen = sidebar.classList.toggle("open");

    mobileMenu.setAttribute(
        "aria-expanded",
        String(isOpen)
    );

});

/* CLOSE MENUS WHEN CLICKING OUTSIDE */

document.addEventListener("click", function(event) {

    if (
        !userDropdown.contains(event.target) &&
        !userMenuButton.contains(event.target)
    ) {
        userDropdown.classList.remove("open");
        userMenuButton.setAttribute("aria-expanded", "false");
    }

    if (
        window.innerWidth <= 700 &&
        !sidebar.contains(event.target) &&
        !mobileMenu.contains(event.target)
    ) {
        sidebar.classList.remove("open");
        mobileMenu.setAttribute("aria-expanded", "false");
    }

});

/* CLOSE WITH ESCAPE */

document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {

        userDropdown.classList.remove("open");
        sidebar.classList.remove("open");

        userMenuButton.setAttribute("aria-expanded", "false");
        mobileMenu.setAttribute("aria-expanded", "false");

    }

});

</script>

</body>
</html>
