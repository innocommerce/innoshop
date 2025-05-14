@foreach($reviews as $review)
    <div class="review-item">
      <br/>
      <hr/>
      <div class="review-list row">
        <div class="row">
          <h5 class="col-2 mb-3">{{ $review->customer->name }}</h5>
          <span class="col-4 text-left"><x-front-review :rating="$review->rating"/></span>
          <span class="col-6 text-end date">{{ $review->created_at }}</span>
        </div>
        <p class="mb-3">{{ $review['content'] }}</p>
      </div>
    </div>
  @endforeach