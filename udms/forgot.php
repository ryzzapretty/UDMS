<?php require 'config.php'; $done=false;
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check(); $em=trim($_POST['email']??'');
  $x=filter_var($em,FILTER_VALIDATE_EMAIL)?q("SELECT * FROM users WHERE email=?",[$em])->fetch():null;
  if($x){ notify_admins("Password reset requested by {$x['full_name']} ({$x['username']}).",'users.php'); log_act('Password reset requested',null,$x['username'].' requested a password reset',$x['id']); }
  $done=true; }
$public=true; $title='Forgot Password'; include 'includes/header.php';
$logoV = file_exists(__DIR__.'/assets/udms-logo.png') ? filemtime(__DIR__.'/assets/udms-logo.png') : 1;
?>

<link rel="stylesheet" href="assets/auth-theme.css?v=<?=@filemtime(__DIR__.'/assets/auth-theme.css')?>">
<div class="fl-page">

  <!-- TOP NAVIGATION BAR -->
  <nav class="fl-nav">
    <div class="fl-nav-brand">
      <img src="assets/udms-logo.png?v=<?=$logoV?>" class="fl-nav-logo" alt="UDMS Seal">
      <div class="fl-nav-sep"></div>
      <div class="fl-nav-name">
        <span class="fl-nav-title">UDMS</span>
        <span class="fl-nav-sub">St. Clare College of Caloocan</span>
      </div>
    </div>
    <div class="fl-nav-links">
      <a href="#" class="fl-nav-link"><i class="bi bi-house-fill"></i> Home</a>
      <a href="#" class="fl-nav-link"><i class="bi bi-info-circle-fill"></i> About</a>
      <a href="#" class="fl-nav-link"><i class="bi bi-envelope-fill"></i> Contact</a>
    </div>
  </nav>
  <div class="fl-nav-gold-bar"></div>

  <!-- HERO -->
  <div class="fl-hero">
    <div class="fl-card-wrap">
      <div class="fl-card">

        <div class="fl-card-brand">
          <img src="assets/udms-logo.png?v=<?=$logoV?>" class="fl-card-logo" alt="UDMS Seal">
          <div class="fl-card-brand-sep"></div>
          <div class="fl-card-brand-text">
            <h1 class="fl-card-title">UNIVERSITY DOCUMENT MANAGEMENT SYSTEM (UDMS)</h1>
            <div class="fl-card-tagline"><em>Manage &bull; Access &bull; Share</em></div>
          </div>
        </div>

        <div class="fl-card-hr"></div>

        <div class="fl-form">

          <div class="fl-login-heading">
            <i class="bi bi-lock-fill"></i>
            <span>Reset Your Password</span>
          </div>

          <?php if($done): ?>
            <div class="fl-success-box">
              <i class="bi bi-check-circle-fill"></i>
              <div>
                <strong>Request Sent!</strong><br>
                If that email belongs to an active account, the administrator has been notified and will reset your password shortly.
              </div>
            </div>
            <div style="text-align:center;margin-top:1rem;">
              <a href="login.php" class="fl-back-link"><i class="bi bi-arrow-left"></i> Back to Login</a>
            </div>
          <?php else: ?>
            <p class="fl-forgot-desc">Enter your registered institutional email address. The system administrator will be notified to reset your password.</p>

            <form method="post" autocomplete="on">
              <?=csrf_field()?>

              <div class="fl-field">
                <span class="fl-field-icon"><i class="bi bi-envelope-fill"></i></span>
                <input type="email" name="email" class="fl-input" placeholder="Your registered email address" required autofocus>
              </div>

              <button type="submit" class="fl-login-btn">
                <i class="bi bi-send-fill"></i> SEND RESET REQUEST
              </button>

              <div class="fl-secure-badge">
                <i class="bi bi-shield-fill-check"></i>
                <span>Secure &amp; Trusted System</span>
              </div>

              <div class="fl-register-row">
                Remembered your password? <a href="login.php">Log in here</a>
              </div>
            </form>
          <?php endif; ?>

        </div><!-- /fl-form -->

        <div class="fl-card-sketch">
          <img src="assets/campus-sketch.svg" alt="Campus Illustration">
        </div>

      </div><!-- /fl-card -->
    </div><!-- /fl-card-wrap -->

    <div class="fl-gold-wave" aria-hidden="true"></div>

  </div><!-- /fl-hero -->

  <div class="fl-bottom-swoosh" aria-hidden="true"></div>

</div><!-- /fl-page -->

<!-- UDMS AI BOT Launcher -->
<button type="button" class="fl-bot-launcher" id="flBotLauncher" aria-label="Open UDMS AI Bot">
  <div class="fl-bot-launcher-inner">
    <img src="assets/udms-logo.png?v=<?=$logoV?>" class="fl-bot-launcher-seal" alt="UDMS">
    <img src="assets/ai-robot-avatar.png" class="fl-bot-launcher-robot" alt="Bot">
  </div>
  <span class="fl-bot-launcher-label">UDMS<br>AI BOT</span>
  <span class="fl-bot-online-dot"></span>
</button>

<!-- AI Chat Window -->
<div class="ai-chat-window" id="aiChatWindow">
  <div class="ai-exact-header">
    <div class="ai-exact-header-left">
      <img src="assets/ai-robot-bust-exact.png" class="ai-exact-robot-bust" alt="UDMS AI Robot">
      <div class="ai-exact-header-text">
        <h3>UDMS AI Assistant</h3>
        <p>Ask me anything about university documentation and procedures!</p>
      </div>
    </div>
    <div class="ai-exact-header-right">
      <span class="ai-exact-online"><span class="ai-exact-online-dot"></span> Online</span>
      <button type="button" class="ai-exact-minimize-btn" id="closeAssistantBtn" title="Minimize">&minus;</button>
    </div>
  </div>
  <div class="ai-exact-body" id="assistantChatBody">
    <div class="ai-msg-row">
      <div class="ai-msg-avatar"><img src="assets/ai-robot-avatar.png" alt="Bot"></div>
      <div class="ai-msg-bubble bot">
        <div>Hello! &#128075;<br>I'm your <strong>UDMS AI Assistant</strong>.<br>You can ask me:<ul><li>How to reset my password?</li><li>How to contact the admin?</li><li>How to use UDMS?</li></ul>I'm here to help!</div>
        <span class="ai-msg-time text-muted">Now</span>
      </div>
    </div>
  </div>
  <div class="ai-exact-footer">
    <div class="ai-exact-input-pill">
      <button type="button" class="ai-exact-clip-btn" title="Attach file"><i class="bi bi-paperclip"></i></button>
      <input type="text" class="ai-exact-input" id="assistantInput" placeholder="Type your question here...">
      <button type="button" class="ai-exact-send-btn" id="assistantSendBtn" aria-label="Send Message"><i class="bi bi-send-fill"></i></button>
    </div>
  </div>
</div>

<script src="assets/auth-assistant.js"></script>
<?php include 'includes/footer.php'; ?>