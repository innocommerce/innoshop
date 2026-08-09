<script src="{{ asset('vendor/marked.min.js') }}"></script>

<div id="ai-chat-widget" class="ai-chat-widget">
  <button id="ai-chat-toggle" class="ai-chat-toggle" title="AI 助手">
    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M12 2a4 4 0 0 1 4 4v1h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h2V6a4 4 0 0 1 4-4z"/>
      <circle cx="12" cy="14" r="1"/>
      <path d="M10 11h4M10 16h4"/>
    </svg>
  </button>

  <div id="ai-chat-panel" class="ai-chat-panel" style="display:none">
    <div class="ai-chat-header">
      <span>AI 助手</span>
      <button id="ai-chat-close" class="ai-chat-close">&times;</button>
    </div>
    <div id="ai-chat-messages" class="ai-chat-messages"></div>
    <div class="ai-chat-input-area">
      <textarea id="ai-chat-input" rows="1" placeholder="输入问题，比如「最近一周销售情况如何？」"></textarea>
      <button id="ai-chat-send" class="ai-chat-send" title="发送">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
          <path d="M2 21l21-9L2 3v7l15 2-15 2v7z"/>
        </svg>
      </button>
    </div>
  </div>
</div>

<style>
.ai-chat-widget { position:fixed; bottom:20px; right:20px; z-index:9999; font-family: system-ui, sans-serif; }
.ai-chat-toggle { width:48px; height:48px; border-radius:50%; background:#4f46e5; color:#fff; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(79,70,229,.4); transition:transform .2s; }
.ai-chat-toggle:hover { transform:scale(1.1); }
.ai-chat-panel { position:fixed; bottom:80px; right:20px; width:420px; height:560px; background:#fff; border-radius:12px; box-shadow:0 8px 32px rgba(0,0,0,.15); display:flex; flex-direction:column; overflow:hidden; }
.ai-chat-header { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:#4f46e5; color:#fff; font-weight:600; font-size:14px; }
.ai-chat-close { background:none; border:none; color:#fff; font-size:22px; cursor:pointer; line-height:1; }
.ai-chat-messages { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:12px; }
.ai-chat-message { max-width:85%; padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.5; word-break:break-word; }
.ai-chat-message.user { align-self:flex-end; background:#4f46e5; color:#fff; border-bottom-right-radius:4px; }
.ai-chat-message.assistant { align-self:flex-start; background:#f3f4f6; color:#1f2937; border-bottom-left-radius:4px; }
.ai-chat-message.tool { align-self:flex-start; background:#fef3c7; color:#92400e; font-size:11px; padding:6px 10px; border-radius:8px; max-width:90%; }
.ai-chat-message.error { align-self:center; background:#fee2e2; color:#991b1b; font-size:12px; }
.ai-chat-input-area { display:flex; padding:12px; border-top:1px solid #e5e7eb; gap:8px; }
.ai-chat-input-area textarea { flex:1; border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:13px; resize:none; outline:none; font-family:inherit; }
.ai-chat-input-area textarea:focus { border-color:#4f46e5; }
.ai-chat-send { width:36px; height:36px; border-radius:50%; background:#4f46e5; color:#fff; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ai-chat-send:hover { background:#4338ca; }
.ai-chat-send:disabled { background:#9ca3af; cursor:not-allowed; }
.ai-chat-typing { display:flex; gap:4px; padding:10px 14px; }
.ai-chat-typing span { width:6px; height:6px; border-radius:50%; background:#9ca3af; animation:ai-bounce 1.4s infinite ease-in-out; }
.ai-chat-typing span:nth-child(2) { animation-delay:.2s; }
.ai-chat-typing span:nth-child(3) { animation-delay:.4s; }
@keyframes ai-bounce { 0%,80%,100% { transform:scale(.6); } 40% { transform:scale(1); } }
.ai-chat-message.assistant p { margin:0 0 6px; }
.ai-chat-message.assistant p:last-child { margin-bottom:0; }
.ai-chat-message.assistant ul,.ai-chat-message.assistant ol { margin:4px 0; padding-left:18px; }
.ai-chat-message.assistant li { margin:2px 0; }
.ai-chat-message.assistant code { background:rgba(0,0,0,.08); padding:1px 5px; border-radius:3px; font-size:12px; }
.ai-chat-message.assistant pre { background:rgba(0,0,0,.06); padding:8px 10px; border-radius:6px; overflow-x:auto; font-size:12px; margin:6px 0; }
.ai-chat-message.assistant pre code { background:none; padding:0; }
.ai-chat-message.assistant table { border-collapse:collapse; width:100%; margin:6px 0; font-size:12px; }
.ai-chat-message.assistant th,.ai-chat-message.assistant td { border:1px solid #d1d5db; padding:4px 8px; text-align:left; }
.ai-chat-message.assistant th { background:#f3f4f6; font-weight:600; }
.ai-chat-message.assistant blockquote { border-left:3px solid #4f46e5; margin:6px 0; padding:4px 10px; color:#6b7280; }
.ai-chat-message.assistant strong { font-weight:600; }
.ai-chat-message.assistant em { font-style:italic; }
.ai-chat-message.assistant h1,.ai-chat-message.assistant h2,.ai-chat-message.assistant h3 { font-size:14px; font-weight:600; margin:8px 0 4px; }
.ai-chat-message.assistant a { color:#4f46e5; text-decoration:underline; }
</style>

<script>
(function() {
  if (document.querySelector('[data-ai-chat-init]')) return;
  document.querySelector('#ai-chat-widget')?.setAttribute('data-ai-chat-init', '1');

  if (typeof marked !== 'undefined') { marked.setOptions({breaks: true, gfm: true}); }

  const chatPath = '{{ panel_route("content_ai.chat") }}';
  let history = [];
  let currentAssistantMsg = null;
  let streaming = false;

  const toggle = document.getElementById('ai-chat-toggle');
  const panel  = document.getElementById('ai-chat-panel');
  const close  = document.getElementById('ai-chat-close');
  const input  = document.getElementById('ai-chat-input');
  const send   = document.getElementById('ai-chat-send');
  const msgs   = document.getElementById('ai-chat-messages');

  toggle.addEventListener('click', () => { panel.style.display = 'flex'; toggle.style.display = 'none'; });
  close.addEventListener('click', () => { panel.style.display = 'none';  toggle.style.display = 'flex'; });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); doSend(); }
  });
  send.addEventListener('click', doSend);

  function doSend() {
    if (streaming) return;
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    addMessage('user', text);
    history.push({role: 'user', content: text});
    streamChat(text);
  }

  async function streamChat(message) {
    streaming = true; send.disabled = true;
    currentAssistantMsg = null;
    let renderPending = false;
    const typing = addTyping();

    try {
      const resp = await fetch(chatPath, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''},
        body: JSON.stringify({message, history})
      });
      if (!resp.ok) throw new Error('HTTP ' + resp.status);

      const reader = resp.body.getReader();
      const decoder = new TextDecoder();
      let buffer = '';

      while (true) {
        const {done, value} = await reader.read();
        if (done) break;
        buffer += decoder.decode(value, {stream: true});
        const lines = buffer.split('\n');
        buffer = lines.pop();

        for (let i = 0; i < lines.length; i++) {
          if (!lines[i].startsWith('event: ')) continue;
          const event = lines[i].slice(7).trim();
          const next = lines[i + 1];
          if (!next?.startsWith('data: ')) continue;
          i++; // consume data line
          const data = JSON.parse(next.slice(6));

          if (event === 'delta') {
            if (!currentAssistantMsg) { removeTyping(); currentAssistantMsg = addMessage('assistant', ''); }
            currentAssistantMsg._raw += data.content;
            if (!renderPending) {
              renderPending = true;
              requestAnimationFrame(() => {
                try { currentAssistantMsg.innerHTML = marked.parse(currentAssistantMsg._raw); } catch(e) {}
                renderPending = false;
                msgs.scrollTop = msgs.scrollHeight;
              });
            }
          } else if (event === 'tool_call') {
            addMessage('tool', '🔧 调用工具: ' + data.name);
          } else if (event === 'tool_result') {
            addMessage('tool', '✅ ' + data.name + ' 完成');
          } else if (event === 'error') {
            addMessage('error', data.message);
          } else if (event === 'done') {
            if (currentAssistantMsg) {
              try { currentAssistantMsg.innerHTML = marked.parse(currentAssistantMsg._raw); } catch(e) {}
              history.push({role: 'assistant', content: currentAssistantMsg._raw});
            }
          }
        }
      }
    } catch(e) {
      removeTyping(typing);
      addMessage('error', e.message);
    } finally {
      removeTyping(typing);
      streaming = false; send.disabled = false;
    }
  }

  function addMessage(role, content) {
    const el = document.createElement('div');
    el.className = 'ai-chat-message ' + role;
    el._raw = content;
    if (role === 'assistant' && typeof marked !== 'undefined') { el.innerHTML = marked.parse(content); }
    else { el.textContent = content; }
    msgs.appendChild(el);
    msgs.scrollTop = msgs.scrollHeight;
    return el;
  }

  function addTyping() {
    const el = document.createElement('div'); el.className = 'ai-chat-typing';
    [1,2,3].forEach(() => { const s = document.createElement('span'); el.appendChild(s); });
    msgs.appendChild(el); msgs.scrollTop = msgs.scrollHeight;
    return el;
  }

  function removeTyping(el) { if (el?.parentNode) el.remove(); }
  const allTyping = () => msgs.querySelectorAll('.ai-chat-typing');
  function removeTyping() { allTyping().forEach(el => el.remove()); }
})();
</script>
