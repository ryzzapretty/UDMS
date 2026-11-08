<?php
require 'config.php';
require_role('Admin');
$u = me();
$title = 'User Management';

$deps = q("SELECT * FROM departments ORDER BY id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['act'] ?? '';
    try {
        if ($act === 'toggle') {
            $tid = (int)$_POST['id'];
            if ($tid == $u['id']) throw new Exception('You cannot deactivate your own account.');
            $x = q("SELECT * FROM users WHERE id=?", [$tid])->fetch();
            if (!$x) throw new Exception('User not found.');
            if ($x['status'] === 'Archived') throw new Exception('Archived accounts cannot be toggled. Please restore them first.');
            $ns = $x['status'] === 'Active' ? 'Inactive' : 'Active';
            q("UPDATE users SET status=? WHERE id=?", [$ns, $tid]);
            log_act('User status changed', null, "{$x['username']} set to $ns");
            if ($ns === 'Active') notify($tid, 'Your account is now active. Welcome to UDMS.', 'dashboard.php');
            flash('success', "{$x['full_name']} is now $ns.");
        }
        elseif ($act === 'archive') {
            $tid = (int)$_POST['id'];
            if ($tid == $u['id']) throw new Exception('You cannot archive your own account.');
            $x = q("SELECT * FROM users WHERE id=?", [$tid])->fetch();
            if (!$x) throw new Exception('User not found.');
            if ($x['status'] === 'Archived') throw new Exception('This account is already archived.');
            q("UPDATE users SET status='Archived', archived_at=NOW() WHERE id=?", [$tid]);
            log_act('User archived', null, "Archived user account {$x['username']} ({$x['full_name']})");
            flash('success', "Account for {$x['full_name']} has been archived (soft-deleted). All documents and activity history remain safely preserved.");
        }
        elseif ($act === 'restore') {
            $tid = (int)$_POST['id'];
            $x = q("SELECT * FROM users WHERE id=?", [$tid])->fetch();
            if (!$x) throw new Exception('User not found.');
            q("UPDATE users SET status='Active', archived_at=NULL WHERE id=?", [$tid]);
            log_act('User restored', null, "Restored archived account {$x['username']} to Active");
            notify($tid, 'Your UDMS account has been restored to Active status by an administrator.', 'dashboard.php');
            flash('success', "Account for {$x['full_name']} has been restored to Active.");
        }
        elseif ($act === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $n = trim($_POST['full_name'] ?? '');
            $un = trim($_POST['username'] ?? '');
            $em = trim($_POST['email'] ?? '');
            $pw = $_POST['password'] ?? '';
            $ro = $_POST['role'] ?? '';
            $dep = (int)($_POST['department_id'] ?? 0) ?: null;
            $st = $_POST['status'] ?? '';
            $idn = trim($_POST['id_number'] ?? '');
            $lim = max(1, (int)($_POST['storage_limit'] ?? 20));
            $tier = in_array($_POST['subscription_tier'] ?? '', ['Free', 'Pro'], true) ? $_POST['subscription_tier'] : 'Free';

            if (strlen($n) < 3) throw new Exception('Enter the full name.');
            if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $un)) throw new Exception('Username: 3-30 letters, numbers, dot or underscore.');
            if (!filter_var($em, FILTER_VALIDATE_EMAIL)) throw new Exception('Enter a valid email.');
            if (!in_array($ro, ['Admin', 'Faculty', 'Student', 'Guest'], true)) throw new Exception('Invalid role.');
            if (!in_array($st, ['Active', 'Inactive', 'Pending', 'Archived'], true)) throw new Exception('Invalid status.');
            if ($dep && !q("SELECT 1 FROM departments WHERE id=?", [$dep])->fetch()) throw new Exception('Invalid grade level.');
            if (($id === 0 || $pw !== '') && (strlen($pw) < 8 || !preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw))) {
                throw new Exception('Password needs 8+ characters with letters and numbers.');
            }
            if (q("SELECT 1 FROM users WHERE (username=? OR email=?) AND id<>?", [$un, $em, $id])->fetch()) {
                throw new Exception('Username or email already exists.');
            }

            if ($id) {
                if ($id == $u['id'] && ($ro !== 'Admin' || $st !== 'Active')) {
                    throw new Exception('You cannot remove your own admin access or deactivate yourself.');
                }
                $archDate = ($st === 'Archived') ? date('Y-m-d H:i:s') : null;
                q("UPDATE users SET full_name=?, username=?, email=?, role=?, department_id=?, status=?, id_number=?, storage_limit=?, subscription_tier=?, archived_at=? WHERE id=?",
                    [$n, $un, $em, $ro, $dep, $st, $idn, $lim, $tier, $archDate, $id]);
                if ($pw !== '') {
                    q("UPDATE users SET password=? WHERE id=?", [password_hash($pw, PASSWORD_DEFAULT), $id]);
                }
                log_act('User updated', null, "Updated account $un ($ro, $st, Tier: $tier)");
                flash('success', 'User updated successfully.');
            } else {
                $archDate = ($st === 'Archived') ? date('Y-m-d H:i:s') : null;
                q("INSERT INTO users(full_name, username, email, password, role, department_id, id_number, status, storage_limit, subscription_tier, archived_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)",
                    [$n, $un, $em, password_hash($pw, PASSWORD_DEFAULT), $ro, $dep, $idn, $st, $lim, $tier, $archDate]);
                log_act('User created', null, "Created account $un ($ro)");
                flash('success', 'User created successfully.');
            }
        }
    } catch (Exception $x) {
        flash('error', $x->getMessage());
    }
    redirect('users.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

// User status counts for quick tabs
$statusCountRows = q("SELECT status, COUNT(*) AS c FROM users GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$countActive = (int)($statusCountRows['Active'] ?? 0);
$countPending = (int)($statusCountRows['Pending'] ?? 0);
$countInactive = (int)($statusCountRows['Inactive'] ?? 0);
$countArchived = (int)($statusCountRows['Archived'] ?? 0);
$countNonArchived = $countActive + $countPending + $countInactive;

$w = ['1=1'];
$p = [];
$qs = trim($_GET['q'] ?? '');
$sf = $_GET['status'] ?? '';
$rf = $_GET['role'] ?? '';

if ($qs !== '') {
    $w[] = '(u.full_name LIKE :a OR u.username LIKE :b OR u.email LIKE :c OR u.id_number LIKE :d)';
    $p[':a'] = $p[':b'] = $p[':c'] = $p[':d'] = "%$qs%";
}

if (in_array($sf, ['Active', 'Inactive', 'Pending', 'Archived'], true)) {
    $w[] = 'u.status = :s';
    $p[':s'] = $sf;
} elseif ($sf === 'non_archived') {
    $w[] = "u.status <> 'Archived'";
}

if (in_array($rf, ['Admin', 'Faculty', 'Student', 'Guest'], true)) {
    $w[] = 'u.role = :r';
    $p[':r'] = $rf;
}

$rows = q(
    "SELECT u.*, d.department_name
     FROM users u
     LEFT JOIN departments d ON d.id = u.department_id
     WHERE " . implode(' AND ', $w) . "
     ORDER BY FIELD(u.status, 'Pending', 'Active', 'Inactive', 'Archived'), u.id DESC",
    $p
)->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="page-title mb-1">User Management & Administration</h1>
        <p class="text-muted small mb-0">Monitor campus user accounts, review pending signups, and manage active or soft-deleted/archived users.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="reports.php" class="btn btn-outline-primary"><i class="bi bi-graph-up-arrow"></i> User & System Reports</a>
        <button class="btn btn-primary" onclick="openUser(null)"><i class="bi bi-person-plus"></i> Add User</button>
    </div>
</div>

<!-- ==========================================
     QUICK STATUS FILTER TABS
========================================== -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="small fw-bold text-muted text-uppercase" style="font-size:11px;min-width:70px;">Filter:</span>
            <a href="users.php" class="btn btn-sm <?= empty($sf) ? 'btn-primary' : 'btn-outline-secondary' ?> py-1 px-3" style="border-radius:15px;font-size:12.5px;">
                All Accounts <span class="badge bg-light text-dark ms-1"><?= $countNonArchived + $countArchived ?></span>
            </a>
            <a href="users.php?status=Active" class="btn btn-sm <?= $sf === 'Active' ? 'btn-success' : 'btn-outline-secondary' ?> py-1 px-3" style="border-radius:15px;font-size:12.5px;">
                Active <span class="badge bg-light text-success ms-1"><?= $countActive ?></span>
            </a>
            <a href="users.php?status=Pending" class="btn btn-sm <?= $sf === 'Pending' ? 'btn-warning text-dark fw-bold' : ($countPending > 0 ? 'btn-outline-warning text-dark' : 'btn-outline-secondary') ?> py-1 px-3" style="border-radius:15px;font-size:12.5px;">
                <i class="bi bi-hourglass-split"></i> Pending Approvals
                <?php if ($countPending > 0): ?>
                    <span class="badge bg-danger text-white ms-1"><?= $countPending ?></span>
                <?php else: ?>
                    <span class="badge bg-light text-dark ms-1">0</span>
                <?php endif; ?>
            </a>
            <a href="users.php?status=Inactive" class="btn btn-sm <?= $sf === 'Inactive' ? 'btn-secondary' : 'btn-outline-secondary' ?> py-1 px-3" style="border-radius:15px;font-size:12.5px;">
                Inactive <span class="badge bg-light text-dark ms-1"><?= $countInactive ?></span>
            </a>
            <a href="users.php?status=Archived" class="btn btn-sm <?= $sf === 'Archived' ? 'btn-dark' : 'btn-outline-dark' ?> py-1 px-3" style="border-radius:15px;font-size:12.5px;">
                <i class="bi bi-archive-fill"></i> Archived / Soft-Deleted <span class="badge bg-secondary ms-1"><?= $countArchived ?></span>
            </a>
        </div>
        <div class="small text-muted">
            Showing <?= count($rows) ?> user(s)
        </div>
    </div>
</div>

<!-- ==========================================
     SEARCH & ADVANCED FILTER FORM
========================================== -->
<form class="card border-0 shadow-sm mb-3" method="get">
    <div class="card-body row g-2">
        <div class="col-md-5">
            <input name="q" class="form-control" placeholder="Search by name, username, email or ID number" value="<?= e($qs) ?>">
        </div>
        <div class="col-md-2">
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <?php foreach (['Admin', 'Faculty', 'Student', 'Guest'] as $x): ?>
                    <option <?= ($rf === $x ? 'selected' : '') ?>><?= $x ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <?php foreach (['Active', 'Pending', 'Inactive', 'Archived'] as $x): ?>
                    <option <?= ($sf === $x ? 'selected' : '') ?>><?= $x ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Search</button>
            <a href="users.php" class="btn btn-light">Reset</a>
        </div>
    </div>
</form>

<!-- ==========================================
     USER LIST TABLE
========================================== -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>User ID</th>
                    <th>Full Name & Credentials</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Grade Level</th>
                    <th>Storage & Plan</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $x): ?>
                    <?php
                        $isArchived = $x['status'] === 'Archived';
                        $statusBadgeClass = [
                            'Active' => 'success',
                            'Inactive' => 'secondary',
                            'Pending' => 'warning text-dark',
                            'Archived' => 'dark'
                        ][$x['status']] ?? 'secondary';
                    ?>
                    <tr class="<?= $isArchived ? 'table-light opacity-75' : '' ?>">
                        <td class="text-nowrap">
                            <span class="badge bg-light text-dark border">U-<?= str_pad($x['id'], 4, '0', STR_PAD_LEFT) ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold <?= $isArchived ? 'text-decoration-line-through text-muted' : '' ?>">
                                <?= e($x['full_name']) ?>
                            </div>
                            <div class="small text-muted">
                                <?= e($x['email']) ?>
                                <?php if (!empty($x['id_number'])): ?>
                                    &bull; ID: <strong><?= e($x['id_number']) ?></strong>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><code><?= e($x['username']) ?></code></td>
                        <td>
                            <span class="badge bg-<?= $x['role'] === 'Admin' ? 'primary' : ($x['role'] === 'Faculty' ? 'info text-dark' : 'secondary') ?>">
                                <?= e($x['role']) ?>
                            </span>
                        </td>
                        <td><?= e($x['department_name'] ?: '—') ?></td>
                        <td>
                            <?php if ($x['role'] === 'Student'): ?>
                                <span class="badge bg-<?= ($x['subscription_tier'] ?? 'Free') === 'Pro' ? 'warning text-dark' : 'secondary' ?>">
                                    <?= e($x['subscription_tier'] ?? 'Free') ?>
                                </span>
                                <small class="text-muted ms-1">(<?= (int)($x['storage_limit'] ?? 20) ?> slots)</small>
                            <?php else: ?>
                                <span class="text-muted small">Standard Unlimited</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $statusBadgeClass ?>">
                                <?php if ($isArchived): ?><i class="bi bi-archive-fill"></i> <?php endif; ?>
                                <?= e($x['status']) ?>
                            </span>
                            <?php if ($isArchived && !empty($x['archived_at'])): ?>
                                <div class="small text-muted" style="font-size:10px;">Archived <?= date('M d, Y', strtotime($x['archived_at'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php if ($isArchived): ?>
                                <!-- RESTORE SOFT-DELETED USER -->
                                <?= post_btn('restore', $x['id'], '<i class="bi bi-arrow-counterclockwise"></i> Restore', 'btn-success', 'Restore account for ' . $x['full_name'] . ' back to Active status?') ?>
                                <button class="btn btn-sm btn-outline-primary"
                                        data-u="<?= e(json_encode([
                                            'id' => $x['id'],
                                            'full_name' => $x['full_name'],
                                            'username' => $x['username'],
                                            'email' => $x['email'],
                                            'role' => $x['role'],
                                            'department_id' => $x['department_id'],
                                            'status' => $x['status'],
                                            'id_number' => $x['id_number'],
                                            'storage_limit' => $x['storage_limit'] ?? 20,
                                            'subscription_tier' => $x['subscription_tier'] ?? 'Free'
                                        ])) ?>"
                                        onclick="openUser(JSON.parse(this.dataset.u))"
                                        title="Edit Details">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a class="btn btn-sm btn-outline-secondary" href="logs.php?user=<?= $x['id'] ?>" title="View Activity History">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                            <?php else: ?>
                                <!-- ACTIVE / PENDING / INACTIVE USER ACTIONS -->
                                <button class="btn btn-sm btn-outline-primary"
                                        data-u="<?= e(json_encode([
                                            'id' => $x['id'],
                                            'full_name' => $x['full_name'],
                                            'username' => $x['username'],
                                            'email' => $x['email'],
                                            'role' => $x['role'],
                                            'department_id' => $x['department_id'],
                                            'status' => $x['status'],
                                            'id_number' => $x['id_number'],
                                            'storage_limit' => $x['storage_limit'] ?? 20,
                                            'subscription_tier' => $x['subscription_tier'] ?? 'Free'
                                        ])) ?>"
                                        onclick="openUser(JSON.parse(this.dataset.u))"
                                        title="Edit User">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <a class="btn btn-sm btn-outline-secondary" href="logs.php?user=<?= $x['id'] ?>" title="Activity Logs">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <?php if ($x['id'] != $u['id']): ?>
                                    <!-- Toggle Active/Inactive -->
                                    <?= post_btn('toggle', $x['id'], $x['status'] === 'Active' ? 'Deactivate' : 'Activate', $x['status'] === 'Active' ? 'btn-outline-secondary' : 'btn-outline-success', ($x['status'] === 'Active' ? 'Deactivate ' : 'Activate ') . $x['full_name'] . '?') ?>
                                    <!-- Archive / Soft-Delete -->
                                    <?= post_btn('archive', $x['id'], '<i class="bi bi-archive"></i> Archive', 'btn-outline-danger', 'Archive (soft-delete) user account for ' . $x['full_name'] . '? The user cannot log in, but all uploaded documents and history are preserved.') ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-person-x fs-1 d-block mb-2 text-muted"></i>
                            No users found matching the selected filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ==========================================
     ADD / EDIT USER MODAL
========================================== -->
<div class="modal fade" id="userM" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="save">
            <input type="hidden" name="id" id="uid">
            
            <div class="modal-header">
                <h5 class="modal-title" id="uTitle">User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input name="full_name" id="f_full_name" class="form-control" required placeholder="e.g. John Doe">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Username</label>
                    <input name="username" id="f_username" class="form-control" required placeholder="e.g. jdoe">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" id="f_email" class="form-control" required placeholder="user@university.edu">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password <small class="text-muted fw-normal" id="pwHint"></small></label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="At least 8 chars with letters & numbers">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Role</label>
                    <select name="role" id="f_role" class="form-select">
                        <?php foreach (['Admin', 'Faculty', 'Student', 'Guest'] as $x): ?>
                            <option><?= $x ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Grade Level</label>
                    <select name="department_id" id="f_department_id" class="form-select">
                        <option value="">None / Unassigned</option>
                        <?php foreach ($deps as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['department_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Account Status</label>
                    <select name="status" id="f_status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Pending">Pending Approval</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Archived">Archived (Soft-Deleted)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Subscription Plan (Student)</label>
                    <select name="subscription_tier" id="f_subscription_tier" class="form-select">
                        <option value="Free">Free (Standard 20 Docs Limit)</option>
                        <option value="Pro">Student Pro (100 Docs Capacity)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Upload Storage Quota (Files)</label>
                    <input type="number" name="storage_limit" id="f_storage_limit" min="1" max="1000" class="form-control" value="20">
                    <div class="form-text">Free default: 20 slots &bull; Pro default: 100 slots.</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Student / Employee ID Number</label>
                    <input name="id_number" id="f_id_number" class="form-control" placeholder="e.g. STU-2026-001 or FAC-001">
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save User Account</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUser(x) {
    const m = new bootstrap.Modal('#userM');
    document.getElementById('uid').value = x ? x.id : '';
    document.getElementById('uTitle').textContent = x ? 'Edit User Account' : 'Add New User';
    document.getElementById('pwHint').textContent = x ? '(leave blank to keep current)' : '(required for new user)';
    ['full_name', 'username', 'email', 'role', 'department_id', 'status', 'id_number'].forEach(k => {
        document.getElementById('f_' + k).value = x ? (x[k] ?? '') : (k === 'role' ? 'Student' : k === 'status' ? 'Active' : '');
    });
    document.getElementById('f_subscription_tier').value = x ? (x.subscription_tier || 'Free') : 'Free';
    document.getElementById('f_storage_limit').value = x ? (x.storage_limit || 20) : 20;
    m.show();
}
</script>

<?php include 'includes/footer.php'; ?>
