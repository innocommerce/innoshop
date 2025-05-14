@if(current_customer() && !system_setting('bought_review'))

  @if(!$reviewed)
    <form action="{{ account_route('reviews.store') }}" method="post">
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">
      <div>
        <div class="review-content">
          <div class="row">
            <label class="col-8 text-left font-size-25 mb-0" for="review">
              <h5>{{ __('front/product.input_your_review')}}</h5>
            </label>

            <div class="rating col-4 text-end">
              <input type="radio" name="rating" value="5" id="5">
              <label for="5">☆</label>
              <input type="radio" name="rating" value="4" id="4">
              <label for="4">☆</label>
              <input type="radio" name="rating" value="3" id="3" checked>
              <label for="3">☆</label>
              <input type="radio" name="rating" value="2" id="2">
              <label for="2">☆</label>
              <input type="radio" name="rating" value="1" id="1">
              <label for="1">☆</label>
            </div>
          </div>

          <textarea class="form-control" name="content" id="review" rows="5"
                    placeholder="{{ __('front/product.input_some_text_here')}}..."></textarea>
        </div>
      </div>
      <div class="col-12 text-end mt-3">
        <button class="btn btn-primary submit_review">{{ __('front/product.submit_review')}}</button>
      </div>
    </form>
  @else
    <div class="m-5 text-center">
      <button class="btn btn-primary">{{ __('front/product.have_reviewed') }}</button>
    </div>
  @endif

@else
  <div class="m-5 text-center">
    @if(!current_customer())
      <a class="btn btn-primary" href="javascript:inno.openLogin()">{{ __('front/product.please_login_first') }}</a>
    @else
      <a class="btn btn-primary" href="{{ account_route('orders.index') }}"
         target="_blank">{{ __('front/product.visit_order_to_review') }}</a>
    @endif
  </div>
@endif

<div class="review-list-container">
  @include('products._review_list', ['reviews' => $reviews])
</div>

@if($reviews->hasMorePages())
  <div class="text-center mt-3">
    <button class="btn btn-outline-primary load-more-reviews" data-page="2" data-product-id="{{ $product->id }}">
      {{ __('front/common.load_more') }}
    </button>
  </div>
@endif

@push('footer')
<script>
$(document).ready(function() {
  $('.load-more-reviews').on('click', function() {
    const button = $(this);
    const page = button.data('page');
    const productId = button.data('product-id');
    
    button.prop('disabled', true).html('<i class="bi bi-arrow-repeat spin"></i> {{ __("front/common.loading") }}');
    
    axios.get(`{{ front_route('products.reviews', ['product' => $product->id]) }}`, {
      params: {
        page: page
      }
    })
    .then(function(response) {
      if (response.success) {
        $('.review-list-container').append(response.data.html);
        
        if (response.data.has_more) {
          button.data('page', page + 1).prop('disabled', false).text('{{ __("front/product.load_more") }}');
        } else {
          button.remove();
        }
      }
    })
    .catch(function(error) {
      console.error('加载评论失败:', error);
      button.prop('disabled', false).text('{{ __("front/product.load_more") }}');
      inno.msg('{{ __("front/product.load_failed") }}');
    });
  });
});
</script>
@endpush
