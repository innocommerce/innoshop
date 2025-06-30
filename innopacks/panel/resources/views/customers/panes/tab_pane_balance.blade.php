<div class="tab-pane fade" id="balance-tab-pane" role="tabpanel" tabindex="0">
  <div class="mb-3 fs-5">
    {{ __('panel/transaction.balance') }}：<span class="fw-bold text-success">{{ currency_format($customer->balance) }}</span>
  </div>
  <div class="card">
    <div class="card-header">
      <strong>{{ __('panel/menu.transactions') }}</strong> 
    </div>
    <div class="card-body p-0">
      @if($transactions->count())
        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <thead>
              <tr>
                <th>{{ __('panel/transaction.amount') }}</th>
                <th>{{ __('panel/transaction.type') }}</th>
                <th>{{ __('panel/transaction.comment') }}</th>
                <th>{{ __('panel/common.created_at') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($transactions as $item)
                <tr>
                  <td>{{ currency_format($item->amount) }}</td>
                  <td>{{ $item->type_format }}</td>
                  <td>{{ $item->comment }}</td>
                  <td>{{ $item->created_at }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="">
          {{ $transactions->appends(['tab' => 'balance'])->links('panel::vendor/pagination/bootstrap-4') }}
        </div>
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
</div> 