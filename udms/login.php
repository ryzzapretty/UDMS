<?php
require_once __DIR__ . '/config.php';

if (me())
    redirect('dashboard.php');

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter your username and password.";
    } else {
        csrf_check();

        try {
            $user = q(
                "SELECT id, username, password, role, status FROM users WHERE username=? OR email=? OR id_number=? LIMIT 1",
                [$username, $username, $username]
            )->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $error = "Invalid username, email, student number, or password.";
            } elseif ($user['status'] !== 'Active') {
                $error = $user['status'] === 'Pending'
                    ? "Your account is pending administrator approval."
                    : ($user['status'] === 'Archived'
                        ? "This account has been archived. Please contact an administrator."
                        : "Your account is inactive. Please contact an administrator.");
            } elseif (!in_array($user['role'], ['Admin', 'Faculty', 'Student'], true)) {
                $error = "This account does not have access to UDMS.";
            } else {
                session_regenerate_id(true);
                $_SESSION['uid'] = (int) $user['id'];
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                unset($_SESSION['flash']);
                log_act('User logged in', null, $user['username'] . ' logged in', (int) $user['id']);
                redirect('dashboard.php');
            }
        } catch (PDOException $e) {
            $error = "Unable to sign in right now. Please check the database connection and try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | UDMS - St. Clare College of Caloocan</title>


    <style>
        /* =========================
   GENERAL
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
            overflow: hidden;

            background-image:
                linear-gradient(90deg,
                    rgba(0, 47, 112, 0.08),
                    rgba(0, 47, 112, 0.02)),
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

            background: linear-gradient(110deg,
                    #001b49,
                    #00316d,
                    #001c4c);

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
   MAIN LOGIN AREA
========================= */

        .main-content {
            position: relative;
            z-index: 3;

            flex: 1;
            width: min(1500px, 100%);
            margin: 0 auto;

            display: flex;
            align-items: center;

            padding: 55px 8% 95px;
        }

        /* =========================
   COMPACT LOGIN PANEL
========================= */

        .login-card {
            width: 570px;
            max-width: 100%;

            background: rgba(240, 248, 255, 0.94);

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
            padding: 22px 30px 0;
        }

        /* =========================
   LOGO AND HEADING
========================= */

        .card-heading {
            display: grid;
            grid-template-columns: 140px 2px minmax(0, 1fr);
            align-items: center;
            gap: 18px;
        }

        .card-logo {
            width: 140px;
            height: 140px;
            object-fit: contain;
        }

        .heading-divider {
            height: 125px;
            background: #082b64;
        }

        .heading-text {
            min-width: 0;
        }

        .heading-text h2 {
            font-size: 23px;
            line-height: 1.1;
            font-weight: 900;
            color: #08295f;
            overflow-wrap: break-word;
        }

        .heading-text p {
            font-size: 15px;
            font-style: italic;
            margin-top: 10px;
            color: #092e66;
            white-space: normal;
        }

        /* =========================
   CARD DIVIDER
========================= */

        .card-line {
            height: 2px;
            background: #b6d2ec;
            margin: 14px 0;
        }

        /* =========================
   LOGIN FORM
========================= */

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* =========================
   INPUT BOX
========================= */

        .input-box {
            display: flex;
            align-items: center;

            height: 50px;

            border: 1.5px solid #b4cde7;
            border-radius: 12px;

            background: rgba(255, 255, 255, 0.87);

            overflow: hidden;
            transition: 0.2s;
        }

        .input-box:focus-within {
            border-color: #0864cc;

            box-shadow:
                0 0 0 3px rgba(0, 96, 208, 0.12);
        }

        /* =========================
   INPUT ICON
========================= */

        .input-icon {
            width: 65px;
            height: 30px;

            display: flex;
            justify-content: center;
            align-items: center;

            flex-shrink: 0;

            border-right: 2px solid #cad9e8;
            color: #002b69;
        }

        .input-icon svg {
            width: 24px;
            height: 24px;
            fill: currentColor;
        }

        /* =========================
   INPUT TEXT
========================= */

        .input-box input {
            width: 100%;
            min-width: 0;

            border: none;
            outline: none;
            background: transparent;

            padding: 0 15px;

            font-size: 16px;
            color: #092b61;
        }

        .input-box input::placeholder {
            color: #395474;
            opacity: 1;
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
   LOGIN BUTTON
========================= */

        .login-btn {
            width: 100%;
            height: 52px;

            border: none;
            border-radius: 12px;

            background: linear-gradient(90deg,
                    #00265c,
                    #001d4c);

            color: white;

            font-size: 21px;
            font-weight: 800;
            letter-spacing: 0.5px;

            cursor: pointer;
            transition: 0.2s;
        }

        .login-btn:hover {
            background: #0759bc;
            transform: translateY(-2px);
        }

        /* =========================
   SECURITY LABEL
========================= */

        .security-label {
            text-align: center;

            color: #09295e;

            font-size: 15px;
            font-weight: 700;

            padding: 10px 0 8px;
        }

        /* =========================
   ERROR MESSAGE
========================= */

        .error-message {
            color: #a21c1c;
            background: #fff1f1;

            border: 1px solid #efb4b4;
            border-radius: 10px;

            padding: 10px 12px;
            margin-bottom: 12px;

            font-size: 13px;
        }

        .success-message {
            color: #0f5132;
            background: #d1e7dd;

            border: 1px solid #badbcc;
            border-radius: 10px;

            padding: 10px 12px;
            margin-bottom: 12px;

            font-size: 13px;
        }

        /* =========================
   CARD BOTTOM DECORATION
========================= */

        .card-bottom {
            position: relative;

            height: 65px;

            background: linear-gradient(180deg,
                    rgba(208, 232, 255, 0.2),
                    rgba(123, 189, 249, 0.42));

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
   RESPONSIVE TABLET
========================= */

        @media (max-width: 1000px) {

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
                padding: 65px 5% 110px;
            }
        }

        /* =========================
   RESPONSIVE MOBILE
========================= */

        @media (max-width: 700px) {

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
                font-size: 27px;
            }

            .brand-info p {
                font-size: 11px;
            }

            .nav-links {
                width: 100%;
                justify-content: center;
                gap: 22px;
            }

            .nav-links a {
                font-size: 13px;
            }

            .header-curve {
                display: none;
            }

            .main-content {
                justify-content: center;
                padding: 35px 15px 105px;
            }

            .login-card {
                width: 100%;
                border-radius: 22px;
            }

            .card-content {
                padding: 18px 18px 0;
            }

            .card-heading {
                grid-template-columns: 90px 2px minmax(0, 1fr);
                gap: 12px;
            }

            .card-logo {
                width: 90px;
                height: 95px;
            }

            .heading-divider {
                height: 90px;
            }

            .heading-text h2 {
                font-size: 17px;
                line-height: 1.1;
            }

            .heading-text p {
                font-size: 12px;
                margin-top: 7px;
                white-space: normal;
            }

            .input-box {
                height: 48px;
            }

            .input-icon {
                width: 55px;
            }

            .input-icon svg {
                width: 22px;
                height: 22px;
            }

            .input-box input {
                font-size: 15px;
                padding: 0 12px;
            }

            .login-btn {
                height: 50px;
                font-size: 19px;
            }

            .security-label {
                font-size: 13px;
            }

            .card-bottom {
                height: 60px;
            }

            .building-art {
                height: 70px;
            }

            .bottom-wave {
                height: 100px;
            }
        }

        /* =========================
   SMALL MOBILE SCREENS
========================= */

        @media (max-width: 390px) {

            .card-heading {
                grid-template-columns: 75px 2px minmax(0, 1fr);
                gap: 9px;
            }

            .card-logo {
                width: 75px;
                height: 85px;
            }

            .heading-divider {
                height: 80px;
            }

            .heading-text h2 {
                font-size: 15px;
            }

            .heading-text p {
                font-size: 11px;
            }

            .card-content {
                padding: 15px 14px 0;
            }

            .input-icon {
                width: 48px;
            }

            .input-box input {
                font-size: 14px;
                padding: 0 9px;
            }

            .nav-links {
                gap: 15px;
            }
        }
    </style>

</head>

<body>

    <div class="login-page">

        <!-- HEADER -->

        <header class="header">

            <a href="index.php" class="brand">

                <img class="brand-logo" src="assets/udms-logo.png" alt="UDMS Logo">

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

            <a href="register.php" style="color:var(--gold); font-weight:bold;">
                <span class="nav-icon">👤</span>
                Register
            </a>

            </nav>

        </header>

        <!-- HEADER CURVE -->

        <div class="header-curve">

            <svg viewBox="0 0 1600 100" preserveAspectRatio="none" aria-hidden="true">

                <path d="M0,75 C330,20 610,85 950,25
                   C1200,-12 1400,0 1600,-10" stroke="#ffd139" stroke-width="20" fill="none" />

                <path d="M0,55 C330,0 610,65 950,5
                   C1200,-32 1400,-20 1600,-30" stroke="#00265c" stroke-width="35" fill="none" />

            </svg>

        </div>

        <!-- MAIN CONTENT -->

        <main class="main-content">

            <section class="login-card">

                <div class="card-content">

                    <div class="card-heading">

                        <img class="card-logo" src="assets/udms-logo.png" alt="University Document Management System">

                        <div class="heading-divider"></div>

                        <div class="heading-text">

                            <h2>
                                UNIVERSITY<br>
                                DOCUMENT<br>
                                MANAGEMENT SYSTEM<br>
                                (UDMS)
                            </h2>

                            <p>
                                Manage • Access • Share
                            </p>

                        </div>

                    </div>

                    <div class="card-line"></div>

                    <?php if (!empty($flash)): ?>

                        <div class="success-message" role="status">
                            <?php
                            echo htmlspecialchars(
                                $flash['m'] ?? '',
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </div>

                    <?php endif; ?>

                    <?php if ($error !== ""): ?>

                        <div class="error-message" role="alert">
                            <?php
                            echo htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </div>

                    <?php endif; ?>

                    <!-- LOGIN FORM -->

                    <form class="login-form" method="POST" action="login.php">
                        <?php echo csrf_field(); ?>

                        <!-- USERNAME -->

                        <div class="input-box">

                            <span class="input-icon">

                                <svg viewBox="0 0 24 24" aria-hidden="true">

                                    <circle cx="12" cy="7" r="4" />

                                    <path d="M4 22v-3c0-4 3-6 8-6s8 2 8 6v3z" />

                                </svg>

                            </span>

                            <input type="text" name="username" placeholder="Username, Email, or Student Number"
                                aria-label="Username, Email, or Student Number" autocomplete="username"
                                value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>

                        </div>

                        <!-- PASSWORD -->

                        <div class="input-box">

                            <span class="input-icon">

                                <svg viewBox="0 0 24 24" aria-hidden="true">

                                    <path d="M7 10V7a5 5 0 0 1 10 0v3h2v12H5V10h2zm2 0h6V7a3 3 0 0 0-6 0v3z" />

                                </svg>

                            </span>

                            <input type="password" id="password" name="password" placeholder="Password"
                                aria-label="Password" autocomplete="current-password" required>

                            <button class="show-password" type="button" id="togglePassword" aria-label="Show password">
                                ◉
                            </button>

                        </div>

                        <!-- LOGIN BUTTON -->

                        <button type="submit" class="login-btn">
                            ⇥ LOGIN
                        </button>

                    </form>

                    <div class="security-label">
                        🛡 Secure &amp; Trusted System
                    </div>

                <div style="text-align: center; font-size: 14px; color: #092e66; margin-top: 10px;">
                    Don't have an account? <a href="register.php" style="color: #00378b; font-weight: 800; text-decoration: underline;">Create account here</a>
                </div>

                </div>

                <!-- DECORATIVE CAMPUS ART -->

                <div class="card-bottom">

                    <div class="building-art">

                        <svg viewBox="0 0 700 110" preserveAspectRatio="xMidYMax meet" aria-hidden="true">

                            <g fill="none" stroke="#1765bb" stroke-width="2">

                                <path d="M0 100H700" />

                                <path d="M110 100V45H250V100" />
                                <path d="M130 45V25H230V45" />
                                <path d="M145 25L180 8L215 25" />

                                <path d="M270 100V32H430V100" />
                                <path d="M290 32V18H410V32" />
                                <path d="M315 18L350 3L385 18" />

                                <path d="M450 100V45H590V100" />
                                <path d="M470 45V25H570V45" />
                                <path d="M485 25L520 8L555 25" />

                                <path d="M160 100V65H200V100" />
                                <path d="M330 100V58H370V100" />
                                <path d="M500 100V65H540V100" />

                                <path d="M20 100V55M40 100V42M60 100V58" />
                                <path d="M630 100V58M650 100V42M680 100V55" />

                                <circle cx="40" cy="32" r="20" />
                                <circle cx="650" cy="32" r="20" />

                            </g>

                            <g fill="#b7ddff" stroke="#1765bb" stroke-width="1.5">

                                <rect x="125" y="55" width="18" height="18" />
                                <rect x="215" y="55" width="18" height="18" />

                                <rect x="290" y="43" width="20" height="20" />
                                <rect x="390" y="43" width="20" height="20" />

                                <rect x="465" y="55" width="18" height="18" />
                                <rect x="555" y="55" width="18" height="18" />

                            </g>

                        </svg>

                    </div>

                </div>

            </section>

        </main>

        <!-- BOTTOM BLUE AND GOLD WAVE -->

        <div class="bottom-wave">

            <svg viewBox="0 0 1600 160" preserveAspectRatio="none" aria-hidden="true">

                <path d="M0,80 C400,190 1100,155 1600,35
                   L1600,160 L0,160 Z" fill="#ffd139" />

                <path d="M0,100 C400,195 1100,165 1600,65
                   L1600,160 L0,160 Z" fill="#07448f" />

                <path d="M0,118 C450,205 1100,175 1600,90
                   L1600,160 L0,160 Z" fill="#001e4c" />

            </svg>

        </div>

    </div>

    <script>

        const passwordInput =
            document.getElementById("password");

        const togglePassword =
            document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function () {

            if (passwordInput.type === "password") {

                passwordInput.type = "text";

                togglePassword.textContent = "◎";

                togglePassword.setAttribute(
                    "aria-label",
                    "Hide password"
                );

            } else {

                passwordInput.type = "password";

                togglePassword.textContent = "◉";

                togglePassword.setAttribute(
                    "aria-label",
                    "Show password"
                );

            }

        });

    </script>

</body>

</html>