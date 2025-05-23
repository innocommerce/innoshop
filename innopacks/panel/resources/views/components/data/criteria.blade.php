@if($criteria)
  <form action="{{ $action }}" method="GET" class="mb-4">
    <div class="row">
      <div class="row col-md-12 {{ has_set_value(request()->all()) ? 'collapse show' : 'collapse'}}"
           id="collapse-filters">

        @foreach($criteria as $item)
          @include('panel::components.data.criteria.' . $item['type'], ['item' => $item])
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
