
<div class="row dashboard-top-card g-2 g-lg-4 mb-3 mb-lg-4">
  <div class="col-6 col-md-3">
    <div class="card dashboard-item">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div class="left">
            <div class="quantity text-dark">{{ $report['member_total'] }}</div>
            <span class="title text-secondary">{{ __('Distribution::common.recommend_customer') }}</span>
          </div>
          <div class="right"><i class="bi bi-people"></i></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card dashboard-item">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div class="left">
            <div class="quantity text-dark">{{ $report['commission_amount'] }}</div>
            <span class="title text-secondary">{{ __('Distribution::common.commission_amount') }}</span>
          </div>
          <div class="right"><i class="bi bi-wallet2"></i></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card dashboard-item">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div class="left">
            <div class="quantity text-dark">{{ $report['order_total'] }}</div>
            <span class="title text-secondary">{{ __('Distribution::common.recommend_order_number') }}</span>
          </div>
          <div class="right"><i class="bi bi-bag"></i></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card dashboard-item">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div class="left">
            <div class="quantity text-dark">{{ $report['order_amount'] }}</div>
            <span class="title text-secondary">{{ __('Distribution::common.commission_total') }}</span>
          </div>
          <div class="right"><i class="bi bi-currency-dollar"></i></div>
        </div>
      </div>
    </div>
  </div>
</div>