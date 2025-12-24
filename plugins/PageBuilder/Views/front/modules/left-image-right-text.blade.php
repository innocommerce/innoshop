@php
  $imagePosition = $content['image_position'] ?? 'left';
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
  $hasImage = !empty($content['image']);

  // Handle link based on type
  if ($btnLinkType !== 'custom' && $btnLink) {
      $btnLink = '/' . $btnLinkType . '/' . $btnLink;
  }
@endphp

@if($hasImage || request('design'))
<section class="module-line py-5">
  <div class="{{ $content['width_class'] ?? 'container' }}">
    @if($hasImage)
    <div class="row align-items-center">
      @if ($imagePosition === 'left')
        <div class="col-md-6 mb-4 mb-md-0" style="padding: {{ $imagePaddingY }}px {{ $imagePaddingX }}px">
          <img src="{{ $content['image'] ?? '' }}" class="w-100 rounded-3 object-fit-cover">
        </div>
        <div class="col-md-6">
          <div class="text-content text-{{ $textAlign }}"
            style="
            margin-left: {{ $contentMarginLeft }}px;
            margin-right: {{ $contentMarginRight }}px;
            margin-top: {{ $contentMarginTop }}px;
            margin-bottom: {{ $contentMarginBottom }}px;
          ">
            @if (!empty($content['title']))
              <div class="h2 fw-bold" style="margin-bottom: {{ $titleSpacing }}px">{{ $content['title'] }}</div>
            @endif
            @if (!empty($content['subtitle']))
              <div class="h5 text-muted" style="margin-bottom: {{ $subtitleSpacing }}px">{{ $content['subtitle'] }}</div>
            @endif
            @if (!empty($content['description']))
              <div style="margin-bottom: {{ $descriptionSpacing }}px">{{ $content['description'] }}</div>
            @endif
            @if (!empty($content['button_text']))
              <div>
                <a href="{{ $btnLink }}" target="{{ $target }}"
                  class="btn btn-danger px-4 py-2">{{ $content['button_text'] }}</a>
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="col-md-6 order-md-2 mb-4 mb-md-0" style="padding: {{ $imagePaddingY }}px {{ $imagePaddingX }}px">
          <img src="{{ $content['image'] ?? '' }}" class="w-100 rounded-3 object-fit-cover" alt="">
        </div>
        <div class="col-md-6 order-md-1">
          <div class="text-content text-{{ $textAlign }}"
            style="
            margin-left: {{ $contentMarginLeft }}px;
            margin-right: {{ $contentMarginRight }}px;
            margin-top: {{ $contentMarginTop }}px;
            margin-bottom: {{ $contentMarginBottom }}px;
          ">
            @if (!empty($content['title']))
              <div class="h2 fw-bold" style="margin-bottom: {{ $titleSpacing }}px">{{ $content['title'] }}</div>
            @endif
            @if (!empty($content['subtitle']))
              <div class="h5 text-muted" style="margin-bottom: {{ $subtitleSpacing }}px">{{ $content['subtitle'] }}</div>
            @endif
            @if (!empty($content['description']))
              <div style="margin-bottom: {{ $descriptionSpacing }}px">{{ $content['description'] }}</div>
            @endif
            @if (!empty($content['button_text']))
              <div>
                <a href="{{ $btnLink }}" target="{{ $target }}"
                  class="btn btn-danger px-4 py-2">{{ $content['button_text'] }}</a>
              </div>
            @endif
          </div>
        </div>
      @endif
    </div>
    @elseif(request('design'))
      @include('PageBuilder::front.partials.module-title', [
          'title' => $content['title'] ?? '',
          'subtitle' => $content['subtitle'] ?? '',
      ])
      @include('PageBuilder::front.partials.module-empty', [
          'moduleClass' => 'left-image-right-text',
          'icon' => 'bi-image',
          'message' => __('PageBuilder::modules.no_image'),
      ])
    @endif
  </div>
</section>
@endif
