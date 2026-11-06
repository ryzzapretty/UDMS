<?php require 'config.php'; if(me()) redirect('dashboard.php');
$deps=q("SELECT * FROM departments ORDER BY department_name")->fetchAll(); $err=[];
if($_SERVER['REQUEST_METHOD']==='POST'){ csrf_check();
  $n=trim($_POST['full_name']??''); $un=trim($_POST['username']??''); $em=trim($_POST['email']??''); $pw=$_POST['password']??''; $cf=$_POST['confirm']??'';
  $ty=$_POST['user_type']??''; $dep=(int)($_POST['department_id']??0); $idn=trim($_POST['id_number']??'');
  if(strlen($n)<3||strlen($n)>120) $err[]='Enter your full name.';
  if(!preg_match('/^[A-Za-z0-9_.]{3,30}$/',$un)) $err[]='Username must be 3-30 characters: letters, numbers, dot or underscore.';
  if(!filter_var($em,FILTER_VALIDATE_EMAIL)) $err[]='Enter a valid email address.';
  if(strlen($pw)<8||!preg_match('/[A-Za-z]/',$pw)||!preg_match('/\d/',$pw)) $err[]='Password needs at least 8 characters with letters and numbers.';
  if($pw!==$cf) $err[]='Passwords do not match.';
  if(!in_array($ty,['Faculty','Student'],true)) $err[]='Choose Faculty or Student.';
  if(!q("SELECT 1 FROM departments WHERE id=?",[$dep])->fetch()) $err[]='Choose your department.';
  if(!preg_match('/^[A-Za-z0-9\-]{3,30}$/',$idn)) $err[]='Enter your Student/Employee ID (letters, numbers, dashes).';
  if(!$err && q("SELECT 1 FROM users WHERE username=? OR email=?",[$un,$em])->fetch()) $err[]='That username or email is already registered.';
  if(!$err){ q("INSERT INTO users(full_name,username,email,password,role,department_id,id_number,status) VALUES(?,?,?,?,?,?,?,'Pending')",[$n,$un,$em,password_hash($pw,PASSWORD_DEFAULT),$ty,$dep,$idn]);
    log_act('User registered',null,"$n ($un) registered as $ty",null); notify_admins("New $ty account awaiting verification: $n ($un).",'users.php');
    flash('success','Account created. You can log in once the administrator verifies it.'); redirect('login.php'); } }
$public=true; $title='Create Account'; include 'includes/header.php';
$v=fn($k)=>e($_POST[$k]??'');
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
  <div class="fl-hero fl-hero-register">
    <div class="fl-card-wrap fl-reg-card-wrap">
      <div class="fl-card fl-reg-card">

        <div class="fl-card-brand">
          <img src="assets/udms-logo.png?v=<?=$logoV?>" class="fl-card-logo" alt="UDMS Seal">
          <div class="fl-card-brand-sep"></div>
          <div class="fl-card-brand-text">
            <h1 class="fl-card-title">UNIVERSITY DOCUMENT MANAGEMENT SYSTEM (UDMS)</h1>
            <div class="fl-card-tagline"><em>Manage &bull; Access &bull; Share</em></div>
          </div>
        </div>

        <div class="fl-card-hr"></div>

        <div class="fl-reg-heading">
          <i class="bi bi-person-plus-fill"></i>
          <span>Create Your Account</span>
        </div>

        <?php if($err): ?>
          <div class="fl-alert fl-alert-reg">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <ul class="mb-0 ps-3">
              <?php foreach($err as $x) echo '<li>'.e($x).'</li>'; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" autocomplete="on" class="fl-form fl-reg-form">
          <?=csrf_field()?>

          <div class="fl-field">
            <span class="fl-field-icon"><i class="bi bi-person-fill"></i></span>
            <input type="text" name="full_name" class="fl-input" placeholder="Full Name" required value="<?=$v('full_name')?>">
          </div>

          <div class="fl-reg-row">
            <div class="fl-field">
              <span class="fl-field-icon"><i class="bi bi-at"></i></span>
              <input type="text" name="username" class="fl-input" placeholder="Username" required value="<?=$v('username')?>">
            </div>
            <div class="fl-field">
              <span class="fl-field-icon"><i class="bi bi-envelope-fill"></i></span>
              <input type="email" name="email" class="fl-input" placeholder="Email Address" required value="<?=$v('email')?>">
            </div>
          </div>

          <div class="fl-reg-row">
            <div class="fl-field">
              <span class="fl-field-icon"><i class="bi bi-lock-fill"></i></span>
              <input type="password" name="password" id="regPwField" class="fl-input" placeholder="Password" required>
              <button type="button" id="regTogglePw" class="fl-eye-btn" aria-label="Toggle password">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="fl-field">
              <span class="fl-field-icon"><i class="bi bi-lock-fill"></i></span>
              <input type="password" name="confirm" id="regCfField" class="fl-input" placeholder="Confirm Password" required>
              <button type="button" id="regToggleCf" class="fl-eye-btn" aria-label="Toggle confirm">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="fl-reg-row">
            <div class="fl-field fl-select-field">
              <span class="fl-field-icon"><i class="bi bi-person-badge-fill"></i></span>
              <select name="user_type" class="fl-input fl-select" required>
                <option value="">Account Role...</option>
                <option value="Student" <?=$v('user_type')==='Student'?'selected':''?>>Student</option>
                <option value="Faculty" <?=$v('user_type')==='Faculty'?'selected':''?>>Faculty</option>
              </select>
            </div>
            <div class="fl-field fl-select-field">
              <span class="fl-field-icon"><i class="bi bi-building"></i></span>
              <select name="department_id" class="fl-input fl-select" required>
                <option value="">Department...</option>
                <?php foreach($deps as $d): ?>
                  <option value="<?=$d['id']?>" <?=(int)$v('department_id')===$d['id']?'selected':''?>><?=e($d['department_name'])?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="fl-field">
            <span class="fl-field-icon"><i class="bi bi-card-text"></i></span>
            <input type="text" name="id_number" class="fl-input" placeholder="Student / Employee ID Number" required value="<?=$v('id_number')?>">
          </div>

          <button type="submit" class="fl-login-btn">
            <i class="bi bi-person-check-fill"></i> CREATE ACCOUNT
          </button>

          <div class="fl-secure-badge">
            <i class="bi bi-shield-fill-check"></i>
            <span>Secure &amp; Trusted System</span>
          </div>

          <div class="fl-register-row">
            Already have an account? <a href="login.php">Log in here</a>
          </div>
        </form>

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
        <div>Hello! &#128075;<br>I'm your <strong>UDMS AI Assistant</strong>.<br>You can ask me:<ul><li>How to submit a document?</li><li>What documents are required?</li><li>How to use UDMS?</li></ul>I'm here to help!</div>
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

<script>
/* Password toggles */
(function(){
  function pwToggle(fieldId, btnId){
    const pw = document.getElementById(fieldId);
    const btn = document.getElementById(btnId);
    if(pw && btn) btn.addEventListener('click', () => {
      const show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      btn.innerHTML = show ? '<i class="bi bi-eye-slash text-primary"></i>' : '<i class="bi bi-eye"></i>';
    });
  }
  pwToggle('regPwField','regTogglePw');
  pwToggle('regCfField','regToggleCf');
})();
</script>
<script src="assets/auth-assistant.js"></script>
<?php include 'includes/footer.php'; ?>