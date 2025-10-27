@extends('panel::layouts.app')

@section('title', __('panel/menu.state'))

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/menu.state') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation mt-3" novalidate action="{{ $state->id ? panel_route('states.update', [$state->id]) : panel_route('states.store') }}" method="POST">
      @csrf
      @method($state->id ? 'PUT' : 'POST')

      <x-common-form-input title="名称" name="name" :value="old('name', $state->name ?? '')" required placeholder="名称" />
      <x-common-form-input title="编码" name="code" :value="old('code', $state->code ?? '')" required placeholder="编码" />
      <x-common-form-image title="标识" name="image" :value="old('image', $state->image ?? '')" placeholder="image" />
      <x-common-form-input title="排序" name="position" :value="old('position', $state->position ?? 0)" required placeholder="排序" />
      <x-common-form-input title="启用" name="active" :value="old('active', $state->active ?? '')" placeholder="启用" />

      <div class="form-row mt-5 d-flex">
        <div class="wp-200 pe-2"></div>
        <button class="btn btn-primary" type="submit">Submit form</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('footer')
  <script>
  </script>
@endpush