<div class="tab-pane fade" id="distribution-tab-pane" role="tabpanel" tabindex="0">
  @include('Distribution::panel.components.distribution-cards')

  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-members" type="button" role="tab">{{ __('Distribution::common.recommend_customer') }}</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-orders" type="button" role="tab">{{ __('Distribution::common.commission_amount') }}</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-commissions" type="button" role="tab">{{ __('Distribution::common.order_number') }}</button>
    </li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tab-members" role="tabpanel">
      @if ($members->count())
      <div class="col-12 col-lg-9">
        <div class="account-card-box account-info">
          <div class="order-info-body">
            <table class="table">
              <thead>
                <tr>
                  <th>{{ __('Distribution::common.name') }}</th>
                  <th>{{ __('Distribution::common.email') }}</th>
                  <th>{{ __('Distribution::common.created_at') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($members as $member)
                <tr>
                  <td>{{ $member->name }}</td>
                  <td>{{ $member->email }}</td>
                  <td>{{ $member->created_at }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @else
      <x-common-no-data />
      @endif
    </div>

    <div class="tab-pane fade" id="tab-orders" role="tabpanel">
      @if ($orders->count())
      <div class="order-info-body">
        <table class="table">
          <thead>
            <tr>
              <th>{{ __('Distribution::common.order_number') }}</th>
              <th>{{ __('Distribution::common.total_amount') }}</th>
              <th>{{ __('Distribution::common.status') }}</th>
              <th>{{ __('Distribution::common.created_at') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)
            <tr>
              <td>{{ $order->number }}</td>
              <td>{{ $order->email }}</td>
              <td>{{ $order->total_format }}</td>
              <td>{{ $order->status_format }}</td>
              <td>{{ $order->created_at }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <x-common-no-data />
      @endif
    </div>

    <div class="tab-pane fade" id="tab-commissions" role="tabpanel">
      @if ($commissions->count())
      <div class="order-info-body">
        <table class="table">
          <thead>
            <tr>
              <th>{{ __('Distribution::common.order_number') }}</th>
              <th>{{ __('Distribution::common.commission_amount') }}</th>
              <th>{{ __('Distribution::common.recommend_customer') }}</th>
              <th>{{ __('Distribution::common.status') }}</th>
              <th>{{ __('Distribution::common.created_at') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($commissions as $commission)
            <tr>
              <td>{{ $commission->order->number }}</td>
              <td>{{ currency_format($commission->commission_amount) }}</td>
              <td>{{ $commission->customer->name}}</td>
              <td>{{ $commission->status}}</td>
              <td>{{ $commission->created_at }}</td>
              <td></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <x-common-no-data />
      @endif
    </div>
  </div>
</div>