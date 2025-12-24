@if(!empty($content) || request('design'))
  <section class="module-line">
    <div class="module-rich-text">
      <div class="{{ $content['width_class'] ?? pb_get_width_class('wide') }}">
        @include('PageBuilder::front.partials.module-title', [
          'title' => $content['title'] ?? '',
          'subtitle' => $content['subtitle'] ?? '',
        ])

        @if(!empty($content['content']))
          <div class="rich-text-content">
            {!! $content['content'] !!}
          </div>
        @elseif(request('design'))
          @include('PageBuilder::front.partials.module-empty', [
            'moduleClass' => 'rich-text',
            'icon' => 'bi-file-text',
            'message' => __('PageBuilder::modules.no_content'),
          ])
        @endif
      </div>
    </div>
  </section>
@endif
