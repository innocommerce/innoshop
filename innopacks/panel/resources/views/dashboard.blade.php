@extends('panel::layouts.app')
@section('body-class', 'page-home')

@section('title', __('panel/menu.dashboard'))

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')

<div class="row dashboard-top-card g-2 g-lg-4 mb-3 mb-lg-4">
  @foreach ($cards as $card)
  <div class="col-6 col-md-3">
    <div class="card dashboard-item">
      <div class="card-body">
        <a href="{{ $card['url'] }}" class="text-decoration-none">
        <div class="d-flex justify-content-between align-items-center">
          <div class="left">
            <div class="quantity text-dark">{{ $card['quantity'] }}</div>
            <span class="title text-secondary">{{ $card['title'] }}</span>
          </div>
          <div class="right"><i class="{{ $card['icon'] }} icon"></i></div>
        </div>
        </a>
      </div>
    </div>
  </div>
  @endforeach
</div>

<div class="row">
  <div class="col-12 col-md-6 mb-3">
    <div class="card">
      <div class="card-header">{{ __('panel/dashboard.order_trends') }}</div>
      <div class="card-body">
        <canvas id="chart-new-quantity"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 mb-3">
    <div class="card top-sale-products">
      <div class="card-header">{{ __('panel/dashboard.top_products') }}</div>
      <div class="card-body pb-0">
        @if ($top_sale_products)
          <table class="table table-last-no-border align-middle mt-n3 mb-0">
            <tbody>
              @foreach($top_sale_products as $product)
              <tr>
                <td class="text-center">
                  @if ($loop->iteration <= 3)
                    <img src="{{ asset('icon/grade-'. $loop->iteration .'.svg') }}" alt="{{ $product['name'] }}" class="img-fluid wh-30">
                  @else
                    <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                  @endif
                </td>
                <td>
                  <a class="d-flex align-items-center text-dark text-decoration-none" href="{{ panel_route('products.edit', $product['product_id']) }}">
                    <div class="wh-30 rounded-circle overflow-hidden border border-1 me-2"><img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="img-fluid"></div>
                    {{ $product['summary'] }}
                  </a>
                </td>
                <td class="text-center">{{ $product['order_count'] }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <x-common-no-data :width="240" />
        @endif
      </div>
    </div>
  </div>
</div>
<img src="{{ config('innoshop.api_url') }}/en/install/dashboard.jpg?version={{ config('innoshop.version') }}&build_date={{ config('innoshop.build') }}" class="d-none" alt=""/>
@endsection

@push('footer')
<script>
  const ctx1 = document.getElementById('chart-new-quantity').getContext('2d');
  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: false
    },
    interaction: {
      mode: 'index',
      intersect: false,
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: {
          drawBorder: false,
          borderDash: [3],
        },
      },
      x: {
        beginAtZero: true,
        grid: {
          drawBorder: false,
          display: false
        },
      }
    },
  };

  const orderGradient = ctx1.createLinearGradient(0, 0, 0, 380);
  orderGradient.addColorStop(0, 'rgba(76,122,247,0.5)');
  orderGradient.addColorStop(1, 'rgba(76,122,247,0)');

  const chart1 = new Chart(ctx1, {
    type: 'line',
    data: {
      labels: @json($order['latest_week']['period']),
      datasets: [{
        label: '{{ __('panel/dashboard.order_quantity') }}',
        data: @json($order['latest_week']['totals']),
        responsive: true,
        backgroundColor : orderGradient,
        borderColor : "#3c7af7",
        fill: true,
        lineTension: 0.4,
        datasetStrokeWidth: 3,
        pointBackgroundColor: '#3c7af7',
        pointDotStrokeWidth: 4,
        pointHoverBorderWidth: 8,
        tension: 0.1
      }]
    },
    options: options
  });
</script>
@endpush
