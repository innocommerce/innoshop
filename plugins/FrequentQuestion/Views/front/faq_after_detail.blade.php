<div class="tab-pane border mt-3" role="tabpanel">
  <div class="faq-container">
    <div class="accordion accordion-flush" id="accordionDetail">
      @foreach($faqs as $item)
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#flush-dcollapse{{ $loop->index }}" aria-expanded="false"
                    aria-controls="flush-dcollapse{{ $loop->index }}">
              {{ $item->translation->question ?? '' }}
            </button>
          </h2>
          <div id="flush-dcollapse{{ $loop->index }}" class="accordion-collapse collapse"
               data-bs-parent="#accordionDetail">
            <div class="accordion-body">
              {!! nl2br($item->translation->answer ?? '') !!}
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
