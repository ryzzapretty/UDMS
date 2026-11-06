// UDMS AI Assistant Interactive Widget (Expandable & Floating)
document.addEventListener('DOMContentLoaded', function () {
  const chatWindow = document.getElementById('aiChatWindow');
  const chatLauncher = document.getElementById('aiBotLauncher');
  const closeBtn = document.getElementById('closeAssistantBtn');
  const minimizeBtn = document.getElementById('minimizeAssistantBtn');
  const chatBody = document.getElementById('assistantChatBody');
  const chatInput = document.getElementById('assistantInput');
  const sendBtn = document.getElementById('assistantSendBtn');
  const hintBubble = document.getElementById('aiBotHintBubble');

  function openAssistant() {
    if (!chatWindow) return;
    chatWindow.classList.add('open');
    if (hintBubble) hintBubble.style.display = 'none';
    if (chatInput) chatInput.focus();
    scrollToBottom();
  }

  function closeAssistant() {
    if (!chatWindow) return;
    chatWindow.classList.remove('open');
  }

  function toggleAssistant() {
    if (!chatWindow) return;
    if (chatWindow.classList.contains('open')) {
      closeAssistant();
    } else {
      openAssistant();
    }
  }

  // Expose to window for inline onclick handlers
  window.openAssistant = openAssistant;
  window.closeAssistant = closeAssistant;
  window.toggleAssistant = toggleAssistant;

  // Support both legacy #aiBotLauncher and new fl-* #flBotLauncher
  if (chatLauncher) chatLauncher.addEventListener('click', toggleAssistant);
  const flLauncher = document.getElementById('flBotLauncher');
  if (flLauncher) flLauncher.addEventListener('click', toggleAssistant);
  if (closeBtn) closeBtn.addEventListener('click', closeAssistant);
  if (minimizeBtn) minimizeBtn.addEventListener('click', closeAssistant);

  // Close on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && chatWindow && chatWindow.classList.contains('open')) {
      closeAssistant();
    }
  });

  if (!chatBody || !chatInput || !sendBtn) return;

  function getTimeString() {
    const d = new Date();
    let h = d.getHours();
    const m = d.getMinutes().toString().padStart(2, '0');
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${m} ${ampm}`;
  }

  function scrollToBottom() {
    setTimeout(() => {
      chatBody.scrollTop = chatBody.scrollHeight;
    }, 50);
  }

  function appendUserMessage(text) {
    const row = document.createElement('div');
    row.className = 'ai-msg-row user';
    row.innerHTML = `
      <div class="ai-msg-avatar user-av">
        <i class="bi bi-person-fill"></i>
      </div>
      <div class="ai-msg-bubble user">
        <div>${escapeHtml(text)}</div>
        <span class="ai-msg-time">${getTimeString()} &check;&check;</span>
      </div>
    `;
    chatBody.appendChild(row);
    scrollToBottom();
  }

  function appendBotTyping() {
    const typingId = 'typing-' + Date.now();
    const row = document.createElement('div');
    row.id = typingId;
    row.className = 'ai-msg-row';
    row.innerHTML = `
      <div class="ai-msg-avatar">
        <img src="assets/ai-robot-avatar.png" alt="Bot">
      </div>
      <div class="ai-msg-bubble bot text-muted">
        <i class="bi bi-three-dots"></i> Typing...
      </div>
    `;
    chatBody.appendChild(row);
    scrollToBottom();
    return typingId;
  }

  function appendBotMessage(textHtml, typingId) {
    if (typingId) {
      const el = document.getElementById(typingId);
      if (el) el.remove();
    }
    const row = document.createElement('div');
    row.className = 'ai-msg-row';
    row.innerHTML = `
      <div class="ai-msg-avatar">
        <img src="assets/ai-robot-avatar.png" alt="Bot">
      </div>
      <div class="ai-msg-bubble bot">
        <div>${textHtml}</div>
        <span class="ai-msg-time text-muted">${getTimeString()}</span>
      </div>
    `;
    chatBody.appendChild(row);
    scrollToBottom();
  }

  function escapeHtml(str) {
    return str.replace(/[&<>'"]/g, tag => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#39;',
      '"': '&quot;'
    }[tag] || tag));
  }

  function generateAnswer(query) {
    const q = query.toLowerCase();

    if (q.includes('pdf') || q.includes('upload') || q.includes('submit a doc') || q.includes('docx') || q.includes('file')) {
      return 'You can upload a <strong>PDF, DOCX, XLS, or image</strong> file by going to the <strong>Documents</strong> section after logging in. Click the <strong>"Upload"</strong> button, select your category & department, and follow the instructions.';
    }
    if (q.includes('account') || q.includes('login') || q.includes('demo') || q.includes('password') || q.includes('credential')) {
      return 'For testing and evaluation, demo accounts are available:<br>• <strong>Admin:</strong> <code>admin</code> / <code>Admin@123</code><br>• <strong>Faculty:</strong> <code>faculty1</code> / <code>Faculty@123</code><br>• <strong>Student:</strong> <code>student1</code> / <code>Student@123</code>';
    }
    if (q.includes('register') || q.includes('sign up') || q.includes('create account') || q.includes('pending')) {
      return 'To register, click <strong>"Create account"</strong> below the sign-in form. Select your user type (Student or Faculty) and Department. All new registrations remain <strong>Pending</strong> until approved by a university Administrator.';
    }
    if (q.includes('required') || q.includes('clearance') || q.includes('types') || q.includes('category')) {
      return 'UDMS supports multiple document types: <strong>Memorandums, Reports, Forms, Certificates, Student Records, Research Documents, and Clearances</strong>. Check with your department coordinator for specific semester checklists.';
    }
    if (q.includes('approve') || q.includes('review') || q.includes('status')) {
      return 'Submitted documents go through the <strong>Review Workflow</strong> (Draft → Pending Review → Approved / Returned for Revision). Only university Administrators have authorization to approve and archive documents.';
    }
    if (q.includes('hi') || q.includes('hello') || q.includes('hey')) {
      return 'Hello! 👋 How can I assist you with your university documentation, account verification, or system access today?';
    }

    return `Thank you for your question! Here are key university procedures you can ask about:<br>
      • <em>"How do I upload a PDF or document?"</em><br>
      • <em>"What demo login accounts can I use?"</em><br>
      • <em>"How does new account verification work?"</em><br>
      • <em>"Who approves submitted documents?"</em>`;
  }

  function handleSend() {
    const val = chatInput.value.trim();
    if (!val) return;

    appendUserMessage(val);
    chatInput.value = '';

    const typingId = appendBotTyping();
    setTimeout(() => {
      const reply = generateAnswer(val);
      appendBotMessage(reply, typingId);
    }, 600);
  }

  sendBtn.addEventListener('click', handleSend);
  chatInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleSend();
    }
  });

  // Toggle password visibility helper
  const pwToggle = document.getElementById('togglePassword');
  const pwField = document.getElementById('passwordField');
  if (pwToggle && pwField) {
    pwToggle.addEventListener('click', function () {
      const isPw = pwField.getAttribute('type') === 'password';
      pwField.setAttribute('type', isPw ? 'text' : 'password');
      const icon = pwToggle.querySelector('i');
      if (icon) {
        icon.className = isPw ? 'bi bi-eye-slash text-primary' : 'bi bi-eye text-muted';
      }
    });
  }
});
