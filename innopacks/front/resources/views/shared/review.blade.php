<form action="{{ account_route('reviews.store') }}" method="POST">
  <div class="modal-body mb-2">
    @csrf
    @if (isset($order))
      <input type="hidden" name="order_number" value="">
      <input type="hidden" name="order_item_id" value="">
      <input type="hidden" name="product_sku" value="">
    @else
      <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
    @endif
    <div>
      <div class="review-content">
        @if (isset($order))
          <div class="mb-3">
            <table class="table table-bordered table-striped table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_number') }}</th>
                <th>{{ __('front/order.product_image') }}</th>
                <th>{{ __('front/order.product_name') }}</th>
                <th>{{ __('front/order.product_spec') }}</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td data-title="Order number" class="align-items-center" id='order_number'></td>
                <td data-title="product-image">
                  <img class="product-image wh-30 justify-content-center align-items-center" id="product-image"
                       src="" class="img-fluid wh-20">
                </td>
                <td data-title="product-name" class="name align-items-center" id="name"></td>
                <td data-title="product-label" class="label mt-2 text-secondary" id="label"></td>
              </tr>
              </tbody>
            </table>
          </div>
        @endif
        <div class="row">
          <label class="col-8 text-left font-size-25 mb-0" for="review">
            <h5>{{ __('front/product.input_your_review') }}</h5>
          </label>
          <div class="rating col-4 text-end">
            <input type="radio" name="rating" value="5" id="5" checked>
            <label for="5">☆</label>
            <input type="radio" name="rating" value="4" id="4">
            <label for="4">☆</label>
            <input type="radio" name="rating" value="3" id="3">
            <label for="3">☆</label>
            <input type="radio" name="rating" value="2" id="2">
            <label for="2">☆</label>
            <input type="radio" name="rating" value="1" id="1">
            <label for="1">☆</label>
          </div>
        </div>
        <textarea class="form-control" name="content" id="review" rows="5"
                  placeholder="{{ __('front/product.input_some_text_here') }}..."></textarea>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <div class="col-12 text-end">
      <button type="submit" class="btn btn-primary submit_review">{{ __('front/product.submit_review') }}</button>
    </div>
  </div>
</form>
