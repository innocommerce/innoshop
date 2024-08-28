@extends('panel::layouts.app')

@section('title', __('panel/menu.plugins'))

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <h6 class="border-bottom pb-3 mb-4">{{ $plugin->getLocaleName() }}</h6>

    <form class="needs-validation" id="app-form" novalidate action="{{ panel_route('plugins.update', [$plugin->getCode()]) }}" method="POST">
      @csrf
      {{ method_field('put') }}
      <div class="row">
        <div class="col-12 col-md-7">
          @foreach ($fields as $field)
            @if ($field['type'] == 'image')
              <x-common-form-image
                :name="$field['name']"
                :title="$field['label']"
                :description="$field['description'] ?? ''"
                :error="$errors->first($field['name'])"
                :required="(bool)$field['required']"
                :value="old($field['name'], $field['value'] ?? '')">
                @if ($field['recommend_size'] ?? false)
                <div class="help-text font-size-12 lh-base">{{ __('common.recommend_size') }} {{ $field['recommend_size'] }}</div>
                @endif
              </x-common-form-image>
            @endif

            @if ($field['type'] == 'string')
              <x-common-form-input
                :name="$field['name']"
                :title="$field['label']"
                :placeholder="$field['placeholder'] ?? ''"
                :description="$field['description'] ?? ''"
                :error="$errors->first($field['name'])"
                :required="(bool)$field['required']"
                :value="old($field['name'], $field['value'] ?? '')" />
            @endif

            @if ($field['type'] == 'multi-string')
              <x-common-form-input
                :name="$field['name']"
                :title="$field['label']"
                :placeholder="$field['placeholder'] ?? ''"
                :description="$field['description'] ?? ''"
                :error="$errors->first($field['name'])"
                :required="(bool)$field['required']"
                :is-locales="true"
                :value="old($field['name'], $field['value'] ?? '')" />
            @endif

            @if ($field['type'] == 'select')
              <x-common-form-select
                :name="$field['name']"
                :title="$field['label']"
                :value="old($field['name'], $field['value'] ?? '')"
                :options="$field['options']"
                :emptyOption="$field['emptyOption'] ?? true" >
                @if (isset($field['description']))
                  <div class="help-text font-size-12 lh-base">{{ $field['description'] }}</div>
                @endif
              </x-common-form-select>
            @endif

            @if ($field['type'] == 'bool')
              <x-common-form-switch-radio
                :name="$field['name']"
                :title="$field['label']"
                :value="old($field['name'], $field['value'] ?? '')">
                @if (isset($field['description']))
                  <div class="help-text font-size-12 lh-base">{{ $field['description'] }}</div>
                @endif
              </x-common-form-switch-radio>
            @endif

            @if ($field['type'] == 'textarea')
              <x-common-form-textarea
                :name="$field['name']"
                :title="$field['label']"
                :required="(bool)$field['required']"
                :value="old($field['name'], $field['value'] ?? '')">
                @if (isset($field['description']))
                  <div class="help-text font-size-12 lh-base">{{ $field['description'] }}</div>
                @endif
              </x-common-form-textarea>
            @endif

            @if ($field['type'] == 'multi-textarea')
              <x-common-form-textarea
                :name="$field['name']"
                :title="$field['label']"
                :required="(bool)$field['required']"
                :is-locales="true"
                :value="old($field['name'], $field['value'] ?? '')">
                @if (isset($field['description']))
                  <div class="help-text font-size-12 lh-base">{{ $field['description'] }}</div>
                @endif
              </x-common-form-textarea>
            @endif

            @if ($field['type'] == 'rich-text')
              <x-common-form-rich-text
                :name="$field['name']"
                :title="$field['label']"
                :value="old($field['name'], $field['value'] ?? '')"
                :required="(bool)$field['required']"
                >
                @if (isset($field['description']))
                  <div class="help-text font-size-12 lh-base">{{ $field['description'] }}</div>
                @endif
              </x-common-form-rich-text>
            @endif

            @if ($field['type'] == 'multi-rich-text')
              <x-common-form-rich-text
                :name="$field['name']"
                :title="$field['label']"
                :value="old($field['name'], $field['value'] ?? '')"
                :required="(bool)$field['required']"
                :is-locales="true"
                >
                @if (isset($field['description']))
                  <div class="help-text font-size-12 lh-base">{{ $field['description'] }}</div>
                @endif
              </x-common-form-rich-text>
            @endif

            @if ($field['type'] == 'checkbox')
              <x-panel::form.row :title="$field['label']" :required="(bool)$field['required']">
                <div class="form-checkbox">
                  @foreach ($field['options'] as $item)
                  <div class="form-check d-inline-block mt-2 me-3">
                    <input
                      class="form-check-input"
                      name="{{ $field['name'] }}[]"
                      type="checkbox"
                      value="{{ old($field['name'], $item['value']) }}"
                      {{ in_array($item['value'], old($field['name'], json_decode($field['value'] ?? '[]', true))) ? 'checked' : '' }}
                      id="flexCheck-{{ $field['name'] }}-{{ $loop->index }}">
                    <label class="form-check-label" for="flexCheck-{{ $field['name'] }}-{{ $loop->index }}">
                      {{ $item['label'] }}
                    </label>
                  </div>
                  @endforeach
                </div>
                @if (isset($field['description']))
                  <div class="help-text font-size-12 lh-base">{{ $field['description'] }}</div>
                @endif
              </x-panel::form.row>
            @endif
          @endforeach
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('footer')
  <script></script>
@endpush
