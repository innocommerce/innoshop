@if (!empty($content['data']))
  <section class="module-line">
    <div class="module-rich-text">
      <div class="container">
        @if (!empty($content['title']))
          <div class="module-title-wrap">
            <div class="module-title">{{ $content['title'] }}</div>
          </div>
        @endif

        <div class="rich-text-content">
          {!! $content['data'] !!}
        </div>
      </div>
    </div>
  </section>
@endif
