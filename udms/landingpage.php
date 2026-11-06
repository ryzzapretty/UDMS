
<?php
// File: landingpage.php
// University Document Management System (UDMS)
// St. Clare College of Caloocan
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UDMS | St. Clare College of Caloocan</title>

<style>
* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    background: #05265d;
    font-family: Arial, sans-serif;
}

.page {
    position: relative;
    max-width: 1024px;
    margin: auto;
}

.page-image {
    width: 100%;
    display: block;
}

/* Clickable areas */
.click {
    position: absolute;
    z-index: 5;
    background: transparent;
    border: 0;
    padding: 0;
    cursor: pointer;
}

.click:focus-visible {
    outline: 3px solid #ffcc32;
}

/* Navigation */
.home {
    left: 41%;
    top: 1%;
    width: 9%;
    height: 3%;
}

.about {
    left: 52%;
    top: 1%;
    width: 9%;
    height: 3%;
}

.faq-nav {
    left: 62%;
    top: 1%;
    width: 9%;
    height: 3%;
}

.contact {
    left: 71%;
    top: 1%;
    width: 10%;
    height: 3%;
}

.login {
    left: 86%;
    top: 1%;
    width: 11%;
    height: 3%;
}

/* Get Started */
.get-started {
    left: 5%;
    top: 23%;
    width: 19%;
    height: 3.4%;
}

/* Learn More */
.learn-more {
    left: 34%;
    top: 49.5%;
    width: 16%;
    height: 3%;
}

/* FAQ clickable areas */
.faq-click {
    left: 30%;
    width: 66%;
    height: 2.5%;
}

.faq1 { top: 60.5%; }
.faq2 { top: 63.3%; }
.faq3 { top: 66.1%; }
.faq4 { top: 68.9%; }
.faq5 { top: 71.7%; }

/* Footer links */
.footer-home {
    left: 36%;
    top: 92%;
    width: 5%;
    height: 2%;
}

.footer-about {
    left: 42%;
    top: 92%;
    width: 5%;
    height: 2%;
}

.footer-faq {
    left: 48%;
    top: 92%;
    width: 5%;
    height: 2%;
}

.footer-contact {
    left: 54%;
    top: 92%;
    width: 7%;
    height: 2%;
}

/* FAQ modal */
.faq-modal {
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(0, 20, 55, 0.75);
    display: none;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.faq-modal.open {
    display: flex;
}

.faq-box {
    background: white;
    color: #05265d;
    padding: 28px;
    width: 500px;
    max-width: 100%;
    border-radius: 16px;
    box-shadow: 0 15px 35px #00163770;
}

.faq-box h2 {
    margin-top: 0;
    font-size: 22px;
}

.faq-box p {
    line-height: 1.7;
}

.faq-box button {
    border: none;
    border-radius: 25px;
    padding: 12px 25px;
    color: white;
    background: #05265d;
    cursor: pointer;
}

/* Floating robot */
.robot {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 101;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    border: 3px solid #ffcc32;
    background: linear-gradient(135deg, #1685ff, #05265d);
    font-size: 38px;
    cursor: pointer;
    box-shadow: 0 8px 25px #001a4a70;
}

/* Chatbot */
.chatbot {
    position: fixed;
    right: 20px;
    bottom: 105px;
    z-index: 100;
    width: 350px;
    max-width: calc(100vw - 30px);
    height: 465px;
    max-height: calc(100vh - 125px);
    background: white;
    border-radius: 18px;
    overflow: hidden;
    display: none;
    flex-direction: column;
    box-shadow: 0 12px 35px #00163770;
}

.chatbot.open {
    display: flex;
}

.chat-header {
    display: flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(110deg, #0755c9, #05265d);
    color: white;
    padding: 14px;
}

.chat-avatar {
    font-size: 30px;
}

.chat-title {
    flex: 1;
    font-weight: bold;
    font-size: 14px;
}

.chat-title small {
    display: block;
    color: #a5f0b5;
    font-size: 11px;
    margin-top: 4px;
}

.chat-close {
    background: transparent;
    color: white;
    border: none;
    font-size: 24px;
    cursor: pointer;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    background: #f5f9ff;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message {
    padding: 11px 13px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.5;
    max-width: 90%;
    overflow-wrap: anywhere;
}

.message.bot {
    background: #dceaff;
    color: #05265d;
    align-self: flex-start;
}

.message.user {
    background: #0755c9;
    color: white;
    align-self: flex-end;
}

.suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 10px;
}

.suggestions button {
    border: 1px solid #b8d4ff;
    background: white;
    color: #05265d;
    border-radius: 20px;
    padding: 7px 9px;
    font-size: 11px;
    cursor: pointer;
}

.chat-form {
    display: flex;
    gap: 8px;
    padding: 10px;
    border-top: 1px solid #dce7f7;
}

.chat-form input {
    flex: 1;
    min-width: 0;
    padding: 11px;
    border: 1px solid #bfd6f7;
    border-radius: 25px;
    outline: none;
}

.chat-form button {
    border: none;
    border-radius: 50%;
    background: #05265d;
    color: white;
    width: 40px;
    cursor: pointer;
}

/* Back to top */
.back-top {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 50;
    width: 44px;
    height: 44px;
    background: #05265d;
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    font-size: 22px;
    cursor: pointer;
}

@media (max-width: 600px) {
    .robot {
        width: 60px;
        height: 60px;
        font-size: 32px;
        right: 12px;
        bottom: 12px;
    }

    .chatbot {
        right: 10px;
        bottom: 85px;
        width: calc(100vw - 20px);
    }
}
</style>
</head>

<body>

<main class="page" id="home">

    <!-- Landing page design -->
    <img
        class="page-image"
        src="assets/udms-landing-design.png"
        alt="UDMS Landing Page"
    >

    <!-- Navigation -->
    <a class="click home" href="#home"
       aria-label="Home"></a>

    <a class="click about" href="#about"
       aria-label="About UDMS"></a>

    <a class="click faq-nav" href="#faqs"
       aria-label="FAQs"></a>

    <a class="click contact" href="#contact"
       aria-label="Contact"></a>

    <!-- Login -->
    <a class="click login" href="login.php"
       aria-label="Login"></a>

    <!-- Get Started -->
    <a class="click get-started" href="login.php"
       aria-label="Get Started"></a>

    <!-- Learn More -->
    <a class="click learn-more" href="#faqs"
       aria-label="Learn More"></a>

    <!-- FAQs -->
    <button class="click faq-click faq1"
        onclick="showFAQ(0)"
        aria-label="Who can use UDMS?"></button>

    <button class="click faq-click faq2"
        onclick="showFAQ(1)"
        aria-label="How do I upload a document?"></button>

    <button class="click faq-click faq3"
        onclick="showFAQ(2)"
        aria-label="Is my data secure?"></button>

    <button class="click faq-click faq4"
        onclick="showFAQ(3)"
        aria-label="What if I encounter an issue?"></button>

    <button class="click faq-click faq5"
        onclick="showFAQ(4)"
        aria-label="Can I access UDMS on mobile?"></button>

    <!-- Footer navigation -->
    <a class="click footer-home" href="#home"
       aria-label="Home"></a>

    <a class="click footer-about" href="#about"
       aria-label="About"></a>

    <a class="click footer-faq" href="#faqs"
       aria-label="FAQs"></a>

    <a class="click footer-contact" href="#contact"
       aria-label="Contact"></a>

    <!-- Section anchors -->
    <span id="about"
        style="position:absolute;top:37%;"></span>

    <span id="faqs"
        style="position:absolute;top:56%;"></span>

    <span id="contact"
        style="position:absolute;top:90%;"></span>

</main>

<!-- FAQ Popup -->
<div class="faq-modal" id="faqModal"
     role="dialog" aria-modal="true"
     aria-labelledby="faqTitle">

    <div class="faq-box">
        <h2 id="faqTitle"></h2>
        <p id="faqAnswer"></p>

        <button type="button" onclick="closeFAQ()">
            Close
        </button>
    </div>

</div>

<!-- Floating AI Assistant Button -->
<button class="robot" id="robotButton"
        type="button"
        aria-label="Open UDMS Assistant"
        aria-expanded="false">
    🤖
</button>

<!-- UDMS Assistant -->
<div class="chatbot" id="chatbot">

    <div class="chat-header">

        <div class="chat-avatar">🤖</div>

        <div class="chat-title">
            UDMS Assistant
            <small>● Online</small>
        </div>

        <button class="chat-close" id="closeChat"
                type="button" aria-label="Close chatbot">
            &times;
        </button>

    </div>

    <div class="chat-messages" id="chatMessages">

        <div class="message bot">
            Hello! 👋 I'm your UDMS Assistant.
            How can I help you today?
        </div>

    </div>

    <div class="suggestions">

        <button type="button"
                data-question="How do I upload a document?">
            Upload document
        </button>

        <button type="button"
                data-question="Where can I view my files?">
            View files
        </button>

        <button type="button"
                data-question="Is my data secure?">
            Data security
        </button>

        <button type="button"
                data-question="How do I reset my password?">
            Reset password
        </button>

    </div>

    <form class="chat-form" id="chatForm">

        <input type="text"
               id="chatInput"
               placeholder="Type your question..."
               maxlength="500"
               required>

        <button type="submit" aria-label="Send message">
            ➤
        </button>

    </form>

</div>

<!-- Back to Top -->
<button class="back-top" type="button"
        onclick="window.scrollTo({top:0,behavior:'smooth'})"
        aria-label="Back to top">
    ↑
</button>

<script>
/* FAQ Data */

const faqData = [
    {
        question: "Who can use the UDMS?",
        answer: "Authorized students, faculty, and staff of St. Clare College of Caloocan can use UDMS based on their assigned account permissions."
    },
    {
        question: "How do I upload a document?",
        answer: "Log in to UDMS, open the Documents section, and select the available upload option."
    },
    {
        question: "Is my data secure?",
        answer: "UDMS uses account authentication and role-based access permissions to help protect documents."
    },
    {
        question: "What should I do if I encounter an issue?",
        answer: "Contact your UDMS administrator or designated school support personnel."
    },
    {
        question: "Can I access my documents on mobile?",
        answer: "You can access UDMS using a compatible mobile browser when the server is reachable."
    }
];

function showFAQ(index) {
    const item = faqData[index];

    if (!item) return;

    document.getElementById("faqTitle").textContent =
        item.question;

    document.getElementById("faqAnswer").textContent =
        item.answer;

    document.getElementById("faqModal")
        .classList.add("open");
}

function closeFAQ() {
    document.getElementById("faqModal")
        .classList.remove("open");
}

document.getElementById("faqModal")
    .addEventListener("click", function(event) {
        if (event.target === this) {
            closeFAQ();
        }
    });

/* UDMS Chatbot */

const robotButton = document.getElementById("robotButton");
const chatbot = document.getElementById("chatbot");
const closeChat = document.getElementById("closeChat");
const chatMessages = document.getElementById("chatMessages");
const chatForm = document.getElementById("chatForm");
const chatInput = document.getElementById("chatInput");

function setChatOpen(open) {
    chatbot.classList.toggle("open", open);
    robotButton.setAttribute("aria-expanded", String(open));

    if (open) {
        chatInput.focus();
    }
}

robotButton.addEventListener("click", function() {
    setChatOpen(!chatbot.classList.contains("open"));
});

closeChat.addEventListener("click", function() {
    setChatOpen(false);
});

document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        closeFAQ();
        setChatOpen(false);
    }
});

function addMessage(text, sender) {
    const message = document.createElement("div");

    message.className = "message " + sender;
    message.textContent = text;

    chatMessages.appendChild(message);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function getAnswer(question) {
    const q = question.toLowerCase();

    if (q.includes("upload") || q.includes("submit")) {
        return "Log in to UDMS, open the Documents section, and use the available upload option.";
    }

    if (q.includes("password") || q.includes("forgot") || q.includes("reset")) {
        return "Use the password recovery option on the Login page if available, or contact your UDMS administrator.";
    }

    if (q.includes("secure") || q.includes("privacy") || q.includes("safe")) {
        return "UDMS uses authentication and role-based permissions to help protect documents.";
    }

    if (q.includes("login") || q.includes("sign in")) {
        return "Click Login or Get Started to open the UDMS login page.";
    }

    if (q.includes("register") || q.includes("sign up")) {
        return "Open the registration page and complete the required account information.";
    }

    if (q.includes("view") || q.includes("file") || q.includes("document")) {
        return "Log in and open the Documents section to view files available to your account.";
    }

    if (q.includes("contact") || q.includes("support") || q.includes("issue")) {
        return "Contact your UDMS administrator or designated school support personnel.";
    }

    if (q.includes("mobile") || q.includes("phone")) {
        return "You can use a compatible mobile browser when the UDMS server is reachable.";
    }

    if (q.includes("hello") || q.includes("hi") || q.includes("hey")) {
        return "Hello! I can help with UDMS login, registration, document uploads, and file access.";
    }

    return "I can answer common UDMS questions about login, registration, uploading documents, viewing files, password recovery, and account security.";
}

function askBot(question) {
    setChatOpen(true);
    addMessage(question, "user");
    addMessage(getAnswer(question), "bot");
}

document.querySelectorAll("[data-question]").forEach(button => {
    button.addEventListener("click", function() {
        askBot(this.dataset.question);
    });
});

chatForm.addEventListener("submit", function(event) {
    event.preventDefault();

    const question = chatInput.value.trim();

    if (!question) return;

    chatInput.value = "";
    askBot(question);
});
</script>

</body>
</html>
