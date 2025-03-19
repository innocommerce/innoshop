<div class="tab-pane fade" id="product-faq" role="tabpanel">
  <div class="faq-container">
    <div class="accordion accordion-flush" id="accordionFlushExample">
      @foreach($faqs as $item)
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#flush-collapse{{ $loop->index }}" aria-expanded="false"
                    aria-controls="flush-collapse{{ $loop->index }}">
              {{ $item->translation->question ?? '' }}
            </button>
          </h2>
          <div id="flush-collapse{{ $loop->index }}" class="accordion-collapse collapse"
               data-bs-parent="#accordionFlushExample">
            <div class="accordion-body">
              {!! nl2br($item->translation->answer ?? '') !!}
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
