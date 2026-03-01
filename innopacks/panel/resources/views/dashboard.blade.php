@extends('panel::layouts.app')
@section('body-class', 'page-home')

@section('title', __('panel/menu.dashboard'))

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')

<div class="row dashboard-top-card g-2 g-lg-3 mb-3 mb-lg-4">
  @foreach ($cards as $card)
  <div class="col-6 col-md-4 col-lg-3">
    <div class="card dashboard-item h-100">
      <div class="card-body">
        @if (!empty($card['url']))
        <a href="{{ $card['url'] }}" class="text-decoration-none">
        @endif
        <div class="d-flex justify-content-between align-items-start">
          <div class="left flex-grow-1">
            <div class="quantity text-dark">{{ $card['quantity'] }}</div>
            <span class="title text-secondary d-block">{{ $card['title'] }}</span>
            @if (isset($card['growth']) && $card['growth'] !== 0.0)
            <div class="growth mt-1">
              @if ($card['growth'] > 0)
              <span class="text-success">
                <i class="bi bi-arrow-up"></i> {{ abs($card['growth']) }}%
              </span>
              @elseif ($card['growth'] < 0)
              <span class="text-danger">
                <i class="bi bi-arrow-down"></i> {{ abs($card['growth']) }}%
              </span>
              @endif
              <small class="text-muted">{{ __('panel/dashboard.vs_yesterday') }}</small>
            </div>
            @endif
          </div>
          <div class="right"><i class="{{ $card['icon'] ?? 'bi bi-box' }} icon"></i></div>
        </div>
        @if (!empty($card['url']))
        </a>
        @endif
      </div>
    </div>
  </div>
  @endforeach
</div>

<div class="row mb-3">
  <div class="col-12 col-lg-8 mb-3">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('panel/dashboard.revenue_orders') }}</span>
        <small class="text-muted">{{ __('panel/dashboard.last_30_days') }}</small>
      </div>
      <div class="card-body">
        <div style="height: 320px;">
          <canvas id="chart-revenue-orders"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4 mb-3">
    <div class="card h-100">
      <div class="card-header">{{ __('panel/dashboard.order_status_dist') }}</div>
      <div class="card-body">
        <div style="height: 320px;">
          <canvas id="chart-order-status"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row mb-3">
  <div class="col-12 col-lg-8 mb-3">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('panel/dashboard.traffic_trends') }}</span>
        <small class="text-muted">{{ __('panel/dashboard.last_30_days') }}</small>
      </div>
      <div class="card-body">
        <div style="height: 320px;">
          <canvas id="chart-traffic"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4 mb-3">
    <div class="card h-100">
      <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs nav-fill" id="rankingTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-panel" type="button" role="tab">{{ __('panel/dashboard.top_products') }}</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers-panel" type="button" role="tab">{{ __('panel/dashboard.top_customers') }}</button>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content" id="rankingTabContent">
          <div class="tab-pane fade show active" id="products-panel" role="tabpanel">
            @if ($top_sale_products)
              <table class="table table-last-no-border align-middle mb-0">
                <tbody>
                  @foreach($top_sale_products as $product)
                  <tr>
                    <td class="text-center" style="width: 40px;">
                      @if ($loop->iteration <= 3)
                        <img src="{{ asset('images/icons/grade-'. $loop->iteration .'.svg') }}" alt="{{ $product['name'] }}" class="img-fluid wh-30">
                      @else
                        <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                      @endif
                    </td>
                    <td>
                      <a class="text-dark text-decoration-none" href="{{ panel_route('products.edit', $product['product_id']) }}">
                        {{ $product['summary'] }}
                      </a>
                      <div class="text-muted small">{{ currency_format($product['total_amount']) }}</div>
                    </td>
                    <td class="text-center">{{ $product['total_quantity'] }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <x-common-no-data :width="200" />
            @endif
          </div>
          <div class="tab-pane fade" id="customers-panel" role="tabpanel">
            @if ($top_customers && count($top_customers) > 0)
              <table class="table table-last-no-border align-middle mb-0">
                <tbody>
                  @foreach($top_customers as $index => $customer)
                  <tr>
                    <td class="text-center" style="width: 40px;">
                      @if ($index < 3)
                        <img src="{{ asset('images/icons/grade-'. ($index + 1) .'.svg') }}" alt="{{ $customer['email'] }}" class="img-fluid wh-30">
                      @else
                        <span class="badge bg-secondary">{{ $index + 1 }}</span>
                      @endif
                    </td>
                    <td>
                      <a class="text-dark text-decoration-none" href="{{ panel_route('customers.edit', $customer['id']) }}">
                        {{ $customer['email'] }}
                      </a>
                      <div class="text-muted small">{{ currency_format($customer['total_amount']) }}</div>
                    </td>
                    <td class="text-center">{{ $customer['order_count'] }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <x-common-no-data :width="200" />
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<img src="{{ dashboard_url() }}" class="d-none" alt="dashboard"/>
@endsection

@push('footer')
<script>
  // Revenue & Orders Dual-Axis Chart
  const revenueCtx = document.getElementById('chart-revenue-orders');
  if (revenueCtx) {
    const revenueOrdersOptions = {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) {
                label += ': ';
              }
              if (context.dataset.yAxisID === 'y1') {
                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.raw);
              } else {
                label += Math.round(context.raw);
              }
              return label;
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            maxTicksLimit: 10,
            callback: function(value, index, ticks) {
              const label = this.getLabelForValue(value);
              if (label) {
                const parts = label.split('-');
                return parts[1] + '-' + parts[2];
              }
              return label;
            }
          }
        },
        y: {
          type: 'linear',
          display: true,
          position: 'left',
          beginAtZero: true,
          grid: {
            borderDash: [3]
          },
          title: {
            display: true,
            text: '{{ __('panel/dashboard.order_quantity') }}'
          }
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          beginAtZero: true,
          grid: {
            drawOnChartArea: false,
          },
          title: {
            display: true,
            text: '{{ __('panel/dashboard.today_revenue') }}'
          }
        }
      }
    };

    const revenueGradient = revenueCtx.getContext('2d').createLinearGradient(0, 0, 0, 320);
    revenueGradient.addColorStop(0, 'rgba(40, 167, 69, 0.3)');
    revenueGradient.addColorStop(1, 'rgba(40, 167, 69, 0)');

    new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: @json($order['latest_month']['period']),
        datasets: [
          {
            label: '{{ __('panel/dashboard.today_revenue') }}',
            data: @json($order['latest_month']['totals']),
            borderColor: '#28a745',
            backgroundColor: revenueGradient,
            fill: true,
            tension: 0.4,
            yAxisID: 'y1',
            pointRadius: 2,
            pointHoverRadius: 5
          },
          {
            label: '{{ __('panel/dashboard.today_orders') }}',
            data: @json($order['latest_month']['counts']),
            borderColor: '#3c7af7',
            backgroundColor: 'transparent',
            tension: 0.4,
            yAxisID: 'y',
            pointRadius: 2,
            pointHoverRadius: 5
          }
        ]
      },
      options: revenueOrdersOptions
    });
  }

  // Order Status Pie Chart
  const statusCtx = document.getElementById('chart-order-status');
  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: @json($order['status_dist']['labels']),
        datasets: [{
          data: @json($order['status_dist']['data']),
          backgroundColor: @json($order['status_dist']['colors']),
          borderWidth: 2,
          borderColor: '#ffffff'
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
              padding: 10,
              font: {
                size: 11
              }
            }
          }
        },
        cutout: '60%'
      }
    });
  }

  // Traffic Trends Chart
  const trafficCtx = document.getElementById('chart-traffic');
  if (trafficCtx) {
    const trafficOptions = {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          position: 'top',
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            maxTicksLimit: 10,
            callback: function(value, index, ticks) {
              const label = this.getLabelForValue(value);
              if (label) {
                const parts = label.split('-');
                return parts[1] + '-' + parts[2];
              }
              return label;
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            borderDash: [3]
          }
        }
      }
    };

    const visitLabels = @json(collect($visits['latest_month'])->pluck('date'));
    const pageViewsData = @json(collect($visits['latest_month'])->pluck('page_views'));
    const uniqueVisitorData = @json(collect($visits['latest_month'])->pluck('unique_visitors'));
    const newCustomerData = @json($customers['latest_month']['totals']);

    new Chart(trafficCtx, {
      type: 'line',
      data: {
        labels: visitLabels,
        datasets: [
          {
            label: '{{ __('panel/dashboard.page_views') }}',
            data: pageViewsData,
            borderColor: '#6f42c1',
            backgroundColor: 'transparent',
            tension: 0.4,
            pointRadius: 2,
            pointHoverRadius: 5
          },
          {
            label: '{{ __('panel/dashboard.unique_visitors') }}',
            data: uniqueVisitorData,
            borderColor: '#20c997',
            backgroundColor: 'transparent',
            tension: 0.4,
            pointRadius: 2,
            pointHoverRadius: 5
          },
          {
            label: '{{ __('panel/dashboard.new_customers') }}',
            data: newCustomerData,
            borderColor: '#fd7e14',
            backgroundColor: 'transparent',
            tension: 0.4,
            pointRadius: 2,
            pointHoverRadius: 5
          }
        ]
      },
      options: trafficOptions
    });
  }
</script>
@endpush
