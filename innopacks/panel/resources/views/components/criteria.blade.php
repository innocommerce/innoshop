@if($criteria)
  <form action="{{ $action }}" method="GET" class="mb-4">
    <div class="row">
      <div class="row col-md-12 {{ has_set_value(request()->all()) ? 'collapse show' : 'collapse'}}"
           id="collapse-filters">

        @foreach($criteria as $item)
          @if($item['type'] == 'input')
            <div class="col-md-3 col-sm-6 mb-3">
              <label for="{{ $item['name'] }}" class="form-label form-label-sm">{{ $item['label'] }}</label>
              <input type="text" name="{{ $item['name'] }}" id="{{ $item['name'] }}"
                     class="form-control form-control-sm"
                     value="{{ request($item['name']) }}"
                     placeholder="{{ $item['label'] }}">
            </div>
          @elseif($item['type'] == 'select')
            <div class="col-md-3 col-sm-6 mb-3">
              <label for="{{ $item['name'] }}" class="form-label form-label-sm">{{ $item['label'] }}</label>
              <select name="{{ $item['name'] }}" id="{{ $item['name'] }}" class="form-select form-select-sm">
                <option value="">{{ panel_trans('common.please_choose') }}</option>
                @foreach($item['options'] as $option)
                  <option
                      value="{{ isset($item['options_key']) ? $option[$item['options_key']] : $option['value'] }}" {{ request($item['name'])==(isset($item['options_key']) ? $option[$item['options_key']] : $option['value']) ? 'selected' : '' }}>{{
                  isset($option['label_key']) ? trans($option['label_key']) : (isset($item['options_label']) ? $option[$item['options_label']] : $option['label']) }}</option>
                @endforeach
              </select>
            </div>
          @elseif($item['type'] == 'date')
            <div class="col-md-3 col-sm-6 mb-3">
              <label for="{{ $item['name'] }}" class="form-label form-label-sm">{{ $item['label'] }}</label>
              <input type="date" name="{{ $item['name'] }}" id="{{ $item['name'] }}"
                     class="form-control form-control-sm"
                     value="{{ request($item['name']) }}"
                     placeholder="{{ $item['label'] }}">
            </div>
          @elseif($item['type'] == 'range')
            @php
                $startName = $item['start']['name'] ?? $item['name'] . '_start';
                $endName = $item['end']['name'] ?? $item['name'] . '_end';
            @endphp
            <div class="col-md-3 col-sm-6 mb-3">
              <label class="form-label form-label-sm">{{ $item['label'] }}</label>
              <div class="input-group input-group-sm">
                <input type="text" name="{{ $startName }}" class="form-control"
                       value="{{ request($startName) }}"
                       placeholder="{{ $item['start']['label'] ?? $item['label'] }}">
                <span class="input-group-text">-</span>
                <input type="text" name="{{ $endName }}" class="form-control"
                       value="{{ request($endName) }}"
                       placeholder="{{ $item['end']['label'] ?? $item['label'] }}">
              </div>
            </div>
          @elseif($item['type'] == 'date_range')
            @php
              $startName = $item['start']['name'] ?? $item['name'] . '_start';
              $endName = $item['end']['name'] ?? $item['name'] . '_end';
            @endphp
            <div class="col-md-3 col-sm-6 mb-3">
              <label class="form-label form-label-sm">{{ $item['label'] }}</label>
              <div class="input-group input-group-sm">
                <input type="date" name="{{ $startName }}" class="form-control"
                       value="{{ request($startName) }}">
                <span class="input-group-text">-</span>
                <input type="date" name="{{ $endName }}" class="form-control"
                       value="{{ request($endName) }}">
              </div>
            </div>
          @endif
        @endforeach

      </div>
    </div>

    <div class="row mt-3">
      <div class="col-6">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i>
          {{ __('panel/common.filter') }}</button>
        <a href="{{ $action }}" class=" btn btn-sm btn-outline-primary" style="margin-left: 5px">
          <i class="bi bi-arrow-clockwise"></i> {{ __('panel/common.reset') }}
        </a>
      </div>
      <div class="col-6 row justify-content-end">
        <div class="col-auto">
          <button id="collapse-button" type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                  data-bs-target="#collapse-filters" aria-expanded="false" aria-controls="collapse-filters">
          <span class="down">
            <i class="bi bi-arrow-down"></i>{{ __('panel/common.expand') }}
          </span>
            <span class="up">
            <i class="bi bi-arrow-up"></i>{{ __('panel/common.collapse') }}
          </span>
          </button>
        </div>
      </div>
    </div>

  </form>

  @push('footer')
    <script>
      let filterEl = $('#collapse-filters');
      let showEl = $('#collapse-button .down');
      let hideEl = $('#collapse-button .up');

      filterEl.on('show.bs.collapse', function () {
        showEl.hide();
        hideEl.show();
      });

      filterEl.on('hide.bs.collapse', function () {
        showEl.show();
        hideEl.hide();
      });

      if (filterEl.hasClass('show')) {
        showEl.hide();
        hideEl.show();
      } else {
        showEl.show();
        hideEl.hide();
      }
    </script>
  @endpush
@endif
