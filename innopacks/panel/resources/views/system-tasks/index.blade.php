@extends('panel::layouts.app')

@section('body-class', 'page-system-tasks')

@section('title', __('panel/system_task.title'))

@section('page-title-right')
<div class="d-flex gap-2">
  <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#usageGuideModal">
    <i class="bi bi-question-circle"></i> {{ __('panel/system_task.guide_title') }}
  </button>
  <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
    <i class="bi bi-arrow-clockwise"></i> {{ __('panel/system_task.aggregate_refresh') }}
  </button>
</div>
@endsection

@section('content')

<div class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>{{ __('panel/system_task.card_scheduler') }}</span>
        @if ($scheduler['stale'])
          <span class="badge text-bg-danger"><i class="bi bi-slash-circle me-1"></i>{{ __('panel/system_task.status_stale') }}</span>
        @else
          <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>{{ __('panel/system_task.status_running') }}</span>
        @endif
      </div>
      <div class="card-body">
        @if ($scheduler['stale'])
        <div class="alert alert-warning py-2 px-3 small mb-3">
          <i class="bi bi-exclamation-triangle me-1"></i>{{ $scheduler['down_hint'] }}
        </div>
        @endif

        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
          <tr>
            <th>{{ __('panel/system_task.task_name') }}</th>
            <th>{{ __('panel/system_task.last_run') }}</th>
            <th class="text-end">{{ __('panel/system_task.status') }}</th>
          </tr>
          </thead>
          <tbody>
          @foreach ($tasks as $task)
          <tr>
            <td>{{ $task['name'] }}</td>
            <td>
              @if ($task['last_run'])
                <span title="{{ $task['last_run'] }}">{{ trans('panel/system_task.minutes_ago', ['minutes' => $task['minutes_ago']]) }}</span>
              @else
                <span class="text-secondary">{{ __('panel/system_task.never_run') }}</span>
              @endif
            </td>
            <td class="text-end">
              @if ($task['stale'])
                @if ($task['last_run'])
                  <span class="badge text-bg-danger">{{ __('panel/system_task.status_stale') }}</span>
                @else
                  <span class="badge text-bg-light text-secondary border">{{ __('panel/system_task.never_run') }}</span>
                @endif
              @else
                <span class="badge text-bg-success">{{ __('panel/system_task.status_running') }}</span>
              @endif
            </td>
          </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-5">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-stack me-2"></i>{{ __('panel/system_task.card_queue') }}</span>
        <span class="d-flex gap-2 align-items-center">
          @if ($queue['redis_ok'] && $queue['is_sync'])
            <span class="badge text-bg-warning">{{ __('panel/system_task.queue_sync_badge') }}</span>
          @elseif (! $queue['redis_ok'])
            <span class="badge text-bg-danger">{{ __('panel/system_task.queue_redis_down') }}</span>
          @elseif ($queue['horizon_alive'] === false)
            <span class="badge text-bg-danger">{{ __('panel/system_task.queue_workers_down') }}</span>
          @elseif ($queue['horizon_alive'])
            <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>{{ __('panel/system_task.queue_workers_running') }}</span>
          @endif
          <span class="badge text-bg-light text-dark border font-monospace">{{ $queue['connection'] }}</span>
        </span>
      </div>
      <div class="card-body d-flex flex-column">
        @if (! $queue['redis_ok'])
        <div class="alert alert-danger py-2 px-3 small mb-3">
          <i class="bi bi-x-octagon me-1"></i>{{ __('panel/system_task.queue_redis_down_hint') }}
        </div>
        @elseif ($queue['horizon_alive'] === false && ! $queue['is_sync'])
        <div class="alert alert-danger py-2 px-3 small mb-3">
          <i class="bi bi-exclamation-octagon me-1"></i>{{ __('panel/system_task.queue_workers_down_hint') }}
        </div>
        @elseif ($queue['horizon_alive'])
        <div class="alert alert-success py-2 px-3 small mb-3">
          <i class="bi bi-hdd-rack me-1"></i>{{ __('panel/system_task.queue_masters') }}：
          @foreach ($queue['master_names'] as $masterName)
            <span class="badge text-bg-light text-dark border font-monospace">{{ $masterName }}</span>
          @endforeach
        </div>
        @endif

        <div class="d-flex gap-3 mb-3">
          <div class="flex-fill border rounded text-center py-3">
            <div class="fs-3 fw-bold {{ ($queue['pending'] ?? 0) > 100 ? 'text-warning' : '' }}">{{ $queue['pending'] ?? '—' }}</div>
            <small class="text-secondary">{{ __('panel/system_task.queue_pending') }}</small>
          </div>
          <div class="flex-fill border rounded text-center py-3">
            <div class="fs-3 fw-bold {{ $queue['failed'] > 0 ? 'text-danger' : '' }}">{{ $queue['failed'] }}</div>
            <small class="text-secondary">{{ __('panel/system_task.queue_failed') }}</small>
          </div>
        </div>

        @if ($queue['can_view_horizon'])
        <a href="{{ url('/horizon') }}" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mb-2">
          <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('panel/system_task.queue_view_horizon') }}
        </a>
        @endif

        @if ($queue['is_sync'])
        <div class="alert alert-warning py-2 px-3 small mb-0 mt-auto">
          <i class="bi bi-exclamation-triangle me-1"></i>{{ __('panel/system_task.queue_sync_warning') }}
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <span><i class="bi bi-graph-up me-2"></i>{{ __('panel/system_task.card_visit_aggregate') }}</span>
  </div>
  <div class="card-body">
    <div class="d-flex flex-wrap gap-4 mb-3">
      <div>
        @if ($coverage['yesterday_ready'])
          <span class="badge text-bg-success">{{ __('panel/system_task.yesterday_ready') }}</span>
        @else
          <span class="badge text-bg-danger">{{ __('panel/system_task.yesterday_missing') }}</span>
        @endif
        @if ($coverage['today_ready'])
          <span class="badge text-bg-success">{{ __('panel/system_task.today_ready') }}</span>
        @else
          <span class="badge text-bg-secondary">{{ __('panel/system_task.today_missing') }}</span>
        @endif
        <small class="text-secondary d-block mt-1">{{ __('panel/system_task.coverage_range') }}：{{ $coverage['daily_min'] ? $coverage['daily_min'].' ~ '.$coverage['daily_max'] : __('panel/system_task.coverage_empty') }}</small>
      </div>
      <div class="ms-auto d-flex flex-wrap gap-2 align-items-start">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="aggregateTasks({ date: '{{ $coverage['yesterday_date'] }}' })">
          <i class="bi bi-skip-forward"></i> {{ __('panel/system_task.aggregate_yesterday') }}
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="aggregateTasks({})">
          <i class="bi bi-arrow-clockwise"></i> {{ __('panel/system_task.aggregate_range') }}
        </button>
        <div class="input-group input-group-sm" style="width: 210px;">
          <input type="date" class="form-control" id="aggregate-date" value="{{ $coverage['yesterday_date'] }}">
          <button type="button" class="btn btn-outline-secondary" onclick="aggregateTasks({ date: document.getElementById('aggregate-date').value })">
            {{ __('panel/system_task.aggregate_submit') }}
          </button>
        </div>
      </div>
    </div>

    @if (count($coverage['missing_dates']) > 0)
    <div class="border rounded p-2" style="max-height: 110px; overflow-y: auto;">
      <small class="text-secondary d-block mb-1">{{ __('panel/system_task.missing_dates') }}（{{ trans('panel/system_task.missing_days', ['count' => count($coverage['missing_dates'])]) }}）</small>
      @foreach ($coverage['missing_dates'] as $missingDate)
        <span class="badge text-bg-warning me-1 mb-1 fw-normal">{{ $missingDate }}</span>
      @endforeach
    </div>
    @else
    <small class="text-secondary"><i class="bi bi-check2 me-1"></i>{{ __('panel/system_task.missing_dates') }}：{{ __('panel/system_task.no_missing') }}</small>
    @endif
  </div>
</div>

<div class="modal fade" id="usageGuideModal" tabindex="-1" aria-labelledby="usageGuideModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="usageGuideModalLabel"><i class="bi bi-question-circle me-1"></i>{{ __('panel/system_task.guide_title') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <div class="fw-bold mb-2"><i class="bi bi-clock-history text-primary me-2"></i>{{ __('panel/system_task.guide_section_scheduler') }}</div>
          <div class="mb-1">{{ __('panel/system_task.guide_scheduler_crontab') }}</div>
          <pre class="bg-light border rounded p-2 mb-3" style="font-size: .84rem;"><code>* * * * * cd /path/to/innoshop &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code></pre>
          <div class="mb-1">{{ __('panel/system_task.guide_scheduler_local') }}</div>
          <pre class="bg-light border rounded p-2 mb-0" style="font-size: .84rem;"><code>php artisan schedule:work</code></pre>
        </div>

        <div class="mb-4">
          <div class="fw-bold mb-2"><i class="bi bi-stack text-primary me-2"></i>{{ __('panel/system_task.guide_section_queue') }}</div>
          <div class="mb-1">{{ __('panel/system_task.guide_queue_connection') }}</div>
          <pre class="bg-light border rounded p-2 mb-2" style="font-size: .84rem;"><code>QUEUE_CONNECTION=redis</code></pre>
          <div class="mb-1">{{ __('panel/system_task.guide_queue_consumer') }}</div>
          <pre class="bg-light border rounded p-2 mb-0" style="font-size: .84rem;"><code>php artisan queue:work</code></pre>
        </div>

        <div class="mb-0">
          <div class="fw-bold mb-2"><i class="bi bi-info-circle text-primary me-2"></i>{{ __('panel/system_task.guide_section_status') }}</div>
          <div class="d-flex align-items-start gap-2 mb-1">
            <span class="badge text-bg-success mt-1">{{ __('panel/system_task.status_running') }}</span>
            <span>{{ __('panel/system_task.guide_status_running') }}</span>
          </div>
          <div class="d-flex align-items-start gap-2 mb-1">
            <span class="badge text-bg-danger mt-1">{{ __('panel/system_task.status_stale') }}</span>
            <span>{{ __('panel/system_task.guide_status_stale') }}</span>
          </div>
          <div class="d-flex align-items-start gap-2 mb-2">
            <span class="badge text-bg-light text-secondary border mt-1">{{ __('panel/system_task.never_run') }}</span>
            <span>{{ __('panel/system_task.guide_status_never') }}</span>
          </div>
          <div>{{ __('panel/system_task.guide_current_queue') }} <span class="badge text-bg-light text-dark border font-monospace">{{ $queue['connection'] }}</span></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('footer')
<script>
  function aggregateTasks(params) {
    axios.post('{{ panel_route("system_tasks.aggregate") }}', params).then(function(data) {
      inno.msg(data.message || '{{ __("panel/analytics.reaggregate_queued") }}');
      setTimeout(function() { location.reload(); }, 1500);
    }).catch(function(err) {
      inno.msg(err.response?.data?.message || '{{ __("common/base.error") }}');
    });
  }
</script>
@endpush
