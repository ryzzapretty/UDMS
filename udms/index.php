
<?php
// index.php
// University Document Management System
// St. Clare College of Caloocan

$schoolName = "St. Clare College of Caloocan";
$siteName = "UDMS";

$logo = "assets/udms-logo.png";
$campus = "assets/St. Clare College Campus Banner.png";
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>UDMS | St. Clare College of Caloocan</title>

<style>

/* ===========================
   GENERAL STYLING
=========================== */

:root {
    --navy: #05245d;
    --dark-blue: #001b4b;
    --blue: #0058cf;
    --light-blue: #eaf4ff;
    --gold: #ffc627;
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
    color: var(--navy);
    background: #f5f9ff;
}

a {
    text-decoration: none;
}

button {
    font-family: inherit;
    cursor: pointer;
}

.container {
    width: min(1200px, 94%);
    margin: auto;
}

section {
    scroll-margin-top: 85px;
}

/* ===========================
   HEADER
=========================== */

.header {
    width: 100%;
    background: linear-gradient(
        110deg,
        #00183f,
        #07357f,
        #001a49
    );
    border-bottom: 5px solid var(--gold);
    position: relative;
    z-index: 20;
}

.navbar {
    min-height: 86px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 17px;
    color: white;
}

.brand img {
    width: 78px;
    height: 78px;
    object-fit: contain;
}

.brand-text {
    padding-left: 18px;
    border-left: 2px solid white;
}

.brand-text h1 {
    font-size: 31px;
    font-weight: 800;
    letter-spacing: 1px;
}

.brand-text p {
    font-size: 13px;
    margin-top: 3px;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-links a {
    color: white;
    padding: 11px 17px;
    border-radius: 28px;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
    white-space: nowrap;
}

.nav-links a:hover,
.nav-links a.active {
    background: #0865d9;
}

.nav-links .login-btn {
    border: 2px solid white;
    padding: 10px 25px;
    margin-left: 20px;
}

.nav-links .login-btn:hover {
    border-color: var(--gold);
    background: var(--gold);
    color: var(--navy);
}

.menu-toggle {
    display: none;
    color: white;
    background: none;
    border: none;
    font-size: 28px;
}

/* ===========================
   HERO SECTION
=========================== */

.hero {
    position: relative;
    min-height: 465px;
    overflow: hidden;
    background:
        linear-gradient(
            90deg,
            rgba(226,242,255,0.96) 0%,
            rgba(218,237,255,0.91) 30%,
            rgba(218,237,255,0.25) 62%,
            rgba(0,40,100,0.05) 100%
        ),
        url("assets/St. Clare College Campus Banner.png");

    background-size: cover;
    background-position: center;
}

.hero::before {
    content: "";
    position: absolute;
    top: -38px;
    left: -5%;
    width: 110%;
    height: 60px;
    background: var(--gold);
    border-radius: 0 0 50% 50%;
    transform: rotate(-1deg);
}

.hero-content {
    position: relative;
    z-index: 2;
    padding-top: 55px;
    padding-bottom: 115px;
}

.hero-copy {
    max-width: 540px;
}

.welcome {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 12px;
}

.hero h2 {
    color: #062967;
    font-size: clamp(32px, 4.3vw, 53px);
    font-weight: 900;
    line-height: 1.06;
    letter-spacing: -1.5px;
}

.hero-tagline {
    margin-top: 17px;
    font-size: 18px;
    font-style: italic;
    font-weight: 700;
}

.hero-description {
    margin-top: 15px;
    font-size: 15px;
    line-height: 1.6;
    max-width: 460px;
}

.primary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    padding: 15px 35px;
    margin-top: 24px;
    background: linear-gradient(
        110deg,
        #0757c8,
        #001d56
    );
    color: white;
    border-radius: 35px;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 5px 15px #00245c45;
    transition: 0.3s;
}

.primary-btn:hover {
    transform: translateY(-3px);
    background: var(--gold);
    color: var(--navy);
}

.hero-wave {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 100px;
    z-index: 3;
}

.hero-wave svg {
    width: 100%;
    height: 100%;
    display: block;
}

/* ===========================
   FEATURES
=========================== */

.features-section {
    padding: 18px 0 40px;
    background: linear-gradient(
        #f4f9ff,
        #e8f3ff
    );
}

.features {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    background: rgba(255,255,255,0.94);
    border: 1px solid #d5e6ff;
    border-radius: 20px;
    box-shadow: 0 6px 18px #124c9220;
    padding: 20px 14px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 8px 18px;
}

.feature:not(:last-child) {
    border-right: 1px solid #0a58cf;
}

.feature-icon {
    flex-shrink: 0;
    width: 57px;
    height: 57px;
    border-radius: 50%;
    background: linear-gradient(
        135deg,
        #0c73ec,
        #00184d
    );
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 26px;
}

.feature h3 {
    font-size: 14px;
    margin-bottom: 6px;
}

.feature p {
    font-size: 12px;
    line-height: 1.5;
}

/* ===========================
   ABOUT SECTION
=========================== */

.about-section {
    background: linear-gradient(
        120deg,
        #e4f1ff,
        #ffffff 52%,
        #d8eaff
    );
    padding: 75px 0 85px;
    position: relative;
    overflow: hidden;
}

.about-layout {
    display: grid;
    grid-template-columns: 1fr 1.25fr 0.75fr;
    gap: 35px;
    align-items: center;
}

.about-art {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.about-art::before {
    content: "";
    position: absolute;
    width: 290px;
    height: 290px;
    border-radius: 50%;
    background: radial-gradient(
        circle,
        #8ac8ff,
        #d7edff 70%
    );
}

.laptop {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 320px;
}

.laptop-screen {
    width: 85%;
    height: 185px;
    margin: auto;
    background: linear-gradient(
        135deg,
        #071d51,
        #075bc7
    );
    border: 11px solid #142b4a;
    border-radius: 12px 12px 5px 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 15px 30px #002f6a40;
}

.laptop-screen img {
    width: 125px;
    height: 125px;
    object-fit: contain;
}

.laptop-base {
    width: 100%;
    height: 18px;
    background: linear-gradient(
        #b2c9dc,
        #657d9b
    );
    clip-path: polygon(
        10% 0,
        90% 0,
        100% 100%,
        0 100%
    );
    border-radius: 0 0 15px 15px;
}

.cloud-decoration {
    position: absolute;
    top: -50px;
    right: 0;
    font-size: 65px;
    z-index: 3;
}

.about-content h2 {
    font-size: 32px;
    margin-bottom: 7px;
}

.gold-line {
    width: 85px;
    height: 4px;
    background: var(--gold);
    margin-bottom: 16px;
}

.about-content p {
    font-size: 14px;
    line-height: 1.75;
    margin-bottom: 16px;
}

.about-content .primary-btn {
    margin-top: 8px;
    padding: 12px 27px;
    font-size: 14px;
}

.about-benefits {
    border-left: 2px solid #0a5cd4;
    padding-left: 25px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.benefit {
    display: flex;
    align-items: center;
    gap: 13px;
    font-size: 14px;
    font-weight: bold;
}

.check {
    background: var(--navy);
    color: white;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

/* ===========================
   FAQ SECTION
=========================== */

.faq-section {
    padding: 75px 0 85px;
    background: linear-gradient(
        140deg,
        #f7fbff,
        #e7f2ff
    );
}

.faq-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 50px;
    align-items: center;
}

.faq-art {
    text-align: center;
}

.question-icon {
    width: 155px;
    height: 155px;
    margin: 0 auto 25px;
    background: linear-gradient(
        135deg,
        #1385ff,
        #002465
    );
    border-radius: 50% 50% 50% 15%;
    color: white;
    display: grid;
    place-items: center;
    font-size: 105px;
    font-weight: bold;
    box-shadow: 0 12px 25px #0953b230;
}

.faq-art h3 {
    font-size: 28px;
    font-style: italic;
    font-family: Georgia, serif;
    transform: rotate(-6deg);
}

.faq-art p {
    margin-top: 10px;
    font-size: 22px;
    font-family: Georgia, serif;
    font-style: italic;
}

.faq-content h2 {
    font-size: 31px;
    margin-bottom: 7px;
}

.faq-subtitle {
    margin-bottom: 23px;
    font-size: 15px;
}

.faq-item {
    background: rgba(255,255,255,0.96);
    border: 1px solid #c9ddff;
    border-radius: 14px;
    margin-bottom: 12px;
    overflow: hidden;
    box-shadow: 0 3px 8px #0057c515;
}

.faq-question {
    width: 100%;
    padding: 15px 19px;
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 13px;
    text-align: left;
    color: var(--navy);
    font-size: 15px;
    font-weight: bold;
}

.faq-question .q-icon {
    background: var(--navy);
    color: white;
    width: 23px;
    height: 23px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.faq-question .plus {
    margin-left: auto;
    font-size: 24px;
    font-weight: 400;
}

.faq-answer {
    display: none;
    padding: 0 20px 17px 55px;
    color: #304a75;
    font-size: 14px;
    line-height: 1.7;
}

.faq-item.open .faq-answer {
    display: block;
}

.faq-item.open .faq-question {
    color: #0065db;
}

/* ===========================
   QUOTE BANNER
=========================== */

.quote-section {
    min-height: 240px;
    position: relative;
    background:
        linear-gradient(
            90deg,
            rgba(0,26,75,0.98),
            rgba(0,48,126,0.86),
            rgba(0,62,150,0.15)
        ),
        url("assets/St. Clare College Campus Banner.png");

    background-size: cover;
    background-position: center;
    border-top: 7px solid var(--gold);
    border-bottom: 7px solid var(--gold);
    display: flex;
    align-items: center;
}

.quote-content {
    color: white;
    padding: 55px 0;
}

.quote-content blockquote {
    max-width: 740px;
    font-family: Georgia, serif;
    font-size: clamp(25px, 3vw, 38px);
    font-style: italic;
    line-height: 1.4;
}

.quote-mark {
    color: var(--gold);
    font-size: 60px;
    line-height: 0.5;
}

/* ===========================
   FOOTER
=========================== */

.footer {
    background: linear-gradient(
        120deg,
        #001842,
        #063377,
        #001943
    );
    color: white;
    padding: 35px 0 20px;
}

.footer-layout {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 25px;
    flex-wrap: wrap;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 16px;
}

.footer-brand img {
    width: 80px;
    height: 80px;
    object-fit: contain;
}

.footer-brand h2 {
    font-size: 26px;
}

.footer-brand p {
    font-size: 12px;
    margin-top: 5px;
}

.footer-links {
    display: flex;
    align-items: center;
    gap: 13px;
    flex-wrap: wrap;
}

.footer-links a {
    color: white;
    font-size: 14px;
}

.footer-links a:hover {
    color: var(--gold);
}

.footer-social {
    display: flex;
    gap: 12px;
}

.footer-social a {
    background: white;
    color: var(--navy);
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: grid;
    place-items: center;
    font-size: 17px;
    font-weight: bold;
}

.footer-motto {
    font-family: Georgia, serif;
    font-style: italic;
    font-size: 17px;
}

.copyright {
    text-align: center;
    font-size: 12px;
    margin-top: 30px;
    color: #d8e9ff;
}

/* ===========================
   CHATBOT
=========================== */

.chatbot {
    position: fixed;
    right: 20px;
    top: 95px;
    width: 345px;
    height: 420px;
    max-height: calc(100vh - 110px);
    background: white;
    border-radius: 17px;
    box-shadow: 0 10px 35px #001b4b65;
    z-index: 1000;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: 1px solid #bad5ff;
}

.chatbot.hidden {
    display: none;
}

.chat-header {
    background: linear-gradient(
        110deg,
        #0754bd,
        #002363
    );
    padding: 13px;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chat-avatar {
    font-size: 31px;
}

.chat-header-text {
    flex: 1;
}

.chat-header-text strong {
    display: block;
    font-size: 13px;
}

.chat-header-text small {
    display: block;
    font-size: 11px;
    color: #b6ffce;
    margin-top: 4px;
}

.chat-controls {
    display: flex;
    gap: 9px;
}

.chat-controls button {
    color: white;
    background: none;
    border: none;
    font-size: 20px;
}

.chat-messages {
    flex: 1;
    padding: 13px;
    overflow-y: auto;
    background: #f4f8ff;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message {
    padding: 11px 13px;
    border-radius: 13px;
    font-size: 13px;
    line-height: 1.5;
    max-width: 90%;
    overflow-wrap: anywhere;
}

.message.bot {
    background: #dbe9ff;
    color: #092657;
    align-self: flex-start;
}

.message.user {
    background: #0656c6;
    color: white;
    align-self: flex-end;
}

.chat-suggestions {
    padding: 9px 11px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.chat-suggestions button {
    padding: 7px 10px;
    border: 1px solid #bcd6ff;
    border-radius: 18px;
    background: #f5f9ff;
    color: #0750b5;
    font-size: 11px;
}

.chat-suggestions button:hover {
    background: #dceaff;
}

.chat-input-area {
    display: flex;
    padding: 10px;
    gap: 8px;
    border-top: 1px solid #e0eaff;
}

.chat-input-area input {
    flex: 1;
    min-width: 0;
    border: 1px solid #c0d6f8;
    border-radius: 22px;
    padding: 11px;
    outline: none;
}

.chat-input-area button {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #05265d;
    color: white;
    border: none;
    font-size: 19px;
}

.chat-launcher {
    position: fixed;
    right: 22px;
    bottom: 22px;
    width: 65px;
    height: 65px;
    background: linear-gradient(
        135deg,
        #1379ef,
        #00235f
    );
    color: white;
    border: 3px solid var(--gold);
    border-radius: 50%;
    font-size: 33px;
    z-index: 999;
    box-shadow: 0 8px 25px #00235f65;
    display: none;
}

.chat-launcher.visible {
    display: block;
}

/* ===========================
   BACK TO TOP
=========================== */

.back-top {
    position: fixed;
    bottom: 20px;
    left: 20px;
    width: 44px;
    height: 44px;
    background: var(--navy);
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    font-size: 22px;
    z-index: 50;
}

/* ===========================
   RESPONSIVE
=========================== */

@media (max-width: 1050px) {

    .brand-text h1 {
        font-size: 25px;
    }

    .nav-links {
        gap: 2px;
    }

    .nav-links a {
        padding: 10px;
    }

    .features {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .feature:nth-child(2) {
        border-right: none;
    }

    .about-layout {
        grid-template-columns: 1fr 1.3fr;
    }

    .about-benefits {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: center;
        flex-wrap: wrap;
    }
}

@media (max-width: 750px) {

    .navbar {
        min-height: 75px;
    }

    .brand img {
        width: 58px;
        height: 58px;
    }

    .brand-text h1 {
        font-size: 23px;
    }

    .brand-text p {
        font-size: 10px;
    }

    .menu-toggle {
        display: block;
    }

    .nav-links {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #00265f;
        flex-direction: column;
        align-items: stretch;
        padding: 15px;
    }

    .nav-links.open {
        display: flex;
    }

    .nav-links a {
        padding: 13px 18px;
    }

    .nav-links .login-btn {
        margin: 8px 0;
        text-align: center;
    }

    .hero {
        min-height: 510px;
        background-position: 60% center;
    }

    .hero-content {
        padding-top: 65px;
    }

    .hero-copy {
        max-width: 440px;
    }

    .hero h2 {
        font-size: 37px;
    }

    .about-layout,
    .faq-layout {
        grid-template-columns: 1fr;
    }

    .about-art {
        margin-bottom: 20px;
    }

    .faq-art {
        margin-bottom: 20px;
    }

    .faq-section {
        padding: 50px 0;
    }

    .footer-layout {
        flex-direction: column;
        text-align: center;
    }

    .footer-brand {
        justify-content: center;
    }

    .chatbot {
        right: 10px;
        top: 85px;
        width: min(345px, calc(100vw - 20px));
    }
}

@media (max-width: 480px) {

    .hero h2 {
        font-size: 31px;
    }

    .welcome {
        font-size: 20px;
    }

    .hero-tagline {
        font-size: 15px;
    }

    .features {
        grid-template-columns: 1fr;
    }

    .feature {
        border-right: none !important;
        border-bottom: 1px solid #d4e5ff;
        padding: 15px;
    }

    .feature:last-child {
        border-bottom: none;
    }

    .about-content h2,
    .faq-content h2 {
        font-size: 26px;
    }

    .about-benefits {
        flex-direction: column;
        align-items: flex-start;
    }

    .quote-content blockquote {
        font-size: 24px;
    }
}

</style>
</head>

<body>

<!-- ===========================
     HEADER
=========================== -->

<header class="header" id="home">

    <div class="container navbar">

        <a href="#home" class="brand">

            <img
                src="<?php echo htmlspecialchars($logo); ?>"
                alt="UDMS Logo"
            >

            <div class="brand-text">
                <h1>UDMS</h1>
                <p>St. Clare College of Caloocan</p>
            </div>

        </a>

        <button
            class="menu-toggle"
            id="menuToggle"
            aria-label="Toggle navigation"
            aria-expanded="false"
        >
            ☰
        </button>

        <nav class="nav-links" id="navLinks">

            <a href="#home" class="active">⌂ &nbsp;Home</a>

            <a href="#about">● &nbsp;About</a>

            <a href="#faqs">? &nbsp;FAQs</a>

            <a href="#contact">✉ &nbsp;Contact</a>

            <a href="login.php" class="login-btn">
                ⇥ &nbsp;Login
            </a>

        </nav>

    </div>

</header>

<!-- ===========================
     HERO
=========================== -->

<section class="hero">

    <div class="container hero-content">

        <div class="hero-copy">

            <p class="welcome">
                Welcome to ────
            </p>

            <h2>
                University<br>
                Document Management<br>
                System (UDMS)
            </h2>

            <p class="hero-tagline">
                Manage • Access • Share – Anytime, Anywhere
            </p>

            <p class="hero-description">
                A secure and efficient platform for managing
                your academic documents at St. Clare College
                of Caloocan.
            </p>

            <a href="login.php" class="primary-btn">
                Get Started
                <span>→</span>
            </a>

        </div>

    </div>

    <div class="hero-wave">

        <svg
            viewBox="0 0 1440 150"
            preserveAspectRatio="none"
            aria-hidden="true"
        >

            <path
                fill="#ffc627"
                d="M0,35 C350,160 700,145 1000,70
                   C1180,25 1330,45 1440,95
                   L1440,150 L0,150 Z"
            />

            <path
                fill="#003b91"
                d="M0,55 C350,165 700,140 1000,82
                   C1180,45 1330,60 1440,110
                   L1440,150 L0,150 Z"
            />

            <path
                fill="#f4f9ff"
                d="M0,110 C360,150 690,150 1000,112
                   C1200,88 1360,105 1440,145
                   L1440,150 L0,150 Z"
            />

        </svg>

    </div>

</section>

<!-- ===========================
     FOUR FEATURES
=========================== -->

<section class="features-section">

    <div class="container">

        <div class="features">

            <div class="feature">

                <div class="feature-icon">🔒</div>

                <div>
                    <h3>Secure Storage</h3>
                    <p>
                        Keep your documents
                        safe and protected.
                    </p>
                </div>

            </div>

            <div class="feature">

                <div class="feature-icon">⚡</div>

                <div>
                    <h3>Fast Access</h3>
                    <p>
                        Find and manage
                        files with ease.
                    </p>
                </div>

            </div>

            <div class="feature">

                <div class="feature-icon">📁</div>

                <div>
                    <h3>Organized System</h3>
                    <p>
                        Stay organized with
                        structured folders.
                    </p>
                </div>

            </div>

            <div class="feature">

                <div class="feature-icon">☁</div>

                <div>
                    <h3>Anywhere Access</h3>
                    <p>
                        Access your documents
                        from any device.
                    </p>
                </div>

            </div>

        </div>

    </div>

</section>

<!-- ===========================
     ABOUT UDMS
=========================== -->

<section class="about-section" id="about">

    <div class="container about-layout">

        <div class="about-art">

            <span class="cloud-decoration">☁️</span>

            <div class="laptop">

                <div class="laptop-screen">

                    <img
                        src="<?php echo htmlspecialchars($logo); ?>"
                        alt="UDMS System"
                    >

                </div>

                <div class="laptop-base"></div>

            </div>

        </div>

        <div class="about-content">

            <h2>About UDMS</h2>

            <div class="gold-line"></div>

            <p>
                The University Document Management System
                (UDMS) is designed to help students, faculty,
                and staff easily manage their documents in
                a secure, efficient, and organized manner.
            </p>

            <p>
                With UDMS, you can upload, access, and share
                important files anytime, anywhere, ensuring
                a smoother and more convenient document
                management experience.
            </p>

            <a href="#faqs" class="primary-btn">
                Learn More →
            </a>

        </div>

        <div class="about-benefits">

            <div class="benefit">
                <span class="check">✓</span>
                Secure & Reliable
            </div>

            <div class="benefit">
                <span class="check">✓</span>
                Easy to Use
            </div>

            <div class="benefit">
                <span class="check">✓</span>
                Time-Saving
            </div>

            <div class="benefit">
                <span class="check">✓</span>
                Accessible Anytime
            </div>

        </div>

    </div>

</section>

<!-- ===========================
     FAQ SECTION
=========================== -->

<section class="faq-section" id="faqs">

    <div class="container faq-layout">

        <div class="faq-art">

            <div class="question-icon">?</div>

            <h3>Have Questions?</h3>

            <p>We're here to help!</p>

        </div>

        <div class="faq-content">

            <h2>Frequently Asked Questions</h2>

            <p class="faq-subtitle">
                Find answers to common questions about UDMS.
            </p>

            <div class="faq-item">

                <button
                    class="faq-question"
                    type="button"
                    aria-expanded="false"
                >
                    <span class="q-icon">?</span>
                    Who can use the UDMS?
                    <span class="plus">+</span>
                </button>

                <div class="faq-answer">
                    UDMS is designed for authorized students,
                    faculty, staff, and administrators of
                    St. Clare College of Caloocan.
                </div>

            </div>

            <div class="faq-item">

                <button
                    class="faq-question"
                    type="button"
                    aria-expanded="false"
                >
                    <span class="q-icon">?</span>
                    How do I upload a document?
                    <span class="plus">+</span>
                </button>

                <div class="faq-answer">
                    Log in to your UDMS account, navigate to
                    the document management section, select
                    the upload option, and choose your file.
                </div>

            </div>

            <div class="faq-item">

                <button
                    class="faq-question"
                    type="button"
                    aria-expanded="false"
                >
                    <span class="q-icon">?</span>
                    Is my data secure?
                    <span class="plus">+</span>
                </button>

                <div class="faq-answer">
                    UDMS is intended to protect documents
                    through user authentication and account
                    access permissions. Actual security
                    depends on the server configuration and
                    implemented protections.
                </div>

            </div>

            <div class="faq-item">

                <button
                    class="faq-question"
                    type="button"
                    aria-expanded="false"
                >
                    <span class="q-icon">?</span>
                    What should I do if I encounter an issue?
                    <span class="plus">+</span>
                </button>

                <div class="faq-answer">
                    Contact the UDMS administrator or
                    designated school support personnel
                    for assistance with your account
                    or documents.
                </div>

            </div>

            <div class="faq-item">

                <button
                    class="faq-question"
                    type="button"
                    aria-expanded="false"
                >
                    <span class="q-icon">?</span>
                    Can I access my documents on mobile?
                    <span class="plus">+</span>
                </button>

                <div class="faq-answer">
                    Yes, the website is designed to support
                    mobile browsers. Your device must be
                    able to connect to the UDMS server.
                </div>

            </div>

        </div>

    </div>

</section>

<!-- ===========================
     SCHOOL QUOTE BANNER
=========================== -->

<section class="quote-section">

    <div class="container quote-content">

        <blockquote>
            <span class="quote-mark">“</span>
            UDMS – Building a more organized
            and connected SCC.
            <span class="quote-mark">”</span>
        </blockquote>

    </div>

</section>

<!-- ===========================
     FOOTER
=========================== -->

<footer class="footer" id="contact">

    <div class="container">

        <div class="footer-layout">

            <div class="footer-brand">

                <img
                    src="<?php echo htmlspecialchars($logo); ?>"
                    alt="UDMS Logo"
                >

                <div>
                    <h2>UDMS</h2>
                    <p>St. Clare College of Caloocan</p>
                </div>

            </div>

            <div class="footer-links">

                <a href="#home">Home</a>
                <span>|</span>

                <a href="#about">About</a>
                <span>|</span>

                <a href="#faqs">FAQs</a>
                <span>|</span>

                <a href="#contact">Contact</a>

            </div>

            <div class="footer-social">

                <a href="#contact" aria-label="Contact">
                    ✉
                </a>

                <a href="#home" aria-label="Home">
                    ⌂
                </a>

            </div>

            <div class="footer-motto">
                Faith. Excellence. Service.
            </div>

        </div>

        <p class="copyright">
            © <?php echo date("Y"); ?>
            St. Clare College of Caloocan.
            All rights reserved.
        </p>

    </div>

</footer>

<!-- ===========================
     UDMS CHATBOT
=========================== -->

<div class="chatbot" id="chatbot">

    <div class="chat-header">

        <div class="chat-avatar">🤖</div>

        <div class="chat-header-text">

            <strong>UDMS AI Assistant</strong>

            <small>● Online</small>

        </div>

        <div class="chat-controls">

            <button
                type="button"
                id="minimizeChat"
                aria-label="Minimize chatbot"
            >
                −
            </button>

            <button
                type="button"
                id="closeChat"
                aria-label="Close chatbot"
            >
                ×
            </button>

        </div>

    </div>

    <div class="chat-messages" id="chatMessages">

        <div class="message bot">
            Hello! 👋<br>
            I'm your UDMS Assistant.
            How can I help you today?
        </div>

    </div>

    <div class="chat-suggestions">

        <button
            type="button"
            data-question="How do I upload a document?"
        >
            How do I upload a document?
        </button>

        <button
            type="button"
            data-question="Where can I view my files?"
        >
            Where can I view my files?
        </button>

        <button
            type="button"
            data-question="Is my data secure?"
        >
            Is my data secure?
        </button>

        <button
            type="button"
            data-question="How do I reset my password?"
        >
            How to reset my password?
        </button>

    </div>

    <form class="chat-input-area" id="chatForm">

        <input
            type="text"
            id="chatInput"
            placeholder="Type your question..."
            maxlength="500"
            required
        >

        <button type="submit" aria-label="Send">
            ➤
        </button>

    </form>

</div>

<!-- Open chatbot button -->

<button
    class="chat-launcher"
    id="chatLauncher"
    type="button"
    aria-label="Open chatbot"
>
    🤖
</button>

<!-- Back to top -->

<button
    class="back-top"
    id="backTop"
    type="button"
    aria-label="Back to top"
>
    ↑
</button>

<script>

/* ===========================
   MOBILE NAVIGATION
=========================== */

const menuToggle =
    document.getElementById("menuToggle");

const navLinks =
    document.getElementById("navLinks");

menuToggle.addEventListener("click", function() {

    const isOpen = navLinks.classList.toggle("open");

    menuToggle.setAttribute(
        "aria-expanded",
        String(isOpen)
    );

});

navLinks.querySelectorAll("a").forEach(link => {

    link.addEventListener("click", function() {

        navLinks.classList.remove("open");

        menuToggle.setAttribute(
            "aria-expanded",
            "false"
        );

    });

});

/* ===========================
   FAQ ACCORDION
=========================== */

const faqButtons =
    document.querySelectorAll(".faq-question");

faqButtons.forEach(button => {

    button.addEventListener("click", function() {

        const item = this.parentElement;

        const isOpen = item.classList.contains("open");

        document.querySelectorAll(".faq-item").forEach(faq => {

            faq.classList.remove("open");

            const faqButton =
                faq.querySelector(".faq-question");

            faqButton.setAttribute(
                "aria-expanded",
                "false"
            );

            faq.querySelector(".plus").textContent = "+";

        });

        if (!isOpen) {

            item.classList.add("open");

            this.setAttribute(
                "aria-expanded",
                "true"
            );

            this.querySelector(".plus").textContent = "−";

        }

    });

});

/* ===========================
   CHATBOT CONTROLS
=========================== */

const chatbot =
    document.getElementById("chatbot");

const chatLauncher =
    document.getElementById("chatLauncher");

const closeChat =
    document.getElementById("closeChat");

const minimizeChat =
    document.getElementById("minimizeChat");

const chatMessages =
    document.getElementById("chatMessages");

const chatForm =
    document.getElementById("chatForm");

const chatInput =
    document.getElementById("chatInput");

function hideChat() {

    chatbot.classList.add("hidden");

    chatLauncher.classList.add("visible");

}

function showChat() {

    chatbot.classList.remove("hidden");

    chatLauncher.classList.remove("visible");

    chatInput.focus();

}

closeChat.addEventListener("click", hideChat);

minimizeChat.addEventListener("click", hideChat);

chatLauncher.addEventListener("click", showChat);

/* ===========================
   ADD CHAT MESSAGE
=========================== */

function addMessage(text, sender) {

    const message = document.createElement("div");

    message.className = "message " + sender;

    message.textContent = text;

    chatMessages.appendChild(message);

    chatMessages.scrollTop =
        chatMessages.scrollHeight;

}

/* ===========================
   UDMS Q&A RESPONSES
=========================== */

function getBotAnswer(question) {

    const q = question.toLowerCase();

    if (
        q.includes("upload") ||
        q.includes("submit")
    ) {

        return "To upload a document, log in to your UDMS account, go to the Documents section, and choose the upload option.";

    }

    if (
        q.includes("view") ||
        q.includes("files") ||
        q.includes("find")
    ) {

        return "You can view your available files by logging in and opening the Documents section.";

    }

    if (
        q.includes("secure") ||
        q.includes("security") ||
        q.includes("safe")
    ) {

        return "UDMS uses account access permissions and authentication to help protect documents. Actual security depends on the system configuration.";

    }

    if (
        q.includes("password") ||
        q.includes("forgot") ||
        q.includes("reset")
    ) {

        return "If you forgot your password, open the Login page and select the Forgot Password option.";

    }

    if (
        q.includes("login") ||
        q.includes("sign in")
    ) {

        return "Click the Login button in the upper-right corner or the Get Started button to access the login page.";

    }

    if (
        q.includes("register") ||
        q.includes("sign up") ||
        q.includes("account")
    ) {

        return "You can create an account using the registration page, if registration is enabled by your UDMS administrator.";

    }

    if (
        q.includes("download")
    ) {

        return "Log in to UDMS, open an available document, and use its download option if your account has permission.";

    }

    if (
        q.includes("mobile") ||
        q.includes("phone")
    ) {

        return "UDMS is designed for mobile browsers. You need a connection to the UDMS server to access your documents.";

    }

    if (
        q.includes("contact") ||
        q.includes("help") ||
        q.includes("problem") ||
        q.includes("issue")
    ) {

        return "For assistance, contact your UDMS administrator or the designated school support personnel.";

    }

    if (
        q.includes("hello") ||
        q.includes("hi") ||
        q.includes("hey")
    ) {

        return "Hello! Welcome to UDMS. You can ask me about uploading documents, viewing files, account access, and password recovery.";

    }

    if (
        q.includes("what is udms") ||
        q.includes("about udms")
    ) {

        return "UDMS stands for University Document Management System. It helps authorized students, faculty, and staff organize and manage academic documents.";

    }

    return "I'm the UDMS Assistant. I can help answer questions about document uploads, viewing files, login, registration, password recovery, and account security.";

}

/* ===========================
   SEND MESSAGE
=========================== */

function askBot(question) {

    const cleanQuestion = question.trim();

    if (!cleanQuestion) {
        return;
    }

    addMessage(cleanQuestion, "user");

    const answer = getBotAnswer(cleanQuestion);

    addMessage(answer, "bot");

}

chatForm.addEventListener("submit", function(event) {

    event.preventDefault();

    const question = chatInput.value.trim();

    if (!question) {
        return;
    }

    chatInput.value = "";

    askBot(question);

});

/* Suggested questions */

document.querySelectorAll("[data-question]").forEach(button => {

    button.addEventListener("click", function() {

        askBot(this.dataset.question);

    });

});

/* ===========================
   BACK TO TOP
=========================== */

document.getElementById("backTop")
    .addEventListener("click", function() {

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });

    });

</script>

</body>
</html>
