@extends('panel::layouts.app')
@section('body-class', 'account')

@section('title', __('panel/menu.account'))

<x-panel::form.right-btns />

@section('content')
<div class="row">
  <div class="col-md-6">
    <div class="card h-min-600">
      <div class="card-body">
        <form class="needs-validation mt-3" id="app-form" novalidate action="{{ panel_route('account.update') }}" method="POST">
          @csrf
          @method('put')

          <x-common-form-input title="{{ __('panel/common.name') }}" name="name" value="{{ old('name', $admin->name) }}" required />
          <x-common-form-input title="{{ __('panel/common.email') }}" name="email" value="{{ old('email', $admin->email) }}" required />
          <x-common-form-input title="{{ __('panel/common.password') }}" name="password" value="" type="password" />
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card h-min-600">
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
</script>
@endpush