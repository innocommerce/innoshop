@if (!empty($title))
  <div class="module-title-wrap text-center">
    <div class="module-title">{{ $title }}</div>
    @if (!empty($subtitle))
      <div class="module-sub-title">{{ $subtitle }}</div>
    @endif
  </div>
@endif

