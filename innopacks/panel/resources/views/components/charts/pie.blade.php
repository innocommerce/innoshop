<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center" style="height: 4rem;">
    <span>{{ $title }}</span>
    @if($id === 'customer_source_distribution')
    <div class="btn-group">
      <button class="btn btn-sm btn-primary active" onclick="switchSource{{ $id }}('pc_web')" id="source_btn_{{ $id }}_pc_web">PC网页</button>
      <button class="btn btn-sm btn-success" onclick="switchSource{{ $id }}('mobile_web')" id="source_btn_{{ $id }}_mobile_web">手机网页</button>
      <button class="btn btn-sm btn-info" onclick="switchSource{{ $id }}('miniapp')" id="source_btn_{{ $id }}_miniapp">小程序</button>
      <button class="btn btn-sm btn-warning" onclick="switchSource{{ $id }}('wechat_official')" id="source_btn_{{ $id }}_wechat_official">微信公众号</button>
      <button class="btn btn-sm btn-danger" onclick="switchSource{{ $id }}('app')" id="source_btn_{{ $id }}_app">APP应用</button>
    </div>
    @endif
  </div>
  <div class="card-body">
    <canvas id="{{ $id }}"></canvas>
  </div>
</div>

<script>
  const ctx{{ $id }} = document.getElementById('{{ $id }}').getContext('2d');
  const colors{{ $id }} = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
  
  @if($id === 'customer_source_distribution')
  function switchSource{{ $id }}(sourceType) {
    const distributionData = @json($items ?? []);
    if (distributionData[sourceType]) {
      // 更新图表数据
      const chart = Chart.getChart('{{ $id }}');
      if (chart) {
        chart.data.labels = distributionData[sourceType].labels;
        chart.data.datasets[0].data = distributionData[sourceType].data;
        chart.data.datasets[0].backgroundColor = colors{{ $id }}.slice(0, distributionData[sourceType].labels.length);
        chart.update();
      }

      // 更新按钮状态
      document.querySelectorAll('[id^="source_btn_{{ $id }}_"]').forEach(btn => {
        btn.classList.remove('active');
      });
      document.getElementById('source_btn_{{ $id }}_' + sourceType).classList.add('active');
    }
  }

  // 初始化时触发第一个来源的数据
  document.addEventListener('DOMContentLoaded', function() {
    switchSource{{ $id }}('pc_web');
  });
  @endif

  // 删除数据
  function removeData{{ $id }}() {
    const chart = Chart.getChart('{{ $id }}');
    if (chart) {
      const data = chart.data;
      if (data.labels.length > 1) {  // 保持至少一个数据点
        data.labels.pop();
        data.datasets[0].data.pop();
        data.datasets[0].backgroundColor.pop();
        chart.update();
      }
    }
  }

  const options{{ $id }} = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          padding: 20,
          usePointStyle: true,
          pointStyle: 'circle'
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            const label = context.label || '';
            const value = context.parsed;
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            const percentage = ((value / total) * 100).toFixed(1);
            return `${label}: ${value} (${percentage}%)`;
          }
        }
      }
    }
  };

  const chart{{ $id }} = new Chart(ctx{{ $id }}, {
    type: 'pie',
    data: {
      labels: @json($labels),
      datasets: [{
        data: @json($items),
        backgroundColor: colors{{ $id }}.slice(0, @json($labels).length),
        borderColor: '#fff',
        borderWidth: 2,
        hoverBorderWidth: 3
      }]
    },
    options: options{{ $id }}
  });
</script> 