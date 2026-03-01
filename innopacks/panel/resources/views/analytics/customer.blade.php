@extends('panel::layouts.app')

@section('title', __('panel/menu.analytics_customer'))

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')
<x-panel::form.date-range
    :action="panel_route('analytics_customer')"
    :date_filter="$date_filter ?? ''"
    :start_date="$start_date ?? ''"
    :end_date="$end_date ?? ''"
/>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-primary">{{ number_format($customer_statistics['total_customers'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.total_customers') }}</div>
          </div>
          <div class="text-primary opacity-50">
            <i class="bi bi-people fs-1"></i>
          </div>
        </div>
        @if(isset($customer_statistics['growth']))
          <div class="mt-2 small">
            <span class="text-{{ $customer_statistics['growth'] >= 0 ? 'success' : 'danger' }}">
              <i class="bi bi-arrow-{{ $customer_statistics['growth'] >= 0 ? 'up' : 'down' }}"></i>
              {{ number_format(abs($customer_statistics['growth']), 1) }}%
            </span>
            <span class="text-muted ms-1">{{ __('panel/analytics.vs_last_period') }}</span>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-6 col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-success">{{ number_format($customer_statistics['active_customers'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.active_customers') }}</div>
          </div>
          <div class="text-success opacity-50">
            <i class="bi bi-person-check fs-1"></i>
          </div>
        </div>
        <div class="mt-2 small text-muted">
          {{ number_format(($customer_statistics['active_customers'] ?? 0) / max(1, $customer_statistics['total_customers'] ?? 1) * 100, 1) }}% {{ __('panel/analytics.of_total') }}
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-info">{{ number_format(array_sum($daily_trends['totals'] ?? [])) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.new_signups') }}</div>
          </div>
          <div class="text-info opacity-50">
            <i class="bi bi-person-plus fs-1"></i>
          </div>
        </div>
        @if(isset($customer_statistics['growth']))
          <div class="mt-2 small">
            <span class="text-{{ $customer_statistics['growth'] >= 0 ? 'success' : 'danger' }}">
              <i class="bi bi-arrow-{{ $customer_statistics['growth'] >= 0 ? 'up' : 'down' }}"></i>
              {{ number_format(abs($customer_statistics['growth']), 1) }}%
            </span>
            <span class="text-muted ms-1">{{ __('panel/analytics.vs_last_period') }}</span>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.customer_trend') }}</h6>
      </div>
      <div class="card-body">
        <div style="height: 300px;">
          <canvas id="customerTrendChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.customer_source') }}</h6>
      </div>
      <div class="card-body">
        @if($source_distribution && count($source_distribution['labels'] ?? []) > 0)
          <div style="height: 200px;">
            <canvas id="sourceChart"></canvas>
          </div>
          <div class="mt-3">
            <table class="table table-sm mb-0">
              <tbody>
                @foreach($source_distribution['labels'] ?? [] as $key => $label)
                <tr>
                  <td>{{ $label }}</td>
                  <td class="text-end fw-bold">{{ $source_distribution['data'][$key] ?? 0 }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-person-plus d-block mx-auto mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mb-0">{{ __('common/base.no_data') }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Customer trend chart
  const customerTrendCtx = document.getElementById('customerTrendChart');
  if (customerTrendCtx) {
    new Chart(customerTrendCtx, {
      type: 'line',
      data: {
        labels: {!! json_encode($daily_trends['labels'] ?? []) !!},
        datasets: [{
          label: '{{ __('panel/analytics.new_customers') }}',
          data: {!! json_encode($daily_trends['totals'] ?? []) !!},
          borderColor: '#0d6efd',
          backgroundColor: 'rgba(13, 110, 253, 0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });
  }

  // Source distribution chart
  const sourceCtx = document.getElementById('sourceChart');
  if (sourceCtx) {
    new Chart(sourceCtx, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($source_distribution['labels'] ?? []) !!},
        datasets: [{
          data: {!! json_encode($source_distribution['data'] ?? []) !!},
          backgroundColor: ['#0d6efd', '#0dcaf0', '#20c997', '#ffc107', '#fd7e14', '#6c757d'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  }
});
</script>
@endpush
