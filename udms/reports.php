<?php
require 'config.php';
require_role('Admin');
$title = 'System Reports & Monitoring';

$df = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : date('Y-m-01');
$dt2 = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : date('Y-m-d');
$rp = [':f' => $df, ':t' => $dt2];
$rng = "DATE(d.created_at) BETWEEN :f AND :t";

if (isset($_GET['csv'])) {
    log_act('Report generated', null, "Document report CSV $df to $dt2");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="udms-report-' . $df . '-to-' . $dt2 . '.csv"');
    $o = fopen('php://output', 'w');
    fputcsv($o, ['Document ID', 'Title', 'Type', 'Category', 'Department', 'Uploaded By', 'Status', 'Access Level', 'Version', 'File Size (KB)', 'Created', 'Approved']);
    $cl = fn($s) => preg_match('/^[=+\-@]/', (string)$s) ? "'" . $s : $s;
    foreach (q("SELECT d.*, c.category_name, dp.department_name, u.full_name FROM documents d LEFT JOIN categories c ON c.id=d.category_id LEFT JOIN departments dp ON dp.id=d.department_id JOIN users u ON u.id=d.uploaded_by WHERE $rng ORDER BY d.id", $rp) as $r) {
        fputcsv($o, array_map($cl, [
            $r['document_id'],
            $r['title'],
            $r['document_type'],
            $r['category_name'],
            $r['department_name'],
            $r['full_name'],
            $r['status'],
            $r['access_level'],
            $r['current_version'],
            round(($r['file_size'] ?? 0) / 1024, 1),
            $r['created_at'],
            $r['approved_at']
        ]));
    }
    exit;
}

// Queries for Monitoring
$totalDocsPeriod = (int)q("SELECT COUNT(*) FROM documents d WHERE $rng", $rp)->fetchColumn();
$totalStoragePeriod = (int)q("SELECT COALESCE(SUM(file_size), 0) FROM documents d WHERE $rng", $rp)->fetchColumn();
$byStatus = q("SELECT status, COUNT(*) c FROM documents d WHERE $rng GROUP BY status", $rp)->fetchAll();
$byCat = q("SELECT COALESCE(c.category_name,'Uncategorized') n, COUNT(*) c FROM documents d LEFT JOIN categories c ON c.id=d.category_id WHERE $rng GROUP BY n ORDER BY c DESC", $rp)->fetchAll();
$byDep = q("SELECT COALESCE(x.department_name,'None') n, COUNT(*) c FROM documents d LEFT JOIN departments x ON x.id=d.department_id WHERE $rng GROUP BY n ORDER BY c DESC", $rp)->fetchAll();
$top = q("SELECT u.full_name n, COUNT(*) c FROM documents d JOIN users u ON u.id=d.uploaded_by WHERE $rng GROUP BY u.id ORDER BY c DESC LIMIT 5", $rp)->fetchAll();

// User Statistics
$usersByRole = q("SELECT role n, COUNT(*) c FROM users GROUP BY role")->fetchAll();
$usersByStatus = q("SELECT status n, COUNT(*) c FROM users GROUP BY status ORDER BY FIELD(status, 'Active', 'Pending', 'Inactive', 'Archived')")->fetchAll();
$studentTiers = q("SELECT CONCAT(COALESCE(subscription_tier, 'Free'), ' Plan') n, COUNT(*) c FROM users WHERE role='Student' GROUP BY subscription_tier")->fetchAll();
$totalUsers = (int)q("SELECT COUNT(*) FROM users")->fetchColumn();
$archivedUsersCount = (int)q("SELECT COUNT(*) FROM users WHERE status='Archived'")->fetchColumn();

include 'includes/header.php';

function tbl($h, $rows, $icon = 'bi-table') {
    echo '<div class="col-md-6"><div class="card border-0 shadow-sm h-100">';
    echo '<div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">';
    echo '<strong class="d-flex align-items-center gap-2"><i class="bi ' . $icon . ' text-primary"></i> ' . e($h) . '</strong>';
    echo '<span class="badge bg-light text-dark border">' . count($rows) . ' entries</span>';
    echo '</div>';
    echo '<div class="table-responsive"><table class="table align-middle mb-0">';
    foreach ($rows as $r) {
        $val = $r['n'] ?? $r['status'];
        echo '<tr>';
        echo '<td class="py-2">' . e($val) . '</td>';
        echo '<td class="text-end fw-semibold py-2">' . number_format($r['c']) . '</td>';
        echo '</tr>';
    }
    if (!$rows) {
        echo '<tr><td colspan="2" class="text-muted text-center py-3">No data recorded for this selection.</td></tr>';
    }
    echo '</table></div></div></div>';
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="page-title mb-1">System Monitoring & Reports</h1>
        <p class="text-muted small mb-0">Audit university document uploads, user account statuses, storage consumption, and departmental activity.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print Summary</button>
        <a class="btn btn-success" href="reports.php?csv=1&from=<?= e($df) ?>&to=<?= e($dt2) ?>"><i class="bi bi-filetype-csv"></i> Export CSV</a>
    </div>
</div>

<!-- ==========================================
     MONITORING SUMMARY TILES
========================================== -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($totalDocsPeriod) ?></div>
                    <div class="small text-muted">Docs in Selected Range</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success fs-3">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($totalUsers) ?></div>
                    <div class="small text-muted">Total Campus Users</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-3">
                    <i class="bi bi-archive-fill"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($archivedUsersCount) ?></div>
                    <div class="small text-muted">Archived Accounts</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info fs-3">
                    <i class="bi bi-hdd-network"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= $totalStoragePeriod > 1048576 ? number_format($totalStoragePeriod / 1048576, 1) . ' MB' : number_format($totalStoragePeriod / 1024, 1) . ' KB' ?></div>
                    <div class="small text-muted">Upload Volume in Period</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     DATE RANGE FILTER FORM
========================================== -->
<form class="card border-0 shadow-sm mb-4" method="get">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">From Date</label>
            <input type="date" name="from" class="form-control" value="<?= e($df) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">To Date</label>
            <input type="date" name="to" class="form-control" value="<?= e($dt2) ?>">
        </div>
        <div class="col-md-6 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Update Report</button>
            <a class="btn btn-light" href="reports.php?from=<?= date('Y-01-01') ?>&to=<?= date('Y-m-d') ?>">Year to Date</a>
            <a class="btn btn-light" href="reports.php?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>">This Month</a>
            <a class="btn btn-success ms-auto" href="reports.php?csv=1&from=<?= e($df) ?>&to=<?= e($dt2) ?>"><i class="bi bi-download"></i> CSV</a>
        </div>
    </div>
</form>

<!-- ==========================================
     REPORTS BREAKDOWN GRID
========================================== -->
<div class="row g-3">
    <?php
    tbl('Documents by Status (Period)', $byStatus, 'bi-pie-chart-fill');
    tbl('Documents by Department (Period)', $byDep, 'bi-buildings-fill');
    tbl('Documents by Category (Period)', $byCat, 'bi-tags-fill');
    tbl('Top Document Contributors (Period)', $top, 'bi-trophy-fill');
    tbl('User Accounts by Status (All Time)', $usersByStatus, 'bi-person-badge-fill');
    tbl('User Accounts by Role (All Time)', $usersByRole, 'bi-people-fill');
    tbl('Student Subscription Tiers (All Time)', $studentTiers, 'bi-gem');
    ?>
</div>

<?php include 'includes/footer.php'; ?>
