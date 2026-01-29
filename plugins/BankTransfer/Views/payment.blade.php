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

    {{-- Receipt Upload Section --}}
    <div class="mt-4">
      <div class="fs-6 mb-2">{{ __('BankTransfer::common.upload_receipt') }}</div>
      @if($order->payments->count() > 0 && $order->payments->first()->certificate)
        <div class="alert alert-success">
          <i class="bi bi-check-circle-fill me-2"></i>
          {{ __('BankTransfer::common.receipt_uploaded_success') }}
          <a href="{{ asset($order->payments->first()->certificate) }}" target="_blank" class="ms-2">
            <i class="bi bi-eye"></i> {{ __('BankTransfer::common.view_receipt') }}
          </a>
        </div>
      @else
        <div class="mb-3">
          <label class="form-label">{{ __('BankTransfer::common.select_receipt') }}</label>
          <input type="file" id="receipt-file" class="form-control" accept="image/*">
          <div class="form-text">{{ __('BankTransfer::common.receipt_tip') }}</div>
        </div>
        <button type="button" id="btn-upload-receipt" class="btn btn-success me-2">
          <i class="bi bi-upload me-1"></i>{{ __('BankTransfer::common.upload_receipt_btn') }}
        </button>
      @endif
    </div>

    <div class="mt-4">
      <button type="button" class="btn btn-primary btn-bank-transfer">{{ __('front/common.confirm') }}</button>
    </div>
  </div>
</div>

<script>
  // Upload receipt functionality
  $('#btn-upload-receipt').click(function () {
    const fileInput = document.getElementById('receipt-file');
    const file = fileInput.files[0];

    if (!file) {
      alert('{{ __('BankTransfer::common.please_select_file') }}');
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      alert('{{ __('BankTransfer::common.file_too_large') }}');
      return;
    }

    const formData = new FormData();
    formData.append('receipt', file);

    const $btn = $(this);
    $btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat me-1 spin"></i>{{ __("BankTransfer::common.uploading") }}');

    $.ajax({
      url: '{{ route('api.orders.receipt_upload', $order->number) }}',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      success: function(response) {
        location.reload();
      },
      error: function(xhr) {
        const msg = xhr.responseJSON?.message || '{{ __("BankTransfer::common.upload_failed") }}';
        alert(msg);
        $btn.prop('disabled', false).html('<i class="bi bi-upload me-1"></i>{{ __("BankTransfer::common.upload_receipt_btn") }}');
      }
    });
  });

  $('.btn-bank-transfer').click(function () {
    location.href = '{{ front_route('payment.success') }}?order_number={{ $order->number }}'
  });
</script>

<style>
  .spin {
    animation: spin 1s linear infinite;
  }
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
</style>
