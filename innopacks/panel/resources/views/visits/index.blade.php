@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.visits'))

@section('page-title-right')
  <button type="button" class="btn btn-outline-primary btn-sm" id="btn-batch-locate">
    <i class="bi bi-lightning-charge"></i> 补全数据
  </button>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <x-panel-data-data-search
      :action="panel_route('visits.index')"
      :searchFields="$searchFields ?? []"
      :filters="$filterButtons ?? []"
      :enableDateRange="true"
    />

    @if ($visits->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('common/base.id') }}</td>
            <td>{{ __('panel/visit.session_id') }}</td>
            <td>{{ __('panel/visit.ip_address') }}</td>
            <td>{{ __('panel/visit.location') }}</td>
            <td>{{ __('panel/visit.device_type') }}</td>
            <td>{{ __('panel/visit.page_views') }}</td>
            <td>{{ __('panel/visit.first_visited_at') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($visits as $visit)
          <tr data-id="{{ $visit->id }}">
            <td>{{ $visit->id }}</td>
            <td><code class="small">{{ \Illuminate\Support\Str::limit($visit->session_id, 20) }}</code></td>
            <td>{{ $visit->ip_address }}</td>
            <td class="col-location">
              @if($visit->country_code)
                <span class="badge bg-info">{{ $visit->country_code }}</span>
                @if($visit->city)
                  <span class="ms-1">{{ $visit->city }}</span>
                @endif
              @else
                <span class="text-muted">-</span>
              @endif
              @unless($visit->country_code && $visit->city)
              <a href="javascript:void(0)" class="text-secondary text-decoration-none btn-refresh-geo ms-1" title="刷新位置"><i class="bi bi-arrow-repeat"></i></a>
              @endunless
            </td>
            <td>
              @if($visit->device_type)
                <span class="badge bg-secondary">{{ $visit->device_type_display }}</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              @if($visit->visit_events_count > 0)
                <a href="{{ panel_route('visits.show', [$visit->id]) }}" class="badge bg-primary text-decoration-none">
                  {{ $visit->visit_events_count }}
                </a>
              @else
                <span class="text-muted">0</span>
              @endif
            </td>
            <td>{{ $visit->first_visited_at }}</td>
            <td>
              <a href="{{ panel_route('visits.show', [$visit->id]) }}" class="btn btn-sm btn-outline-primary">
                {{ __('common/base.view') }}
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $visits->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>

@push('footer')
<script>
(function() {
    var locateUrl = '{{ panel_route("visits.locate", ["visit" => "__ID__"]) }}';
    var batchUrl  = '{{ panel_route("visits.batch_locate") }}';

    // Batch locate button
    var batchBtn = document.getElementById('btn-batch-locate');
    if (batchBtn) {
        batchBtn.addEventListener('click', function() {
            if (batchBtn.disabled) return;
            batchBtn.disabled = true;
            batchBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> 处理中...';
            axios.post(batchUrl).then(function(data) {
                if (data.success) {
                    inno.msg('已补全 ' + data.updated + ' 条记录');
                    setTimeout(function() { location.reload(); }, 800);
                }
            }).catch(function(err) {
                var msg = (err.response && err.response.data && err.response.data.error) || '补全失败，请重试';
                inno.msg(msg);
                batchBtn.disabled = false;
                batchBtn.innerHTML = '<i class="bi bi-lightning-charge"></i> 补全数据';
            });
        });
    }

    // Per-row refresh geo button
    document.querySelectorAll('.btn-refresh-geo').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var row = btn.closest('tr');
            var id  = row.dataset.id;
            if (btn.disabled) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            var locCell = row.querySelector('.col-location');

            axios.post(locateUrl.replace('__ID__', id)).then(function(data) {
                if (data.success) {
                    var parts = [];
                    if (data.country_name) parts.push(data.country_name);
                    if (data.city) parts.push(data.city);
                    locCell.textContent = parts.join(' - ') || '-';
                }
            }).catch(function(err) {
                var msg = (err.response && err.response.data && err.response.data.error) || '';
                if (msg) inno.msg(msg);
            }).finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
            });
        });
    });
})();
</script>
@endpush
@endsection
