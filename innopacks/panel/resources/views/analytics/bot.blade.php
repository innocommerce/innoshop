@extends('panel::layouts.app')

@section('title', __('panel/menu.analytics_bot'))

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')
<x-panel::form.date-range
    :action="panel_route('analytics_bot')"
    :date_filter="$date_filter ?? ''"
    :start_date="$start_date ?? ''"
    :end_date="$end_date ?? ''"
/>

{{-- 总览卡片 --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-secondary">{{ number_format($statistics['sessions'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.bot_sessions') }}</div>
          </div>
          <div class="text-secondary opacity-50">
            <i class="bi bi-bug-fill fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-info">{{ number_format($statistics['unique_visitors'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.bot_unique_ips') }}</div>
          </div>
          <div class="text-info opacity-50">
            <i class="bi bi-hdd-network-fill fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-primary">{{ number_format($statistics['page_views'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.bot_page_views') }}</div>
          </div>
          <div class="text-primary opacity-50">
            <i class="bi bi-bar-chart-line-fill fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-warning">{{ number_format($statistics['event_sessions'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.bot_event_sessions') }}</div>
          </div>
          <div class="text-warning opacity-50">
            <i class="bi bi-lightning-charge-fill fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- 每日趋势 --}}
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0">{{ __('panel/visit.bot_daily_trends') }}</h6>
        <div class="d-flex align-items-center gap-2">
          <div class="btn-group btn-group-sm" id="metricTabs">
            <button class="btn btn-outline-secondary active" data-metric="sessions" onclick="switchMetric('sessions')">{{ __('panel/visit.bot_sessions') }}</button>
            <button class="btn btn-outline-secondary" data-metric="ips" onclick="switchMetric('ips')">{{ __('panel/visit.bot_unique_ips') }}</button>
            <button class="btn btn-outline-secondary" data-metric="pv" onclick="switchMetric('pv')">{{ __('panel/visit.bot_page_views') }}</button>
          </div>
          <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#botGuideModal" title="{{ __('panel/visit.usage_guide') }}">
            <i class="bi bi-question-circle"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        @if(! empty($daily_statistics))
          <div style="height: 320px;">
            <canvas id="botDailyChart"></canvas>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-bar-chart-line d-block mx-auto mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mb-0">{{ __('common/base.no_data') }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- 品牌分布 --}}
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0">{{ __('panel/visit.by_bot_brand') }}</h6>
        @if(! empty($bot_by_brand))
          <span class="text-muted small">{{ count($bot_by_brand) }} {{ __('panel/visit.brands') }}</span>
        @endif
      </div>
      <div class="card-body">
        @if(! empty($bot_by_brand))
          {{-- 分类汇总条 --}}
          @if(! empty($bot_category_summary))
            <div class="mb-3">
              <div class="d-flex flex-wrap gap-3 mb-2">
                @foreach($bot_category_summary as $row)
                  <div class="d-flex align-items-center" @if($row['category_tip']) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $row['category_tip'] }}" @endif>
                    <span class="badge me-2" style="{{ $row['category_color'] }}">{{ $row['category_label'] }}</span>
                    <span class="fw-bold">{{ number_format($row['sessions']) }}</span>
                    <span class="text-muted small ms-1">({{ number_format($row['share'], 1) }}%)</span>
                  </div>
                @endforeach
              </div>
              <div class="progress" style="height: 12px;">
                @foreach($bot_category_summary as $row)
                  <div class="progress-bar" role="progressbar"
                       style="width: {{ $row['share'] }}%; {{ $row['category_color'] }}"
                       aria-valuenow="{{ round($row['share'], 1) }}" aria-valuemin="0" aria-valuemax="100"></div>
                @endforeach
              </div>
            </div>
          @endif

          <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 28%;">{{ __('panel/visit.bot_brand') }}</th>
                  <th style="width: 18%;">{{ __('panel/visit.bot_category') }}</th>
                  <th class="text-end">{{ __('panel/visit.bot_sessions') }}</th>
                  <th class="text-end">{{ __('panel/visit.bot_unique_ips') }}</th>
                  <th style="width: 28%;">{{ __('panel/visit.bot_share') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($bot_by_brand as $row)
                  <tr>
                    <td>
                      <span class="badge bg-secondary bg-opacity-10 text-secondary border me-1">{{ $row['brand'] }}</span>
                      {{ $row['brand_label'] }}
                    </td>
                    <td>
                      <span class="badge" style="{{ $row['category_color'] }}" @if($row['category_tip']) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $row['category_tip'] }}" @endif>{{ $row['category_label'] }}</span>
                    </td>
                    <td class="text-end">{{ number_format($row['sessions']) }}</td>
                    <td class="text-end">{{ number_format($row['unique_ips']) }}</td>
                    <td>
                      <div class="progress" style="height: 16px;">
                        <div class="progress-bar bg-secondary" role="progressbar"
                             style="width: {{ min(100, $row['share']) }}%;"
                             aria-valuenow="{{ round($row['share'], 1) }}" aria-valuemin="0" aria-valuemax="100">
                          {{ number_format($row['share'], 1) }}%
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center text-muted py-5">
            <i class="bi bi-bug d-block mx-auto mb-2" style="font-size: 3rem; opacity: .5;"></i>
            <span class="small">{{ __('common/base.no_data') }}</span>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
{{-- 机器人过滤指南弹窗 --}}
<div class="modal fade" id="botGuideModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-question-circle me-1"></i>{{ __('panel/visit.usage_guide') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">{{ __('panel/visit.bot_guide_intro') }}</p>

        <ul class="list-unstyled small mb-3">
          <li class="mb-2">
            <code>php artisan visits:tag-bots --include-suspicious</code>
            <div class="text-muted">{{ __('panel/visit.guide_cmd_tag_bots') }}</div>
          </li>
        </ul>

        <div class="alert alert-light border small mb-0">
          <i class="bi bi-clock-history me-1"></i>
          {{ __('panel/visit.bot_guide_cron_desc') }}
          <code class="d-block mt-2">* * * * * cd /var/www/innoshop && php artisan schedule:run >> /dev/null 2>&1</code>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common/base.close') }}</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('footer')
<script>
let botChart = null;
const botDailyLabels = {!! json_encode($chart_labels ?? []) !!};
const botAllMetrics = {
  sessions: { data: {!! json_encode($chart_sessions ?? []) !!}, label: '{{ __('panel/visit.bot_sessions') }}', color: '#6c757d', bg: 'rgba(108, 117, 125, 0.12)' },
  ips:      { data: {!! json_encode($chart_unique_ips ?? []) !!}, label: '{{ __('panel/visit.bot_unique_ips') }}', color: '#0dcaf0', bg: 'rgba(13, 202, 240, 0.12)' },
  pv:       { data: {!! json_encode($chart_page_views ?? []) !!}, label: '{{ __('panel/visit.bot_page_views') }}', color: '#0d6efd', bg: 'rgba(13, 110, 253, 0.12)' },
};
let currentMetric = 'sessions';

function switchMetric(metric) {
  currentMetric = metric;
  document.querySelectorAll('#metricTabs .btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.metric === metric);
  });
  renderBotDailyChart();
}

function renderBotDailyChart() {
  const ctx = document.getElementById('botDailyChart');
  if (!ctx) return;
  if (botChart) botChart.destroy();
  const m = botAllMetrics[currentMetric];
  botChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: botDailyLabels,
      datasets: [{
        label: m.label,
        data: m.data,
        borderColor: m.color,
        backgroundColor: m.bg,
        fill: true,
        tension: 0.35
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom' } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
    if (window.bootstrap) new bootstrap.Tooltip(el);
  });

  @if(! empty($daily_statistics))
  renderBotDailyChart();
  @endif
});
</script>
@endpush
