@extends('panel::layouts.app')

@section('title', __('panel/menu.customer_groups'))

<x-panel::form.right-btns/>

@section('content')
  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/menu.customer_groups') }}</h5>
    </div>
    <div class="card-body">
      <form class="needs-validation" novalidate id="app-form"
            action="{{ $group->id ? panel_route('customer_groups.update', [$group->id]) : panel_route('customer_groups.store') }}"
            method="POST">
        @csrf
        @method($group->id ? 'PUT' : 'POST')

        <div class="row">
          <div class="col-12 col-md-6"><x-common-form-input :multiple="true" title="{{ __('panel/common.name') }}" name="name" :value="old('name', $group->name)" required /></div>
          <div class="col-12 col-md-6"></div>
          <div class="col-12 col-md-6"><x-common-form-input title="{{ __('panel/customer.level') }}" name="level" value="{{ old('level', $group->level) }}" required /></div>
          <div class="col-12 col-md-6"><x-common-form-input title="{{ __('panel/customer.mini_cost') }}" name="mini_cost" value="{{ old('mini_cost', $group->mini_cost) }}" required /></div>
          <div class="col-12 col-md-6"><x-common-form-input title="{{ __('panel/customer.discount_rate') }}" name="discount_rate" value="{{ old('level', $group->level) }}" required /></div>
        </div>

        <button type="submit" class="d-none"></button>
      </form>
    </div>
  </div>
@endsection

@push('footer')
  <script></script>
@endpush
