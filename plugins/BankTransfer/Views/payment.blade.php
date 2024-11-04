<div class="bank-transfer card w-max-700 m-auto h-min-300">
  <div class="card-body">
    <div class="fs-5 mb-3">{{ __('BankTransfer::common.order_success') }}</div>
    <table class="table mb-3 table-bordered">
      <thead>
      <tr>
        <th>{{ __('BankTransfer::common.number') }}</th>
        <th>{{ __('BankTransfer::common.order_time') }}</th>
        <th>{{ __('BankTransfer::common.total') }}</th>
        <th>{{ __('BankTransfer::common.payment_method') }}</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td>{{ $order->number }}</td>
        <td>{{ $order->created_at->format('Y-m-d') }}</td>
        <td>{{ currency_format($order->total) }}</td>
        <td>{{ __('BankTransfer::common.bank_transfer') }}</td>
      </tr>
      </tbody>
    </table>
    <div class="fs-5 mb-3">{{ __('BankTransfer::common.bank_info') }}</div>
    <div>
      <p>{{ plugin_setting('bank_transfer.bank_name') }}</p>
      <p>{{ plugin_setting('bank_transfer.bank_account') }}</p>
      <p>{{ plugin_setting('bank_transfer.bank_comment') }}</p>
    </div>
    <button type="button" class="btn btn-primary btn-bank-transfer">{{ __('front/common.confirm') }}</button>
  </div>
</div>

<script>
  $('.btn-bank-transfer').click(function () {
    location.href = '{{ front_route('checkout.success') }}?order_number={{ $order->number }}'
  });
</script>