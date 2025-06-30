{{-- Payment Information Section --}}
@if($order->payments->count() > 0)
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/order.payment_info') }}</h5>
    </div>
    <div class="card-body">
      <table class="table table-response align-middle">
        <thead>
        <tr>
          <th>{{ __('panel/order.payment_id') }}</th>
          <th>{{ __('panel/order.charge_id') }}</th>
          <th>{{ __('panel/order.payment_amount') }}</th>
          <th>{{ __('panel/order.handling_fee') }}</th>
          <th>{{ __('panel/order.payment_status') }}</th>
          <th>{{ __('panel/order.payment_time') }}</th>
          <th>{{ __('panel/order.payment_certificate') }}</th>
          <th>{{ __('panel/order.payment_details') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($order->payments as $payment)
          <tr>
            <td data-title="{{ __('panel/order.payment_id') }}">{{ $payment->id }}</td>
            <td data-title="{{ __('panel/order.charge_id') }}">{{ $payment->charge_id }}</td>
            <td data-title="{{ __('panel/order.payment_amount') }}">{{ $payment->amount_format }}</td>
            <td data-title="{{ __('panel/order.handling_fee') }}">{{ $payment->handling_fee_format }}</td>
            <td data-title="{{ __('panel/order.payment_status') }}">
              @include('panel::shared.list_switch', [
                'value' => $payment->paid,
                'url' => panel_route('payments.active', $payment->id)
              ])
            </td>
            <td data-title="{{ __('panel/order.payment_time') }}">{{ $payment->created_at }}</td>
            <td data-title="{{ __('panel/order.payment_certificate') }}">
              @if($payment->certificate)
                <div class="certificate-preview">
                  <img src="{{ asset($payment->certificate) }}"
                       alt="{{ __('panel/order.payment_certificate') }}"
                       class="img-thumbnail certificate-thumb"
                       style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                       onclick="showCertificateModal('{{ asset($payment->certificate) }}', '{{ $payment->charge_id }}')"
                       title="{{ __('panel/order.click_to_enlarge') }}">
                </div>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td data-title="{{ __('panel/order.payment_details') }}">
              @if($payment->reference)
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#paymentReferenceModal-{{ $payment->id }}">
                  <i class="bi bi-eye me-1"></i>{{ __('panel/order.view_details') }}
                </button>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- Payment Reference Modals --}}
  @foreach ($order->payments as $payment)
    @if($payment->reference)
      <div class="modal fade" id="paymentReferenceModal-{{ $payment->id }}" tabindex="-1"
           aria-labelledby="paymentReferenceModalLabel-{{ $payment->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="paymentReferenceModalLabel-{{ $payment->id }}">
                {{ __('panel/order.payment_details') }} - {{ $payment->id }}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="card bg-light">
                <div class="card-body">
                  @if(is_array($payment->reference))
                    <pre
                      class="mb-0 small"><code>{{ json_encode($payment->reference, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                  @else
                    <pre class="mb-0 small"><code>{{ $payment->reference }}</code></pre>
                  @endif
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary"
                      data-bs-dismiss="modal">{{ __('panel/order.close') }}</button>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endforeach

  {{-- Certificate Image Modal --}}
  <div class="modal fade" id="certificateModal" tabindex="-1" aria-labelledby="certificateModalLabel"
       aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="certificateModalLabel">{{ __('panel/order.payment_certificate') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img id="certificateImage" src="" alt="{{ __('panel/order.payment_certificate') }}" class="img-fluid"
               style="max-height: 70vh;">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/order.close') }}</button>
          <a id="certificateDownload" href="" download class="btn btn-primary">
            <i class="bi bi-download me-1"></i>{{ __('panel/order.download') }}
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Payment Related JavaScript Functions --}}
  @push('footer')
    <script>
      // Show certificate modal function
      function showCertificateModal(imageUrl, chargeId) {
        const modalTitle = '{{ __('panel/order.payment_certificate') }}' + (chargeId ? ' - ' + chargeId : '');
        
        $('#certificateModalLabel').text(modalTitle);
        $('#certificateImage').attr('src', imageUrl);
        $('#certificateDownload').attr('href', imageUrl);
        
        $('#certificateModal').modal('show');
      }
    </script>
  @endpush
@endif

@hookinsert('panel.orders.detail.payment_info.after')
