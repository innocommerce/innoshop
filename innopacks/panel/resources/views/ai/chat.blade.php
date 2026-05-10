@extends('panel::layouts.app')

@section('title', $agent->label)

@section('content')
<div class="ai-chat-container" id="aiChatApp" data-scene="{{ $agent->scene }}">
  <div class="card">
    <div class="card-header d-flex align-items-center">
      <i class="{{ $agent->icon }} me-2"></i>
      <h5 class="mb-0">{{ $agent->label }}</h5>
      @if($agent->description)
        <small class="text-muted ms-3">{{ $agent->description }}</small>
      @endif
      <button class="btn btn-sm btn-outline-secondary ms-auto" onclick="aiChat.clearHistory()">
        {{ __('common/base.clear') }}
      </button>
    </div>

    <div class="card-body ai-chat-messages" id="aiChatMessages" style="height: 500px; overflow-y: auto;">
      @empty($agent->description)
        <div class="text-center text-muted py-5">
          <i class="bi bi-robot" style="font-size: 3rem;"></i>
          <p class="mt-3">Start a conversation with {{ $agent->label }}</p>
        </div>
      @endempty
    </div>

    <div class="card-footer">
      <div class="input-group">
        <textarea
          class="form-control"
          id="aiChatInput"
          rows="1"
          placeholder="Type your message..."
          onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();aiChat.send();}"
        ></textarea>
        <button class="btn btn-primary" id="aiChatSendBtn" onclick="aiChat.send()">
          <i class="bi bi-send"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.ai-chat-messages .msg { margin-bottom: 1rem; }
.ai-chat-messages .msg-user { text-align: right; }
.ai-chat-messages .msg-user .msg-bubble {
  display: inline-block; background: #0d6efd; color: #fff;
  border-radius: 12px 12px 0 12px; padding: 8px 14px; max-width: 70%;
  text-align: left;
}
.ai-chat-messages .msg-assistant .msg-bubble {
  display: inline-block; background: #f0f0f0;
  border-radius: 12px 12px 12px 0; padding: 8px 14px; max-width: 70%;
}
.ai-chat-messages .msg-tool .msg-bubble {
  display: inline-block; background: #fff3cd;
  border-radius: 8px; padding: 6px 10px; max-width: 70%;
  font-size: 0.85rem;
}
.ai-chat-messages .msg-loading .msg-bubble {
  display: inline-block; background: #f0f0f0;
  border-radius: 12px 12px 12px 0; padding: 8px 14px;
  color: #999;
}
</style>
@endsection

@push('footer')
<script>
const aiChat = {
  scene: document.getElementById('aiChatApp').dataset.scene,
  messagesEl: document.getElementById('aiChatMessages'),
  inputEl: document.getElementById('aiChatInput'),
  sendBtn: document.getElementById('aiChatSendBtn'),
  history: [],
  loading: false,

  send() {
    const message = this.inputEl.value.trim();
    if (!message || this.loading) return;

    this.appendMessage('user', message);
    this.inputEl.value = '';
    this.inputEl.style.height = 'auto';

    this.loading = true;
    this.sendBtn.disabled = true;
    this.appendLoading();

    fetch('{{ $chatApiUrl }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content,
      },
      body: JSON.stringify({ message: message, history: this.history }),
    })
    .then(r => r.json())
    .then(data => {
      this.removeLoading();
      if (data.status) {
        this.history.push({ role: 'user', content: message });
        this.history.push({ role: 'assistant', content: data.message });
        this.appendMessage('assistant', data.message);
      } else {
        this.appendMessage('assistant', 'Error: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => {
      this.removeLoading();
      this.appendMessage('assistant', 'Error: ' + err.message);
    })
    .finally(() => {
      this.loading = false;
      this.sendBtn.disabled = false;
      this.scrollToBottom();
    });
  },

  appendMessage(role, content) {
    const div = document.createElement('div');
    div.className = 'msg msg-' + role;
    div.innerHTML = '<div class="msg-bubble">' + this.escapeHtml(content) + '</div>';
    this.messagesEl.appendChild(div);
    this.scrollToBottom();
  },

  appendLoading() {
    const div = document.createElement('div');
    div.className = 'msg msg-loading';
    div.id = 'aiChatLoading';
    div.innerHTML = '<div class="msg-bubble"><i class="bi bi-three-dots"></i> Thinking...</div>';
    this.messagesEl.appendChild(div);
    this.scrollToBottom();
  },

  removeLoading() {
    const el = document.getElementById('aiChatLoading');
    if (el) el.remove();
  },

  clearHistory() {
    this.history = [];
    this.messagesEl.innerHTML = '';
  },

  scrollToBottom() {
    this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
  },

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
  },
};
</script>
@endpush
