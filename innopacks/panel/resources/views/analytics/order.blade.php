@extends('panel::layouts.app')

@section('title', __('panel/menu.analytics_order'))

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')
<x-panel::form.date-range
    :action="panel_route('analytics_order')"
    :date_filter="$date_filter ?? ''"
    :start_date="$start_date ?? ''"
    :end_date="$end_date ?? ''"
/>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-primary">{{ number_format($order_statistics['total_orders'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/menu.orders') }}</div>
          </div>
          <div class="text-primary opacity-50">
            <i class="bi bi-cart-check fs-1"></i>
          </div>
        </div>
        @if(isset($order_statistics['order_growth']))
          <div class="mt-2 small">
            <span class="text-{{ $order_statistics['order_growth'] >= 0 ? 'success' : 'danger' }}">
              <i class="bi bi-arrow-{{ $order_statistics['order_growth'] >= 0 ? 'up' : 'down' }}"></i>
              {{ number_format(abs($order_statistics['order_growth']), 1) }}%
            </span>
            <span class="text-muted ms-1">{{ __('panel/analytics.vs_last_period') }}</span>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-success">{{ currency_format($order_statistics['total_revenue'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.total_trends') }}</div>
          </div>
          <div class="text-success opacity-50">
            <i class="bi bi-currency-dollar fs-1"></i>
          </div>
        </div>
        @if(isset($order_statistics['revenue_growth']))
          <div class="mt-2 small">
            <span class="text-{{ $order_statistics['revenue_growth'] >= 0 ? 'success' : 'danger' }}">
              <i class="bi bi-arrow-{{ $order_statistics['revenue_growth'] >= 0 ? 'up' : 'down' }}"></i>
              {{ number_format(abs($order_statistics['revenue_growth']), 1) }}%
            </span>
            <span class="text-muted ms-1">{{ __('panel/analytics.vs_last_period') }}</span>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-info">{{ currency_format($order_statistics['avg_order_value'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.avg_order_value') }}</div>
          </div>
          <div class="text-info opacity-50">
            <i class="bi bi-calculator fs-1"></i>
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
            <div class="fs-3 fw-bold text-warning">{{ number_format($status_distribution['total_orders'] ?? array_sum($status_distribution['data'] ?? [])) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.completed_orders') }}</div>
          </div>
          <div class="text-warning opacity-50">
            <i class="bi bi-check-circle fs-1"></i>
          </div>
        </div>
        <div class="mt-2 small text-muted">
          @php
            $completedIdx = array_search(__('panel/order.completed'), $status_distribution['labels'] ?? []);
            $completedCount = $completedIdx !== false ? ($status_distribution['data'][$completedIdx] ?? 0) : 0;
            $totalOrders = array_sum($status_distribution['data'] ?? []);
          @endphp
          {{ $totalOrders > 0 ? number_format($completedCount / $totalOrders * 100, 1) : 0 }}% {{ __('panel/analytics.completion_rate') }}
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.order_trend') }}</h6>
      </div>
      <div class="card-body">
        <div style="height: 300px;">
          <canvas id="orderTrendChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.order_status') }}</h6>
      </div>
      <div class="card-body">
        <div style="height: 200px;">
          <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-3">
          <table class="table table-sm mb-0">
            <tbody>
              @foreach($status_distribution['labels'] ?? [] as $key => $label)
              <tr>
                <td><span class="badge" style="background: {{ $status_distribution['colors'][$key] ?? '#6c757d' }}">&nbsp;</span> {{ $label }}</td>
                <td class="text-end fw-bold">{{ $status_distribution['data'][$key] ?? 0 }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Top Selling Products --}}
<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/dashboard.top_products') }}</h6>
      </div>
      <div class="card-body">
        @if($top_sale_products && count($top_sale_products) > 0)
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>{{ __('panel/product.name') }}</th>
                  <th class="text-end">{{ __('panel/product.sales') }}</th>
                  <th class="text-end">{{ __('panel/analytics.total_amount') }}</th>
                  <th class="text-end">{{ __('panel/menu.orders') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($top_sale_products as $index => $product)
                <tr>
                  <td>
                    @if($index < 3)
                      <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'info') }}">
                        <i class="bi bi-trophy-fill"></i>
                      </span>
                    @else
                      <span class="text-muted">{{ $index + 1 }}</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ panel_route('products.edit', $product['id']) }}" class="text-decoration-none">
                      {{ $product['name'] }}
                    </a>
                  </td>
                  <td class="text-end">{{ number_format($product['total_quantity'] ?? 0) }}</td>
                  <td class="text-end fw-bold text-success">{{ currency_format($product['total_amount'] ?? 0) }}</td>
                  <td class="text-end">{{ number_format($product['order_count'] ?? 0) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-box-seam d-block mx-auto mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
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
  // Order trend chart (dual axis)
  const orderTrendCtx = document.getElementById('orderTrendChart');
  if (orderTrendCtx) {
    new Chart(orderTrendCtx, {
      type: 'bar',
      data: {
        labels: {!! json_encode($daily_trends['labels'] ?? []) !!},
        datasets: [
          {
            label: '{{ __('panel/menu.orders') }}',
            data: {!! json_encode($daily_trends['counts'] ?? []) !!},
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderColor: '#0d6efd',
            borderWidth: 1,
            yAxisID: 'y'
          },
          {
            label: '{{ __('panel/analytics.total_trends') }}',
            data: {!! json_encode($daily_trends['totals'] ?? []) !!},
            type: 'line',
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            fill: false,
            tension: 0.4,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        scales: {
          y: {
            type: 'linear',
            position: 'left',
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          },
          y1: {
            type: 'linear',
            position: 'right',
            beginAtZero: true,
            grid: {
              drawOnChartArea: false
            }
          }
        }
      }
    });
  }

  // Status distribution chart
  const statusCtx = document.getElementById('statusChart');
  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($status_distribution['labels'] ?? []) !!},
        datasets: [{
          data: {!! json_encode($status_distribution['data'] ?? []) !!},
          backgroundColor: {!! json_encode($status_distribution['colors'] ?? []) !!},
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
