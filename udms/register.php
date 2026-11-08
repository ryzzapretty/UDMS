<?php
require_once __DIR__ . '/config.php';

if (me()) redirect('dashboard.php');

$deps = q("SELECT * FROM departments ORDER BY id")->fetchAll();
$errors = [];

$first_name = '';
$last_name = '';
$username = '';
$email = '';
$user_type = '';
$department_id = 0;
$id_number = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $department_id = (int)($_POST['department_id'] ?? 0);
    $id_number = trim($_POST['id_number'] ?? '');

    // Strict Name Validation (No numbers, no special characters, no emojis)
    $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F1E6}-\x{1F1FF}]/u';

    // 1. First Name validation
    if ($first_name === '') {
        $errors[] = "First Name is required.";
    } elseif (preg_match('/\d/', $first_name)) {
        $errors[] = "First Name cannot contain numbers.";
    } elseif (preg_match($emojiPattern, $first_name)) {
        $errors[] = "First Name cannot contain emojis.";
    } elseif (!preg_match('/^[\p{L}\s\.\'-]{2,50}$/u', $first_name) || preg_match('/[!@#$%^&*()_+={}\[\]|\\:;"\'<>,?\/~`]/u', str_replace(['.', '-', "'"], '', $first_name))) {
        $errors[] = "First Name can only contain letters (no numbers, special characters, or emojis allowed).";
    }

    // 2. Last Name validation
    if ($last_name === '') {
        $errors[] = "Last Name is required.";
    } elseif (preg_match('/\d/', $last_name)) {
        $errors[] = "Last Name cannot contain numbers.";
    } elseif (preg_match($emojiPattern, $last_name)) {
        $errors[] = "Last Name cannot contain emojis.";
    } elseif (!preg_match('/^[\p{L}\s\.\'-]{2,50}$/u', $last_name) || preg_match('/[!@#$%^&*()_+={}\[\]|\\:;"\'<>,?\/~`]/u', str_replace(['.', '-', "'"], '', $last_name))) {
        $errors[] = "Last Name can only contain letters (no numbers, special characters, or emojis allowed).";
    }

    // 3. Username validation
    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
        $errors[] = "Username is required and must be 3-30 characters (letters, numbers, dot, or underscore).";
    }

    // 4. Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid Email Address is required.";
    }

    // 5. Password validation
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = "Password is required and must be at least 8 characters with both letters and numbers.";
    }

    // 6. Confirm Password validation
    if ($password !== $confirm) {
        $errors[] = "Password confirmation does not match.";
    }

    // 7. Role validation
    if (!in_array($user_type, ['Faculty', 'Student'], true)) {
        $errors[] = "Please select an Account Role (Faculty or Student).";
    }

    // 8. Grade Level / Department validation
    $deptExists = false;
    foreach ($deps as $d) {
        if ((int)$d['id'] === $department_id) {
            $deptExists = true;
            break;
        }
    }
    if (!$deptExists) {
        $errors[] = "Please choose a valid Grade Level / Department.";
    }

    // 9. Student Number / Employee ID validation
    if (!preg_match('/^[A-Za-z0-9\-]{3,30}$/', $id_number)) {
        $errors[] = "Student Number / Employee ID is required (letters, numbers, and dashes only).";
    }

    // Check duplicate username, email, or student ID
    if (empty($errors)) {
        $existing = q("SELECT username, email, id_number FROM users WHERE username=? OR email=? OR id_number=? LIMIT 1", [$username, $email, $id_number])->fetch();
        if ($existing) {
            if (strcasecmp($existing['username'], $username) === 0) {
                $errors[] = "That username is already taken. Please choose another.";
            } elseif (strcasecmp($existing['email'], $email) === 0) {
                $errors[] = "That email address is already registered.";
            } elseif (!empty($id_number) && strcasecmp($existing['id_number'], $id_number) === 0) {
                $errors[] = "That Student Number / Employee ID is already registered.";
            }
        }
    }

    // Insert new user
    if (empty($errors)) {
        $fullName = trim($first_name . ' ' . $last_name);
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        q(
            "INSERT INTO users (full_name, username, email, password, role, department_id, id_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')",
            [$fullName, $username, $email, $hashed, $user_type, $department_id, $id_number]
        );

        $newUserId = (int)db()->lastInsertId();

        log_act('User registered', null, "$fullName ($username) registered as $user_type", $newUserId);
        notify_admins("New $user_type account awaiting verification: $fullName ($username).", 'users.php');

        flash('success', "Your account has been created successfully! You can log in once an administrator approves your account.");
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | UDMS - St. Clare College of Caloocan</title>

<style>
/* =========================
   GENERAL VARIABLES & RESETS
========================= */
:root {
    --navy: #00245c;
    --deep-blue: #001b49;
    --gold: #ffd139;
    --white: #ffffff;
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
    min-height: 100vh;
    background: var(--deep-blue);
}

/* =========================
   MAIN PAGE
========================= */
.login-page {
    position: relative;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;

    background-image:
        linear-gradient(
            90deg,
            rgba(0, 47, 112, 0.08),
            rgba(0, 47, 112, 0.02)
        ),
        url("assets/St. Clare College Campus Banner.png");

    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
}

/* =========================
   HEADER
========================= */
.header {
    position: relative;
    z-index: 5;

    background: linear-gradient(
        110deg,
        #001b49,
        #00316d,
        #001c4c
    );

    min-height: 95px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 10px 4%;
    gap: 20px;

    border-bottom: 5px solid var(--gold);
}

.brand {
    display: flex;
    align-items: center;
    gap: 18px;

    text-decoration: none;
    color: white;
}

.brand-logo {
    width: 85px;
    height: 78px;
    object-fit: contain;
}

.brand-info {
    padding-left: 20px;
    border-left: 2px solid white;
}

.brand-info h1 {
    font-size: 36px;
    font-weight: 800;
    letter-spacing: 1px;
}

.brand-info p {
    font-size: 14px;
    margin-top: 2px;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 30px;
}

.nav-links a {
    color: white;
    font-size: 15px;
    text-decoration: none;

    display: flex;
    align-items: center;
    gap: 9px;

    transition: 0.2s;
}

.nav-links a:hover {
    color: var(--gold);
}

.nav-icon {
    font-size: 20px;
}

/* =========================
   GOLD CURVE BELOW HEADER
========================= */
.header-curve {
    position: absolute;
    top: 83px;
    left: 0;

    width: 100%;
    height: 85px;

    z-index: 4;
    pointer-events: none;
}

.header-curve svg {
    display: block;
    width: 100%;
    height: 100%;
}

/* =========================
   MAIN REGISTRATION AREA
========================= */
.main-content {
    position: relative;
    z-index: 3;

    flex: 1;
    width: min(1500px, 100%);
    margin: 0 auto;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 45px 5% 100px;
}

/* =========================
   COMPACT REGISTRATION PANEL
========================= */
.login-card {
    width: 720px;
    max-width: 100%;

    background: rgba(240, 248, 255, 0.95);

    border: 1px solid rgba(255, 255, 255, 0.85);
    border-radius: 25px;

    box-shadow:
        0 15px 35px rgba(0, 31, 79, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);

    backdrop-filter: blur(8px);
    overflow: hidden;
}

/* =========================
   CARD CONTENT
========================= */
.card-content {
    padding: 24px 32px 0;
}

/* =========================
   LOGO AND HEADING
========================= */
.card-heading {
    display: grid;
    grid-template-columns: 120px 2px minmax(0, 1fr);
    align-items: center;
    gap: 18px;
}

.card-logo {
    width: 120px;
    height: 120px;
    object-fit: contain;
}

.heading-divider {
    height: 110px;
    background: #082b64;
}

.heading-text {
    min-width: 0;
}

.heading-text h2 {
    font-size: 21px;
    line-height: 1.15;
    font-weight: 900;
    color: #08295f;
    overflow-wrap: break-word;
}

.heading-text p {
    font-size: 14px;
    font-style: italic;
    margin-top: 8px;
    color: #092e66;
    white-space: normal;
}

.card-line {
    height: 2px;
    background: #b6d2ec;
    margin: 14px 0;
}

/* =========================
   MESSAGES
========================= */
.error-message {
    color: #a21c1c;
    background: #fff1f1;
    border: 1px solid #efb4b4;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 12px;
    font-size: 13px;
    line-height: 1.4;
}

.error-message ul {
    margin: 4px 0 0 18px;
    padding: 0;
}

.error-message li {
    margin-bottom: 3px;
}

/* =========================
   REGISTRATION FORM & GRID
========================= */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    position: relative;
}

.field-label {
    font-size: 12px;
    font-weight: 700;
    color: #002b69;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.field-label .req {
    color: #c92a2a;
    margin-left: 3px;
}

.field-tip {
    font-size: 11px;
    color: #637b98;
    font-weight: normal;
}

/* =========================
   INPUT BOX
========================= */
.input-box {
    display: flex;
    align-items: center;

    height: 48px;

    border: 1.5px solid #b4cde7;
    border-radius: 12px;

    background: rgba(255, 255, 255, 0.92);

    overflow: hidden;
    transition: 0.2s;
    position: relative;
}

.input-box:focus-within {
    border-color: #0864cc;
    box-shadow: 0 0 0 3px rgba(0, 96, 208, 0.12);
}

.input-box.invalid-input {
    border-color: #c92a2a !important;
    background: #fffafa;
}

.input-icon {
    width: 55px;
    height: 30px;

    display: flex;
    justify-content: center;
    align-items: center;

    flex-shrink: 0;

    border-right: 2px solid #cad9e8;
    color: #002b69;
}

.input-icon svg {
    width: 22px;
    height: 22px;
    fill: currentColor;
}

.input-box input,
.input-box select {
    width: 100%;
    min-width: 0;
    height: 100%;

    border: none;
    outline: none;
    background: transparent;

    padding: 0 14px;

    font-size: 15px;
    color: #092b61;
    font-family: inherit;
}

.input-box select {
    cursor: pointer;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='10' viewBox='0 0 14 10'%3E%3Cpath fill='%23002b69' d='M7 10L0 0h14z'/%3E%3C/svg%3E") no-repeat right 14px center;
    background-size: 10px 8px;
    padding-right: 32px;
    appearance: none;
    -webkit-appearance: none;
}

.input-box input::placeholder {
    color: #4b678a;
    opacity: 0.9;
    font-size: 14px;
}

/* =========================
   REALTIME VALIDATION BADGE
========================= */
.validation-hint {
    font-size: 11px;
    color: #b02a37;
    margin-top: 2px;
    display: none;
    font-weight: 600;
}

.validation-hint.active {
    display: block;
}

/* =========================
   SHOW PASSWORD BUTTON
========================= */
.show-password {
    border: none;
    background: transparent;

    color: #002b69;

    padding: 0 14px;

    font-size: 20px;
    font-weight: bold;

    cursor: pointer;
}

.show-password:hover {
    color: #0864cc;
}

/* =========================
   SUBMIT BUTTON & LINKS
========================= */
.login-btn {
    width: 100%;
    height: 52px;
    margin-top: 6px;

    border: none;
    border-radius: 12px;

    background: linear-gradient(
        90deg,
        #00265c,
        #001d4c
    );

    color: white;

    font-size: 20px;
    font-weight: 800;
    letter-spacing: 0.5px;

    cursor: pointer;
    transition: 0.2s;
}

.login-btn:hover {
    background: #0759bc;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 36, 92, 0.35);
}

.login-link-row {
    text-align: center;
    font-size: 14px;
    color: #092e66;
    margin-top: 10px;
}

.login-link-row a {
    color: #00378b;
    font-weight: 800;
    text-decoration: underline;
    transition: 0.2s;
}

.login-link-row a:hover {
    color: #d19f00;
}

.security-label {
    text-align: center;
    color: #09295e;
    font-size: 14px;
    font-weight: 700;
    padding: 10px 0 14px;
}

/* =========================
   CARD BOTTOM DECORATION
========================= */
.card-bottom {
    position: relative;
    height: 65px;

    background: linear-gradient(
        180deg,
        rgba(208, 232, 255, 0.2),
        rgba(123, 189, 249, 0.42)
    );

    border-top: 1px solid #d7e9f9;
    overflow: hidden;
}

.building-art {
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);

    width: 95%;
    height: 75px;
    opacity: 0.72;
}

.building-art svg {
    width: 100%;
    height: 100%;
}

/* =========================
   BOTTOM BLUE AND GOLD WAVE
========================= */
.bottom-wave {
    position: absolute;
    bottom: 0;
    left: 0;

    width: 100%;
    height: 135px;

    z-index: 2;
    pointer-events: none;
}

.bottom-wave svg {
    width: 100%;
    height: 100%;
    display: block;
}

/* =========================
   RESPONSIVE DESIGN
========================= */
@media (max-width: 900px) {
    .header {
        padding: 10px 3%;
    }

    .brand-logo {
        width: 75px;
        height: 70px;
    }

    .brand-info h1 {
        font-size: 30px;
    }

    .brand-info p {
        font-size: 12px;
    }

    .nav-links {
        gap: 15px;
    }

    .nav-links a {
        font-size: 14px;
    }

    .main-content {
        padding: 55px 4% 110px;
    }
}

@media (max-width: 680px) {
    .header {
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 12px;
    }

    .brand {
        gap: 12px;
    }

    .brand-logo {
        width: 65px;
        height: 60px;
    }

    .brand-info {
        padding-left: 15px;
    }

    .brand-info h1 {
        font-size: 26px;
    }

    .brand-info p {
        font-size: 11px;
    }

    .nav-links {
        width: 100%;
        justify-content: center;
        gap: 20px;
    }

    .nav-links a {
        font-size: 13px;
    }

    .header-curve {
        display: none;
    }

    .main-content {
        padding: 30px 14px 105px;
    }

    .card-content {
        padding: 20px 16px 0;
    }

    .form-grid-2 {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .card-heading {
        grid-template-columns: 80px 2px minmax(0, 1fr);
        gap: 12px;
    }

    .card-logo {
        width: 80px;
        height: 80px;
    }

    .heading-divider {
        height: 85px;
    }

    .heading-text h2 {
        font-size: 16px;
    }

    .heading-text p {
        font-size: 12px;
    }

    .login-btn {
        height: 48px;
        font-size: 18px;
    }
}
</style>
</head>

<body>

<div class="login-page">

    <!-- HEADER -->
    <header class="header">
        <a href="index.php" class="brand">
            <img
                class="brand-logo"
                src="assets/udms-logo.png"
                alt="UDMS Logo"
            >
            <div class="brand-info">
                <h1>UDMS</h1>
                <p>St. Clare College of Caloocan</p>
            </div>
        </a>

        <nav class="nav-links">
            <a href="index.php">
                <span class="nav-icon">⌂</span>
                Home
            </a>
            <a href="index.php#about">
                <span class="nav-icon">●</span>
                About
            </a>
            <a href="index.php#contact">
                <span class="nav-icon">✉</span>
                Contact
            </a>
            <a href="login.php" style="color:var(--gold); font-weight:bold;">
                <span class="nav-icon">⇥</span>
                Login
            </a>
        </nav>
    </header>

    <!-- HEADER CURVE -->
    <div class="header-curve">
        <svg viewBox="0 0 1600 100" preserveAspectRatio="none" aria-hidden="true">
            <path
                d="M0,75 C330,20 610,85 950,25 C1200,-12 1400,0 1600,-10"
                stroke="#ffd139"
                stroke-width="20"
                fill="none"
            />
            <path
                d="M0,55 C330,0 610,65 950,5 C1200,-32 1400,-20 1600,-30"
                stroke="#00265c"
                stroke-width="35"
                fill="none"
            />
        </svg>
    </div>

    <!-- MAIN REGISTRATION CONTENT -->
    <main class="main-content">

        <section class="login-card">

            <div class="card-content">

                <!-- CARD HEADING -->
                <div class="card-heading">
                    <img
                        class="card-logo"
                        src="assets/udms-logo.png"
                        alt="University Document Management System"
                    >
                    <div class="heading-divider"></div>
                    <div class="heading-text">
                        <h2>
                            UNIVERSITY<br>
                            DOCUMENT<br>
                            MANAGEMENT SYSTEM<br>
                            (UDMS)
                        </h2>
                        <p>Student &amp; Faculty Registration</p>
                    </div>
                </div>

                <div class="card-line"></div>

                <!-- SERVER VALIDATION ERRORS -->
                <?php if (!empty($errors)): ?>
                    <div class="error-message" role="alert">
                        <strong>Please resolve the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- REGISTRATION FORM -->
                <form
                    class="login-form"
                    method="POST"
                    action="register.php"
                    id="registrationForm"
                    novalidate
                >
                    <?php echo csrf_field(); ?>

                    <!-- ROW 1: FIRST NAME & LAST NAME (STRICT VALIDATION: NO NUMBERS, SPECIAL CHARS, OR EMOJIS) -->
                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label" for="first_name">
                                <span>First Name<span class="req">*</span></span>
                                <span class="field-tip">Letters only</span>
                            </label>
                            <div class="input-box" id="firstNameBox">
                                <span class="input-icon" title="First Name">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="7" r="4"/>
                                        <path d="M4 22v-3c0-4 3-6 8-6s8 2 8 6v3z"/>
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    placeholder="e.g. Maria Clara"
                                    autocomplete="given-name"
                                    value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                    maxlength="50"
                                >
                            </div>
                            <div class="validation-hint" id="firstNameHint">
                                ⚠ Only letters are allowed. No numbers, symbols, or emojis.
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="last_name">
                                <span>Last Name<span class="req">*</span></span>
                                <span class="field-tip">Letters only</span>
                            </label>
                            <div class="input-box" id="lastNameBox">
                                <span class="input-icon" title="Last Name">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="7" r="4"/>
                                        <path d="M4 22v-3c0-4 3-6 8-6s8 2 8 6v3z"/>
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    placeholder="e.g. Delos Santos"
                                    autocomplete="family-name"
                                    value="<?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                    maxlength="50"
                                >
                            </div>
                            <div class="validation-hint" id="lastNameHint">
                                ⚠ Only letters are allowed. No numbers, symbols, or emojis.
                            </div>
                        </div>
                    </div>

                    <!-- ROW 2: USERNAME & EMAIL ADDRESS -->
                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label" for="username">
                                <span>Username<span class="req">*</span></span>
                            </label>
                            <div class="input-box">
                                <span class="input-icon" title="Username">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10h5v-2h-5c-4.34 0-8-3.66-8-8s3.66-8 8-8 8 3.66 8 8v1.43c0 .79-.71 1.57-1.5 1.57s-1.5-.78-1.5-1.57V12c0-2.76-2.24-5-5-5s-5 2.24-5 5 2.24 5 5 5c1.38 0 2.63-.56 3.54-1.46.65.89 1.77 1.46 2.96 1.46 1.93 0 3.5-1.57 3.5-3.57V12c0-5.52-4.48-10-10-10zm0 13c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/>
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    placeholder="e.g. marias26"
                                    autocomplete="username"
                                    value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                    maxlength="30"
                                >
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="email">
                                <span>Email Address<span class="req">*</span></span>
                            </label>
                            <div class="input-box">
                                <span class="input-icon" title="Email Address">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                    </svg>
                                </span>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="e.g. maria@gmail.com"
                                    autocomplete="email"
                                    value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                    maxlength="120"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- ROW 3: PASSWORD & CONFIRM PASSWORD -->
                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label" for="password">
                                <span>Password<span class="req">*</span></span>
                                <span class="field-tip">Min. 8 chars (letters &amp; numbers)</span>
                            </label>
                            <div class="input-box">
                                <span class="input-icon" title="Password">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M7 10V7a5 5 0 0 1 10 0v3h2v12H5V10h2zm2 0h6V7a3 3 0 0 0-6 0v3z"/>
                                    </svg>
                                </span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter password"
                                    autocomplete="new-password"
                                    required
                                >
                                <button
                                    class="show-password"
                                    type="button"
                                    id="togglePassword"
                                    aria-label="Show password"
                                    tabindex="-1"
                                >
                                    ◉
                                </button>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="confirm">
                                <span>Confirm Password<span class="req">*</span></span>
                            </label>
                            <div class="input-box">
                                <span class="input-icon" title="Confirm Password">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M7 10V7a5 5 0 0 1 10 0v3h2v12H5V10h2zm2 0h6V7a3 3 0 0 0-6 0v3z"/>
                                    </svg>
                                </span>
                                <input
                                    type="password"
                                    id="confirm"
                                    name="confirm"
                                    placeholder="Re-type password"
                                    autocomplete="new-password"
                                    required
                                >
                                <button
                                    class="show-password"
                                    type="button"
                                    id="toggleConfirm"
                                    aria-label="Show confirm password"
                                    tabindex="-1"
                                >
                                    ◉
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 4: ACCOUNT ROLE & GRADE LEVEL -->
                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label" for="user_type">
                                <span>Account Role<span class="req">*</span></span>
                            </label>
                            <div class="input-box">
                                <span class="input-icon" title="Account Role">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                    </svg>
                                </span>
                                <select id="user_type" name="user_type" required>
                                    <option value="" disabled <?php echo empty($user_type) ? 'selected' : ''; ?>>Select Account Role...</option>
                                    <option value="Student" <?php echo ($user_type === 'Student' || empty($user_type)) ? 'selected' : ''; ?>>Student</option>
                                    <option value="Faculty" <?php echo ($user_type === 'Faculty') ? 'selected' : ''; ?>>Faculty / Professor</option>
                                </select>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="department_id">
                                <span>Grade Level / Department<span class="req">*</span></span>
                            </label>
                            <div class="input-box">
                                <span class="input-icon" title="Grade Level / Department">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                    </svg>
                                </span>
                                <select id="department_id" name="department_id" required>
                                    <option value="" disabled <?php echo ($department_id === 0) ? 'selected' : ''; ?>>Select Grade Level...</option>
                                    <?php foreach ($deps as $d): ?>
                                        <option value="<?php echo (int)$d['id']; ?>" <?php echo ($department_id === (int)$d['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($d['department_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 5: STUDENT NUMBER / EMPLOYEE ID -->
                    <div class="field-group">
                        <label class="field-label" for="id_number">
                            <span>Student Number / Employee ID<span class="req">*</span></span>
                            <span class="field-tip">e.g. STU-2026-001 or 2024-00123</span>
                        </label>
                        <div class="input-box">
                            <span class="input-icon" title="ID Number">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4V6h16v12zM6 10h4v2H6zm0-3h12v2H6zm0 6h4v2H6zm6-3h6v5h-6z"/>
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="id_number"
                                name="id_number"
                                placeholder="Student Number / Employee ID"
                                autocomplete="off"
                                value="<?php echo htmlspecialchars($id_number, ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                maxlength="30"
                            >
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <button type="submit" class="login-btn" id="submitBtn">
                        ⇥ CREATE ACCOUNT
                    </button>

                    <!-- LOGIN LINK -->
                    <div class="login-link-row">
                        Already have an account? <a href="login.php">Log in here</a>
                    </div>

                    <!-- SECURITY BADGE -->
                    <div class="security-label">
                        🛡 Secure &amp; Verified Academic System
                    </div>

                </form>

            </div>

            <!-- DECORATIVE CAMPUS ART -->
            <div class="card-bottom">
                <div class="building-art">
                    <svg viewBox="0 0 700 110" preserveAspectRatio="xMidYMax meet" aria-hidden="true">
                        <g fill="none" stroke="#1765bb" stroke-width="2">
                            <path d="M0 100H700"/>
                            <path d="M110 100V45H250V100"/>
                            <path d="M130 45V25H230V45"/>
                            <path d="M145 25L180 8L215 25"/>
                            <path d="M270 100V32H430V100"/>
                            <path d="M290 32V18H410V32"/>
                            <path d="M315 18L350 3L385 18"/>
                            <path d="M450 100V45H590V100"/>
                            <path d="M470 45V25H570V45"/>
                            <path d="M485 25L520 8L555 25"/>
                            <path d="M160 100V65H200V100"/>
                            <path d="M330 100V58H370V100"/>
                            <path d="M500 100V65H540V100"/>
                            <path d="M20 100V55M40 100V42M60 100V58"/>
                            <path d="M630 100V58M650 100V42M680 100V55"/>
                            <circle cx="40" cy="32" r="20"/>
                            <circle cx="650" cy="32" r="20"/>
                        </g>
                        <g fill="#b7ddff" stroke="#1765bb" stroke-width="1.5">
                            <rect x="125" y="55" width="18" height="18"/>
                            <rect x="215" y="55" width="18" height="18"/>
                            <rect x="290" y="43" width="20" height="20"/>
                            <rect x="390" y="43" width="20" height="20"/>
                            <rect x="465" y="55" width="18" height="18"/>
                            <rect x="555" y="55" width="18" height="18"/>
                        </g>
                    </svg>
                </div>
            </div>

        </section>

    </main>

    <!-- BOTTOM BLUE AND GOLD WAVE -->
    <div class="bottom-wave">
        <svg viewBox="0 0 1600 160" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0,80 C400,190 1100,155 1600,35 L1600,160 L0,160 Z" fill="#ffd139"/>
            <path d="M0,100 C400,195 1100,165 1600,65 L1600,160 L0,160 Z" fill="#07448f"/>
            <path d="M0,118 C450,205 1100,175 1600,90 L1600,160 L0,160 Z" fill="#001e4c"/>
        </svg>
    </div>

</div>

<script>
/* ========================================================
   STRICT FIRST NAME & LAST NAME VALIDATION
   Rule: NO NUMBERS, NO SPECIAL CHARACTERS, NO EMOJIS
======================================================== */
const firstNameInput = document.getElementById("first_name");
const lastNameInput = document.getElementById("last_name");
const firstNameHint = document.getElementById("firstNameHint");
const lastNameHint = document.getElementById("lastNameHint");
const firstNameBox = document.getElementById("firstNameBox");
const lastNameBox = document.getElementById("lastNameBox");

// Regex allowing only letters (including accented), spaces, and standard name separators (. ' -)
// Strictly blocks any digits, emojis, and symbols (!@#$%^&*()_+={}[] etc.)
const invalidNameCharsRegex = /[^a-zA-Z\u00C0-\u024F\s\.\'\-]/g;

function handleStrictNameFilter(inputEl, hintEl, boxEl) {
    let original = inputEl.value;
    // Replace any disallowed character immediately
    let sanitized = original.replace(invalidNameCharsRegex, "");

    if (original !== sanitized) {
        inputEl.value = sanitized;
        hintEl.classList.add("active");
        boxEl.classList.add("invalid-input");
        setTimeout(() => {
            hintEl.classList.remove("active");
            boxEl.classList.remove("invalid-input");
        }, 3000);
    }
}

// Attach input, paste, and keydown listeners to First Name
firstNameInput.addEventListener("input", function() {
    handleStrictNameFilter(this, firstNameHint, firstNameBox);
});
firstNameInput.addEventListener("paste", function() {
    setTimeout(() => handleStrictNameFilter(this, firstNameHint, firstNameBox), 10);
});

// Attach input, paste, and keydown listeners to Last Name
lastNameInput.addEventListener("input", function() {
    handleStrictNameFilter(this, lastNameHint, lastNameBox);
});
lastNameInput.addEventListener("paste", function() {
    setTimeout(() => handleStrictNameFilter(this, lastNameHint, lastNameBox), 10);
});

// Keydown prevention for numbers and common special characters
[firstNameInput, lastNameInput].forEach(el => {
    el.addEventListener("keydown", function(e) {
        // Allow control keys (Backspace, Tab, Delete, Arrow keys, etc.)
        if (e.ctrlKey || e.altKey || e.metaKey || e.key.length > 1) {
            return;
        }
        // Block numbers (0-9)
        if (/[0-9]/.test(e.key)) {
            e.preventDefault();
            const hint = el === firstNameInput ? firstNameHint : lastNameHint;
            const box = el === firstNameInput ? firstNameBox : lastNameBox;
            hint.textContent = "⚠ Numbers are not allowed in names.";
            hint.classList.add("active");
            box.classList.add("invalid-input");
            setTimeout(() => {
                hint.classList.remove("active");
                box.classList.remove("invalid-input");
            }, 2500);
            return;
        }
        // Block special characters and emojis
        if (invalidNameCharsRegex.test(e.key)) {
            e.preventDefault();
            const hint = el === firstNameInput ? firstNameHint : lastNameHint;
            const box = el === firstNameInput ? firstNameBox : lastNameBox;
            hint.textContent = "⚠ Special characters and emojis are not allowed in names.";
            hint.classList.add("active");
            box.classList.add("invalid-input");
            setTimeout(() => {
                hint.classList.remove("active");
                box.classList.remove("invalid-input");
            }, 2500);
            return;
        }
    });
});

/* ========================================================
   PASSWORD TOGGLES
======================================================== */
const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
togglePassword.addEventListener("click", function() {
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        togglePassword.textContent = "◎";
        togglePassword.setAttribute("aria-label", "Hide password");
    } else {
        passwordInput.type = "password";
        togglePassword.textContent = "◉";
        togglePassword.setAttribute("aria-label", "Show password");
    }
});

const confirmInput = document.getElementById("confirm");
const toggleConfirm = document.getElementById("toggleConfirm");
toggleConfirm.addEventListener("click", function() {
    if (confirmInput.type === "password") {
        confirmInput.type = "text";
        toggleConfirm.textContent = "◎";
        toggleConfirm.setAttribute("aria-label", "Hide confirm password");
    } else {
        confirmInput.type = "password";
        toggleConfirm.textContent = "◉";
        toggleConfirm.setAttribute("aria-label", "Show confirm password");
    }
});

/* ========================================================
   CLIENT-SIDE FORM VALIDATION BEFORE SUBMIT
======================================================== */
const form = document.getElementById("registrationForm");
form.addEventListener("submit", function(e) {
    let isValid = true;
    let errorMsg = [];

    const fn = firstNameInput.value.trim();
    const ln = lastNameInput.value.trim();
    const un = document.getElementById("username").value.trim();
    const em = document.getElementById("email").value.trim();
    const pw = passwordInput.value;
    const cf = confirmInput.value;
    const ut = document.getElementById("user_type").value;
    const dep = document.getElementById("department_id").value;
    const idn = document.getElementById("id_number").value.trim();

    if (!fn || invalidNameCharsRegex.test(fn) || /\d/.test(fn)) {
        isValid = false;
        errorMsg.push("First Name is required and can only contain letters (no numbers, symbols, or emojis).");
        firstNameBox.classList.add("invalid-input");
    }
    if (!ln || invalidNameCharsRegex.test(ln) || /\d/.test(ln)) {
        isValid = false;
        errorMsg.push("Last Name is required and can only contain letters (no numbers, symbols, or emojis).");
        lastNameBox.classList.add("invalid-input");
    }
    if (!un || un.length < 3) {
        isValid = false;
        errorMsg.push("Username is required (min 3 characters).");
    }
    if (!em || !em.includes("@")) {
        isValid = false;
        errorMsg.push("A valid Email Address is required.");
    }
    if (!pw || pw.length < 8) {
        isValid = false;
        errorMsg.push("Password must be at least 8 characters.");
    }
    if (pw !== cf) {
        isValid = false;
        errorMsg.push("Passwords do not match.");
    }
    if (!ut) {
        isValid = false;
        errorMsg.push("Please select an Account Role.");
    }
    if (!dep) {
        isValid = false;
        errorMsg.push("Please select a Grade Level.");
    }
    if (!idn) {
        isValid = false;
        errorMsg.push("Student Number / Employee ID is required.");
    }

    if (!isValid) {
        e.preventDefault();
        alert(errorMsg.join("\n"));
    }
});
</script>

</body>
</html>
