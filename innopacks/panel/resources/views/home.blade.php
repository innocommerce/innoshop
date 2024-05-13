@extends('panel::layouts.app')
@section('body-class', 'page-home')

@push('header')
<script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')

<div class="mb-4 mt-n3">
  <div class="card dashboard-top-card">
    <div class="card-body">
      <div class="row">
        @foreach ($cards as $card)
        <div class="col-6 col-md-3">
          <div class="card dashboard-item">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div class="left">
                  <div class="quantity">{{ $card['quantity'] }}</div>
                  <span class="title">{{ $card['title'] }}</span>
                </div>
                <div class="right"><i class="{{ $card['icon'] }} icon"></i></div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header">文章发布数量</div>
      <div class="card-body">
        <canvas id="chart-new-quantity" height="380"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header">文章浏览量</div>
      <div class="card-body">
        <canvas id="chart-new-view" height="380"></canvas>
      </div>
    </div>
  </div>
</div>
@endsection

@push('footer')
<script>
  const ctx1 = document.getElementById('chart-new-quantity').getContext('2d');
  const ctx2 = document.getElementById('chart-new-view').getContext('2d');
  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: false // Hide legend
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
      labels: @json($article['latest_week']['period']),
      datasets: [{
        label: '发布数量',
        data: @json($article['latest_week']['totals']),
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

  const chart2 = new Chart(ctx2, {
    type: 'pie',
    data: {
      labels: @json($article['top_viewed']['period']),
      datasets: [{
        label: '浏览量',
        data: @json($article['top_viewed']['totals']),
        backgroundColor: [
          'rgba(255, 99, 132, 0.8)',
          'rgba(54, 162, 235, 0.8)',
          'rgba(255, 206, 86, 0.8)',
          'rgba(75, 192, 192, 0.8)',
          'rgba(153, 102, 255, 0.8)',
          'rgba(255, 159, 64, 0.8)',
          'rgba(255, 99, 132, 0.8)',
          'rgba(54, 162, 235, 0.8)',
          'rgba(255, 206, 86, 0.8)',
          'rgba(75, 192, 192, 0.8)',
          'rgba(153, 102, 255, 0.8)',
          'rgba(255, 159, 64, 0.8)',
          ],
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
    },
  });
</script>
@endpush
