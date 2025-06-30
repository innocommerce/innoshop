@extends('panel::layouts.app')

@section('title', $plugin->getLocaleName())

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-body">

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
                  <div class="text-secondary"><small>{{ __('common.recommend_size') }} {{ $field['recommend_size'] }}</small></div>
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
                :required="(bool)($field['required'] ?? false)"
                :value="old($field['name'], $field['value'] ?? '')" />
            @endif

            @if ($field['type'] == 'multi-string')
              @php
                $value = $field['value'] ?? '';
                if (is_string($value)) {
                    $value = json_decode($value, true) ?? [];
                }
              @endphp
              <x-common-form-input
                :name="$field['name']"
                :title="$field['label']"
                :placeholder="$field['placeholder'] ?? ''"
                :description="$field['description'] ?? ''"
                :error="$errors->first($field['name'])"
                :required="(bool)$field['required']"
                :is-locales="true"
                :multiple="true"
                :value="$value" />
            @endif

            @if ($field['type'] == 'select')
              <x-common-form-select
                :name="$field['name']"
                :title="$field['label']"
                :value="old($field['name'], $field['value'] ?? '')"
                :options="$field['options']"
                :emptyOption="$field['emptyOption'] ?? true" >
                @if (isset($field['description']))
                  <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
                @endif
              </x-common-form-select>
            @endif

            @if ($field['type'] == 'bool')
              <x-common-form-switch-radio
                :name="$field['name']"
                :title="$field['label']"
                :value="old($field['name'], $field['value'] ?? '')">
                @if (isset($field['description']))
                  <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
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
                  <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
                @endif
              </x-common-form-textarea>
            @endif

            @if ($field['type'] == 'multi-textarea')
              <x-common-form-textarea
                :name="$field['name']"
                :title="$field['label']"
                :required="(bool)$field['required']"
                :is-locales="true"
                :multiple="true"
                :value="old($field['name'], $field['value'] ?? '')">
                @if (isset($field['description']))
                  <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
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
                  <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
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
                  <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
                @endif
              </x-common-form-rich-text>
            @endif

            @if ($field['type'] == 'checkbox')
              <x-panel::form.row :title="$field['label']" :required="(bool)($field['required'] ?? false)">
                <div class="form-checkbox">
                  @php
                    $checkedValues = old($field['name'], $field['value'] ?? []);
                    if (is_string($checkedValues)) {
                        $checkedValues = json_decode($checkedValues, true);
                    }
                    if (!is_array($checkedValues)) {
                        $checkedValues = [];
                    }
                  @endphp
                  @foreach ($field['options'] as $item)
                  <div class="form-check d-inline-block mt-2 me-3">
                    <input
                      class="form-check-input"
                      name="{{ $field['name'] }}[]"
                      type="checkbox"
                      value="{{ $item['value'] }}"
                      {{ in_array($item['value'], $checkedValues) ? 'checked' : '' }}
                      id="flexCheck-{{ $field['name'] }}-{{ $loop->index }}">
                    <label class="form-check-label" for="flexCheck-{{ $field['name'] }}-{{ $loop->index }}">
                      {{ $item['label'] }}
                    </label>
                  </div>
                  @endforeach
                </div>
                @if (isset($field['description']))
                  <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
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
