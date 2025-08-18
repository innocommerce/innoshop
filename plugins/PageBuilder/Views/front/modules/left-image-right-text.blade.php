@php
  $imagePosition = $content['image_position'] ?? 'left';
  $title = is_array($content['title'] ?? '') ? ($content['title'][front_locale_code()] ?? '') : ($content['title'] ?? '');
  $subtitle = is_array($content['subtitle'] ?? '') ? ($content['subtitle'][front_locale_code()] ?? '') : ($content['subtitle'] ?? '');
  $desc = is_array($content['description'] ?? '') ? ($content['description'][front_locale_code()] ?? '') : ($content['description'] ?? '');
  $img = is_array($content['image'] ?? '') ? ($content['image'][front_locale_code()] ?? '') : ($content['image'] ?? '');
  $btnText = is_array($content['button_text'] ?? '') ? ($content['button_text'][front_locale_code()] ?? '') : ($content['button_text'] ?? '');
  $btnLink = $content['link']['value'] ?? 'javascript:void(0)';
  $btnLinkType = $content['link']['type'] ?? 'custom';
  $btnNewWindow = $content['link']['new_window'] ?? false;
  $target = $btnNewWindow ? '_blank' : '_self';
  $textAlign = $content['text_align'] ?? 'left';
  $titleSpacing = $content['title_spacing'] ?? 20;
  $subtitleSpacing = $content['subtitle_spacing'] ?? 15;
  $descriptionSpacing = $content['description_spacing'] ?? 20;
  $contentMarginLeft = $content['content_margin_left'] ?? 0;
  $contentMarginRight = $content['content_margin_right'] ?? 0;
  $contentMarginTop = $content['content_margin_top'] ?? 0;
  $contentMarginBottom = $content['content_margin_bottom'] ?? 0;
  $imagePaddingX = $content['image_padding_x'] ?? 0;
  $imagePaddingY = $content['image_padding_y'] ?? 0;

  // Handle link based on type
  if ($btnLinkType !== 'custom' && $btnLink) {
      $btnLink = '/' . $btnLinkType . '/' . $btnLink;
  }
@endphp
<section class="module-line py-5">
  <div class="{{ $content['width_class'] ?? 'container' }}">
    <div class="row align-items-center">
      @if ($imagePosition === 'left')
        <div class="col-md-6 mb-4 mb-md-0" style="padding: {{ $imagePaddingY }}px {{ $imagePaddingX }}px">
          <img src="{{ $img }}" class="w-100 rounded-3 object-fit-cover">
        </div>
        <div class="col-md-6">
          <div class="text-content text-{{ $textAlign }}"
            style="
            margin-left: {{ $contentMarginLeft }}px;
            margin-right: {{ $contentMarginRight }}px;
            margin-top: {{ $contentMarginTop }}px;
            margin-bottom: {{ $contentMarginBottom }}px;
          ">
            @if ($title)
              <div class="h2 fw-bold" style="margin-bottom: {{ $titleSpacing }}px">{{ $title }}</div>
            @endif
            @if ($subtitle)
              <div class="h5 text-muted" style="margin-bottom: {{ $subtitleSpacing }}px">{{ $subtitle }}</div>
            @endif
            @if ($desc)
              <div style="margin-bottom: {{ $descriptionSpacing }}px">{{ $desc }}</div>
            @endif
            @if ($btnText)
              <div>
                <a href="{{ $btnLink }}" target="{{ $target }}"
                  class="btn btn-danger px-4 py-2">{{ $btnText }}</a>
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="col-md-6 order-md-2 mb-4 mb-md-0" style="padding: {{ $imagePaddingY }}px {{ $imagePaddingX }}px">
          <img src="{{ $img }}" class="w-100 rounded-3 object-fit-cover" alt="">
        </div>
        <div class="col-md-6 order-md-1">
          <div class="text-content text-{{ $textAlign }}"
            style="
            margin-left: {{ $contentMarginLeft }}px;
            margin-right: {{ $contentMarginRight }}px;
            margin-top: {{ $contentMarginTop }}px;
            margin-bottom: {{ $contentMarginBottom }}px;
          ">
            @if ($title)
              <div class="h2 fw-bold" style="margin-bottom: {{ $titleSpacing }}px">{{ $title }}</div>
            @endif
            @if ($subtitle)
              <div class="h5 text-muted" style="margin-bottom: {{ $subtitleSpacing }}px">{{ $subtitle }}</div>
            @endif
            @if ($desc)
              <div style="margin-bottom: {{ $descriptionSpacing }}px">{{ $desc }}</div>
            @endif
            @if ($btnText)
              <div>
                <a href="{{ $btnLink }}" target="{{ $target }}"
                  class="btn btn-danger px-4 py-2">{{ $btnText }}</a>
              </div>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</section>
