@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.plugin_coordination'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    @foreach($configs as $type => $configData)
    @if(!$loop->first)
    <hr class="my-4">
    @endif

    <div class="mb-4">
      <h5 class="mb-3">
        <i class="bi bi-{{ $type == 'price' ? 'tag' : 'receipt' }} me-2"></i>
        {{ $types[$type] }}
      </h5>

      <form method="POST" action="{{ panel_route('plugin_coordination.update') }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="type" value="{{ $type }}">

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">{{ __('panel/plugin_coordination.execution_order') }}</label>
          <div class="col-sm-10">
            <div class="border rounded p-3" id="sortable-{{ $type }}">
              @foreach($configData['plugins'] as $plugin)
              <div class="d-flex align-items-center p-2 border-bottom sortable-item" data-code="{{ $plugin->code }}">
                <i class="bi bi-list me-3 text-muted cursor-move"></i>
                <span class="flex-grow-1">{{ $plugin->name }}</span>
                <small class="text-muted">({{ $plugin->code }})</small>
              </div>
              @endforeach
            </div>
            <input type="hidden" name="sort_order" id="sort_order_{{ $type }}" value='{{ json_encode($configData['sort_order']) }}'>
            <p class="form-text text-muted">{{ __('panel/plugin_coordination.execution_order_desc') }}</p>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">{{ __('panel/plugin_coordination.exclusive_mode') }}</label>
          <div class="col-sm-10">
            <div class="btn-group" role="group">
              @foreach($exclusive_modes as $mode => $label)
              <input type="radio" class="btn-check" name="exclusive_mode" id="mode_{{ $type }}_{{ $mode }}" value="{{ $mode }}"
                {{ $configData['exclusive_mode'] == $mode ? 'checked' : '' }}
                autocomplete="off"
                onchange="toggleExclusivePairs('{{ $type }}', this.value)">
              <label class="btn btn-outline-primary" for="mode_{{ $type }}_{{ $mode }}">
                {{ $label }}
              </label>
              @endforeach
            </div>
            <p class="form-text text-muted mt-2">{{ __('panel/plugin_coordination.exclusive_mode_desc') }}</p>
          </div>
        </div>

        <div class="row mb-3 {{ $configData['exclusive_mode'] != 'custom' ? 'd-none' : '' }}" id="exclusive-pairs-group-{{ $type }}">
          <label class="col-sm-2 col-form-label">{{ __('panel/plugin_coordination.exclusive_rules') }}</label>
          <div class="col-sm-10">
            <div id="exclusive-pairs-{{ $type }}" class="mb-2">
              @foreach($configData['exclusive_pairs'] as $index => $pair)
              <div class="input-group mb-2 exclusive-pair">
                <select class="form-select" name="exclusive_pairs[{{ $index }}][]">
                  <option value="">{{ __('panel/plugin_coordination.select_plugin') }}</option>
                  @foreach($configData['plugins'] as $plugin)
                  <option value="{{ $plugin->code }}" {{ in_array($plugin->code, $pair) ? 'selected' : '' }}>
                    {{ $plugin->name }}
                  </option>
                  @endforeach
                </select>
                <select class="form-select" name="exclusive_pairs[{{ $index }}][]">
                  <option value="">{{ __('panel/plugin_coordination.select_plugin') }}</option>
                  @foreach($configData['plugins'] as $plugin)
                  <option value="{{ $plugin->code }}" {{ (isset($pair[1]) && $pair[1] == $plugin->code) ? 'selected' : '' }}>
                    {{ $plugin->name }}
                  </option>
                  @endforeach
                </select>
                <button type="button" class="btn btn-outline-danger remove-pair" onclick="this.closest('.exclusive-pair').remove()">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addExclusivePair('{{ $type }}')">
              <i class="bi bi-plus-lg me-1"></i> {{ __('panel/plugin_coordination.add_exclusive_rule') }}
            </button>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-10 offset-sm-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> {{ __('panel/plugin_coordination.save') }}
            </button>
          </div>
        </div>
      </form>
    </div>

    @endforeach
  </div>
</div>
@endsection

@push('styles')
<style>
  .cursor-move { cursor: move; }
  .sortable-ghost { opacity: 0.4; background-color: #f8f9fa; }
</style>
@endpush

@push('footer')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
const pluginsData = @json($configs);

// Initialize Sortable
['price', 'orderfee'].forEach(function(type) {
  new Sortable(document.getElementById('sortable-' + type), {
    handle: '.cursor-move',
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd: function(evt) {
      const codes = [];
      evt.to.querySelectorAll('.sortable-item').forEach(function(item) {
        codes.push(item.dataset.code);
      });
      document.getElementById('sort_order_' + type).value = JSON.stringify(codes);
    }
  });
});

function toggleExclusivePairs(type, mode) {
  const group = document.getElementById('exclusive-pairs-group-' + type);
  if (mode === 'custom') {
    group.classList.remove('d-none');
  } else {
    group.classList.add('d-none');
  }
}

function addExclusivePair(type) {
  const container = document.getElementById('exclusive-pairs-' + type);
  const index = container.querySelectorAll('.exclusive-pair').length;
  const plugins = pluginsData[type].plugins;

  let html = `
    <div class="input-group mb-2 exclusive-pair">
      <select class="form-select" name="exclusive_pairs[${index}][]">
        <option value="">{{ __('panel/plugin_coordination.select_plugin') }}</option>
  `;

  plugins.forEach(function(plugin) {
    html += `<option value="${plugin.code}">${plugin.name}</option>`;
  });

  html += `
      </select>
      <select class="form-select" name="exclusive_pairs[${index}][]">
        <option value="">{{ __('panel/plugin_coordination.select_plugin') }}</option>
  `;

  plugins.forEach(function(plugin) {
    html += `<option value="${plugin.code}">${plugin.name}</option>`;
  });

  html += `
      </select>
      <button type="button" class="btn btn-outline-danger remove-pair" onclick="this.closest('.exclusive-pair').remove()">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  `;

  container.insertAdjacentHTML('beforeend', html);
}
</script>
@endpush
