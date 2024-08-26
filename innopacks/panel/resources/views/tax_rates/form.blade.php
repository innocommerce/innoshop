@extends('panel::layouts.app')

@section('title', __('panel/menu.tax_rates'))

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/menu.tax_rates') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $tax_rate->id ? panel_route('tax_rates.update', [$tax_rate->id]) : panel_route('tax_rates.store') }}"
      method="POST">
      @csrf
      @method($tax_rate->id ? 'PUT' : 'POST')

      <div class="wp-500 m-auto">
        <x-common-form-input title="税种" name="name" value="{{ old('name', $tax_rate->name) }}" required placeholder="税种" />

        <x-common-form-select title="类型" :empty-option="false" name="type" :options="$types" value="{{ old('type', $tax_rate->type) }}" required />

        <x-panel::form.row title="税率" required>
          <div class="input-group mb-3">
            <input type="text" name="rate" value="{{ old('rate', $tax_rate->rate) }}" class="form-control" placeholder="税率">
            <span class="input-group-text rate-icon">%</span>
          </div>
        </x-panel::form.row>

        <x-common-form-select title="区域" :empty-option="false" name="region_id" :options="$regions" key="id" label="name"
                             value="{{ old('region_id', $tax_rate->region_id) }}" required />
      </div>

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script>

</script>
@endpush