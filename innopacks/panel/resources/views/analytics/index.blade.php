@extends('panel::layouts.app')

@section('title', __('panel/menu.analytics'))

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')
<x-panel::form.date-range
    :action="panel_route('analytics.index')"
    :date_filter="$date_filter ?? ''"
    :start_date="$start_date ?? ''"
    :end_date="$end_date ?? ''"
/>

{{-- KPI Cards - Row 1 --}}
<div class="row g-3 mb-4">
  {{-- Orders --}}
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

  {{-- Revenue --}}
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

  {{-- Products --}}
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-info">{{ number_format($product_statistics['total_products'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/menu.products') }}</div>
          </div>
          <div class="text-info opacity-50">
            <i class="bi bi-box-seam fs-1"></i>
          </div>
        </div>
        @if(isset($product_statistics['active_products']))
          <div class="mt-2 small text-muted">
            {{ __('panel/common.active') }}: {{ number_format($product_statistics['active_products']) }}
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Customers --}}
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-warning">{{ number_format($customer_statistics['total_customers'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/menu.customers') }}</div>
          </div>
          <div class="text-warning opacity-50">
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
</div>

{{-- KPI Cards - Row 2 --}}
<div class="row g-3 mb-4">
  {{-- Pending Orders --}}
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-warning">{{ number_format($pending_orders ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.pending_orders') }}</div>
          </div>
          <div class="text-warning opacity-50">
            <i class="bi bi-clock-history fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Unpaid Orders --}}
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-danger">{{ number_format($unpaid_orders ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.unpaid_orders') }}</div>
          </div>
          <div class="text-danger opacity-50">
            <i class="bi bi-credit-card fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Page Views --}}
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-info">{{ number_format($visit_statistics['page_views'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.page_views') }}</div>
          </div>
          <div class="text-info opacity-50">
            <i class="bi bi-eye fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Unique Visitors --}}
  <div class="col-6 col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-primary">{{ number_format($visit_statistics['unique_visitors'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/visit.unique_visitors') }}</div>
          </div>
          <div class="text-primary opacity-50">
            <i class="bi bi-people-fill fs-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Charts Row 1 --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.revenue_trend') }}</h6>
      </div>
      <div class="card-body">
        <div style="height: 280px;">
          <canvas id="revenueChart"></canvas>
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

{{-- Charts Row 2 --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/menu.orders') }}</h6>
      </div>
      <div class="card-body">
        <div style="height: 200px;">
          <canvas id="orderChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.product_trends') }}</h6>
      </div>
      <div class="card-body">
        <div style="height: 200px;">
          <canvas id="productChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.customer_trends') }}</h6>
      </div>
      <div class="card-body">
        <div style="height: 200px;">
          <canvas id="customerChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Top Products --}}
<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/dashboard.top_products') }}</h6>
      </div>
      <div class="card-body">
        @if($top_products && count($top_products) > 0)
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>{{ __('panel/product.name') }}</th>
                  <th class="text-end">{{ __('panel/product.sales') }}</th>
                  <th class="text-end">{{ __('panel/analytics.total_amount') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($top_products as $index => $product)
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
  // Revenue trend chart (dual axis)
  const revenueCtx = document.getElementById('revenueChart');
  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: 'bar',
      data: {
        labels: {!! json_encode($order_trends['labels'] ?? []) !!},
        datasets: [
          {
            label: '{{ __('panel/menu.orders') }}',
            data: {!! json_encode($order_trends['counts'] ?? []) !!},
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderColor: '#0d6efd',
            borderWidth: 1,
            yAxisID: 'y'
          },
          {
            label: '{{ __('panel/analytics.total_trends') }}',
            data: {!! json_encode($order_trends['totals'] ?? []) !!},
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

  // Order chart
  const orderCtx = document.getElementById('orderChart');
  if (orderCtx) {
    new Chart(orderCtx, {
      type: 'line',
      data: {
        labels: {!! json_encode($order_trends['labels'] ?? []) !!},
        datasets: [{
          label: '{{ __('panel/menu.orders') }}',
          data: {!! json_encode($order_trends['counts'] ?? []) !!},
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

  // Product chart
  const productCtx = document.getElementById('productChart');
  if (productCtx) {
    new Chart(productCtx, {
      type: 'line',
      data: {
        labels: {!! json_encode($product_trends['labels'] ?? []) !!},
        datasets: [{
          label: '{{ __('panel/analytics.product_trends') }}',
          data: {!! json_encode($product_trends['totals'] ?? []) !!},
          borderColor: '#0dcaf0',
          backgroundColor: 'rgba(13, 202, 240, 0.1)',
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

  // Customer chart
  const customerCtx = document.getElementById('customerChart');
  if (customerCtx) {
    new Chart(customerCtx, {
      type: 'line',
      data: {
        labels: {!! json_encode($customer_trends['labels'] ?? []) !!},
        datasets: [{
          label: '{{ __('panel/analytics.customer_trends') }}',
          data: {!! json_encode($customer_trends['totals'] ?? []) !!},
          borderColor: '#ffc107',
          backgroundColor: 'rgba(255, 193, 7, 0.1)',
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
});
</script>
@endpush
