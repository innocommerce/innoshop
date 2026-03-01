@extends('panel::layouts.app')

@section('title', __('panel/menu.analytics_product'))

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')
<x-panel::form.date-range
    :action="panel_route('analytics_product')"
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
            <div class="fs-3 fw-bold text-primary">{{ number_format($product_statistics['total_products'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.total_products') }}</div>
          </div>
          <div class="text-primary opacity-50">
            <i class="bi bi-box-seam fs-1"></i>
          </div>
        </div>
        @if(isset($product_statistics['growth']))
          <div class="mt-2 small">
            <span class="text-{{ $product_statistics['growth'] >= 0 ? 'success' : 'danger' }}">
              <i class="bi bi-arrow-{{ $product_statistics['growth'] >= 0 ? 'up' : 'down' }}"></i>
              {{ number_format(abs($product_statistics['growth']), 1) }}%
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
            <div class="fs-3 fw-bold text-success">{{ number_format($product_statistics['active_products'] ?? 0) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.active_products') }}</div>
          </div>
          <div class="text-success opacity-50">
            <i class="bi bi-check-circle fs-1"></i>
          </div>
        </div>
        <div class="mt-2 small text-muted">
          {{ number_format(($product_statistics['active_products'] ?? 0) / max(1, $product_statistics['total_products'] ?? 1) * 100, 1) }}% {{ __('panel/analytics.of_total') }}
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-3 fw-bold text-info">{{ currency_format(array_sum(array_column($top_products ?? [], 'total_amount'))) }}</div>
            <div class="text-muted small">{{ __('panel/analytics.total_sales') }}</div>
          </div>
          <div class="text-info opacity-50">
            <i class="bi bi-currency-dollar fs-1"></i>
          </div>
        </div>
        <div class="mt-2 small text-muted">
          {{ __('panel/analytics.from_top_products', ['count' => count($top_products ?? [])]) }}
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
        <h6 class="mb-0">{{ __('panel/analytics.product_trend') }}</h6>
      </div>
      <div class="card-body">
        <div style="height: 300px;">
          <canvas id="productTrendChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.category_distribution') }}</h6>
      </div>
      <div class="card-body">
        @if($category_distribution && count($category_distribution['labels'] ?? []) > 0)
          <div style="height: 200px;">
            <canvas id="categoryChart"></canvas>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-tags d-block mx-auto mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mb-0">{{ __('common/base.no_data') }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Top Products --}}
<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">{{ __('panel/analytics.top_selling_products') }}</h6>
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
                  <th class="text-end">{{ __('panel/menu.orders') }}</th>
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
  // Product trend chart
  const productTrendCtx = document.getElementById('productTrendChart');
  if (productTrendCtx) {
    new Chart(productTrendCtx, {
      type: 'line',
      data: {
        labels: {!! json_encode($daily_trends['labels'] ?? []) !!},
        datasets: [{
          label: '{{ __('panel/analytics.new_products') }}',
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

  // Category distribution chart
  const categoryCtx = document.getElementById('categoryChart');
  if (categoryCtx) {
    new Chart(categoryCtx, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($category_distribution['labels'] ?? []) !!},
        datasets: [{
          data: {!! json_encode($category_distribution['data'] ?? []) !!},
          backgroundColor: ['#0d6efd', '#0dcaf0', '#20c997', '#ffc107', '#fd7e14', '#6c757d', '#dc3545', '#d63384'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              padding: 8,
              font: {
                size: 11
              }
            }
          }
        }
      }
    });
  }
});
</script>
@endpush
