@extends('panel::layouts.app')

@section('title', __('panel/menu.currencies'))

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/menu.currencies') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $currency->id ? panel_route('currencies.update', [$currency->id]) : panel_route('currencies.store') }}"
      method="POST">
      @csrf
      @method($currency->id ? 'PUT' : 'POST')

      <x-common-form-input title="{{ __('panel/common.name') }}" name="name" value="{{ old('name', $currency->name) }}" required />
      <x-common-form-input title="{{ __('panel/currency.code') }}" name="code" value="{{ old('code', $currency->code) }}" required />
      <x-common-form-input title="{{ __('panel/currency.symbol_left') }}" name="symbol_left" value="{{ old('symbol_left', $currency->symbol_left) }}" />
      <x-common-form-input title="{{ __('panel/currency.symbol_right') }}" name="symbol_right" value="{{ old('symbol_right', $currency->symbol_right) }}" />
      <x-common-form-input title="{{ __('panel/currency.decimal_place') }}" name="decimal_place" value="{{ old('decimal_place', $currency->decimal_place) }}" required />
      <x-common-form-input title="{{ __('panel/currency.value') }}" name="value" value="{{ old('value', $currency->value) }}" />
      <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)" placeholder="{{ __('panel/common.whether_enable') }}"/>

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script></script>
@endpush