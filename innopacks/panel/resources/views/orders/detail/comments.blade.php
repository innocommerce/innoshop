{{-- Comments Section --}}
<div class="mt-4">
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('front/checkout.order_comment') }}</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12 col-md-6 mb-4">
          <h6 class="fs-5">{{ __('panel/order.customer_remarks') }}</h6>
          <p class="mb-0">{{ $order->comment }}</p>
        </div>
        <div class="col-12 col-md-6 mb-3">
          <h6 class="fs-5">{{ __('panel/order.administrator_remarks') }}</h6>
          <p class="mb-0">{{ $order->admin_note }}</p>
          <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#admin_note">
            {{ __('panel/common.edit') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Admin Note Modal --}}
<div class="modal fade" id="admin_note" tabindex="-1" aria-labelledby="admin_noteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border border-secondary rounded">
      <div class="modal-header">
        <h4 class="modal-title" id="admin_noteLabel">{{ __('panel/order.administrator_remarks') }}</h4>
      </div>
      <div class="modal-body">
        <textarea class="form-control admin-comment-input" rows="5" data-order-id="{{ $order->id }}">{{ $order->admin_note }}</textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default"
          data-bs-dismiss="modal">{{ __('panel/order.close') }}</button>
        <button type="button" class="btn btn-primary"
          onclick="submitComment()">{{ __('panel/order.submit') }}</button>
      </div>
    </div>
  </div>
</div>

@hookinsert('panel.orders.detail.comment.after')
