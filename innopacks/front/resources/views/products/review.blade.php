@if(current_customer() && !system_setting('bought_review'))
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
    @if(!current_customer())
      <a class="btn btn-primary" href="javascript:inno.openLogin()">{{ __('front/product.please_login_first') }}</a>
    @else
      <a class="btn btn-primary" href="{{ account_route('orders.index') }}" target="_blank">{{ __('front/product.visit_order_to_review') }}</a>
    @endif
  </div>
@endif

@foreach($reviews as $review)
  <br/>
  <hr/>
  <div class="review-list row">
    <div class="row">
      <h5 class="col-2 mb-3">{{ $review['customer_name'] }}</h5>
      <span class="col-4 text-left"><x-front-review :rating="$review['rating']"/></span>
      <span class="col-6 text-end date">{{ $review['created_at'] }}</span>
    </div>
    <p class="mb-3">{{ $review['content'] }}</p>
  </div>
@endforeach