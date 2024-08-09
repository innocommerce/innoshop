<div class="card mb-3">
  <div class="card-header">{{ $title }}</div>
  <div class="card-body">
    <canvas id="{{ $id }}"></canvas>
  </div>
</div>

<script>
  const ctx{{ $id }} = document.getElementById('{{ $id }}').getContext('2d');
  const options{{ $id }} = {
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

  const orderGradient{{ $id }} = ctx{{ $id }}.createLinearGradient(0, 0, 0, 380);
  orderGradient{{ $id }}.addColorStop(0, 'rgba(76,122,247,0.5)');
  orderGradient{{ $id }}.addColorStop(1, 'rgba(76,122,247,0)');

  const chart{{ $id }} = new Chart(ctx{{ $id }}, {
    type: 'line',
    data: {
      labels: @json($labels),
      datasets: [{
        label: '{{ $title }}',
        data: @json($items),
        responsive: true,
        backgroundColor : orderGradient{{ $id }},
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
    options: options{{ $id }}
  });
</script>