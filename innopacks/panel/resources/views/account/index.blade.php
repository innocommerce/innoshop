@extends('panel::layouts.app')
@section('body-class', 'account')

@section('title', __('panel/menu.account'))

<x-panel::form.right-btns />

@section('content')
@php
  $tokenLength  = strlen($token ?? '');
  $tokenVisible = max(8, $tokenLength - 12);
  $tokenMasked  = $tokenLength > 12
      ? substr($token, 0, 8).str_repeat('•', $tokenVisible).substr($token, -4)
      : $token;
@endphp
<div class="row">
  <div class="col-md-6">
    <div class="card h-min-600">
      <div class="card-body">
        <form class="needs-validation mt-3" id="app-form" novalidate action="{{ panel_route('account.update') }}" method="POST">
          @csrf
          @method('put')

          <x-common-form-input title="{{ __('common/base.name') }}" name="name" value="{{ old('name', $admin->name) }}" required />
          <x-common-form-input title="{{ __('common/base.email') }}" name="email" value="{{ old('email', $admin->email) }}" required />
          <x-common-form-input title="{{ __('panel/common.password') }}" name="password" value="" type="password" />

          @hookinsert('panel.account.form.password.after')

        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('panel/account.share_link') }}</h5>
      </div>
      <div class="card-body">
        <div class="input-group">
          <input type="text" class="form-control" readonly value="{{ front_root_route('home.index', ['adminref' => $admin->id]) }}" id="shareLink">
          <button class="btn btn-outline-secondary" type="button" onclick="copyShareLink()">
            <i class="bi-clipboard"></i>
          </button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('panel/account.api_token') }}</h5>
        <p class="text-muted small mb-0">{{ __('panel/account.api_token_desc') }}</p>
      </div>
      <div class="card-body">
        <div class="input-group">
          <input type="text" class="form-control font-monospace small" readonly
                 id="apiToken"
                 value="{{ $tokenMasked }}"
                 data-masked="{{ $tokenMasked }}"
                 data-full="{{ $token }}">
          <button class="btn btn-outline-secondary" type="button" id="toggleTokenBtn" onclick="toggleApiToken()">
            <i class="bi bi-eye"></i>
          </button>
          <button class="btn btn-outline-secondary" type="button" onclick="copyApiToken()">
            <i class="bi-clipboard"></i>
          </button>
        </div>
        <div class="mt-3">
          <button class="btn btn-outline-warning btn-sm" type="button" onclick="regenerateApiToken()">
            <i class="bi bi-arrow-clockwise"></i> {{ __('panel/account.regenerate') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('footer')
<script>
function copyShareLink() {
  const shareLink = document.getElementById('shareLink');
  shareLink.select();
  document.execCommand('copy');
  layer.msg('{{ __("panel/account.copied") }}');
}

function toggleApiToken() {
  const input = document.getElementById('apiToken');
  const btn   = document.getElementById('toggleTokenBtn');
  if (input.dataset.revealed === '1') {
    input.value = input.dataset.masked;
    input.dataset.revealed = '0';
    btn.innerHTML = '<i class="bi bi-eye"></i>';
    layer.msg('{{ __("panel/account.token_hidden") }}', {time: 800});
  } else {
    input.value = input.dataset.full;
    input.dataset.revealed = '1';
    btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    layer.msg('{{ __("panel/account.token_revealed") }}', {time: 800});
  }
}

function copyApiToken() {
  const input = document.getElementById('apiToken');
  const full  = input.dataset.full;
  const showOk = () => layer.msg('{{ __("panel/account.copied") }}');
  if (navigator.clipboard) {
    navigator.clipboard.writeText(full).then(showOk).catch(() => fallbackCopy(full, showOk));
  } else {
    fallbackCopy(full, showOk);
  }
}

function fallbackCopy(text, cb) {
  const tmp = document.createElement('textarea');
  tmp.value = text;
  tmp.style.position = 'fixed';
  tmp.style.opacity  = '0';
  document.body.appendChild(tmp);
  tmp.select();
  document.execCommand('copy');
  document.body.removeChild(tmp);
  cb();
}

function regenerateApiToken() {
  layer.confirm('{{ __('panel/account.regenerate_confirm') }}', {
    btn: ['{{ __('common/base.confirm') }}', '{{ __('common/base.cancel') }}']
  }, function () {
    axios.post('{{ panel_route('account.regenerate_token') }}', {}, {
      headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}
    }).then(function (res) {
      if (res.data && res.data.success) {
        layer.msg(res.data.message || '{{ __('panel/account.regenerated') }}', {icon: 1, time: 1000}, function () {
          window.location.reload();
        });
      } else {
        layer.msg((res.data && res.data.message) || '{{ __('common/base.error') }}', {icon: 2});
      }
    }).catch(function (err) {
      const msg = (err.response && err.response.data && err.response.data.message) || '{{ __('common/base.error') }}';
      layer.msg(msg, {icon: 2});
    });
  });
}
</script>
@endpush