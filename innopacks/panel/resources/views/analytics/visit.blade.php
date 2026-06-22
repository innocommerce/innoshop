@extends('panel::layouts.app')

@section('title', __('panel/menu.analytics_visit'))

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
<script src="{{ asset('vendor/echarts/echarts.min.js') }}"></script>
@endpush

@section('content')
<x-panel::form.date-range
    :action="panel_route('analytics_visit')"
    :date_filter="$date_filter ?? ''"
    :start_date="$start_date ?? ''"
    :end_date="$end_date ?? ''"
/>

{{-- 数据总览卡片 --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-primary">{{ number_format($statistics['total_visits'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.total_visits') }}</div>
          </div>
          <div class="text-primary opacity-50">
            <i class="bi bi-eye-fill fs-1"></i>
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
            <div class="fs-3 fw-bold text-success">{{ number_format($statistics['unique_visitors'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.unique_visitors') }}</div>
          </div>
          <div class="text-success opacity-50">
            <i class="bi bi-people-fill fs-1"></i>
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
            <div class="fs-3 fw-bold text-info">{{ number_format($statistics['page_views'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.page_views') }}</div>
          </div>
          <div class="text-info opacity-50">
            <i class="bi bi-bar-chart-line fs-1"></i>
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
            <div class="fs-3 fw-bold text-warning">{{ number_format(($avg_visit_duration ?? 0) / 60, 1) }}</div>
            <div class="text-muted small">{{ __('panel/visit.avg_duration') }} (min)</div>
          </div>
          <div class="text-warning opacity-50">
            <i class="bi bi-clock-history fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- 转化漏斗 --}}
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/visit.conversion_funnel') }}</h6>
      </div>
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-center align-items-center text-center">
          {{-- Home Views --}}
          <div class="px-2 py-3" style="min-width: 100px;">
            <div class="fs-5 fw-bold text-secondary">{{ number_format($conversion_funnel['home_views'] ?? 0) }}</div>
            <div class="small text-muted">{{ trans('panel/visit.event_home_view') }}</div>
          </div>
          <div class="text-muted px-1"><i class="bi bi-arrow-right"></i></div>

          {{-- Category Views --}}
          <div class="px-2 py-3" style="min-width: 100px;">
            <div class="fs-5 fw-bold text-info">{{ number_format($conversion_funnel['category_views'] ?? 0) }}</div>
            <div class="small text-muted">{{ trans('panel/visit.event_category_view') }}</div>
            <div class="small text-primary">{{ number_format($conversion_funnel['conversion_rates']['home_to_category'] ?? 0, 1) }}%</div>
          </div>
          <div class="text-muted px-1"><i class="bi bi-arrow-right"></i></div>

          {{-- Product Views --}}
          <div class="px-2 py-3" style="min-width: 100px;">
            <div class="fs-5 fw-bold text-primary">{{ number_format($conversion_funnel['product_views'] ?? 0) }}</div>
            <div class="small text-muted">{{ __('panel/visit.product_views') }}</div>
            <div class="small text-primary">{{ number_format($conversion_funnel['conversion_rates']['category_to_product'] ?? 0, 1) }}%</div>
          </div>
          <div class="text-muted px-1"><i class="bi bi-arrow-right"></i></div>

          {{-- Add to Carts --}}
          <div class="px-2 py-3" style="min-width: 100px;">
            <div class="fs-5 fw-bold" style="color: #6f42c1;">{{ number_format($conversion_funnel['add_to_carts'] ?? 0) }}</div>
            <div class="small text-muted">{{ __('panel/visit.add_to_carts') }}</div>
            <div class="small text-primary">{{ number_format($conversion_funnel['conversion_rates']['product_to_cart'] ?? 0, 1) }}%</div>
          </div>
          <div class="text-muted px-1"><i class="bi bi-arrow-right"></i></div>

          {{-- Cart Views --}}
          <div class="px-2 py-3" style="min-width: 100px;">
            <div class="fs-5 fw-bold" style="color: #fd7e14;">{{ number_format($conversion_funnel['cart_views'] ?? 0) }}</div>
            <div class="small text-muted">{{ trans('panel/visit.event_cart_view') }}</div>
          </div>
          <div class="text-muted px-1"><i class="bi bi-arrow-right"></i></div>

          {{-- Checkout Starts --}}
          <div class="px-2 py-3" style="min-width: 100px;">
            <div class="fs-5 fw-bold text-warning">{{ number_format($conversion_funnel['checkout_starts'] ?? 0) }}</div>
            <div class="small text-muted">{{ __('panel/visit.checkout_starts') }}</div>
            <div class="small text-primary">{{ number_format($conversion_funnel['conversion_rates']['cart_to_checkout'] ?? 0, 1) }}%</div>
          </div>
          <div class="text-muted px-1"><i class="bi bi-arrow-right"></i></div>

          {{-- Order Placed --}}
          <div class="px-2 py-3" style="min-width: 100px;">
            <div class="fs-5 fw-bold">{{ number_format($conversion_funnel['order_placed'] ?? 0) }}</div>
            <div class="small text-muted">{{ __('panel/visit.order_placed') }}</div>
            <div class="small text-primary">{{ number_format($conversion_funnel['conversion_rates']['checkout_to_order'] ?? 0, 1) }}%</div>
          </div>
          <div class="text-muted px-1"><i class="bi bi-arrow-right"></i></div>

          {{-- Payment Completed --}}
          <div class="px-2 py-3" style="min-width: 100px;">
            <div class="fs-5 fw-bold text-success">{{ number_format($conversion_funnel['payment_completed'] ?? 0) }}</div>
            <div class="small text-muted">{{ __('panel/visit.payment_completed') }}</div>
            <div class="small text-primary">{{ number_format($conversion_funnel['conversion_rates']['order_to_payment'] ?? 0, 1) }}%</div>
          </div>
        </div>
        <div class="text-center mt-3 pt-3 border-top">
          <span class="text-muted">{{ __('panel/visit.overall_conversion') }}: </span>
          <span class="fs-5 fw-bold text-success">{{ number_format($conversion_funnel['conversion_rates']['overall_conversion'] ?? 0, 1) }}%</span>
          @if(($conversion_funnel['order_cancelled'] ?? 0) > 0)
            <span class="ms-3 text-muted">|</span>
            <span class="ms-3 text-muted">{{ trans('panel/visit.event_order_cancelled') }}: </span>
            <span class="fw-bold text-danger">{{ number_format($conversion_funnel['order_cancelled'] ?? 0) }}</span>
            <span class="small text-muted">({{ number_format($conversion_funnel['conversion_rates']['order_cancel_rate'] ?? 0, 1) }}%)</span>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

{{-- 趋势图表和设备分布 --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0">{{ __('panel/visit.daily_trends') }}</h6>
        <div class="d-flex align-items-center gap-2">
          <div class="btn-group btn-group-sm" id="metricTabs">
            <button class="btn btn-outline-primary active" data-metric="pv" onclick="switchMetric('pv')">PV</button>
            <button class="btn btn-outline-primary" data-metric="uv" onclick="switchMetric('uv')">UV</button>
            <button class="btn btn-outline-primary" data-metric="ip" onclick="switchMetric('ip')">IP</button>
          </div>
          <button class="btn btn-sm btn-outline-secondary" onclick="reaggregate()" title="{{ __('panel/analytics.reaggregate') }}">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
          <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#visitGuideModal" title="{{ __('panel/visit.usage_guide') }}">
            <i class="bi bi-question-circle"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        @if($daily_statistics && (is_countable($daily_statistics) ? count($daily_statistics) : $daily_statistics->count()) > 0)
          @php
            $dailyData = is_array($daily_statistics) ? $daily_statistics : $daily_statistics->toArray();
            $dailyLabels = array_column($dailyData, 'date');
            $dailyPv = array_column($dailyData, 'page_views');
            $dailyUv = array_column($dailyData, 'visits');
            $dailyIp = array_column($dailyData, 'unique_visitors');
          @endphp
          <div style="height: 300px;">
            <canvas id="dailyChart"></canvas>
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

  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/visit.by_device') }}</h6>
      </div>
      <div class="card-body">
        @if($visits_by_device && (is_countable($visits_by_device) ? count($visits_by_device) : $visits_by_device->count()) > 0)
          @php
            $deviceData = is_array($visits_by_device) ? $visits_by_device : $visits_by_device->toArray();
            $deviceLabels = array_map(function($d) { return __('panel/visit.device_' . $d['device_type']); }, $deviceData);
            $deviceVisits = array_column($deviceData, 'visits');
          @endphp
          <div style="height: 200px;">
            <canvas id="deviceChart"></canvas>
          </div>
          <div class="mt-3">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>{{ __('panel/visit.device_type') }}</th>
                  <th class="text-end">{{ __('panel/visit.visits') }}</th>
                  <th class="text-end">{{ __('panel/visit.page_views') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($deviceData as $device)
                <tr>
                  <td>
                    <i class="bi bi-{{ $device['device_type'] == 'desktop' ? 'display' : ($device['device_type'] == 'mobile' ? 'phone' : 'tablet') }} me-1"></i>
                    {{ __('panel/visit.device_' . $device['device_type']) }}
                  </td>
                  <td class="text-end">{{ number_format($device['visits']) }}</td>
                  <td class="text-end">{{ number_format($device['page_views']) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-device d-block mx-auto mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mb-0">{{ __('common/base.no_data') }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- 世界地图 --}}
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0">{{ __('panel/visit.by_country_map') }}</h6>
        <div class="d-flex align-items-center gap-2">
          @if($world_map_total > 0)
            <span class="text-muted small">{{ number_format($world_map_total) }} {{ __('panel/visit.visits') }} · {{ count($world_map_data) }} {{ __('panel/visit.countries') }}</span>
          @endif
          <button type="button" class="btn btn-sm btn-outline-secondary" id="worldMapLockBtn" title="{{ __('panel/visit.map_unlock_zoom') }}">
            <i class="bi bi-lock-fill"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        @if(! empty($world_map_data))
          <div id="worldMap" style="height: 480px;"></div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-globe d-block mx-auto mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mb-0">{{ __('common/base.no_data') }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- 国家分布和24小时分布 --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/visit.by_country') }}</h6>
      </div>
      <div class="card-body">
        @if($visits_by_country && (is_countable($visits_by_country) ? count($visits_by_country) : $visits_by_country->count()) > 0)
          @php
            $countryData = is_array($visits_by_country) ? array_slice($visits_by_country, 0, 10) : $visits_by_country->take(10)->toArray();
          @endphp
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>{{ __('panel/visit.country') }}</th>
                  <th class="text-end">{{ __('panel/visit.visits') }}</th>
                  <th class="text-end">{{ __('panel/visit.unique_visitors') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($countryData as $country)
                <tr>
                  <td>
                    <i class="bi bi-geo-alt me-1 text-muted"></i>
                    {{ $country['country_name'] ?? $country['country_code'] }}
                  </td>
                  <td class="text-end">{{ number_format($country['visits']) }}</td>
                  <td class="text-end">{{ number_format($country['unique_visitors']) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-globe d-block mx-auto mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mb-0">{{ __('common/base.no_data') }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/visit.hourly_distribution') }}</h6>
      </div>
      <div class="card-body">
        @if($hourly_statistics && (is_countable($hourly_statistics) ? count($hourly_statistics) : $hourly_statistics->count()) > 0)
          @php
            $hourlyData = is_array($hourly_statistics) ? $hourly_statistics : $hourly_statistics->toArray();
            $hourlyByHour = [];
            $maxVisits = 0;
            foreach ($hourlyData as $h) {
              $hourlyByHour[$h['hour']] = $h['visits'];
              $maxVisits = max($maxVisits, $h['visits']);
            }
          @endphp
          <div class="d-flex justify-content-between align-items-end" style="height: 150px;">
            @for($i = 0; $i < 24; $i++)
              @php $visits = $hourlyByHour[$i] ?? 0; $percent = $maxVisits > 0 ? ($visits / $maxVisits * 100) : 0; @endphp
              <div class="text-center" style="flex: 1; padding: 0 3px;">
                <div class="rounded bg-primary bar-hover"
                     style="height: {{ max(4, $percent * 1.3) }}px; min-height: 4px; cursor: pointer; transition: opacity .15s;"
                     data-bs-toggle="tooltip" data-bs-placement="top"
                     title="{{ $i }}:00 · {{ number_format($visits) }} {{ __('panel/visit.visits') }}"></div>
                <div class="small text-muted mt-1">{{ $i }}</div>
              </div>
            @endfor
          </div>
          <div class="text-center mt-2">
            <small class="text-muted">{{ __('panel/visit.hour') }}</small>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-clock d-block mx-auto mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mb-0">{{ __('common/base.no_data') }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- 使用指南弹窗 --}}
<div class="modal fade" id="visitGuideModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-question-circle me-1"></i>{{ __('panel/visit.usage_guide') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">{{ __('panel/visit.guide_intro') }}</p>

        <ul class="list-unstyled small mb-3">
          <li class="mb-2">
            <code>php artisan migrate</code>
            <div class="text-muted">{{ __('panel/visit.guide_cmd_migrate') }}</div>
          </li>
          <li class="mb-2">
            <code>php artisan visits:backfill-geo</code>
            <div class="text-muted">{{ __('panel/visit.guide_cmd_backfill_geo') }}</div>
          </li>
          <li class="mb-2">
            <code>php artisan visits:aggregate --backfill --from=YYYY-MM-DD --to=YYYY-MM-DD</code>
            <div class="text-muted">{{ __('panel/visit.guide_cmd_aggregate') }}</div>
          </li>
        </ul>

        <div class="alert alert-light border small mb-0">
          <i class="bi bi-clock-history me-1"></i>
          {{ __('panel/visit.guide_cron_laravel_desc') }}
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
<style>
.bar-hover:hover { opacity: 0.7; }
</style>
<script>
let dailyChart = null;
const dailyLabels = {!! json_encode($dailyLabels ?? []) !!};
const allMetrics = {
  pv: { data: {!! json_encode($dailyPv ?? []) !!}, label: 'PV', color: '#0d6efd', bg: 'rgba(13, 110, 253, 0.1)' },
  uv: { data: {!! json_encode($dailyUv ?? []) !!}, label: 'UV', color: '#0dcaf0', bg: 'rgba(13, 202, 240, 0.1)' },
  ip: { data: {!! json_encode($dailyIp ?? []) !!}, label: 'IP', color: '#6f42c1', bg: 'rgba(111, 66, 193, 0.1)' },
};
let currentMetric = 'pv';

function switchMetric(metric) {
  currentMetric = metric;
  document.querySelectorAll('#metricTabs .btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.metric === metric);
  });
  renderDailyChart();
}

function renderDailyChart() {
  const ctx = document.getElementById('dailyChart');
  if (!ctx) return;
  if (dailyChart) dailyChart.destroy();
  const m = allMetrics[currentMetric];
  dailyChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: dailyLabels,
      datasets: [{
        label: m.label,
        data: m.data,
        borderColor: m.color,
        backgroundColor: m.bg,
        fill: true,
        tension: 0.4
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

function reaggregate() {
  const params = new URLSearchParams(window.location.search);
  axios.post('{{ panel_route("analytics.reaggregate") }}', {
    start_date: params.get('start_date') || '{{ $start_date }}',
    end_date: params.get('end_date') || '{{ $end_date }}'
  }).then(function(data) {
    inno.msg(data.message || '{{ __("panel/analytics.reaggregate_success") }}');
    setTimeout(function() { location.reload(); }, 1000);
  }).catch(function(err) {
    inno.msg(err.response?.data?.message || '{{ __("common/base.error") }}');
  });
}

const worldMapData = {!! json_encode($world_map_data ?? []) !!};
const worldMapTotal = {{ (int) ($world_map_total ?? 0) }};
let worldMapChart = null;
let worldMapZoomUnlocked = false;

function renderWorldMap() {
  const el = document.getElementById('worldMap');
  if (!el || typeof echarts === 'undefined') return;

  fetch('{{ asset("vendor/echarts/world.json") }}')
    .then(r => r.json())
    .then(geo => {
      echarts.registerMap('world', geo);

      const max = Math.max(1, ...worldMapData.map(d => d.value));

      worldMapChart = echarts.init(el);
      worldMapChart.setOption({
        tooltip: {
          trigger: 'item',
          formatter: function(p) {
            const v = p.data ? p.data.value : 0;
            const pct = worldMapTotal > 0 ? (v / worldMapTotal * 100).toFixed(1) : '0.0';
            return p.name + ': <b>' + Number(v).toLocaleString() + '</b> ' +
              '({{ __("panel/visit.visits") }}) <span class="text-muted">' + pct + '%</span>';
          }
        },
        visualMap: {
          left: 'left',
          bottom: 20,
          min: 0,
          max: max,
          inRange: {
            color: ['#e0f3f8', '#abd9e9', '#74add1', '#4575b4', '#313695']
          },
          text: ['{{ __("panel/visit.map_high") }}', '{{ __("panel/visit.map_low") }}'],
          calculable: true
        },
        series: [{
          name: '{{ __("panel/visit.visits") }}',
          type: 'map',
          map: 'world',
          roam: 'move',
          scaleLimit: { min: 1, max: 10 },
          emphasis: {
            label: { show: true },
            itemStyle: { areaColor: '#f59e0b' }
          },
          data: worldMapData
        }]
      });

      window.addEventListener('resize', function() {
        worldMapChart && worldMapChart.resize();
      });
    })
    .catch(function(err) {
      console.error('World map load failed:', err);
      el.innerHTML = '<div class="text-center text-danger py-5">{{ __("common/base.error") }}</div>';
    });
}

function toggleWorldMapLock() {
  if (!worldMapChart) return;
  worldMapZoomUnlocked = !worldMapZoomUnlocked;
  worldMapChart.setOption({ series: [{ roam: worldMapZoomUnlocked ? true : 'move' }] });

  const btn = document.getElementById('worldMapLockBtn');
  if (btn) {
    btn.innerHTML = worldMapZoomUnlocked
      ? '<i class="bi bi-unlock-fill"></i>'
      : '<i class="bi bi-lock-fill"></i>';
    btn.title = worldMapZoomUnlocked
      ? '{{ __("panel/visit.map_lock_zoom") }}'
      : '{{ __("panel/visit.map_unlock_zoom") }}';
    btn.classList.toggle('active', worldMapZoomUnlocked);
  }
}

document.addEventListener('DOMContentLoaded', function() {
  // 24小时分布柱状图 tooltip
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
    if (window.bootstrap) new bootstrap.Tooltip(el);
  });

  // 每日趋势图
  @if($daily_statistics && (is_countable($daily_statistics) ? count($daily_statistics) : $daily_statistics->count()) > 0)
  renderDailyChart();
  @endif

  // 世界地图
  @if(! empty($world_map_data))
  renderWorldMap();
  const lockBtn = document.getElementById('worldMapLockBtn');
  if (lockBtn) lockBtn.addEventListener('click', toggleWorldMapLock);
  @endif

  // 设备分布图
  @if($visits_by_device && (is_countable($visits_by_device) ? count($visits_by_device) : $visits_by_device->count()) > 0)
  const deviceCtx = document.getElementById('deviceChart');
  if (deviceCtx) {
    new Chart(deviceCtx, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($deviceLabels ?? []) !!},
        datasets: [{
          data: {!! json_encode($deviceVisits ?? []) !!},
          backgroundColor: ['#0d6efd', '#0dcaf0', '#6c757d'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  }
  @endif
});
</script>
@endpush
